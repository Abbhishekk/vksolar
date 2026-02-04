<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../connect/db.php';
require_once __DIR__ . '/../connect/auth_middleware.php';

/* ================= ACCESS CONTROL ================= */
$auth->requireAnyRole(['warehouse_staff']);

$warehouse_id = $_SESSION['warehouse_id'] ?? 0;
if ($warehouse_id <= 0) {
    die('Warehouse not assigned');
}

$title = 'purchase_order_requests';

/* ================= FETCH PENDING PURCHASE ORDERS ================= */
$stmt = $conn->prepare("
    SELECT 
        po.id,
        po.po_number,
        po.created_at,
        u.full_name AS created_by
    FROM purchase_orders po
    JOIN users u ON u.id = po.created_by
    WHERE po.status = 'pending'
      AND po.warehouse_id = ?
    ORDER BY po.id DESC
");
$stmt->bind_param("i", $warehouse_id);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Pending Purchase Orders</title>

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
<div class="card-header bg-warning text-dark">
<h5 class="mb-0">Pending Purchase Order Requests</h5>
</div>

<div class="card-body p-0">

<table class="table table-bordered mb-0">
<thead class="table-light">
<tr>
<th>PO Number</th>
<th>Created By</th>
<th>Created At</th>
<th width="160">Action</th>
</tr>
</thead>
<tbody>

<?php if (empty($orders)): ?>
<tr>
<td colspan="4" class="text-center text-muted">No pending purchase orders</td>
</tr>
<?php endif; ?>

<?php foreach ($orders as $po): ?>
<tr>
<td><?= htmlspecialchars($po['po_number']) ?></td>
<td><?= htmlspecialchars($po['created_by']) ?></td>
<td><?= date('d-m-Y H:i', strtotime($po['created_at'])) ?></td>
<td>

<a href="purchase_order_view.php?id=<?= $po['id'] ?>"
   class="btn btn-sm btn-outline-primary">
View
</a>

<a href="purchase_approve.php?id=<?= $po['id'] ?>"
   class="btn btn-sm btn-success"
   onclick="return confirm('Approve this purchase order?')">
Approve
</a>

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
