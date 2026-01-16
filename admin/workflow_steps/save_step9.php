<?php
// admin/workflow_steps/save_step9.php
session_start();
ini_set('display_errors',1); error_reporting(E_ALL);

require_once __DIR__ . '/../connect/db.php'; // $conn (mysqli)

// only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("HTTP/1.1 405 Method Not Allowed");
    echo "This endpoint accepts POST only.";
    exit;
}

// config
$MAX_FILE_BYTES = 5 * 1024 * 1024; // 5MB
$ALLOWED_MIMES = ['image/jpeg','image/png','image/webp','image/jpg'];
// Public path where files will be accessible (adjust if you prefer another public folder)
$PUBLIC_UPLOAD_DIR = '/admin/uploads/client_documents/'; // used for storing path in DB (leading slash from site root)
$FS_UPLOAD_DIR = __DIR__ . '/../uploads/client_documents/'; // filesystem path

if (!is_dir($FS_UPLOAD_DIR)) {
    @mkdir($FS_UPLOAD_DIR, 0755, true);
}

$client_id = isset($_POST['client_id']) ? intval($_POST['client_id']) : 0;
if (!$client_id) {
    $_SESSION['workflow_errors'] = ['Client not specified.'];
    header('Location: ../workflow.php?step=9');
    exit;
}

// verify client exists
$stmt = $conn->prepare("SELECT id FROM clients WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $client_id);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows === 0) {
    $_SESSION['workflow_errors'] = ['Selected client does not exist.'];
    header('Location: ../workflow.php?step=9');
    exit;
}
$stmt->close();

// allowed document inputs -> document_type enum in DB
$map = [
  'solar_panel_photo' => 'solar_panel_photo',
  'inverter_photo'    => 'inverter_photo',
  'geotag_photo'      => 'geotag_photo'
];

$errors = [];
$processed = []; // doc_type => metadata

foreach ($map as $inputName => $docType) {
    if (!isset($_FILES[$inputName]) || $_FILES[$inputName]['error'] === UPLOAD_ERR_NO_FILE) {
        continue; // optional
    }

    $f = $_FILES[$inputName];

    if ($f['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Error uploading file for {$inputName}.";
        continue;
    }

    if ($f['size'] > $MAX_FILE_BYTES) {
        $errors[] = ucfirst($inputName) . " must be <= 5MB.";
        continue;
    }

    // MIME check
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $f['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime, $ALLOWED_MIMES, true)) {
        $errors[] = ucfirst($inputName) . " must be an image (jpg, png, webp).";
        continue;
    }

    // safe filename and unique name
    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    $base = preg_replace('/[^a-zA-Z0-9_\-]/', '_', pathinfo($f['name'], PATHINFO_FILENAME));
    $uniq = time() . '_' . bin2hex(random_bytes(6));
    $finalName = $client_id . '_' . $docType . '_' . $uniq . '.' . $ext;

    $destFs = rtrim($FS_UPLOAD_DIR, '/') . '/' . $finalName;
    $destPublic = rtrim($PUBLIC_UPLOAD_DIR, '/') . '/' . $finalName; // store this in DB

    if (!move_uploaded_file($f['tmp_name'], $destFs)) {
        $errors[] = "Failed to move uploaded file for {$inputName}.";
        continue;
    }

    @chmod($destFs, 0644);

    $processed[$docType] = [
        'file_path' => $destPublic,
        'file_name' => $finalName,
        'file_size' => intval($f['size']),
        'mime_type' => $mime,
        'original_filename' => $f['name']
    ];
}

// if any upload errors, delete any moved files from this run and return
if (!empty($errors)) {
    // remove any files we already saved for this request
    foreach ($processed as $meta) {
        $possible = $FS_UPLOAD_DIR . $meta['file_name'];
        if (file_exists($possible)) @unlink($possible);
    }
    $_SESSION['workflow_errors'] = $errors;
    header('Location: ../workflow.php?step=9&client_id=' . intval($client_id));
    exit;
}

// Insert/update client_documents rows for each processed file
foreach ($processed as $docType => $meta) {
    // check existing row
    $qr = $conn->prepare("SELECT id, file_path, file_name FROM client_documents WHERE client_id = ? AND document_type = ? LIMIT 1");
    if (!$qr) {
        error_log("save_step9 prepare error: " . $conn->error);
        continue;
    }
    $qr->bind_param('is', $client_id, $docType);
    $qr->execute();
    $r = $qr->get_result();
    if ($r && $r->num_rows > 0) {
        $row = $r->fetch_assoc();
        $existingId = intval($row['id']);
        $existingFileName = $row['file_name'];

        // update row
        $upd = $conn->prepare("UPDATE client_documents SET file_path = ?, file_name = ?, file_size = ?, mime_type = ?, original_filename = ?, created_at = NOW() WHERE id = ? LIMIT 1");
        $upd->bind_param('ssissi',
            $meta['file_path'],
            $meta['file_name'],
            $meta['file_size'],
            $meta['mime_type'],
            $meta['original_filename'],
            $existingId
        );
        $upd->execute();
        $upd->close();

        // delete old file from disk if different
        if (!empty($existingFileName) && $existingFileName !== $meta['file_name']) {
            $oldFs = rtrim($FS_UPLOAD_DIR, '/') . '/' . $existingFileName;
            if (file_exists($oldFs)) @unlink($oldFs);
        }

    } else {
        // insert new
        $ins = $conn->prepare("INSERT INTO client_documents (client_id, document_type, file_path, file_name, file_size, mime_type, original_filename, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $ins->bind_param('isssiss',
            $client_id,
            $docType,
            $meta['file_path'],
            $meta['file_name'],
            $meta['file_size'],
            $meta['mime_type'],
            $meta['original_filename']
        );
        $ins->execute();
        $ins->close();
    }
    $qr->close();
}

// success
$_SESSION['workflow_success'] = 'Photos uploaded successfully.';
header('Location: ../workflow.php?step=10&client_id=' . intval($client_id));
exit;
