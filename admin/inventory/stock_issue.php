<?php
// admin/inventory/stock_issue.php


if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php'; // provides $conn (mysqli)
require_once __DIR__ . '/../connect/auth_middleware.php';
require_once __DIR__ . '/inventory_functions.php';
$auth->requirePermission('inventory_management', 'create');

$title = 'stock_issue';

/* ================= MASTER DATA ================= */

// Products
$products = $conn->query("
    SELECT id, name, sku, serial_tracked 
    FROM products 
    ORDER BY name
")->fetch_all(MYSQLI_ASSOC);

// Warehouses
$warehouses = $conn->query("
    SELECT id, name 
    FROM warehouses 
    ORDER BY name
")->fetch_all(MYSQLI_ASSOC);

// Customers (clients)
$clients = $conn->query("
    SELECT id, name 
    FROM clients 
    ORDER BY name
")->fetch_all(MYSQLI_ASSOC);

/* ================= HANDLE SUBMIT ================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $product_id   = (int)($_POST['product_id'] ?? 0);
    $warehouse_id = (int)($_POST['warehouse_id'] ?? 0);
    $issue_type   = $_POST['issue_type'] ?? ''; // client | retailer
    $reference_id = (int)($_POST['reference_id'] ?? 0);
    $quantity     = (int)($_POST['quantity'] ?? 0);
    $note         = trim($_POST['note'] ?? '');
    $serials_raw  = trim($_POST['serials'] ?? '');
    $user_id      = $_SESSION['user_id'] ?? null;

    if (!$product_id || !$warehouse_id || !$reference_id || $quantity <= 0) {
        $_SESSION['inv_error'] = 'Invalid input.';
        header('Location: stock_issue.php'); exit;
    }

    if (!in_array($issue_type, ['client','retailer'], true)) {
        $_SESSION['inv_error'] = 'Invalid issue type.';
        header('Location: stock_issue.php'); exit;
    }

    /* CHECK AVAILABLE STOCK */
    $available = getWarehouseProductStock($conn, $product_id, $warehouse_id);
    if ($available < $quantity) {
        $_SESSION['inv_error'] = 'Insufficient stock in selected warehouse.';
        header('Location: stock_issue.php'); exit;
    }

    /* DECREASE STOCK (warehouse_stock) */
    adjustStock(
        $conn,
        $product_id,
        $warehouse_id,
        -$quantity,
        'consume',
        $note,
        $user_id
    );

   

    /* HANDLE SERIALS (IF ANY) */
    if ($serials_raw !== '') {
        $serials = array_filter(array_map('trim', preg_split('/[\r\n,]+/', $serials_raw)));
        foreach ($serials as $sn) {
            $u = $conn->prepare("
                UPDATE product_serials
                SET status='sold', warehouse_id=NULL
                WHERE serial_number=? AND product_id=? AND warehouse_id=?
            ");
            $u->bind_param('sii', $sn, $product_id, $warehouse_id);
            $u->execute();
            $u->close();
        }
    }

    $_SESSION['inv_success'] = 'Stock issued successfully.';
    header('Location: stock_movements.php');
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Issue Stock</title>

<?php require_once __DIR__ . '/../include/head2.php'; ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
.issue-card {
  max-width: 1000px;
  margin: auto;
}
.stock-badge {
  font-weight: 600;
}
</style>
</head>
<body>

<?php
$cwd = getcwd(); chdir(__DIR__ . '/..'); include 'include/sidebar.php'; chdir($cwd);
?>
<div id="main-content">
<?php
$cwd = getcwd(); chdir(__DIR__ . '/..'); include 'include/navbar.php'; chdir($cwd);
?>


<main class="container-fluid py-4">

<div class="issue-card card shadow-sm">
<div class="card-header bg-danger text-white">
  <h5 class="mb-0">📤 Issue Stock (Customer / Retailer)</h5>
</div>

<div class="card-body">

<?php if (!empty($_SESSION['inv_error'])): ?>
  <div class="alert alert-danger">
    <?= htmlspecialchars($_SESSION['inv_error']); unset($_SESSION['inv_error']); ?>
  </div>
<?php endif; ?>

<?php if (!empty($_SESSION['inv_success'])): ?>
  <div class="alert alert-success">
    <?= htmlspecialchars($_SESSION['inv_success']); unset($_SESSION['inv_success']); ?>
  </div>
<?php endif; ?>

<form method="post">

<div class="row mb-3">
  <div class="col-md-4">
    <label class="form-label">Product</label>
    <select name="product_id" id="product_id" class="form-select" required>
      <option value="">Select product</option>
      <?php foreach ($products as $p): ?>
        <option value="<?= $p['id'] ?>" data-serial="<?= $p['serial_tracked'] ?>">
          <?= htmlspecialchars($p['name'].' ('.$p['sku'].')') ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="col-md-4">
    <label class="form-label">Warehouse</label>
    <select name="warehouse_id" id="warehouse_id" class="form-select" required>
      <option value="">Select warehouse</option>
      <?php foreach ($warehouses as $w): ?>
        <option value="<?= $w['id'] ?>"><?= htmlspecialchars($w['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <small class="text-muted">
      Available stock: <span class="stock-badge text-primary" id="stock_count">—</span>
    </small>
  </div>

  <div class="col-md-4">
    <label class="form-label">Issue To</label>
    <select name="issue_type" class="form-select" required>
      <option value="">Select</option>
      <option value="client">Customer</option>
      <option value="retailer">Retailer</option>
    </select>
  </div>
</div>

<div class="row mb-3">
  <div class="col-md-6">
    <label class="form-label">Customer / Retailer</label>
    <select name="reference_id" class="form-select" required>
      <option value="">Select</option>
      <?php foreach ($clients as $c): ?>
        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="col-md-3">
    <label class="form-label">Quantity</label>
    <input type="number" name="quantity" class="form-control" min="1" required>
  </div>
</div>

<div class="mb-3" id="serial_box" style="display:none;">
  <label class="form-label">Serial Numbers (one per line)</label>
  <textarea name="serials" class="form-control" rows="3"></textarea>
</div>

<div class="mb-3">
  <label class="form-label">Note</label>
  <textarea name="note" class="form-control" rows="2"></textarea>
</div>

<div class="d-flex gap-2">
  <button class="btn btn-danger">Issue Stock</button>
  <a href="inventory_dashboard.php" class="btn btn-outline-secondary">Cancel</a>
</div>

</form>
</div>
</div>

</main>
</div>

<script>
const productSel   = document.getElementById('product_id');
const warehouseSel = document.getElementById('warehouse_id');
const stockSpan    = document.getElementById('stock_count');
const serialBox    = document.getElementById('serial_box');

function loadStock() {
  if (!productSel.value || !warehouseSel.value) {
    stockSpan.innerText = '—';
    return;
  }
  fetch(`ajax_get_stock1.php?product_id=${productSel.value}&warehouse_id=${warehouseSel.value}`)
    .then(r => r.json())
    .then(d => stockSpan.innerText = d.stock ?? '0');
}

productSel.addEventListener('change', () => {
  serialBox.style.display =
    productSel.selectedOptions[0].dataset.serial === '1' ? 'block' : 'none';
  loadStock();
});

warehouseSel.addEventListener('change', loadStock);
</script>

</body>
</html>
