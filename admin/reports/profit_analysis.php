<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php';
require_once __DIR__ . '/../connect/auth_middleware.php';
$auth->requireAuth();
$auth->requirePermission('reports', 'view');

$title = 'profit_report';

$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

$sales = $conn->query("SELECT SUM(total) as total FROM invoices WHERE invoice_type='client' AND status='final' AND invoice_date BETWEEN '$start_date' AND '$end_date'")->fetch_assoc()['total'] ?? 0;

$purchases = $conn->query("SELECT SUM(poi.total) as total FROM purchase_order_items poi JOIN purchase_orders po ON po.id = poi.purchase_order_id WHERE po.status='approved' AND po.created_at BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59'")->fetch_assoc()['total'] ?? 0;

$profit = $sales - $purchases;
$margin = $sales > 0 ? ($profit / $sales) * 100 : 0;
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Profit Analysis</title>
<?php require_once __DIR__ . '/../include/head2.php'; ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php $cwd = getcwd(); chdir(__DIR__ . '/..'); include 'include/sidebar.php'; chdir($cwd); ?>
<div id="main-content">
<?php $cwd = getcwd(); chdir(__DIR__ . '/..'); include 'include/navbar.php'; chdir($cwd); ?>

<main class="container-fluid py-4">
<h3>💰 Profit Analysis</h3>

<div class="card mb-3">
  <div class="card-body">
    <form method="get" class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Start Date</label>
        <input type="date" name="start_date" class="form-control" value="<?= $start_date ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">End Date</label>
        <input type="date" name="end_date" class="form-control" value="<?= $end_date ?>">
      </div>
      <div class="col-md-4 d-flex align-items-end">
        <button class="btn btn-primary w-100">Generate</button>
      </div>
    </form>
  </div>
</div>

<div class="row">
  <div class="col-md-3">
    <div class="card">
      <div class="card-body">
        <h6 class="text-muted">Total Sales</h6>
        <h3 class="text-success">₹<?= number_format($sales, 2) ?></h3>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card">
      <div class="card-body">
        <h6 class="text-muted">Total Purchases</h6>
        <h3 class="text-danger">₹<?= number_format($purchases, 2) ?></h3>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card">
      <div class="card-body">
        <h6 class="text-muted">Gross Profit</h6>
        <h3 class="<?= $profit >= 0 ? 'text-success' : 'text-danger' ?>">₹<?= number_format($profit, 2) ?></h3>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card">
      <div class="card-body">
        <h6 class="text-muted">Profit Margin</h6>
        <h3 class="<?= $margin >= 0 ? 'text-success' : 'text-danger' ?>"><?= number_format($margin, 2) ?>%</h3>
      </div>
    </div>
  </div>
</div>

</main>
</div>
</body>
</html>
