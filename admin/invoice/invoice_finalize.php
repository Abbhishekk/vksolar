<?php
// admin/invoice/invoice_finalize.php

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../connect/db.php';
require_once __DIR__ . '/../inventory/inventory_functions.php';
require_once __DIR__ . '/../connect/auth_middleware.php';
$auth->requireAuth();
$auth->requirePermission('invoice_management', 'edit');

$id = (int)($_GET['id'] ?? 0);
$user_id = $_SESSION['user_id'] ?? null;

if (!$id) {
    $_SESSION['inv_error'] = 'Invalid invoice.';
    header('Location: invoices.php');
    exit;
}

/* 🔎 Fetch invoice */
$stmt = $conn->prepare("
    SELECT i.id, i.status, i.invoice_no, i.invoice_type, i.reference_id, i.warehouse_id
    FROM invoices i
    WHERE i.id = ? 
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

/* ❌ Already finalized */
if ($invoice['status'] === 'final') {
    $_SESSION['inv_error'] = 'Invoice is already finalized.';
    header('Location: invoice_view.php?id=' . $id);
    exit;
}

$conn->begin_transaction();

try {
    /* Get invoice items for stock deduction */
    $items_stmt = $conn->prepare("
        SELECT product_id, quantity
        FROM invoice_items
        WHERE invoice_id = ?
    ");
    $items_stmt->bind_param('i', $id);
    $items_stmt->execute();
    $items = $items_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $items_stmt->close();

    /* Check stock availability and deduct */
    foreach ($items as $item) {
        $available = getWarehouseProductStock($conn, $item['product_id'], $invoice['warehouse_id']);
        if ($available < $item['quantity']) {
            throw new Exception("Insufficient stock for product ID {$item['product_id']}");
        }

        $result = adjustStock(
            $conn,
            $item['product_id'],
            $invoice['warehouse_id'],
            -$item['quantity'],
            'sale',
            "Invoice {$invoice['invoice_no']}",
            $user_id,
            $invoice['invoice_type'],
            $invoice['reference_id']
        );
        
        if (!$result) {
            throw new Exception("Failed to adjust stock for product ID {$item['product_id']}");
        }
    }

    /* ✅ Finalize invoice */
    $upd = $conn->prepare("
        UPDATE invoices 
        SET status = 'final'
        WHERE id = ?
    ");
    $upd->bind_param('i', $id);
    $upd->execute();
    $upd->close();

    $conn->commit();
    $_SESSION['inv_success'] = 'Invoice finalized successfully. Stock has been deducted.';

} catch (Throwable $e) {
    $conn->rollback();
    error_log('Invoice finalize error: ' . $e->getMessage());
    $_SESSION['inv_error'] = 'Failed to finalize invoice: ' . $e->getMessage();
}

header('Location: invoices.php');
exit;
