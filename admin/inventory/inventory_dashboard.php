<?php
// admin/inventory/inventory_dashboard.php

if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php';
require_once __DIR__ . '/../connect/auth_middleware.php';
require_once __DIR__ . '/inventory_functions.php';

$auth->requirePermission('inventory_management', 'view');

// get warehouses, product counts and low stock snapshot
$warehouses = $conn->query("SELECT id,name,code,city,image FROM warehouses ORDER BY name")->fetch_all(MYSQLI_ASSOC);

// top 10 products by total qty
$top = $conn->query("SELECT p.id,p.name,p.sku, COALESCE(SUM(ws.quantity),0) as total
                     FROM products p
                     LEFT JOIN warehouse_stock ws ON ws.product_id = p.id
                     GROUP BY p.id ORDER BY total DESC LIMIT 12")->fetch_all(MYSQLI_ASSOC);

// low stock: products with total <= threshold (example 5)
$threshold = 5;
$low = $conn->query("SELECT p.id,p.name,p.sku, COALESCE(SUM(ws.quantity),0) as total
                     FROM products p
                     LEFT JOIN warehouse_stock ws ON ws.product_id = p.id
                     GROUP BY p.id HAVING total <= {$threshold} ORDER BY total ASC LIMIT 20")->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html><html><head><meta charset="utf-8"><title>Inventory Dashboard</title>
  <?php require_once __DIR__ . '/../include/head2.php'; ?>
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body>
    <?php $cwd = getcwd(); chdir(__DIR__ . '/..'); include 'include/sidebar.php'; chdir($cwd); ?>
<div id="main-content">
<?php $cwd = getcwd(); chdir(__DIR__ . '/..'); include 'include/navbar.php'; chdir($cwd); ?>
<main class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Inventory Dashboard</h3>
    <div>
      <a href="products.php" class="btn btn-outline-secondary">Products</a>
      <a href="warehouses.php" class="btn btn-outline-secondary">Warehouses</a>
      <a href="stock_movements.php" class="btn btn-outline-secondary">Movements</a>
    </div>
  </div>

  <div class="row mb-4">
    <div class="col-md-6">
      <div class="card p-3 mb-3">
        <h5>Warehouses (<?= count($warehouses) ?>)</h5>
        <div class="row">
          <?php foreach($warehouses as $w): ?>
            <div class="col-12 mb-2">
              <a class="d-block p-2" href="warehouse_view.php?id=<?= intval($w['id']) ?>" style="text-decoration:none">
                <strong><?= htmlspecialchars($w['name']) ?></strong>
                <div class="text-muted small"><?= htmlspecialchars($w['code'] ?? '') ?> <?= $w['city'] ? ' • '.htmlspecialchars($w['city']) : '' ?></div>
              </a>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card p-3 mb-3">
        <h5>Top products (by quantity)</h5>
        <ul class="list-unstyled mb-0">
          <?php foreach($top as $t): ?>
            <li class="py-1">
              <?= htmlspecialchars($t['name']) ?> (<?= htmlspecialchars($t['sku']) ?>) — <strong><?= intval($t['total']) ?></strong>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <div class="card p-3">
        <h5>Low stock (≤ <?= intval($threshold) ?>)</h5>
        <?php if (empty($low)): ?><div class="text-muted">No low stock items</div><?php else: ?>
        <ul class="list-unstyled mb-0">
          <?php foreach($low as $l): ?>
            <li class="py-1"><?= htmlspecialchars($l['name']) ?> — <strong><?= intval($l['total']) ?></strong></li>
          <?php endforeach; ?>
        </ul>
        <?php endif; ?>
      </div>
    </div>
  </div>

</main>
</div>
</body></html>
