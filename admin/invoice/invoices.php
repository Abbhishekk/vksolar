<?php
// admin/invoice/invoices.php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../connect/db.php';
require_once __DIR__ . '/../connect/auth_middleware.php';
$auth->requireAuth();
$auth->requirePermission('invoice_management', 'view');

$title = 'invoices';

/* ---------------- FILTERS ---------------- */
$status      = $_GET['status'] ?? '';
$invoice_type= $_GET['type'] ?? '';
$customer_id = (int)($_GET['id'] ?? 0);
$banks = $conn->query("
    SELECT id, bank_name, account_number 
    FROM company_bank_details 
    WHERE is_active = 1
    ORDER BY bank_name
")->fetch_all(MYSQLI_ASSOC);

/* ---------------- QUERY ---------------- */
$sql = "
SELECT 
  i.id,
  i.invoice_no,
  i.invoice_type,
  i.status,
  i.total,
  i.created_at,
  c.name AS customer_name
FROM invoices i
LEFT JOIN clients c ON c.id = i.reference_id
WHERE 1=1
";

$params = [];
$types  = '';

if ($status !== '') {
  $sql .= " AND i.status = ?";
  $params[] = $status;
  $types .= 's';
}
if ($invoice_type !== '') {
  $sql .= " AND i.invoice_type = ?";
  $params[] = $invoice_type;
  $types .= 's';
}
if ($customer_id) {
  $sql .= " AND i.customer_id = ?";
  $params[] = $customer_id;
  $types .= 'i';
}

$sql .= " ORDER BY i.created_at DESC LIMIT 500";

$stmt = $conn->prepare($sql);
if ($params) {
  $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$invoices = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

/* ---------------- FILTER DATA ---------------- */
$customers = $conn->query("SELECT id,name FROM clients ORDER BY name")->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Invoices</title>

<?php require_once __DIR__ . '/../include/head2.php'; ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
.badge-draft { background:#ffc107; color:#000; }
.badge-final { background:#28a745; }
.amount { font-weight:600; }
</style>
</head>
<body>

<?php $cwd = getcwd(); chdir(__DIR__ . '/..'); include 'include/sidebar.php'; chdir($cwd); ?>
<div id="main-content">
<?php $cwd = getcwd(); chdir(__DIR__ . '/..'); include 'include/navbar.php'; chdir($cwd); ?>

<main class="container-fluid py-4">
<?php 
  if(isset($_SESSION['success'])) {
    echo '<div class="alert alert-success">'.$_SESSION['success'].'</div>';
    unset($_SESSION['success']);
  }
  if(isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger">'.$_SESSION['error'].'</div>';
    unset($_SESSION['error']);
  }
  if(isset($_SESSION['inv_error'])) {
    echo '<div class="alert-danger">'.$_SESSION['inv_error'].'</div>';
    unset($_SESSION['inv_error']);
  }
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>🧾 Invoices</h3>
  <a href="invoice_create.php" class="btn btn-primary">+ Create Invoice</a>
</div>

<!-- FILTERS -->
<form class="row g-2 mb-3">

  <div class="col-md-3">
    <select name="status" class="form-select">
      <option value="">All Status</option>
      <option value="draft" <?= $status==='draft'?'selected':'' ?>>Draft</option>
      <option value="final" <?= $status==='final'?'selected':'' ?>>Final</option>
    </select>
  </div>

  <div class="col-md-3">
    <select name="type" class="form-select">
      <option value="">All Types</option>
      <option value="customer" <?= $invoice_type==='customer'?'selected':'' ?>>Customer</option>
      <option value="retailer" <?= $invoice_type==='retailer'?'selected':'' ?>>Retailer</option>
    </select>
  </div>

  <div class="col-md-3">
    <select name="customer_id" class="form-select">
      <option value="">All Customers</option>
      <?php foreach ($customers as $c): ?>
        <option value="<?= $c['id'] ?>" <?= $customer_id==$c['id']?'selected':'' ?>>
          <?= htmlspecialchars($c['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="col-md-2">
    <button class="btn btn-primary w-100">Filter</button>
  </div>

</form>

<!-- TABLE -->
<div class="table-responsive">
<table class="table table-bordered table-striped table-sm">
<thead class="table-light">
<tr>
  <th>#</th>
  <th>Invoice No</th>
  <th>Type</th>
  <th>Customer</th>
  <th>Total</th>
  <th>Status</th>
  <th>Date</th>
  <th width="160">Actions</th>
</tr>
</thead>
<tbody>

<?php if (!$invoices): ?>
<tr><td colspan="8" class="text-center text-muted">No invoices found</td></tr>
<?php endif; ?>

<?php foreach ($invoices as $i): ?>
<tr>
  <td><?= $i['id'] ?></td>
  <td><?= htmlspecialchars($i['invoice_no']) ?></td>
  <td><?= ucfirst($i['invoice_type']) ?></td>
  <td><?= htmlspecialchars($i['customer_name'] ?? '—') ?></td>
  <td class="amount">₹<?= number_format($i['total'],2) ?></td>
  <td>
    <?php if ($i['status']==='draft'): ?>
      <span class="badge badge-draft">Draft</span>
    <?php else: ?>
      <span class="badge badge-final">Final</span>
    <?php endif; ?>
  </td>
  <td><?= date('d M Y', strtotime($i['created_at'])) ?></td>
  <td>
<form method="get" action="invoice_view.php" class="d-flex flex-wrap gap-1">
    <input type="hidden" name="id" value="<?= $i['id'] ?>">

    <select name="bank_id" class="form-select form-select-sm mt-2" required>
        <option value="">Bank</option>
        <?php foreach ($banks as $b): ?>
            <option value="<?= $b['id'] ?>">
                <?= htmlspecialchars($b['bank_name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button class="btn btn-sm btn-outline-primary my-2">View</button>

  </form>
  <?php if ($i['status']==='draft'): ?>
      <a href="invoice_create.php?id=<?= $i['id'] ?>" class="btn mb-2 btn-sm btn-outline-secondary">Edit</a>
      <a href="invoice_finalize.php?id=<?= $i['id'] ?>" class="btn mb-2 btn-sm btn-success" onclick="return confirm('Finalize this invoice? This will deduct stock and cannot be undone.')">Finalize</a>
  <?php endif; ?>
  <?php if ($i['status'] === 'final'): ?>
<a href="invoice_payments.php?invoice_id=<?= $i['id'] ?>"
   class="btn btn-sm btn-success mb-2">
   Payments
</a>
<?php endif; ?>
</td>

</tr>
<?php endforeach; ?>

</tbody>
</table>
</div>

</main>
</div>
</body>
</html>
