<?php
// admin/inventory/supplier_delete.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) { header('Location: suppliers.php'); exit; }

try {
    $stmt = $conn->prepare("DELETE FROM suppliers WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    $_SESSION['sup_flash'] = 'Supplier deleted';
} catch (Exception $e) {
    $_SESSION['sup_flash'] = 'Delete failed';
}

header('Location: suppliers.php');
exit;
