<?php
// workflow_steps/save_step3.php
session_start();
ini_set('display_errors',1); error_reporting(E_ALL);
require_once __DIR__ . '/../connect/db.php'; // provides $conn (mysqli)

// Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("HTTP/1.1 405 Method Not Allowed");
    echo "This endpoint accepts POST only. Current method: " . htmlspecialchars($_SERVER['REQUEST_METHOD']);
    exit;
}

// sanitizer
function clean($v) { return trim($v); }

$client_id = isset($_POST['client_id']) ? intval($_POST['client_id']) : 0;
$maha_email = clean($_POST['mahadiscom_email'] ?? '');
$maha_pass  = clean($_POST['mahadiscom_email_password'] ?? '');
$maha_mobile= clean($_POST['mahadiscom_mobile'] ?? '');

// Validation
$errors = [];
if (!$client_id) $errors[] = 'Client not specified. Please select a client.';
if ($maha_email === '') $errors[] = 'MAHADISCOM email is required.';
if ($maha_mobile === '') $errors[] = 'MAHADISCOM mobile is required.';
if ($maha_email !== '' && !filter_var($maha_email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid MAHADISCOM email format.';

// normalize mobile
if ($maha_mobile !== '') {
    $digits = preg_replace('/\D+/', '', $maha_mobile);
    if (strlen($digits) < 7) $errors[] = 'MAHADISCOM mobile seems too short.';
    else $maha_mobile = $digits;
}

if (!empty($errors)) {
    $_SESSION['workflow_errors'] = $errors;
    header('Location: ../workflow.php?step=3&client_id=' . intval($client_id));
    exit;
}

// ensure $conn is mysqli
if (!isset($conn) || !($conn instanceof mysqli)) {
    error_log('save_step3: $conn not set or not mysqli');
    $_SESSION['workflow_errors'] = ['Server DB error.'];
    header('Location: ../workflow.php?step=3&client_id=' . intval($client_id));
    exit;
}

// Ensure client exists
$stmt = $conn->prepare("SELECT id FROM clients WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $client_id);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows === 0) {
    $_SESSION['workflow_errors'] = ['Selected client does not exist.'];
    header('Location: ../workflow.php?step=3');
    exit;
}
$stmt->close();

// Update record
try {
    $sql = "UPDATE clients SET mahadiscom_email = ?, mahadiscom_email_password = ?, mahadiscom_mobile = ?, updated_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) throw new Exception('Prepare failed: ' . $conn->error);
    $stmt->bind_param('sssi', $maha_email, $maha_pass, $maha_mobile, $client_id);
    if (!$stmt->execute()) throw new Exception('Execute failed: ' . $stmt->error);
    $stmt->close();

    $_SESSION['workflow_success'] = 'MAHADISCOM details updated successfully.';
    header('Location: ../workflow.php?step=4&client_id=' . intval($client_id));
    exit;
} catch (Exception $ex) {
    error_log('save_step3 error: ' . $ex->getMessage());
    $_SESSION['workflow_errors'] = ['Server error. Please try again later.'];
    header('Location: ../workflow.php?step=3&client_id=' . intval($client_id));
    exit;
}
