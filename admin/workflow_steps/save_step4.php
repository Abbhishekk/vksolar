<?php
// admin/workflow_steps/save_step4.php
session_start();
ini_set('display_errors',1); error_reporting(E_ALL);

// include DB connection
require_once __DIR__ . '/../connect/db.php'; // must supply $conn (mysqli)

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("HTTP/1.1 405 Method Not Allowed");
    echo "This endpoint accepts POST only. Current method: " . htmlspecialchars($_SERVER['REQUEST_METHOD']);
    exit;
}

// helpers
function clean($v) { return trim($v); }

$client_id = isset($_POST['client_id']) ? intval($_POST['client_id']) : 0;
$maha_user = clean($_POST['mahadiscom_user_id'] ?? '');
$maha_pass = clean($_POST['mahadiscom_password'] ?? '');

// validation
$errors = [];
if (!$client_id) $errors[] = 'Client not specified. Please select a client.';
if ($maha_user === '') $errors[] = 'MAHADISCOM user id is required.';
if ($maha_pass === '') $errors[] = 'MAHADISCOM password is required.';

if (!empty($errors)) {
    $_SESSION['workflow_errors'] = $errors;
    header('Location: ../workflow.php?step=4&client_id=' . intval($client_id));
    exit;
}

// ensure DB
if (!isset($conn) || !($conn instanceof mysqli)) {
    error_log('save_step4: $conn not set or not mysqli');
    $_SESSION['workflow_errors'] = ['Server DB error.'];
    header('Location: ../workflow.php?step=4&client_id=' . intval($client_id));
    exit;
}

// ensure client exists
$stmt = $conn->prepare("SELECT id FROM clients WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $client_id);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows === 0) {
    $_SESSION['workflow_errors'] = ['Selected client does not exist.'];
    header('Location: ../workflow.php?step=4');
    exit;
}
$stmt->close();

// perform update
try {
    $sql = "UPDATE clients SET mahadiscom_user_id = ?, mahadiscom_password = ?, updated_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) throw new Exception('Prepare failed: ' . $conn->error);
    $stmt->bind_param('ssi', $maha_user, $maha_pass, $client_id);
    if (!$stmt->execute()) throw new Exception('Execute failed: ' . $stmt->error);
    $stmt->close();

    $_SESSION['workflow_success'] = 'MAHADISCOM registration details updated successfully.';
    header('Location: ../workflow.php?step=5&client_id=' . intval($client_id));
    exit;
} catch (Exception $ex) {
    error_log('save_step4 error: ' . $ex->getMessage());
    $_SESSION['workflow_errors'] = ['Server error. Please try again later.'];
    header('Location: ../workflow.php?step=4&client_id=' . intval($client_id));
    exit;
}
