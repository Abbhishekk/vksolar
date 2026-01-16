<?php
// admin/invoice/invoice_payment_save.php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../connect/db.php';

$invoice_id   = (int)($_POST['invoice_id'] ?? 0);
$payment_date = $_POST['payment_date'] ?? '';
$amount       = (float)($_POST['amount'] ?? 0);
$payment_mode = $_POST['payment_mode'] ?? '';
$note         = trim($_POST['note'] ?? '');
$user_id      = $_SESSION['user_id'] ?? null;

if (!$invoice_id || $amount <= 0) {
    die('Invalid payment data');
}

/* ================= INSERT PAYMENT ================= */
$stmt = $conn->prepare("
    INSERT INTO invoice_payments
    (invoice_id, payment_date, amount, payment_mode, note, created_at)
    VALUES (?, ?, ?, ?, ?, ?)
");
$stmt->bind_param(
    'isdssi',
    $invoice_id,
    $payment_date,
    $amount,
    $payment_mode,
    $note,
    $user_id
);
$stmt->execute();
$stmt->close();

/* ================= UPDATE INVOICE PAYMENT STATUS ================= */
$total = $conn->prepare("SELECT total FROM invoices WHERE id = ?");
$total->bind_param('i', $invoice_id);
$total->execute();
$invTotal = $total->get_result()->fetch_assoc()['total'];
$total->close();

$paid = $conn->prepare("SELECT COALESCE(SUM(amount),0) as paid FROM invoice_payments WHERE invoice_id = ?");
$paid->bind_param('i', $invoice_id);
$paid->execute();
$paidAmount = $paid->get_result()->fetch_assoc()['paid'];
$paid->close();

$status = ($paidAmount >= $invTotal) ? 'paid' : 'partial';

$upd = $conn->prepare("
    UPDATE invoices
    SET payment_status = ?
    WHERE id = ?
");
$upd->bind_param('si', $status, $invoice_id);
$upd->execute();
$upd->close();

header('Location: invoice_payments.php?invoice_id=' . $invoice_id);
exit;
