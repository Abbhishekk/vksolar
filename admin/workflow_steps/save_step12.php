<?php
// admin/workflow_steps/save_step12.php
session_start();
ini_set('display_errors',1);
error_reporting(E_ALL);

require_once __DIR__ . '/../connect/db.php'; // $conn (mysqli)

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo "POST only";
    exit;
}

function clean($v){ return trim($v); }

// upload config
$MAX_FILE_BYTES = 5 * 1024 * 1024;
$ALLOWED_MIMES = ['image/jpeg','image/jpg','image/png','image/webp'];
$PUBLIC_UPLOAD_DIR = '/admin/uploads/client_documents/';
$FS_UPLOAD_DIR = __DIR__ . '/../uploads/client_documents/';
if (!is_dir($FS_UPLOAD_DIR)) @mkdir($FS_UPLOAD_DIR, 0755, true);

// inputs
$client_id = isset($_POST['client_id']) ? intval($_POST['client_id']) : 0;
$meter_number = isset($_POST['meter_number']) ? clean($_POST['meter_number']) : '';
$meter_installation_date = isset($_POST['meter_installation_date']) ? clean($_POST['meter_installation_date']) : '';

$errors = [];
if (!$client_id) $errors[] = 'Client not specified. Please select a client.';

// basic date validation (if provided)
if ($meter_installation_date !== '') {
    $dt = DateTime::createFromFormat('Y-m-d', $meter_installation_date);
    if (!$dt || $dt->format('Y-m-d') !== $meter_installation_date) {
        $errors[] = 'Installation date is invalid. Use YYYY-MM-DD.';
    }
}

if (!empty($errors)) {
    $_SESSION['workflow_errors'] = $errors;
    header('Location: ../workflow.php?step=12&client_id=' . intval($client_id));
    exit;
}

// ensure client exists
$stmt = $conn->prepare("SELECT id FROM clients WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $client_id);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows === 0) {
    $_SESSION['workflow_errors'] = ['Selected client does not exist.'];
    header('Location: ../workflow.php?step=12');
    exit;
}
$stmt->close();

// handle optional file upload for meter_photo
$uploadedMeta = null;
if (isset($_FILES['meter_photo']) && $_FILES['meter_photo']['error'] !== UPLOAD_ERR_NO_FILE) {
    $f = $_FILES['meter_photo'];
    if ($f['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['workflow_errors'] = ['Upload error for meter installation photo.'];
        header('Location: ../workflow.php?step=12&client_id=' . intval($client_id));
        exit;
    }
    if ($f['size'] > $MAX_FILE_BYTES) {
        $_SESSION['workflow_errors'] = ['Meter photo exceeds 5MB.'];
        header('Location: ../workflow.php?step=12&client_id=' . intval($client_id));
        exit;
    }

    // mime check
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $f['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime, $ALLOWED_MIMES, true)) {
        $_SESSION['workflow_errors'] = ['Meter photo must be JPG or PNG.'];
        header('Location: ../workflow.php?step=12&client_id=' . intval($client_id));
        exit;
    }

    // move file
    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    $base = preg_replace('/[^a-zA-Z0-9_\-]/', '_', pathinfo($f['name'], PATHINFO_FILENAME));
    $uniq = time() . '_' . bin2hex(random_bytes(6));
    $finalName = $client_id . '_meter_photo_' . $uniq . '.' . $ext;
    $destFs = rtrim($FS_UPLOAD_DIR, '/') . '/' . $finalName;
    $destPublic = rtrim($PUBLIC_UPLOAD_DIR, '/') . '/' . $finalName;

    if (!move_uploaded_file($f['tmp_name'], $destFs)) {
        $_SESSION['workflow_errors'] = ['Failed to move uploaded meter photo.'];
        header('Location: ../workflow.php?step=12&client_id=' . intval($client_id));
        exit;
    }
    @chmod($destFs, 0644);

    $uploadedMeta = [
        'file_name' => $finalName,
        'file_path' => $destPublic,
        'file_size' => intval($f['size']),
        'mime_type' => $mime,
        'original_filename' => $f['name']
    ];
}

// If uploaded, upsert into client_documents with document_type = 'meter_photo'
if ($uploadedMeta) {
    $qt = $conn->prepare("SELECT id, file_name FROM client_documents WHERE client_id = ? AND document_type = 'meter_photo' LIMIT 1");
    if ($qt) {
        $qt->bind_param('i', $client_id);
        $qt->execute();
        $r = $qt->get_result();
        if ($r && $r->num_rows > 0) {
            $row = $r->fetch_assoc();
            $existingId = intval($row['id']);
            $existingFile = $row['file_name'] ?? '';
            $upd = $conn->prepare("UPDATE client_documents SET file_path = ?, file_name = ?, file_size = ?, mime_type = ?, original_filename = ?, created_at = NOW() WHERE id = ? LIMIT 1");
            if ($upd) {
                $upd->bind_param('ssissi', $uploadedMeta['file_path'], $uploadedMeta['file_name'], $uploadedMeta['file_size'], $uploadedMeta['mime_type'], $uploadedMeta['original_filename'], $existingId);
                $upd->execute();
                $upd->close();
            }
            // delete old file if it exists and name differs
            if (!empty($existingFile) && $existingFile !== $uploadedMeta['file_name']) {
                $oldFs = rtrim($FS_UPLOAD_DIR, '/') . '/' . $existingFile;
                if (file_exists($oldFs)) @unlink($oldFs);
            }
        } else {
            $ins = $conn->prepare("INSERT INTO client_documents (client_id, document_type, file_path, file_name, file_size, mime_type, original_filename, created_at) VALUES (?, 'meter_photo', ?, ?, ?, ?, ?, NOW())");
            if ($ins) {
                $ins->bind_param('ississ', $client_id, $uploadedMeta['file_path'], $uploadedMeta['file_name'], $uploadedMeta['file_size'], $uploadedMeta['mime_type'], $uploadedMeta['original_filename']);
                $ins->execute();
                $ins->close();
            }
        }
        $qt->close();
    }
}

// Update clients table: meter_number, meter_installation_date (NULL if empty) and updated_at
try {
    $sql = "UPDATE clients SET meter_number = ?, meter_installation_date = NULLIF(?, ''), updated_at = NOW() WHERE id = ?";
    $u = $conn->prepare($sql);
    if ($u === false) throw new Exception('Prepare failed: ' . $conn->error);
    if (!$u->bind_param('ssi', $meter_number, $meter_installation_date, $client_id)) throw new Exception('bind_param failed: ' . $u->error);
    if (!$u->execute()) throw new Exception('Execute failed: ' . $u->error);
    $u->close();

    $_SESSION['workflow_success'] = 'Meter installation details saved successfully.';
    header('Location: ../workflow.php?step=13&client_id=' . intval($client_id));
    exit;
} catch (Exception $ex) {
    error_log('save_step12 error: ' . $ex->getMessage());
    $_SESSION['workflow_errors'] = ['Server error. Please try again later.'];
    header('Location: ../workflow.php?step=12&client_id=' . intval($client_id));
    exit;
}
