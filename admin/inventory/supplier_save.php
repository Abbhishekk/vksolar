<?php
// admin/inventory/supplier_save.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php';

function clean($v){ return trim($v); }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: suppliers.php'); exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$name = clean($_POST['name'] ?? '');
$contact_person = clean($_POST['contact_person'] ?? '');
$phone = clean($_POST['phone'] ?? '');
$email = clean($_POST['email'] ?? '');
$address = $_POST['address'] ?? null;

if ($name === '') {
    $_SESSION['sup_flash'] = 'Supplier name required';
    header('Location: supplier_form.php' . ($id ? '?id=' . $id : ''));
    exit;
}

try {
    if ($id) {
        $stmt = $conn->prepare("UPDATE suppliers SET name=?, contact_person=?, phone=?, email=?, address=? WHERE id = ?");
        $stmt->bind_param('sssssi', $name, $contact_person, $phone, $email, $address, $id);
        $stmt->execute();
        $stmt->close();
        $_SESSION['sup_flash'] = 'Supplier updated';
    } else {
        $stmt = $conn->prepare("INSERT INTO suppliers (name, contact_person, phone, email, address) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('sssss', $name, $contact_person, $phone, $email, $address);
        $stmt->execute();
        $stmt->close();
        $_SESSION['sup_flash'] = 'Supplier created';
    }
} catch (Exception $e) {
    error_log('supplier_save: ' . $e->getMessage());
    $_SESSION['sup_flash'] = 'Server error';
}

header('Location: suppliers.php');
exit;
