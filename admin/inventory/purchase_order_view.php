<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once '../connect/db.php';
require_once '../connect/auth_middleware.php';
$auth->requirePermission('invoice_management','view');

$title = 'purchase_order_view';

$po_id = (int)($_GET['id'] ?? 0);
if ($po_id <= 0) {
    die('Invalid Purchase Order ID');
}

/* ================= FETCH PURCHASE ORDER ================= */
$stmt = $conn->prepare("
    SELECT 
        po.*,
        w.name AS warehouse,
        m.name AS manufacturer,
        u.full_name AS created_by_name
    FROM purchase_orders po
    JOIN warehouses w ON w.id = po.warehouse_id
    JOIN product_manufacturers m ON m.id = po.manufacturer_id
    LEFT JOIN users u ON u.id = po.created_by
    WHERE po.id = ?
");
$stmt->bind_param("i", $po_id);
$stmt->execute();
$po = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$po) {
    die('Purchase Order not found');
}

/* ================= FETCH ITEMS ================= */
$stmt = $conn->prepare("
    SELECT 
        poi.*,
        p.name AS product_name,
        p.sku
    FROM purchase_order_items poi
    JOIN products p ON p.id = poi.product_id
    WHERE poi.purchase_order_id = ?
");
$stmt->bind_param("i", $po_id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

/* ================= TOTAL ================= */
$grand_total = 0;
foreach ($items as $it) {
    $grand_total += $it['total'];
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Purchase Order <?= htmlspecialchars($po['po_number']) ?></title>

<?php require_once __DIR__ . '/../include/head2.php'; ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
<?php
$cwd = getcwd(); 
chdir(__DIR__ . '/..'); 
include 'include/sidebar.php'; 
chdir($cwd);
?>
<div id="main-content">
<?php
$cwd = getcwd(); 
chdir(__DIR__ . '/..'); 
include 'include/navbar.php'; 
chdir($cwd);
?>

<div class="container-fluid py-4">

<div class="card shadow">
<div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
<h5 class="mb-0">Purchase Order Details</h5>

<span class="badge bg-<?=
    $po['status'] === 'pending'  ? 'warning' :
    ($po['status'] === 'approved' ? 'success' : 'danger')
?>">
<?= strtoupper($po['status']) ?>
</span>
</div>

<div class="card-body">

<!-- HEADER INFO -->
<div class="row mb-3">
  <div class="col-md-4">
    <strong>PO Number:</strong><br>
    <?= htmlspecialchars($po['po_number']) ?>
  </div>

  <div class="col-md-4">
    <strong>Manufacturer:</strong><br>
    <?= htmlspecialchars($po['manufacturer']) ?>
  </div>

  <div class="col-md-4">
    <strong>Warehouse:</strong><br>
    <?= htmlspecialchars($po['warehouse']) ?>
  </div>
</div>

<div class="row mb-3">
  <div class="col-md-4">
    <strong>Created By:</strong><br>
    <?= htmlspecialchars($po['created_by_name'] ?? '-') ?>
  </div>

  <div class="col-md-4">
    <strong>Created At:</strong><br>
    <?= date('d-m-Y H:i', strtotime($po['created_at'])) ?>
  </div>

  <div class="col-md-4">
    <strong>Approved At:</strong><br>
    <?= $po['approved_at'] ? date('d-m-Y H:i', strtotime($po['approved_at'])) : '-' ?>
  </div>
</div>

<!-- ITEMS TABLE -->
<div class="table-responsive">
<table class="table table-bordered">
<thead class="table-light">
<tr>
<th>#</th>
<th>Product</th>
<th>SKU</th>
<th class="text-end">Qty</th>
<th class="text-end">Rate</th>
<th class="text-end">Total</th>
</tr>
</thead>
<tbody>

<?php if (empty($items)): ?>
<tr>
<td colspan="6" class="text-center text-muted">No items</td>
</tr>
<?php endif; ?>

<?php foreach ($items as $i => $it): ?>
<tr>
<td><?= $i + 1 ?></td>
<td><?= htmlspecialchars($it['product_name']) ?></td>
<td><?= htmlspecialchars($it['sku']) ?></td>
<td class="text-end"><?= number_format($it['quantity'], 2) ?></td>
<td class="text-end"><?= number_format($it['rate'], 2) ?></td>
<td class="text-end"><?= number_format($it['total'], 2) ?></td>
</tr>
<?php endforeach; ?>

</tbody>

<tfoot>
<tr>
<th colspan="5" class="text-end">Grand Total</th>
<th class="text-end"><?= number_format($grand_total, 2) ?></th>
</tr>
</tfoot>
</table>
</div>

<!-- ACTIONS -->
<div class="d-flex justify-content-between mt-3">

<a href="purchase_order_list.php" class="btn btn-outline-secondary">
← Back to List
</a>

<?php if (
    $po['status'] === 'pending' &&
    $auth->checkAnyRole(['warehouse_staff'])
): ?>
<a href="/admin/inventory/purchase_approve.php?id=<?= $po['id'] ?>"
   class="btn btn-success"
   onclick="return confirm('Approve this purchase order?')">
✔ Approve Purchase Order
</a>
<?php endif; ?>

</div>

</div>
</div>

</div>
</div>
</body>
</html>
