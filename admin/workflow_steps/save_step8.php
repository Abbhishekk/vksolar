<?php
// admin/workflow_steps/save_step8.php
session_start();
ini_set('display_errors',1); error_reporting(E_ALL);
require_once __DIR__ . '/../connect/db.php'; // $conn (mysqli)

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("HTTP/1.1 405 Method Not Allowed");
    echo "This endpoint accepts POST only.";
    exit;
}
function clean($v){ return trim($v); }

$client_id = isset($_POST['client_id']) ? intval($_POST['client_id']) : 0;
$bank_loan_status = isset($_POST['bank_loan_status']) ? clean($_POST['bank_loan_status']) : '';

// normalize fields
$bank_name = clean($_POST['bank_name'] ?? '');
$account_number = clean($_POST['account_number'] ?? '');
$ifsc_code = clean($_POST['ifsc_code'] ?? '');
$jan_samartha_application_no = clean($_POST['jan_samartha_application_no'] ?? '');
$loan_amount = isset($_POST['loan_amount']) && $_POST['loan_amount'] !== '' ? (float)$_POST['loan_amount'] : 0.00;
$first_installment_amount = isset($_POST['first_installment_amount']) && $_POST['first_installment_amount'] !== '' ? (float)$_POST['first_installment_amount'] : 0.00;
$second_installment_amount = isset($_POST['second_installment_amount']) && $_POST['second_installment_amount'] !== '' ? (float)$_POST['second_installment_amount'] : 0.00;
$remaining_amount = isset($_POST['remaining_amount']) && $_POST['remaining_amount'] !== '' ? (float)$_POST['remaining_amount'] : 0.00;

$errors = [];
if (!$client_id) $errors[] = 'Client not specified. Please select a client.';
if ($bank_loan_status === '') $errors[] = 'Please select whether bank loan is required.';

if ($bank_loan_status === 'yes') {
    if ($bank_name === '') $errors[] = 'Bank name is required.';
    if ($account_number === '') $errors[] = 'Account number is required.';
    if ($ifsc_code === '') $errors[] = 'IFSC code is required.';
    if ($loan_amount <= 0) $errors[] = 'Loan amount must be greater than zero.';
    // basic IFSC format check
    if ($ifsc_code !== '' && !preg_match('/^[A-Za-z]{4}0[A-Za-z0-9]{6}$/', strtoupper($ifsc_code))) {
        $errors[] = 'IFSC code format appears invalid.';
    }
}

// If NO, we will clear the bank fields (set to empty / 0)
if (!empty($errors)) {
    $_SESSION['workflow_errors'] = $errors;
    header('Location: ../workflow.php?step=8&client_id=' . intval($client_id));
    exit;
}

// ensure DB
if (!isset($conn) || !($conn instanceof mysqli)) {
    error_log('save_step8: $conn not set or not mysqli');
    $_SESSION['workflow_errors'] = ['Server DB error.'];
    header('Location: ../workflow.php?step=8&client_id=' . intval($client_id));
    exit;
}

// verify client
$stmt = $conn->prepare("SELECT id FROM clients WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $client_id);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows === 0) {
    $_SESSION['workflow_errors'] = ['Selected client does not exist.'];
    header('Location: ../workflow.php?step=8');
    exit;
}
$stmt->close();

// prepare final values (if no loan, clear fields)
if ($bank_loan_status !== 'yes') {
    $bank_name = '';
    $account_number = '';
    $ifsc_code = '';
    $jan_samartha_application_no = '';
    $loan_amount = 0.00;
    $first_installment_amount = 0.00;
    $second_installment_amount = 0.00;
    $remaining_amount = 0.00;
}

try {
    $sql = "UPDATE clients SET bank_loan_status = ?, bank_name = ?, account_number = ?, ifsc_code = ?, jan_samartha_application_no = ?, loan_amount = ?, first_installment_amount = ?, second_installment_amount = ?, remaining_amount = ?, updated_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) throw new Exception('Prepare failed: ' . $conn->error);

    // types: 4 strings (bank_loan_status, bank_name, account_number, ifsc), 1 string jan_no, 4 doubles, 1 int
    // We'll bind as: ssss s d d d d i  -> combined: 'ssss s d d d d i' => 'sssssddddi'
    $stmt->bind_param('sssssddddi',
        $bank_loan_status,
        $bank_name,
        $account_number,
        $ifsc_code,
        $jan_samartha_application_no,
        $loan_amount,
        $first_installment_amount,
        $second_installment_amount,
        $remaining_amount,
        $client_id
    );

    if (!$stmt->execute()) throw new Exception('Execute failed: ' . $stmt->error);
    $stmt->close();

    $_SESSION['workflow_success'] = 'Bank loan details saved successfully.';
    header('Location: ../workflow.php?step=9&client_id=' . intval($client_id));
    exit;
} catch (Exception $ex) {
    error_log('save_step8 error: ' . $ex->getMessage());
    $_SESSION['workflow_errors'] = ['Server error. Please try again later.'];
    header('Location: ../workflow.php?step=8&client_id=' . intval($client_id));
    exit;
}
