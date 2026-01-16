<?php
session_start();
require_once 'connect/auth_middleware.php';
$auth->requireAuth();
$auth->requirePermission('quotation_management', 'create');

// Collect all form fields (same names as your inputs)
$customer_name = $_POST['customer_name'] ?? '';
$project_type = $_POST['project_type'] ?? '';
$capacity = $_POST['capacity'] ?? 0;
$price_per_kw = $_POST['price_per_kw'] ?? 0;
$tax_percent = $_POST['tax_percent'] ?? 18;

$subtotal = $capacity * $price_per_kw;
$tax = ($subtotal * $tax_percent) / 100;
$total = $subtotal + $tax;

// Save data to session for PDF
$_SESSION['quote_data'] = compact(
    'customer_name',
    'project_type',
    'capacity',
    'price_per_kw',
    'tax_percent',
    'subtotal',
    'tax',
    'total'
);

if ($_POST['action'] === 'pdf') {
    header("Location: generate_pdf.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Quotation Preview</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<style>
body { background: #fff; margin: 40px; }
.table th, .table td { vertical-align: middle; }
</style>
</head>
<body>

<div class="container">
  <div class="text-center mb-4">
    <h2>Solar Quotation</h2>
    <p><strong>Date:</strong> <?= date('d-m-Y') ?></p>
  </div>

  <h5>Customer Details</h5>
  <table class="table table-bordered">
    <tr><th>Name</th><td><?= htmlspecialchars($customer_name) ?></td></tr>
    <tr><th>Project Type</th><td><?= htmlspecialchars($project_type) ?></td></tr>
  </table>

  <h5>Quotation Summary</h5>
  <table class="table table-bordered">
    <tr><th>Capacity (kW)</th><td><?= $capacity ?></td></tr>
    <tr><th>Price per kW (₹)</th><td><?= number_format($price_per_kw, 2) ?></td></tr>
    <tr><th>Subtotal</th><td>₹<?= number_format($subtotal, 2) ?></td></tr>
    <tr><th>Tax (<?= $tax_percent ?>%)</th><td>₹<?= number_format($tax, 2) ?></td></tr>
    <tr class="table-success"><th>Total</th><td><strong>₹<?= number_format($total, 2) ?></strong></td></tr>
  </table>

  <div class="text-center mt-4">
    <button class="btn btn-primary" onclick="window.print()">Print</button>
  </div>
</div>

</body>
</html>
