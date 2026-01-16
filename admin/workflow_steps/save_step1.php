<?php
// workflow_steps/save_step1.php
session_start();

// Adjust path to your DB include if needed. This file should set $conn (mysqli).
require_once __DIR__ . '/../connect/db.php'; // <-- uses your project's db.php which provides $conn

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../workflow.php?step=1');
    exit;
}

// simple sanitizer
function clean($v) {
    return trim($v);
}

$name = clean($_POST['name'] ?? '');
$consumer_number = clean($_POST['consumer_number'] ?? '');
$billing_unit = clean($_POST['billing_unit'] ?? '');
$location_url = clean($_POST['location_url'] ?? '');
$client_id = isset($_POST['client_id']) ? intval($_POST['client_id']) : 0;

// Basic validation
$errors = [];
if ($name === '') $errors[] = 'Name is required.';
if ($consumer_number === '') $errors[] = 'Consumer number is required.';
if ($location_url !== '' && !filter_var($location_url, FILTER_VALIDATE_URL)) {
    $errors[] = 'Location URL is not valid.';
}

if (!empty($errors)) {
    $_SESSION['workflow_errors'] = $errors;
    // redirect back to step 1 and preserve client_id (if provided)
    $redir = '../workflow.php?step=1' . ($client_id ? '&client_id=' . $client_id : '');
    header('Location: ' . $redir);
    exit;
}

try {
    if ($client_id > 0) {
        // UPDATE existing client
        $sql = "UPDATE clients
                SET name = ?, consumer_number = ?, billing_unit = ?, location = ?, updated_at = NOW()
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) throw new Exception($conn->error);
        $stmt->bind_param('ssssi', $name, $consumer_number, $billing_unit, $location_url, $client_id);
        if (!$stmt->execute()) throw new Exception($stmt->error);
        $stmt->close();

        $_SESSION['workflow_success'] = 'Client updated successfully.';
    } else {
        // INSERT new client (id is AUTO_INCREMENT)
        $sql = "INSERT INTO clients (name, consumer_number, billing_unit, location, created_at)
                VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) throw new Exception($conn->error);
        $stmt->bind_param('ssss', $name, $consumer_number, $billing_unit, $location_url);
        if (!$stmt->execute()) throw new Exception($stmt->error);
        $client_id = $stmt->insert_id;
        $stmt->close();

        $_SESSION['workflow_success'] = 'Client created successfully.';
    }

    // On success redirect to step=2 with client_id so Step 2 loads for the selected client
    header('Location: ../workflow.php?step=2&client_id=' . intval($client_id));
    exit;

} catch (Exception $e) {
    error_log('save_step1 error: ' . $e->getMessage());
    $_SESSION['workflow_errors'] = ['Server error. Please try again later.'];
    $redir = '../workflow.php?step=1' . ($client_id ? '&client_id=' . $client_id : '');
    header('Location: ' . $redir);
    exit;
}
