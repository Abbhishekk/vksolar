<?php
// admin/warehousemanagement/warehouse_view.php


require_once __DIR__ . '/../connect/auth_middleware.php';
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php';

$auth->requirePermission('inventory_management', 'view');

$warehouse_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($warehouse_id <= 0) {
    header('Location: warehouses.php');
    exit;
}

/* ================= FETCH WAREHOUSE ================= */

$stmt = $conn->prepare("SELECT * FROM warehouses WHERE id=? LIMIT 1");
$stmt->bind_param("i",$warehouse_id);
$stmt->execute();
$warehouse = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$warehouse) {
    $_SESSION['inv_errors'] = ["Warehouse not found."];
    header('Location: warehouses.php');
    exit;
}

$warehouse_image = !empty($warehouse['image'])
    ? 'uploads/' . $warehouse['image']
    : null;

/* ================= FETCH STOCK ================= */

$stock_rows = [];

$stmt = $conn->prepare("
    SELECT ws.product_id,
           ws.quantity,
           p.name AS product_name,
           p.product_id AS product_code
    FROM warehouse_stock ws
    LEFT JOIN products p ON p.id = ws.product_id
    WHERE ws.warehouse_id = ?
    ORDER BY p.name ASC
");

$stmt->bind_param("i",$warehouse_id);
$stmt->execute();
$res = $stmt->get_result();
$stock_rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
$stmt->close();


/* ================= FETCH PENDING PURCHASE ORDERS ================= */

$stmt = $conn->prepare("
    SELECT 
        po.id,
        po.po_number,
        po.created_at,
        pm.manufacturer_name,
        SUM(poi.quantity) AS total_qty
    FROM purchase_orders po
    LEFT JOIN product_manufacturers pm ON pm.id = po.manufacturer_id
    LEFT JOIN purchase_order_items poi ON poi.purchase_order_id = po.id
    WHERE po.warehouse_id = ?
      AND po.status = 'pending'
    GROUP BY po.id
    ORDER BY po.created_at DESC
");

$stmt->bind_param("i", $warehouse_id);
$stmt->execute();
$pending_pos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Warehouse: <?= htmlspecialchars($warehouse['name']) ?></title>
<?php require_once __DIR__ . '/../include/head2.php'; ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
.warehouse-hero {
  border-radius: 12px;
  overflow: hidden;
  position: relative;
  height: 220px;
  background-color: #6c757d;
  background-position: center;
  background-size: cover;
  display:flex;
  align-items:flex-end;
  color:#fff;
  box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}
.warehouse-hero .meta {
  padding:20px;
  width:100%;
  background: linear-gradient(to top, rgba(0,0,0,0.6), rgba(0,0,0,0));
}
.warehouse-hero h2 { margin:0; }
</style>
</head>
<body>

<?php include __DIR__ . '/../include/sidebar.php'; ?>
<div id="main-content">
<?php include __DIR__ . '/../include/navbar.php'; ?>

<main class="container py-4">

<div class="warehouse-hero"
<?php if ($warehouse_image): ?>
style="background-image:url('<?= htmlspecialchars($warehouse_image) ?>');"
<?php endif; ?>
>
<div class="meta">
<h2><?= htmlspecialchars($warehouse['name']) ?></h2>
<div class="small">
<?= htmlspecialchars($warehouse['code']) ?>
<?php if (!empty($warehouse['city'])): ?>
 • <?= htmlspecialchars($warehouse['city']) ?>
<?php endif; ?>
</div>

<div class="mt-2">
<a href="warehouses.php?edit=<?= $warehouse_id ?>"
class="btn btn-sm btn-light">Edit</a>

<!--<a href="warehouses.php?delete=<?= $warehouse_id ?>"-->
<!--class="btn btn-sm btn-danger"-->
<!--onclick="return confirm('Delete this warehouse?')">-->
<!--Delete-->
<!--</a>-->

<a href="stock_in.php?warehouse_id=<?= $warehouse_id ?>"
class="btn btn-sm btn-primary">
Add Stock
</a>
</div>

</div>
</div>

<!-- DETAILS CARD -->
<div class="card mt-4">
<div class="card-body">
<h5>Warehouse Details</h5>
<div class="row">
<div class="col-md-6">
<p><strong>Address:</strong><br>
<?= nl2br(htmlspecialchars($warehouse['address'])) ?></p>
<p><strong>State:</strong> <?= htmlspecialchars($warehouse['state']) ?></p>
<p><strong>Pincode:</strong> <?= htmlspecialchars($warehouse['pincode']) ?></p>
</div>
<div class="col-md-6">
<p><strong>Contact Person:</strong> <?= htmlspecialchars($warehouse['contact_name']) ?></p>
<p><strong>Contact Phone:</strong> <?= htmlspecialchars($warehouse['contact_phone']) ?></p>
<p><strong>Created At:</strong> <?= htmlspecialchars($warehouse['created_at']) ?></p>
</div>
</div>
</div>
</div>

<!-- STOCK CARD -->
<div class="card mt-4">
<div class="card-body">
<h5>Current Stock</h5>

<?php if (!empty($stock_rows)): ?>
<div class="table-responsive">
<table class="table table-bordered table-hover">
<thead class="table-light">
<tr>
<th>Product</th>
<th>Product Code</th>
<th class="text-end">Quantity</th>
<th width="150">Action</th>
</tr>
</thead>
<tbody>
<?php foreach ($stock_rows as $row): ?>
<tr>
<td><?= htmlspecialchars($row['product_name']) ?></td>
<td><?= htmlspecialchars($row['product_code']) ?></td>
<td class="text-end"><?= $row['quantity'] ?></td>
<td>
<a href="../productmanagement/products.php"
class="btn btn-sm btn-outline-primary">
View Product
</a>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php else: ?>
<div class="alert alert-info">
No stock available in this warehouse.
</div>
<?php endif; ?>

</div>
</div>

<!-- PENDING PURCHASE ORDERS -->
<div class="card mt-4">
<div class="card-body">
<h5>Pending Purchase Orders</h5>

<?php if (!empty($pending_pos)): ?>
<div class="table-responsive">
<table class="table table-bordered table-hover">
<thead class="table-light">
<tr>
<th width="160">PO Number</th>
<th>Manufacturer</th>
<th width="120" class="text-center">Total Qty</th>
<th width="160">Date</th>
<th width="140">Action</th>
</tr>
</thead>
<tbody>

<?php foreach ($pending_pos as $po): ?>
<tr>
<td><?= htmlspecialchars($po['po_number']) ?></td>
<td><?= htmlspecialchars($po['manufacturer_name']) ?></td>
<td class="text-center fw-bold">
<?= $po['total_qty'] ?? 0 ?>
</td>
<td><?= date('d M Y H:i', strtotime($po['created_at'])) ?></td>
<td>
<a href="purchase_approval.php?id=<?= $po['id'] ?>&return_to=warehouse&warehouse_id=<?= $warehouse_id ?>"
class="btn btn-sm btn-success">
Receive
</a>
</td>
</tr>
<?php endforeach; ?>

</tbody>
</table>
</div>
<?php else: ?>
<div class="alert alert-info mb-0">
No pending purchase orders for this warehouse.
</div>
<?php endif; ?>

</div>
</div>


</main>
</div>
</body>
</html>
