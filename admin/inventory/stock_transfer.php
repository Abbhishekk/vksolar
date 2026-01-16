<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php';
require_once __DIR__ . '/../connect/auth_middleware.php';
$auth->requireAuth();
$auth->requirePermission('inventory_management', 'edit');

$user_id = $_SESSION['user_id'] ?? null;

// Fetch products & warehouses
$products = $conn->query("SELECT id,name,sku,serial_tracked FROM products ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$warehouses = $conn->query("SELECT id,name FROM warehouses ORDER BY name")->fetch_all(MYSQLI_ASSOC);

function getStock($conn, $product_id, $warehouse_id) {
    $q = $conn->prepare("SELECT quantity FROM warehouse_stock WHERE product_id=? AND warehouse_id=?");
    $q->bind_param('ii', $product_id, $warehouse_id);
    $q->execute();
    $r = $q->get_result()->fetch_assoc();
    return $r['quantity'] ?? 0;
}

// Handle submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $product_id     = intval($_POST['product_id']);
    $from_wh        = intval($_POST['warehouse_from']);
    $to_wh          = intval($_POST['warehouse_to']);
    $qty            = floatval($_POST['quantity']);
    $note           = trim($_POST['note'] ?? '');

    if (!$product_id || !$from_wh || !$to_wh || $qty <= 0 || $from_wh === $to_wh) {
        $_SESSION['inv_error'] = 'Invalid transfer data.';
        header('Location: stock_transfer.php'); exit;
    }

    $fromStock = getStock($conn, $product_id, $from_wh);
    if ($fromStock < $qty) {
        $_SESSION['inv_error'] = 'Insufficient stock in source warehouse.';
        header('Location: stock_transfer.php'); exit;
    }

    $conn->begin_transaction();

    try {
        // Decrease from warehouse
        $conn->query("UPDATE warehouse_stock 
                      SET quantity = quantity - $qty 
                      WHERE product_id=$product_id AND warehouse_id=$from_wh");

        // Increase to warehouse
        $conn->query("INSERT INTO warehouse_stock (product_id, warehouse_id, quantity)
                      VALUES ($product_id,$to_wh,$qty)
                      ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)");

        // Stock movement OUT
        $stmt = $conn->prepare("
            INSERT INTO stock_movements
            (movement_type, product_id, warehouse_from, warehouse_to, quantity, note, created_by)
            VALUES ('transfer_out', ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param('iiidsi', $product_id, $from_wh, $to_wh, $qty, $note, $user_id);
        $stmt->execute();

        // Stock movement IN
        $stmt = $conn->prepare("
            INSERT INTO stock_movements
            (movement_type, product_id, warehouse_from, warehouse_to, quantity, note, created_by)
            VALUES ('transfer_in', ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param('iiidsi', $product_id, $from_wh, $to_wh, $qty, $note, $user_id);
        $stmt->execute();

        $conn->commit();

        $_SESSION['inv_success'] = 'Stock transferred successfully.';
        header('Location: stock_movements.php'); exit;

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['inv_error'] = 'Transfer failed.';
        header('Location: stock_transfer.php'); exit;
    }
}
?>
<!doctype html>
<html>
<head>
  <title>Stock Transfer</title>
  <?php require_once __DIR__ . '/../include/head2.php'; ?>
</head>
<body>
    <?php $cwd = getcwd(); chdir(__DIR__ . '/..'); include 'include/sidebar.php'; chdir($cwd); ?>
<div id="main-content">
<?php $cwd = getcwd(); chdir(__DIR__ . '/..'); include 'include/navbar.php'; chdir($cwd); ?>
<main class="container py-4">

<h3>Transfer Stock</h3>

<form method="post" class="card p-4">

  <div class="mb-3">
    <label>Product</label>
    <select name="product_id" class="form-select" required>
      <option value="">Select product</option>
      <?php foreach($products as $p): ?>
        <option value="<?= $p['id'] ?>">
          <?= htmlspecialchars($p['name'].' ('.$p['sku'].')') ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="row">
    <div class="col-md-6 mb-3">
      <label>From Warehouse</label>
      <select name="warehouse_from" class="form-select" required>
        <option value="">Select</option>
        <?php foreach($warehouses as $w): ?>
          <option value="<?= $w['id'] ?>"><?= htmlspecialchars($w['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <div class="mb-2 text-muted">
        Current stock: <strong id="fromStock">—</strong>
      </div>
    </div>

    <div class="col-md-6 mb-3">
      <label>To Warehouse</label>
      <select name="warehouse_to" class="form-select" required>
        <option value="">Select</option>
        <?php foreach($warehouses as $w): ?>
          <option value="<?= $w['id'] ?>"><?= htmlspecialchars($w['name']) ?></option>
        <?php endforeach; ?>
      </select>
        <div class="mb-2 text-muted">
          Current stock: <strong id="toStock">—</strong>
        </div>
    </div>
  </div>

  <div class="mb-3">
    <label>Quantity</label>
    <input type="number" step="0.001" name="quantity" class="form-control" required>
  </div>

  <div class="mb-3">
    <label>Note</label>
    <textarea name="note" class="form-control"></textarea>
  </div>

  <button class="btn btn-primary">Transfer Stock</button>
</form>

</main>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {

  const productSelect = document.querySelector('[name="product_id"]');
  const fromSelect    = document.querySelector('[name="warehouse_from"]');
  const toSelect      = document.querySelector('[name="warehouse_to"]');

  const fromStockEl = document.getElementById('fromStock');
  const toStockEl   = document.getElementById('toStock');

  function fetchStock(productId, warehouseId, targetEl) {
    if (!productId || !warehouseId) {
      targetEl.textContent = '—';
      return;
    }

    fetch(`ajax_get_stock.php?product_id=${productId}&warehouse_id=${warehouseId}`)
      .then(res => res.json())
      .then(data => {
        targetEl.textContent = data.success ? data.quantity : '0';
      })
      .catch(() => {
        targetEl.textContent = 'ERR';
      });
  }

  function updateFromStock() {
    fetchStock(productSelect.value, fromSelect.value, fromStockEl);
  }

  function updateToStock() {
    fetchStock(productSelect.value, toSelect.value, toStockEl);
  }

  productSelect.addEventListener('change', () => {
    updateFromStock();
    updateToStock();
  });

  fromSelect.addEventListener('change', updateFromStock);
  toSelect.addEventListener('change', updateToStock);

});
</script>

</body>
</html>
