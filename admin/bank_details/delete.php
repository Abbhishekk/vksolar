<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../connect/db.php';
require_once __DIR__ . '/../connect/auth_middleware.php';

$auth->requireAuth();
$auth->requireAnyRole(['super_admin', 'admin']);
$auth->checkPermission('bank_details_management', 'delete');

/* =========================
   VALIDATE ID
========================= */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Invalid bank reference');
}

$bank_id = (int) $_GET['id'];

/* =========================
   SOFT DELETE (DEACTIVATE)
========================= */
$stmt = $conn->prepare("
    UPDATE company_bank_details 
    SET is_active = 0 
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $bank_id);

if ($stmt->execute()) {
    header("Location: index.php?deleted=1");
    exit;
} else {
    die('Failed to deactivate bank');
}
