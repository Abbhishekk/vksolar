<?php
// admin/workflow_steps/save_step7.php
session_start();
ini_set('display_errors',1); error_reporting(E_ALL);
require_once __DIR__ . '/../connect/db.php'; // $conn (mysqli)

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("HTTP/1.1 405 Method Not Allowed");
    echo "This endpoint accepts POST only.";
    exit;
}

function clean($v) { return trim($v); }

$client_id = isset($_POST['client_id']) ? intval($_POST['client_id']) : 0;
$load_change_application_number = isset($_POST['load_change_application_number']) ? clean($_POST['load_change_application_number']) : '';
$rooftop_solar_application_number = isset($_POST['rooftop_solar_application_number']) ? clean($_POST['rooftop_solar_application_number']) : '';

$errors = [];
if (!$client_id) $errors[] = 'Client not specified. Please select a client.';
if ($load_change_application_number === '') $errors[] = 'Load Change Application Number is required.';
if ($rooftop_solar_application_number === '') $errors[] = 'Rooftop Solar Application Number is required.';

if (!empty($errors)) {
    $_SESSION['workflow_errors'] = $errors;
    header('Location: ../workflow.php?step=7&client_id=' . intval($client_id));
    exit;
}

// ensure DB connection
if (!isset($conn) || !($conn instanceof mysqli)) {
    error_log('save_step7: $conn not set or not mysqli');
    $_SESSION['workflow_errors'] = ['Server DB error.'];
    header('Location: ../workflow.php?step=7&client_id=' . intval($client_id));
    exit;
}

// verify client exists
$stmt = $conn->prepare("SELECT id FROM clients WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $client_id);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows === 0) {
    $_SESSION['workflow_errors'] = ['Selected client does not exist.'];
    header('Location: ../workflow.php?step=7');
    exit;
}
$stmt->close();

// update
try {
    $sql = "UPDATE clients SET load_change_application_number = ?, rooftop_solar_application_number = ?, updated_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) throw new Exception('Prepare failed: ' . $conn->error);
    $stmt->bind_param('ssi', $load_change_application_number, $rooftop_solar_application_number, $client_id);
    if (!$stmt->execute()) throw new Exception('Execute failed: ' . $stmt->error);
    $stmt->close();

    $_SESSION['workflow_success'] = 'MAHADISCOM sanction load data saved successfully.';
    header('Location: ../workflow.php?step=8&client_id=' . intval($client_id));
    exit;
} catch (Exception $ex) {
    error_log('save_step7 error: ' . $ex->getMessage());
    $_SESSION['workflow_errors'] = ['Server error. Please try again later.'];
    header('Location: ../workflow.php?step=7&client_id=' . intval($client_id));
    exit;
}
