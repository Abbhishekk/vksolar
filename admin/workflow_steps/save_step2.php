<?php
// workflow_steps/save_step2.php
session_start();

// include DB connection
require_once __DIR__ . '/../connect/db.php'; // adjust if your db path differs

// Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../workflow.php?step=2');
    exit;
}

// helper sanitize
function clean($v) {
    return trim($v);
}

$client_id = isset($_POST['client_id']) ? intval($_POST['client_id']) : 0;
$adhar = clean($_POST['adhar'] ?? '');
$mobile = clean($_POST['mobile'] ?? '');
$email = clean($_POST['email'] ?? '');
$district = clean($_POST['district'] ?? '');
$block = clean($_POST['block'] ?? '');
$taluka = clean($_POST['taluka'] ?? '');
$pincode = clean($_POST['pincode'] ?? '');
$village = clean($_POST['village'] ?? '');

// Validate
$errors = [];
if (!$client_id) $errors[] = 'Client not specified. Please select a client.';
if ($mobile === '' && $email === '') $errors[] = 'Please provide at least a mobile number or an email address.';

if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email address.';
}

// Optional: normalize mobile (strip non-digits)
if ($mobile !== '') {
    $digits = preg_replace('/\D+/', '', $mobile);
    if (strlen($digits) < 7) { // basic sanity check
        $errors[] = 'Mobile number seems too short.';
    } else {
        $mobile = $digits;
    }
}

if (!empty($errors)) {
    $_SESSION['workflow_errors'] = $errors;
    $redir = '../workflow.php?step=2&client_id=' . intval($client_id);
    header('Location: ' . $redir);
    exit;
}

// Ensure client exists
$stmt = $conn->prepare("SELECT id FROM clients WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $client_id);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows === 0) {
    $_SESSION['workflow_errors'] = ['Selected client does not exist.'];
    header('Location: ../workflow.php?step=2');
    exit;
}
$stmt->close();

// Perform UPDATE
try {
    $sql = "UPDATE clients SET adhar = ?, mobile = ?, email = ?, district = ?, block = ?, taluka = ?,pincode = ?, village = ?, updated_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) throw new Exception('Prepare failed: ' . $conn->error);
    $stmt->bind_param('isssssisi', $adhar, $mobile, $email, $district, $block, $taluka,$pincode, $village, $client_id);
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }
    $stmt->close();

    $_SESSION['workflow_success'] = 'Communication & Address updated successfully.';
    header('Location: ../workflow.php?step=3&client_id=' . intval($client_id));
    exit;

} catch (Exception $ex) {
    error_log('save_step2 error: ' . $ex->getMessage());
    $_SESSION['workflow_errors'] = ['Server error. Please try again later.'];
    header('Location: ../workflow.php?step=2&client_id=' . intval($client_id));
    exit;
}
