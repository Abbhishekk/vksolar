<?php
// admin/workflow_steps/save_step11.php
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

$client_id = isset($_POST['client_id']) ? intval($_POST['client_id']) : 0;
$rts_portal_status = isset($_POST['rts_portal_status']) ? clean($_POST['rts_portal_status']) : '';

$errors = [];
if (!$client_id) $errors[] = 'Client not specified. Please select a client.';
if ($rts_portal_status === '') $errors[] = 'Please select RTS Portal status.';
if ($rts_portal_status !== 'yes' && $rts_portal_status !== 'no') $errors[] = 'Invalid RTS Portal status selection.';

if (!empty($errors)) {
    $_SESSION['workflow_errors'] = $errors;
    header('Location: ../workflow.php?step=11&client_id=' . intval($client_id));
    exit;
}

// verify client exists
$stmt = $conn->prepare("SELECT id FROM clients WHERE id = ? LIMIT 1");
if (!$stmt) {
    $_SESSION['workflow_errors'] = ['Server DB error.'];
    header('Location: ../workflow.php?step=11&client_id=' . intval($client_id));
    exit;
}
$stmt->bind_param('i', $client_id);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows === 0) {
    $_SESSION['workflow_errors'] = ['Selected client does not exist.'];
    header('Location: ../workflow.php?step=11');
    exit;
}
$stmt->close();

// perform update
try {
    $sql = "UPDATE clients SET rts_portal_status = ?, updated_at = NOW() WHERE id = ?";
    $u = $conn->prepare($sql);
    if ($u === false) throw new Exception('Prepare failed: ' . $conn->error);
    $u->bind_param('si', $rts_portal_status, $client_id);
    if (!$u->execute()) throw new Exception('Execute failed: ' . $u->error);
    $u->close();

    $_SESSION['workflow_success'] = 'RTS Portal status saved successfully.';
    header('Location: ../workflow.php?step=12&client_id=' . intval($client_id));
    exit;
} catch (Exception $ex) {
    error_log('save_step11 error: ' . $ex->getMessage());
    $_SESSION['workflow_errors'] = ['Server error. Please try again later.'];
    header('Location: ../workflow.php?step=11&client_id=' . intval($client_id));
    exit;
}
