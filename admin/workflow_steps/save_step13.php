<?php
// admin/workflow_steps/save_step13.php
session_start();
ini_set('display_errors',1);
error_reporting(E_ALL);
require_once __DIR__ . '/../connect/db.php'; // $conn (mysqli)

function clean($v){ return trim($v); }

// Only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed'); echo "POST only"; exit;
}

$client_id = isset($_POST['client_id']) ? intval($_POST['client_id']) : 0;
$pm_redeem_status = isset($_POST['pm_redeem_status']) ? clean($_POST['pm_redeem_status']) : '';

$errors = [];
if (!$client_id) $errors[] = 'Client not specified.';
if ($pm_redeem_status === '') $errors[] = 'Please select redeem status.';
if ($pm_redeem_status !== 'yes' && $pm_redeem_status !== 'no') $errors[] = 'Invalid redeem status.';

$subsidy_amount = null;
$subsidy_redeem_date = null;

if ($pm_redeem_status === 'yes') {
    // required fields
    if (!isset($_POST['subsidy_amount']) || $_POST['subsidy_amount'] === '') $errors[] = 'Subsidy amount required.';
    if (!isset($_POST['subsidy_redeem_date']) || $_POST['subsidy_redeem_date'] === '') $errors[] = 'Subsidy redeem date required.';

    if (empty($errors)) {
        $amt = str_replace(',', '', $_POST['subsidy_amount']);
        if (!is_numeric($amt) || floatval($amt) <= 0) $errors[] = 'Invalid subsidy amount.';
        else $subsidy_amount = floatval($amt);

        $date = clean($_POST['subsidy_redeem_date']);
        $dt = DateTime::createFromFormat('Y-m-d', $date);
        if (!$dt || $dt->format('Y-m-d') !== $date) $errors[] = 'Invalid subsidy redeem date.';
        else $subsidy_redeem_date = $date;
    }
}

if (!empty($errors)) {
    $_SESSION['workflow_errors'] = $errors;
    header('Location: ../workflow.php?step=13&client_id=' . intval($client_id));
    exit;
}

// ensure client exists
$chk = $conn->prepare("SELECT id FROM clients WHERE id = ? LIMIT 1");
$chk->bind_param('i', $client_id);
$chk->execute();
$res = $chk->get_result();
if (!$res || $res->num_rows === 0) {
    $_SESSION['workflow_errors'] = ['Selected client does not exist.'];
    header('Location: ../workflow.php?step=13');
    exit;
}
$chk->close();

// perform update
try {
    if ($pm_redeem_status === 'yes') {
        $sql = "UPDATE clients SET pm_redeem_status = ?, subsidy_amount = ?, subsidy_redeem_date = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) throw new Exception('Prepare failed: ' . $conn->error);
        // 'sdsi' => string, double, string, int
        $stmt->bind_param('sdsi', $pm_redeem_status, $subsidy_amount, $subsidy_redeem_date, $client_id);
    } else {
        // set subsidy fields to NULL if redeem not done
        $sql = "UPDATE clients SET pm_redeem_status = ?, subsidy_amount = NULL, subsidy_redeem_date = NULL, updated_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) throw new Exception('Prepare failed: ' . $conn->error);
        $stmt->bind_param('si', $pm_redeem_status, $client_id);
    }

    if (!$stmt->execute()) throw new Exception('Execute failed: ' . $stmt->error);
    $stmt->close();

    $_SESSION['workflow_success'] = 'Step 13 saved successfully.';
    header('Location: ../workflow.php?step=14&client_id=' . intval($client_id));
    exit;
} catch (Exception $ex) {
    error_log('save_step13 error: ' . $ex->getMessage());
    $_SESSION['workflow_errors'] = ['Server error. Please try again later.'];
    header('Location: ../workflow.php?step=13&client_id=' . intval($client_id));
    exit;
}
