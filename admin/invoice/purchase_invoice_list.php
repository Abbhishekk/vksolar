<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../connect/db.php';
require_once __DIR__ . '/../connect/auth_middleware.php';

$auth->requirePermission('invoice_management', 'view');

$title = 'purchase_invoice_list';

$role = $_SESSION['role'] ?? '';
$employee_id = $_SESSION['employee_id'] ?? 0;

/* ================= FETCH PURCHASE ORDERS ================= */

$sql = "
SELECT 
    po.id,
    po.po_number,
    po.status,
    po.created_at,
    pm.manufacturer_name,
    pc.category_name,
    w.name AS warehouse_name,
    GROUP_CONCAT(p.name SEPARATOR ', ') AS product_names,
    SUM(poi.quantity) AS total_qty
FROM purchase_orders po
LEFT JOIN product_manufacturers pm ON pm.id = po.manufacturer_id
LEFT JOIN product_categories pc ON pc.id = pm.category_id
LEFT JOIN warehouses w ON w.id = po.warehouse_id
LEFT JOIN purchase_order_items poi ON poi.purchase_order_id = po.id
LEFT JOIN products p ON p.id = poi.product_id
WHERE 1=1
";


$params = [];
$types = '';

if ($role === 'warehouse_staff') {
    // show only their warehouse
    $sql .= " AND po.warehouse_id IN (
                SELECT warehouse_id 
                FROM warehouse_employees 
                WHERE employee_id = ?
              )";
    $params[] = $employee_id;
    $types .= 'i';
}

$sql .= " GROUP BY po.id ORDER BY po.created_at DESC";

$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Purchase Orders</title>
<?php require_once __DIR__ . '/../include/head2.php'; ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
.badge-pending { background:#ffc107; }
.badge-approved { background:#28a745; }
.badge-rejected { background:#dc3545; }
</style>
</head>
<body>

<?php include __DIR__ . '/../include/sidebar.php'; ?>
<div id="main-content">
<?php include __DIR__ . '/../include/navbar.php'; ?>

<main class="container-fluid py-4">

<div class="d-flex justify-content-between align-items-center mb-4">
<h4 class="mb-0">📦 Purchase Orders</h4>
<a href="purchase_invoice_create.php" class="btn btn-primary">
+ Create Purchase Order
</a>
</div>

<div class="card shadow">
<div class="card-body p-0">

<table class="table table-bordered table-hover mb-0">
<thead class="table-light">
<tr>
<th width="140">PO Number</th>
<th>Category</th>
<th>Products</th>
<th width="100">Total Qty</th>
<th>Manufacturer</th>
<th>Warehouse</th>
<th width="120">Status</th>
<th width="160">Created</th>
<th width="180">Action</th>
</tr>
</thead>

<tbody>

<?php if (!$orders): ?>
<tr>
<td colspan="7" class="text-center text-muted">
No purchase orders found.
</td>
</tr>
<?php endif; ?>

<?php foreach ($orders as $o): ?>

<?php
$statusClass = 'badge-pending';
if ($o['status'] === 'approved') $statusClass = 'badge-approved';
if ($o['status'] === 'rejected') $statusClass = 'badge-rejected';
?>

<tr>
<td><?= htmlspecialchars($o['po_number']) ?></td>
<td><?= htmlspecialchars($o['category_name'] ?? '-') ?></td>
<td style="max-width:250px;"><?= htmlspecialchars($o['product_names']) ?></td>
<td class="text-center fw-bold"><?= $o['total_qty'] ?? 0 ?></td>
<td><?= htmlspecialchars($o['manufacturer_name']) ?></td>
<td><?= htmlspecialchars($o['warehouse_name']) ?></td>
<td>
<span class="badge <?= $statusClass ?>">
<?= ucfirst($o['status']) ?>
</span>
</td>
<td><?= date('d M Y H:i', strtotime($o['created_at'])) ?></td>
<td>

<a href="purchase_invoice_view.php?id=<?= $o['id'] ?>"
class="btn btn-sm btn-outline-primary">
View
</a>

<?php if ($o['status'] === 'pending'): ?>
<a href="../warehousemanagement/purchase_approval.php?id=<?= $o['id'] ?>&return_to=invoice"
class="btn btn-sm btn-success">
Receive
</a>

<?php endif; ?>

</td>
</tr>

<?php endforeach; ?>

</tbody>
</table>

</div>
</div>

</main>
</div>
</body>
</html>
