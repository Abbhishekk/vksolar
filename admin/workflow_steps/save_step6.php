<?php
// admin/workflow_steps/save_step6.php
session_start();
ini_set('display_errors',1); error_reporting(E_ALL);
require_once __DIR__ . '/../connect/db.php'; // $conn (mysqli)

// Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("HTTP/1.1 405 Method Not Allowed");
    echo "This endpoint accepts POST only.";
    exit;
}

function clean($v){ return trim($v); }

$client_id = isset($_POST['client_id']) ? intval($_POST['client_id']) : 0;
$pm_reg = isset($_POST['pm_suryaghar_registration']) ? clean($_POST['pm_suryaghar_registration']) : '';
$pm_app = isset($_POST['pm_suryaghar_app_id']) ? clean($_POST['pm_suryaghar_app_id']) : '';
$pm_date = isset($_POST['pm_registration_date']) ? clean($_POST['pm_registration_date']) : '';

$errors = [];
if (!$client_id) $errors[] = 'Client not specified. Please select a client.';
if ($pm_reg === '') $errors[] = 'Please select PM Suryaghar registration Yes/No.';

if ($pm_reg === 'yes') {
    if ($pm_app === '') $errors[] = 'Application ID is required when registration is Yes.';
    if ($pm_date === '') $errors[] = 'Registration date is required when registration is Yes.';
    // optional: validate date format YYYY-MM-DD
    if ($pm_date !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $pm_date)) $errors[] = 'Registration date format invalid. Use YYYY-MM-DD.';
}

if (!empty($errors)) {
    $_SESSION['workflow_errors'] = $errors;
    header('Location: ../workflow.php?step=6&client_id=' . intval($client_id));
    exit;
}

// ensure $conn
if (!isset($conn) || !($conn instanceof mysqli)) {
    error_log('save_step6: $conn not set or not mysqli');
    $_SESSION['workflow_errors'] = ['Server DB error.'];
    header('Location: ../workflow.php?step=6&client_id=' . intval($client_id));
    exit;
}

// ensure client exists
$stmt = $conn->prepare("SELECT id FROM clients WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $client_id);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows === 0) {
    $_SESSION['workflow_errors'] = ['Selected client does not exist.'];
    header('Location: ../workflow.php?step=6');
    exit;
}
$stmt->close();

// perform update
try {
    $sql = "UPDATE clients SET pm_suryaghar_registration = ?, pm_suryaghar_app_id = ?, pm_registration_date = ?, updated_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) throw new Exception('Prepare failed: ' . $conn->error);
    $stmt->bind_param('sssi', $pm_reg, $pm_app, $pm_date, $client_id);
    if (!$stmt->execute()) throw new Exception('Execute failed: ' . $stmt->error);
    $stmt->close();

    $_SESSION['workflow_success'] = 'PM Suryaghar registration saved successfully.';
    header('Location: ../workflow.php?step=7&client_id=' . intval($client_id));
    exit;
} catch (Exception $ex) {
    error_log('save_step6 error: ' . $ex->getMessage());
    $_SESSION['workflow_errors'] = ['Server error. Please try again later.'];
    header('Location: ../workflow.php?step=6&client_id=' . intval($client_id));
    exit;
}
