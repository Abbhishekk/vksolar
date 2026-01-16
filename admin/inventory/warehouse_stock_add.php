<?php
// admin/inventory/warehouse_stock_add.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php';
require_once __DIR__ . '/inventory_functions.php';
require_once __DIR__ . '/../connect/auth_middleware.php';
$auth->requireAuth();
$auth->requirePermission('inventory_management', 'create');

// inputs
$warehouse_id = intval($_GET['warehouse_id'] ?? 0);
$product_id   = intval($_GET['product_id'] ?? 0);

// handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $warehouse_id = intval($_POST['warehouse_id'] ?? 0);
    $product_id = intval($_POST['product_id'] ?? 0);
    $qty = intval($_POST['quantity'] ?? 0);
    $serials_text = trim($_POST['serials'] ?? ''); // newline or comma separated
    $user_id = $_SESSION['user_id'] ?? null;

    if (!$warehouse_id || !$product_id || $qty <= 0) {
        $_SESSION['inv_error'] = "Please provide warehouse, product and positive quantity.";
        header("Location: warehouse_stock_add.php?warehouse_id={$warehouse_id}&product_id={$product_id}");
        exit;
    }

    // begin transaction - if serial-tracked, we'll insert serials and adjust stock accordingly
    $conn->begin_transaction();
    try {
        // Add aggregated stock
        adjustStock($conn, $product_id, $warehouse_id, $qty, 'add_stock', 'Added via add-stock form', $user_id, true);

        // If serial-tracked product and serials provided, insert serials and attach to warehouse
        $pstmt = $conn->prepare("SELECT serial_tracked FROM products WHERE id = ? LIMIT 1");
        $pstmt->bind_param('i', $product_id);
        $pstmt->execute();
        $prod = $pstmt->get_result()->fetch_assoc();
        $pstmt->close();

        if ($prod && intval($prod['serial_tracked']) === 1 && $serials_text !== '') {
            // normalize serials: split by newline or comma
            $lines = preg_split('/[\r\n,]+/', $serials_text);
            $insertSerial = $conn->prepare("INSERT INTO product_serials (product_id, warehouse_id, serial_number, status, created_at) VALUES (?, ?, ?, 'in_stock', NOW())");
            foreach ($lines as $ln) {
                $s = trim($ln);
                if ($s === '') continue;
                // unique constraint on serial_number may fail; we try/catch
                try {
                    $insertSerial->bind_param('iis', $product_id, $warehouse_id, $s);
                    $insertSerial->execute();
                } catch (Exception $e) {
                    // ignore duplicates but continue
                }
            }
            $insertSerial->close();
        }

        $conn->commit();
        $_SESSION['inv_success'] = 'Stock added successfully.';
    } catch (Exception $ex) {
        $conn->rollback();
        error_log('warehouse_stock_add error: '.$ex->getMessage());
        $_SESSION['inv_error'] = 'Failed to add stock.';
    }
    header("Location: warehouse_view.php?id=" . intval($warehouse_id));
    exit;
}

// fetch warehouses and products for form
$warehouses = $conn->query("SELECT id, name FROM warehouses ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$products = $conn->query("SELECT id, name, sku FROM products ORDER BY name")->fetch_all(MYSQLI_ASSOC);

// try to preselect names
$whName = '';
if ($warehouse_id) {
    $q = $conn->prepare("SELECT name FROM warehouses WHERE id = ? LIMIT 1"); $q->bind_param('i',$warehouse_id); $q->execute(); $whName = $q->get_result()->fetch_assoc()['name'] ?? ''; $q->close();
}
?>
<!doctype html><html><head>
<meta charset="utf-8"><title>Add Stock</title>
  <?php require_once __DIR__ . '/../include/head2.php'; ?>
  
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php $cwd = getcwd(); chdir(__DIR__ . '/..'); include 'include/sidebar.php'; chdir($cwd); ?>
<div id="main-content">
<?php $cwd = getcwd(); chdir(__DIR__ . '/..'); include 'include/navbar.php'; chdir($cwd); ?>
<main class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Add Stock</h3>
    <a href="warehouse_view.php?id=<?= intval($warehouse_id) ?>" class="btn btn-outline-secondary">Back</a>
  </div>

  <?php if (!empty($_SESSION['inv_error'])): ?><div class="alert alert-danger"><?=htmlspecialchars($_SESSION['inv_error']); unset($_SESSION['inv_error']);?></div><?php endif;?>
  <?php if (!empty($_SESSION['inv_success'])): ?><div class="alert alert-success"><?=htmlspecialchars($_SESSION['inv_success']); unset($_SESSION['inv_success']);?></div><?php endif;?>

  <form method="post">
    <input type="hidden" name="warehouse_id" value="<?= intval($warehouse_id) ?>">
    <div class="mb-3">
      <label>Warehouse</label>
      <select name="warehouse_id_disabled" class="form-select" disabled>
        <option><?= htmlspecialchars($whName ?: 'Select warehouse') ?></option>
      </select>
    </div>

    <div class="mb-3">
      <label>Product</label>
      <select name="product_id" class="form-select" required>
        <option value="">-- Select product --</option>
        <?php foreach($products as $p): ?>
          <option value="<?= intval($p['id']) ?>" <?= $product_id && $product_id == $p['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($p['name'] . ' (' . $p['sku'] . ')') ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="row">
      <div class="col-md-4 mb-3">
        <label>Quantity</label>
        <input type="number" class="form-control" name="quantity" min="1" required>
      </div>
      <div class="col-md-8 mb-3">
        <label>Serials (optional, newline or comma separated) — only for serial-tracked products</label>
        <textarea class="form-control" name="serials" rows="4" placeholder="one serial per line or comma separated"></textarea>
      </div>
    </div>

    <button class="btn btn-primary">Add Stock</button>
  </form>
</main>
</div>
</body></html>
