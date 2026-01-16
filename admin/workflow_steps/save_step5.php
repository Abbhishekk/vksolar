<?php
// admin/workflow_steps/save_step5.php
session_start();
ini_set('display_errors',1); error_reporting(E_ALL);
require_once __DIR__ . '/../connect/db.php'; // provides $conn (mysqli)

// Only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("HTTP/1.1 405 Method Not Allowed");
    echo "This endpoint accepts POST only.";
    exit;
}

function clean($v){ return trim($v); }

$client_id = isset($_POST['client_id']) ? intval($_POST['client_id']) : 0;
$name_change_require = isset($_POST['name_change_require']) ? clean($_POST['name_change_require']) : '';
$application_no = isset($_POST['application_no_name_change']) ? clean($_POST['application_no_name_change']) : '';

$errors = [];
if (!$client_id) $errors[] = 'Client not specified. Please select a client.';
if ($name_change_require === '') $errors[] = 'Please select whether name change is required.';
if ($name_change_require === 'yes' && $application_no === '') $errors[] = 'Application number is required when name change is Yes.';

if (!empty($errors)) {
    $_SESSION['workflow_errors'] = $errors;
    header('Location: ../workflow.php?step=5&client_id=' . intval($client_id));
    exit;
}

// ensure $conn
if (!isset($conn) || !($conn instanceof mysqli)) {
    error_log('save_step5: $conn not set or not mysqli');
    $_SESSION['workflow_errors'] = ['Server DB error.'];
    header('Location: ../workflow.php?step=5&client_id=' . intval($client_id));
    exit;
}

// ensure client exists
$stmt = $conn->prepare("SELECT id FROM clients WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $client_id);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows === 0) {
    $_SESSION['workflow_errors'] = ['Selected client does not exist.'];
    header('Location: ../workflow.php?step=5');
    exit;
}
$stmt->close();

// perform update
try {
    $sql = "UPDATE clients SET name_change_require = ?, application_no_name_change = ?, updated_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) throw new Exception('Prepare failed: ' . $conn->error);
    $stmt->bind_param('ssi', $name_change_require, $application_no, $client_id);
    if (!$stmt->execute()) throw new Exception('Execute failed: ' . $stmt->error);
    $stmt->close();

    $_SESSION['workflow_success'] = 'Step 5 saved successfully.';
    header('Location: ../workflow.php?step=6&client_id=' . intval($client_id));
    exit;
} catch (Exception $ex) {
    error_log('save_step5 error: ' . $ex->getMessage());
    $_SESSION['workflow_errors'] = ['Server error. Please try again later.'];
    header('Location: ../workflow.php?step=5&client_id=' . intval($client_id));
    exit;
}
