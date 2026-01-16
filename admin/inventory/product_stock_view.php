<?php
// admin/inventory/product_stock_view.php

if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php';
require_once __DIR__ . '/../connect/auth_middleware.php';
require_once __DIR__ . '/inventory_functions.php';
$auth->requirePermission('inventory_management', 'view');

$product_id = intval($_GET['product_id'] ?? 0);

// fetch all products for dropdown
$products = $conn->query("SELECT id, name, sku FROM products ORDER BY name")->fetch_all(MYSQLI_ASSOC);

$product = null;
$stocks = [];
$total = 0;

if ($product_id) {
    // product details
    $pstmt = $conn->prepare("SELECT id, name, sku, serial_tracked FROM products WHERE id = ? LIMIT 1");
    $pstmt->bind_param("i", $product_id);
    $pstmt->execute();
    $product = $pstmt->get_result()->fetch_assoc();
    $pstmt->close();

    // warehouse stock
    $stmt = $conn->prepare("
        SELECT ws.warehouse_id, ws.quantity, ws.reserved, w.name AS warehouse_name
        FROM warehouse_stock ws
        JOIN warehouses w ON w.id = ws.warehouse_id
        WHERE ws.product_id = ?
        ORDER BY w.name
    ");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stocks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // total stock
    $total = getProductTotalStock($conn, $product_id);
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Product Stock View</title>
<?php require_once __DIR__ . '/../include/head2.php'; ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>
<?php $cwd=getcwd(); chdir(__DIR__.'/..'); include 'include/sidebar.php'; chdir($cwd); ?>
<div id="main-content">
<?php $cwd=getcwd(); chdir(__DIR__.'/..'); include 'include/navbar.php'; chdir($cwd); ?>

<main class="container py-4">

  <h3 class="mb-3">Product Stock</h3>

  <!-- PRODUCT SELECT -->
  <form method="get" class="row g-2 mb-4">
    <div class="col-md-6">
      <label class="form-label fw-bold">Select Product</label>
      <select name="product_id" class="form-select" onchange="this.form.submit()" required>
        <option value="">-- Select Product --</option>
        <?php foreach ($products as $p): ?>
          <option value="<?= $p['id'] ?>" <?= $product_id==$p['id']?'selected':'' ?>>
            <?= htmlspecialchars($p['name'].' ('.$p['sku'].')') ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
  </form>

  <?php if ($product): ?>

    <!-- PRODUCT SUMMARY -->
    <div class="card mb-3">
      <div class="card-body">
        <h5><?= htmlspecialchars($product['name']) ?> (<?= htmlspecialchars($product['sku']) ?>)</h5>
        <p class="mb-0">
          Total Stock: <strong><?= $total ?></strong>
        </p>
      </div>
    </div>

    <!-- STOCK TABLE -->
    <div class="table-responsive">
      <table class="table table-bordered table-sm align-middle">
        <thead class="table-light">
          <tr>
            <th>Warehouse</th>
            <th class="text-end">Quantity</th>
            <th class="text-end">Reserved</th>
            <th>Serials</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
        <?php if (empty($stocks)): ?>
          <tr>
            <td colspan="5" class="text-center text-muted">No stock available</td>
          </tr>
        <?php else: ?>
          <?php foreach ($stocks as $s): ?>
            <tr>
              <td><?= htmlspecialchars($s['warehouse_name']) ?></td>
              <td class="text-end"><?= intval($s['quantity']) ?></td>
              <td class="text-end"><?= intval($s['reserved']) ?></td>
              <td>
                <?php if (intval($product['serial_tracked']) === 1): 
                    $q = $conn->prepare("SELECT COUNT(*) c FROM product_serials WHERE product_id=? AND warehouse_id=?");
                    $q->bind_param("ii", $product_id, $s['warehouse_id']);
                    $q->execute();
                    $cnt = $q->get_result()->fetch_assoc()['c'] ?? 0;
                    $q->close();
                ?>
                  <a class="btn btn-sm btn-outline-primary"
                     href="product_serials.php?product_id=<?= $product_id ?>&warehouse_id=<?= $s['warehouse_id'] ?>">
                     Serials (<?= $cnt ?>)
                  </a>
                <?php else: ?>
                  <span class="text-muted">N/A</span>
                <?php endif; ?>
              </td>
              <td>
                <a class="btn btn-sm btn-outline-secondary"
                   href="warehouse_view.php?id=<?= $s['warehouse_id'] ?>">
                   Open Warehouse
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
      </table>
    </div>

  <?php endif; ?>

</main>
</div>
</body>
</html>
