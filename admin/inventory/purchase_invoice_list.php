<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once '../connect/db.php';
require_once '../connect/auth_middleware.php';
$auth->requirePermission('invoice_management','view');

$title = 'purchase_order_list';

/* ================= FETCH PURCHASE ORDERS ================= */
$list = $conn->query("
    SELECT 
        po.id,
        po.po_number,
        po.status,
        po.created_at,
        w.name AS warehouse,
        m.name AS manufacturer
    FROM purchase_orders po
    JOIN warehouses w ON w.id = po.warehouse_id
    JOIN product_manufacturers m ON m.id = po.manufacturer_id
    ORDER BY po.id DESC
")->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Purchase Orders</title>

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
<div class="card-header bg-primary text-white">
<h5 class="mb-0">Purchase Orders</h5>
</div>

<div class="card-body p-0">

<table class="table table-bordered table-striped mb-0">
<thead class="table-light">
<tr>
<th>PO Number</th>
<th>Manufacturer</th>
<th>Warehouse</th>
<th>Status</th>
<th>Created At</th>
<th width="120">Action</th>
</tr>
</thead>
<tbody>

<?php if (empty($list)): ?>
<tr>
<td colspan="6" class="text-center text-muted">No purchase orders found</td>
</tr>
<?php endif; ?>

<?php foreach ($list as $po): ?>
<tr>
<td><?= htmlspecialchars($po['po_number']) ?></td>
<td><?= htmlspecialchars($po['manufacturer']) ?></td>
<td><?= htmlspecialchars($po['warehouse']) ?></td>
<td>
<?php
$badge = match($po['status']) {
    'pending'  => 'warning',
    'approved' => 'success',
    'rejected' => 'danger',
    default    => 'secondary'
};
?>
<span class="badge bg-<?= $badge ?>">
<?= strtoupper($po['status']) ?>
</span>
</td>
<td><?= date('d-m-Y', strtotime($po['created_at'])) ?></td>
<td>

<a href="purchase_order_view.php?id=<?= $po['id'] ?>"
   class="btn btn-sm btn-outline-primary">
View
</a>

<?php if ($po['status'] === 'pending' && $auth->checkAnyRole(['warehouse_staff'])): ?>
<a href="admin/inventory/purchase_approve.php?id=<?= $po['id'] ?>"
   class="btn btn-sm btn-success"
   onclick="return confirm('Approve this purchase order?')">
Approve
</a>
<?php endif; ?>

</td>
</tr>
<?php endforeach; ?>

</tbody>
</table>

</div>
</div>

</div>
</div>
</body>
</html>
