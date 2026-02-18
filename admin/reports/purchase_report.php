<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php';
require_once __DIR__ . '/../connect/auth_middleware.php';
$auth->requireAuth();
$auth->requirePermission('reports', 'view');

$title = 'purchase_report';

$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');
$manufacturer_id = intval($_GET['manufacturer_id'] ?? 0);

$manufacturers = $conn->query("SELECT id, manufacturer_name FROM product_manufacturers ORDER BY manufacturer_name")->fetch_all(MYSQLI_ASSOC);

$sql = "SELECT po.*, pm.manufacturer_name, w.name AS warehouse_name
        FROM purchase_orders po
        LEFT JOIN product_manufacturers pm ON pm.id = po.manufacturer_id
        LEFT JOIN warehouses w ON w.id = po.warehouse_id
        WHERE po.created_at BETWEEN ? AND ?";

$params = [$start_date . ' 00:00:00', $end_date . ' 23:59:59'];
$types = 'ss';

if ($manufacturer_id) {
    $sql .= " AND po.manufacturer_id = ?";
    $params[] = $manufacturer_id;
    $types .= 'i';
}

$sql .= " ORDER BY po.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$total_amount = 0;
foreach($orders as $order) {
    $stmt = $conn->query("SELECT SUM(total) as total FROM purchase_order_items WHERE purchase_order_id = " . $order['id']);
    $total_amount += $stmt->fetch_assoc()['total'] ?? 0;
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Purchase Report</title>
<?php require_once __DIR__ . '/../include/head2.php'; ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php $cwd = getcwd(); chdir(__DIR__ . '/..'); include 'include/sidebar.php'; chdir($cwd); ?>
<div id="main-content">
<?php $cwd = getcwd(); chdir(__DIR__ . '/..'); include 'include/navbar.php'; chdir($cwd); ?>

<main class="container-fluid py-4">
<h3>📦 Purchase Report</h3>

<div class="card mb-3">
  <div class="card-body">
    <form method="get" class="row g-3">
      <div class="col-md-3">
        <label class="form-label">Start Date</label>
        <input type="date" name="start_date" class="form-control" value="<?= $start_date ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">End Date</label>
        <input type="date" name="end_date" class="form-control" value="<?= $end_date ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Manufacturer</label>
        <select name="manufacturer_id" class="form-select">
          <option value="">All Manufacturers</option>
          <?php foreach($manufacturers as $m): ?>
            <option value="<?= $m['id'] ?>" <?= $manufacturer_id == $m['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($m['manufacturer_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2 d-flex align-items-end">
        <button class="btn btn-primary w-100">Generate</button>
      </div>
    </form>
  </div>
</div>

<div class="row mb-3">
  <div class="col-md-6">
    <div class="card">
      <div class="card-body">
        <h6>Total Purchase Amount</h6>
        <h3>₹<?= number_format($total_amount, 2) ?></h3>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card">
      <div class="card-body">
        <h6>Total Purchase Orders</h6>
        <h3><?= count($orders) ?></h3>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-bordered">
        <thead class="table-light">
          <tr>
            <th>PO Number</th>
            <th>Date</th>
            <th>Manufacturer</th>
            <th>Warehouse</th>
            <th>Status</th>
            <th>Approved At</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($orders)): ?>
            <tr><td colspan="6" class="text-center">No purchases found</td></tr>
          <?php else: ?>
            <?php foreach($orders as $po): ?>
              <tr>
                <td><?= htmlspecialchars($po['po_number']) ?></td>
                <td><?= date('d-m-Y', strtotime($po['created_at'])) ?></td>
                <td><?= htmlspecialchars($po['manufacturer_name']) ?></td>
                <td><?= htmlspecialchars($po['warehouse_name']) ?></td>
                <td><span class="badge bg-<?= $po['status'] === 'approved' ? 'success' : ($po['status'] === 'pending' ? 'warning' : 'danger') ?>"><?= $po['status'] ?></span></td>
                <td><?= $po['approved_at'] ? date('d-m-Y', strtotime($po['approved_at'])) : '-' ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

</main>
</div>
</body>
</html>
