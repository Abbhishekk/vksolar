<?php
// admin/invoice/invoice_payments.php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../connect/db.php';
require_once __DIR__ . '/../connect/auth_middleware.php';
$auth->requireAuth();
$auth->requirePermission('invoice_management', 'view');

$title = 'invoice_payments';

$invoice_id = (int)($_GET['invoice_id'] ?? 0);
if (!$invoice_id) {
    die('Invoice not selected');
}

/* ================= FETCH INVOICE ================= */
$inv = $conn->prepare("
    SELECT id, invoice_no, total, status
    FROM invoices
    WHERE id = ?
    LIMIT 1
");
$inv->bind_param('i', $invoice_id);
$inv->execute();
$invoice = $inv->get_result()->fetch_assoc();
$inv->close();
// print_r($invoice);

if (!$invoice) {
    die('Invoice not found');
}

if ($invoice['status'] !== 'final') {
    die('Payments allowed only after invoice is finalized.');
}

/* ================= FETCH PAYMENTS ================= */
$payments = $conn->prepare("
    SELECT *
    FROM invoice_payments
    WHERE invoice_id = ?
    ORDER BY payment_date DESC
");
$payments->bind_param('i', $invoice_id);
$payments->execute();
$paymentRows = $payments->get_result()->fetch_all(MYSQLI_ASSOC);
$payments->close();

/* ================= CALCULATIONS ================= */
$total_paid = 0;
foreach ($paymentRows as $p) {
    $total_paid += $p['amount'];
}
$balance = $invoice['total'] - $total_paid;
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Invoice Payments</title>
<?php require_once __DIR__ . '/../include/head2.php'; ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
<?php $cwd = getcwd(); chdir(__DIR__ . '/..'); include 'include/sidebar.php'; chdir($cwd); ?>
<div id="main-content">
<?php $cwd = getcwd(); chdir(__DIR__ . '/..'); include 'include/navbar.php'; chdir($cwd); ?>
<main class="container py-4">

<h4>💰 Payments for Invoice: <strong><?= htmlspecialchars($invoice['invoice_no']) ?></strong></h4>

<div class="row g-3 mb-4">
    <!-- Total -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0 text-center h-100">
            <div class="card-body">
                <div class="text-uppercase small text-muted fw-semibold mb-1">
                    Total Amount
                </div>
                <div class="fs-4 fw-bold text-primary">
                    ₹<?= number_format($invoice['total'], 2) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Paid -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0 text-center h-100">
            <div class="card-body">
                <div class="text-uppercase small text-muted fw-semibold mb-1">
                    Paid Amount
                </div>
                <div class="fs-4 fw-bold text-success">
                    ₹<?= number_format($total_paid, 2) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Balance -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0 text-center h-100">
            <div class="card-body">
                <div class="text-uppercase small text-muted fw-semibold mb-1">
                    Balance Due
                </div>
                <div class="fs-4 fw-bold text-danger">
                    ₹<?= number_format($balance, 2) ?>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- ADD PAYMENT -->
<?php if ($balance > 0): ?>
<div class="card mb-4">
  <div class="card-header bg-success text-white">Add Payment</div>
  <div class="card-body">
    <form method="post" action="invoice_payment_save">
      <input type="hidden" name="invoice_id" value="<?= $invoice_id ?>">

      <div class="row">
        <div class="col-md-4 mb-2">
          <label>Payment Date</label>
          <input type="date" name="payment_date" class="form-control" required>
        </div>

        <div class="col-md-4 mb-2">
          <label>Amount</label>
          <input type="number" step="0.01" max="<?= $balance ?>" name="amount" class="form-control" required>
        </div>

        <div class="col-md-4 mb-2">
          <label>Mode</label>
          <select name="payment_mode" class="form-select" required>
            <option value="cash">Cash</option>
            <option value="upi">UPI</option>
            <option value="bank">Bank Transfer</option>
            <option value="cheque">Cheque</option>
          </select>
        </div>
      </div>

      <div class="mb-2">
        <label>Reference / Note</label>
        <textarea name="note" class="form-control" rows="2"></textarea>
      </div>

      <button class="btn btn-success">Save Payment</button>
    </form>
  </div>
</div>
<?php endif; ?>

<!-- PAYMENT HISTORY -->
<h5>Payment History</h5>

<table class="table table-bordered table-sm">
<thead class="table-light">
<tr>
  <th>Date</th>
  <th>Amount</th>
  <th>Mode</th>
  <th>Note</th>
</tr>
</thead>
<tbody>
<?php if (!$paymentRows): ?>
<tr><td colspan="4" class="text-center text-muted">No payments yet</td></tr>
<?php endif; ?>

<?php foreach ($paymentRows as $p): ?>
<tr>
  <td><?= htmlspecialchars($p['payment_date']) ?></td>
  <td>₹<?= number_format($p['amount'], 2) ?></td>
  <td><?= ucfirst($p['payment_mode']) ?></td>
  <td><?= htmlspecialchars($p['note'] ?? '') ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<a href="invoice_view.php?id=<?= $invoice_id ?>" class="btn btn-outline-secondary mt-3">⬅ Back to Invoice</a>

</main>
</div>
</body>
</html>
