<?php
// admin/workflow_steps/save_step10.php
session_start();
ini_set('display_errors',1);
error_reporting(E_ALL);

require_once __DIR__ . '/../connect/db.php'; // $conn (mysqli)

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed'); echo 'POST only'; exit;
}

function clean($v){ return trim($v); }

// upload config
$MAX_FILE_BYTES = 5 * 1024 * 1024;
$ALLOWED_MIMES = [
  'application/pdf','image/jpeg','image/jpg','image/png','image/webp'
];
$PUBLIC_UPLOAD_DIR = '/admin/uploads/client_documents/';
$FS_UPLOAD_DIR = __DIR__ . '/../uploads/client_documents/';
if (!is_dir($FS_UPLOAD_DIR)) @mkdir($FS_UPLOAD_DIR, 0755, true);

// mapping: form input => document_type enum value in DB
$docMap = [
  'aadhar_card'   => 'aadhar',
  'pan_card'      => 'pan_card',
  'electric_bill' => 'electric_bill',
  'bank_passbook' => 'bank_passbook',
  'model_agreement' => 'model_agreement',
  'dcr_certificate' => 'dcr_certificate',
  'bank_statement'  => 'bank_statement',
  'salary_slip'     => 'salary_slip',
  'it_return'       => 'it_return',
  'gumasta'         => 'gumasta',
  'client_signature'=>'client_signature'
];

$requiredDocs = ['aadhar_card','pan_card','electric_bill','bank_passbook','client_signature'];

$client_id = isset($_POST['client_id']) ? intval($_POST['client_id']) : 0;
if (!$client_id) {
  $_SESSION['workflow_errors'] = ['Client not specified.'];
  header('Location: ../workflow.php?step=10'); exit;
}

// verify client exists
$stc = $conn->prepare("SELECT id FROM clients WHERE id = ? LIMIT 1");
$stc->bind_param('i', $client_id);
$stc->execute();
$rc = $stc->get_result();
if (!$rc || $rc->num_rows === 0) {
  $_SESSION['workflow_errors'] = ['Client not found.'];
  header('Location: ../workflow.php?step=10'); exit;
}
$stc->close();

// ensure required docs: either uploaded now or already present in DB
$missing = [];
foreach ($requiredDocs as $input) {
  if (!isset($_FILES[$input]) || $_FILES[$input]['error'] === UPLOAD_ERR_NO_FILE) {
    // check DB
    $q = $conn->prepare("SELECT id FROM client_documents WHERE client_id = ? AND document_type = ? LIMIT 1");
    $docType = $docMap[$input];
    $q->bind_param('is', $client_id, $docType);
    $q->execute();
    $res = $q->get_result();
    if (!$res || $res->num_rows === 0) $missing[] = $input;
    $q->close();
  }
}
if (!empty($missing)) {
  $_SESSION['workflow_errors'] = ['Please upload required documents: ' . implode(', ', $missing)];
  header('Location: ../workflow.php?step=10&client_id=' . intval($client_id)); exit;
}

// Process file uploads using $docMap. Build $processed[document_type] = meta
$processed = []; $errors = [];
foreach ($docMap as $inputName => $docType) {
  if (!isset($_FILES[$inputName]) || $_FILES[$inputName]['error'] === UPLOAD_ERR_NO_FILE) continue;
  $f = $_FILES[$inputName];
  if ($f['error'] !== UPLOAD_ERR_OK) { $errors[] = "Upload error for $inputName"; continue; }
  if ($f['size'] > $MAX_FILE_BYTES) { $errors[] = ucfirst($inputName).' exceeds 5MB'; continue; }

  $finfo = finfo_open(FILEINFO_MIME_TYPE);
  $mime = finfo_file($finfo, $f['tmp_name']);
  finfo_close($finfo);
  if (!in_array($mime, $ALLOWED_MIMES, true)) { $errors[] = ucfirst($inputName).' invalid file type'; continue; }

  $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
  $base = preg_replace('/[^a-zA-Z0-9_\-]/','_', pathinfo($f['name'], PATHINFO_FILENAME));
  $uniq = time() . '_' . bin2hex(random_bytes(6));
  $finalName = $client_id . '_' . $docType . '_' . $uniq . '.' . $ext;
  $destFs = rtrim($FS_UPLOAD_DIR, '/') . '/' . $finalName;
  $destPublic = rtrim($PUBLIC_UPLOAD_DIR, '/') . '/' . $finalName;

  if (!move_uploaded_file($f['tmp_name'], $destFs)) { $errors[] = "Failed to move file for $inputName"; continue; }
  @chmod($destFs, 0644);

  $processed[$docType] = [
    'file_name' => $finalName,
    'file_path' => $destPublic,
    'file_size' => intval($f['size']),
    'mime_type' => $mime,
    'original_filename' => $f['name']
  ];
}

// if errors with uploads, remove moved files and redirect
if (!empty($errors)) {
  foreach ($processed as $p) {
    $fpath = rtrim($FS_UPLOAD_DIR, '/') . '/' . $p['file_name'];
    if (file_exists($fpath)) @unlink($fpath);
  }
  $_SESSION['workflow_errors'] = $errors;
  header('Location: ../workflow.php?step=10&client_id=' . intval($client_id)); exit;
}

// Upsert client_documents rows for each processed docType
foreach ($processed as $docType => $meta) {
  $qr = $conn->prepare("SELECT id, file_name FROM client_documents WHERE client_id = ? AND document_type = ? LIMIT 1");
  $qr->bind_param('is', $client_id, $docType);
  $qr->execute();
  $r = $qr->get_result();
  if ($r && $r->num_rows > 0) {
    $row = $r->fetch_assoc();
    $existingId = intval($row['id']);
    $existingFile = $row['file_name'];

    $upd = $conn->prepare("UPDATE client_documents SET file_path = ?, file_name = ?, file_size = ?, mime_type = ?, original_filename = ?, created_at = NOW() WHERE id = ? LIMIT 1");
    $upd->bind_param('ssissi', $meta['file_path'], $meta['file_name'], $meta['file_size'], $meta['mime_type'], $meta['original_filename'], $existingId);
    $upd->execute();
    $upd->close();

    // delete old file if changed
    if (!empty($existingFile) && $existingFile !== $meta['file_name']) {
      $oldFs = rtrim($FS_UPLOAD_DIR, '/') . '/' . $existingFile;
      if (file_exists($oldFs)) @unlink($oldFs);
    }

  } else {
    $ins = $conn->prepare("INSERT INTO client_documents (client_id, document_type, file_path, file_name, file_size, mime_type, original_filename, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    $ins->bind_param('isssiss', $client_id, $docType, $meta['file_path'], $meta['file_name'], $meta['file_size'], $meta['mime_type'], $meta['original_filename']);
    $ins->execute();
    $ins->close();
  }
  $qr->close();
}

// Collect system fields and panel serials from POST
$inverter_company_name = clean($_POST['inverter_company_name'] ?? '');
$inverter_capacity = clean($_POST['inverter_capacity'] ?? '');
$inverter_serial_number = clean($_POST['inverter_serial_number'] ?? '');
$dcr_certificate_number = clean($_POST['dcr_certificate_number'] ?? '');
$panel_company = clean($_POST['panel_company_name'] ?? '');
$panel_wattage = clean($_POST['Wattage'] ?? '');
$number_of_panels = isset($_POST['number_of_panels']) ? intval($_POST['number_of_panels']) : 0;

$panel_serials = [];
if ($number_of_panels > 0) {
  for ($i = 1; $i <= $number_of_panels; $i++) {
    $k = 'panel_serial_' . $i;
    $val = isset($_POST[$k]) ? clean($_POST[$k]) : '';
    if ($val === '') {
      $_SESSION['workflow_errors'] = ["Please enter serial number for panel $i."];
      header('Location: ../workflow.php?step=10&client_id=' . intval($client_id)); exit;
    }
    $panel_serials[] = $val;
  }
}

// Validations
$errors2 = [];
if ($inverter_company_name === '') $errors2[] = 'Inverter company name required.';
if ($inverter_serial_number === '') $errors2[] = 'Inverter serial number required.';
if ($dcr_certificate_number === '') $errors2[] = 'DCR certificate number required.';
if ($panel_company === '') $errors2[] = 'Panel company required.';
if ($panel_wattage === '') $errors2[] = 'Panel wattage required.';
if ($number_of_panels <= 0) $errors2[] = 'Number of panels must be >= 1.';

if (!empty($errors2)) {
  $_SESSION['workflow_errors'] = $errors2;
  header('Location: ../workflow.php?step=10&client_id=' . intval($client_id)); exit;
}

// Persist:
// 1) Update clients fields
// 2) Replace solar_panels rows (delete existing -> insert new)
$conn->begin_transaction();
try {
  // update clients
  $ps_csv = !empty($panel_serials) ? implode(',', $panel_serials) : '';
  $stmt = $conn->prepare("UPDATE clients SET inverter_company_name = ?, inverter_capacity = ?, inverter_serial_number = ?, dcr_certificate_number = ?, number_of_panels = ?, panel_serial_numbers = ?,company_name = ?, wattage = ?, updated_at = NOW() WHERE id = ?");
  if ($stmt === false) throw new Exception('Prepare failed: ' . $conn->error);
  $bind_types = 'ssssissii'; // s, s, s, i, s, i
  if (!$stmt->bind_param($bind_types, $inverter_company_name,$inverter_capacity, $inverter_serial_number, $dcr_certificate_number, $number_of_panels,$ps_csv,$panel_company, $panel_wattage, $client_id)) {
    throw new Exception('bind_param failed: ' . $stmt->error);
  }
  if (!$stmt->execute()) throw new Exception('Execute failed: ' . $stmt->error);
  $stmt->close();

  // replace solar_panels rows: delete then insert
  $del = $conn->prepare("DELETE FROM solar_panels WHERE client_id = ?");
  $del->bind_param('i', $client_id);
  $del->execute();
  $del->close();

  if (!empty($panel_serials)) {
    $ins = $conn->prepare("INSERT INTO solar_panels (client_id, panel_number, company_name, wattage, serial_number, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    if ($ins === false) throw new Exception('Prepare failed (insert panels): ' . $conn->error);
    foreach ($panel_serials as $idx => $serial) {
      $panel_num = $idx + 1;
      $s = $serial;
      $ins->bind_param('iisis', $client_id, $panel_num,$panel_company,$panel_wattage, $s);
      if (!$ins->execute()) throw new Exception('Insert panel failed: ' . $ins->error);
    }
    $ins->close();
  }

  $conn->commit();
  $_SESSION['workflow_success'] = 'PM SuryaGhar documents and system details saved successfully.';
  header('Location: ../workflow.php?step=11&client_id=' . intval($client_id));
  exit;

} catch (Exception $ex) {
  $conn->rollback();
  error_log('save_step10 transaction error: ' . $ex->getMessage());
  $_SESSION['workflow_errors'] = ['Server error while saving step 10. Please try again later.'];
  header('Location: ../workflow.php?step=10&client_id=' . intval($client_id));
  exit;
}
