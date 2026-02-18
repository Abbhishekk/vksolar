<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php';
require_once __DIR__ . '/../connect/auth_middleware.php';
$auth->requireAuth();
$auth->requirePermission('reports', 'view');

$title = 'sales_report';

$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');
$client_id = intval($_GET['client_id'] ?? 0);

$clients = $conn->query("SELECT id, name FROM clients ORDER BY name")->fetch_all(MYSQLI_ASSOC);

$sql = "SELECT i.*, c.name AS client_name, w.name AS warehouse_name
        FROM invoices i
        LEFT JOIN clients c ON c.id = i.reference_id
        LEFT JOIN warehouses w ON w.id = i.warehouse_id
        WHERE i.invoice_type = 'client' 
        AND i.invoice_date BETWEEN ? AND ?";

$params = [$start_date, $end_date];
$types = 'ss';

if ($client_id) {
    $sql .= " AND i.reference_id = ?";
    $params[] = $client_id;
    $types .= 'i';
}

$sql .= " ORDER BY i.invoice_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$invoices = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$total_sales = array_sum(array_column($invoices, 'total'));
$total_invoices = count($invoices);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Sales Report</title>
<?php require_once __DIR__ . '/../include/head2.php'; ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php $cwd = getcwd(); chdir(__DIR__ . '/..'); include 'include/sidebar.php'; chdir($cwd); ?>
<div id="main-content">
<?php $cwd = getcwd(); chdir(__DIR__ . '/..'); include 'include/navbar.php'; chdir($cwd); ?>

<main class="container-fluid py-4">
<h3>📊 Sales Report</h3>

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
        <label class="form-label">Customer</label>
        <select name="client_id" class="form-select">
          <option value="">All Customers</option>
          <?php foreach($clients as $c): ?>
            <option value="<?= $c['id'] ?>" <?= $client_id == $c['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($c['name']) ?>
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
        <h6>Total Sales</h6>
        <h3>₹<?= number_format($total_sales, 2) ?></h3>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card">
      <div class="card-body">
        <h6>Total Invoices</h6>
        <h3><?= $total_invoices ?></h3>
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
            <th>Invoice No</th>
            <th>Date</th>
            <th>Customer</th>
            <th>Warehouse</th>
            <th class="text-end">Subtotal</th>
            <th class="text-end">CGST</th>
            <th class="text-end">SGST</th>
            <th class="text-end">Total</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($invoices)): ?>
            <tr><td colspan="9" class="text-center">No sales found</td></tr>
          <?php else: ?>
            <?php foreach($invoices as $inv): ?>
              <tr>
                <td><?= htmlspecialchars($inv['invoice_no']) ?></td>
                <td><?= date('d-m-Y', strtotime($inv['invoice_date'])) ?></td>
                <td><?= htmlspecialchars($inv['client_name']) ?></td>
                <td><?= htmlspecialchars($inv['warehouse_name']) ?></td>
                <td class="text-end"><?= number_format($inv['subtotal'], 2) ?></td>
                <td class="text-end"><?= number_format($inv['cgst'], 2) ?></td>
                <td class="text-end"><?= number_format($inv['sgst'], 2) ?></td>
                <td class="text-end"><?= number_format($inv['total'], 2) ?></td>
                <td><span class="badge bg-<?= $inv['status'] === 'final' ? 'success' : 'warning' ?>"><?= $inv['status'] ?></span></td>
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
