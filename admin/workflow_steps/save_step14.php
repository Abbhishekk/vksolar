<?php
// admin/workflow_steps/save_step14.php
session_start();
ini_set('display_errors',1);
error_reporting(E_ALL);

require_once __DIR__ . '/../connect/db.php'; // expects $conn (mysqli)

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../workflow.php?step=14");
    exit;
}

function clean($v){ return trim((string)$v); }

$client_id = isset($_POST['client_id']) ? intval($_POST['client_id']) : 0;
$reference_name = isset($_POST['reference_name']) ? clean($_POST['reference_name']) : '';
$reference_contact = isset($_POST['reference_contact']) ? clean($_POST['reference_contact']) : '';

$errors = [];
if (!$client_id) $errors[] = "Client not selected.";

if (!empty($errors)) {
    $_SESSION['workflow_errors'] = $errors;
    header("Location: ../workflow.php?step=14&client_id=" . intval($client_id));
    exit;
}

// verify client exists
$stmt = $conn->prepare("SELECT id FROM clients WHERE id = ? LIMIT 1");
if ($stmt === false) {
    $_SESSION['workflow_errors'] = ['Server DB error.'];
    header("Location: ../workflow.php?step=14&client_id=" . intval($client_id));
    exit;
}
$stmt->bind_param('i', $client_id);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows === 0) {
    $_SESSION['workflow_errors'] = ["Client does not exist."];
    $stmt->close();
    header("Location: ../workflow.php?step=14");
    exit;
}
$stmt->close();

try {
    $sql = "UPDATE clients 
            SET reference_name = ?, reference_contact = ?, updated_at = NOW()
            WHERE id = ?";
    $u = $conn->prepare($sql);
    if ($u === false) throw new Exception('Prepare failed: ' . $conn->error);

    $u->bind_param('ssi', $reference_name, $reference_contact, $client_id);
    if (!$u->execute()) throw new Exception('Execute failed: ' . $u->error);
    $u->close();

    $_SESSION['workflow_success'] = "Reference details saved successfully.";
    header("Location: ../workflow.php?step=14&client_id=" . intval($client_id));
    exit;

} catch (Exception $e) {
    error_log("Step14 save error: " . $e->getMessage());
    $_SESSION['workflow_errors'] = ["Server error. Try again."];
    header("Location: ../workflow.php?step=14&client_id=" . intval($client_id));
    exit;
}
