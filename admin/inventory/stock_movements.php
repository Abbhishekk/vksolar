<?php
// admin/inventory/stock_movements.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php';
require_once __DIR__ . '/../connect/auth_middleware.php';
$auth->requireAuth();
$auth->requirePermission('inventory_management', 'view');

$title = 'stock_movements';

$product_id   = (int)($_GET['product_id'] ?? 0);
$warehouse_id = (int)($_GET['warehouse_id'] ?? 0);

/* QUERY */
$sql = "
SELECT
  sm.*,
  p.name AS product_name,
  p.sku,
  wf.name AS from_warehouse,
  wt.name AS to_warehouse,
  c.name  AS client_name
FROM stock_movements sm
LEFT JOIN products p   ON p.id = sm.product_id
LEFT JOIN warehouses wf ON wf.id = sm.warehouse_from
LEFT JOIN warehouses wt ON wt.id = sm.warehouse_to
LEFT JOIN clients c ON (sm.reference_type='client' AND c.id = sm.reference_id)
WHERE 1=1
";

$params = [];
$types = '';

if ($product_id) {
    $sql .= " AND sm.product_id=?";
    $params[] = $product_id;
    $types .= 'i';
}
if ($warehouse_id) {
    $sql .= " AND (sm.warehouse_from=? OR sm.warehouse_to=?)";
    $params[] = $warehouse_id;
    $params[] = $warehouse_id;
    $types .= 'ii';
}

$sql .= " ORDER BY sm.created_at DESC LIMIT 500";

$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$products = $conn->query("SELECT id,name,sku FROM products ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$warehouses = $conn->query("SELECT id,name FROM warehouses ORDER BY name")->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Stock Movements</title>
<?php require_once __DIR__ . '/../include/head2.php'; ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
.badge-in{background:#28a745}
.badge-out{background:#dc3545}
.badge-transfer{background:#0d6efd}
.qty-pos{color:#28a745;font-weight:600}
.qty-neg{color:#dc3545;font-weight:600}
</style>
</head>
<body>

<?php
$cwd=getcwd(); chdir(__DIR__.'/..'); include 'include/sidebar.php'; chdir($cwd);
?>
<div id="main-content">
<?php
$cwd=getcwd(); chdir(__DIR__.'/..'); include 'include/navbar.php'; chdir($cwd);
?>


<main class="container-fluid py-4">

<h3>📦 Stock Movements</h3>

<form class="row g-2 mb-3">
  <div class="col-md-4">
    <select name="product_id" class="form-select">
      <option value="">All Products</option>
      <?php foreach($products as $p): ?>
        <option value="<?= $p['id'] ?>" <?= $product_id==$p['id']?'selected':'' ?>>
          <?= htmlspecialchars($p['name'].' ('.$p['sku'].')') ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="col-md-4">
    <select name="warehouse_id" class="form-select">
      <option value="">All Warehouses</option>
      <?php foreach($warehouses as $w): ?>
        <option value="<?= $w['id'] ?>" <?= $warehouse_id==$w['id']?'selected':'' ?>>
          <?= htmlspecialchars($w['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="col-md-2">
    <button class="btn btn-primary w-100">Filter</button>
  </div>
</form>

<table class="table table-sm table-bordered table-striped">
<thead class="table-light">
<tr>
  <th>Date</th>
  <th>Product</th>
  <th>From</th>
  <th>To</th>
  <th class="text-center">Qty</th>
  <th>Type</th>
  <th>Reference</th>
  <th>Note</th>
</tr>
</thead>
<tbody>

<?php if (!$rows): ?>
<tr><td colspan="8" class="text-center text-muted">No movements found</td></tr>
<?php endif; ?>

<?php foreach($rows as $r):
  $badge = 'badge-transfer';
  if ($r['quantity'] > 0) $badge = 'badge-in';
  if ($r['quantity'] < 0) $badge = 'badge-out';
?>
<tr>
  <td><?= $r['created_at'] ?></td>
  <td><?= htmlspecialchars($r['product_name'].' ('.$r['sku'].')') ?></td>
  <td><?= htmlspecialchars($r['from_warehouse'] ?? '—') ?></td>
  <td><?= htmlspecialchars($r['to_warehouse'] ?? '—') ?></td>
  <td class="text-center <?= $r['quantity']>=0?'qty-pos':'qty-neg' ?>">
    <?= $r['quantity']>0?'+':'' ?><?= $r['quantity'] ?>
  </td>
  <td><span class="badge <?= $badge ?>"><?= $r['movement_type'] ?></span></td>
  <td>
    <?php if ($r['reference_type']==='client'): ?>
      Customer: <?= htmlspecialchars($r['client_name'] ?? 'ID '.$r['reference_id']) ?>
    <?php elseif ($r['reference_type']==='retailer'): ?>
      Retailer ID: <?= (int)$r['reference_id'] ?>
    <?php else: ?> — <?php endif; ?>
  </td>
  <td><?= htmlspecialchars($r['note'] ?? '') ?></td>
</tr>
<?php endforeach; ?>

</tbody>
</table>

</main>
</div>
</body>
</html>
