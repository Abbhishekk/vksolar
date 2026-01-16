<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php';
require_once __DIR__ . '/../connect/auth_middleware.php';
$auth->requireAuth();
$auth->requirePermission('invoice_management', 'delete');

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: invoices.php');
    exit;
}

/* Fetch invoice */
$stmt = $conn->prepare("
    SELECT status 
    FROM invoices 
    WHERE id = ? 
    LIMIT 1
");
$stmt->bind_param('i', $id);
$stmt->execute();
$invoice = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$invoice) {
    $_SESSION['inv_error'] = 'Invoice not found.';
    header('Location: invoices.php');
    exit;
}

/* Prevent delete if finalized */
if ($invoice['status'] !== 'draft') {
    $_SESSION['inv_error'] = 'Finalized invoice cannot be deleted.';
    header('Location: invoices.php');
    exit;
}


$conn->begin_transaction();

try {
    // delete invoice items
    $stmt = $conn->prepare("DELETE FROM invoice_items WHERE invoice_id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();

    // delete invoice
    $stmt = $conn->prepare("DELETE FROM invoices WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();

    $conn->commit();
    $_SESSION['inv_success'] = 'Invoice deleted successfully.';

} catch (Throwable $e) {
    $conn->rollback();
    $_SESSION['inv_error'] = 'Failed to delete invoice.';
}

$_SESSION['inv_success'] = 'Draft invoice deleted successfully.';
header('Location: invoices.php');
exit;
