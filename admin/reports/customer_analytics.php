<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php';
require_once __DIR__ . '/../connect/auth_middleware.php';
$auth->requireAuth();

$title = 'customer_analytics';

$total_customers = $conn->query("SELECT COUNT(*) as c FROM clients")->fetch_assoc()['c'];
$total_invoices = $conn->query("SELECT COUNT(*) as c FROM invoices WHERE invoice_type='client'")->fetch_assoc()['c'];
$total_revenue = $conn->query("SELECT SUM(total) as t FROM invoices WHERE invoice_type='client' AND status='final'")->fetch_assoc()['t'] ?? 0;

$top_customers = $conn->query("
    SELECT c.name, COUNT(i.id) as invoice_count, SUM(i.total) as total_spent
    FROM clients c
    LEFT JOIN invoices i ON i.reference_id = c.id AND i.invoice_type='client'
    GROUP BY c.id
    ORDER BY total_spent DESC
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Customer Analytics</title>
<?php require_once __DIR__ . '/../include/head2.php'; ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php $cwd = getcwd(); chdir(__DIR__ . '/..'); include 'include/sidebar.php'; chdir($cwd); ?>
<div id="main-content">
<?php $cwd = getcwd(); chdir(__DIR__ . '/..'); include 'include/navbar.php'; chdir($cwd); ?>

<main class="container-fluid py-4">
<h3>👥 Customer Analytics</h3>

<div class="row mb-4">
  <div class="col-md-4">
    <div class="card">
      <div class="card-body">
        <h6>Total Customers</h6>
        <h3><?= $total_customers ?></h3>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card">
      <div class="card-body">
        <h6>Total Invoices</h6>
        <h3><?= $total_invoices ?></h3>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card">
      <div class="card-body">
        <h6>Total Revenue</h6>
        <h3>₹<?= number_format($total_revenue, 2) ?></h3>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <h5>Top 10 Customers by Revenue</h5>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-bordered">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Customer Name</th>
            <th class="text-end">Invoice Count</th>
            <th class="text-end">Total Spent</th>
          </tr>
        </thead>
        <tbody>
          <?php $i = 1; foreach($top_customers as $c): ?>
            <tr>
              <td><?= $i++ ?></td>
              <td><?= htmlspecialchars($c['name']) ?></td>
              <td class="text-end"><?= $c['invoice_count'] ?></td>
              <td class="text-end">₹<?= number_format($c['total_spent'] ?? 0, 2) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

</main>
</div>
</body>
</html>
