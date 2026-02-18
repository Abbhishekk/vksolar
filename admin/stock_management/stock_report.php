<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php';
require_once __DIR__ . '/../connect/auth_middleware.php';
$auth->requireAuth();
$auth->requirePermission('inventory_management', 'view');

$title = 'stock_report';

// Filters
$warehouse_id = intval($_GET['warehouse_id'] ?? 0);
$category_id = intval($_GET['category_id'] ?? 0);

// Fetch warehouses and categories for filters
$warehouses = $conn->query("SELECT id, name FROM warehouses ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$categories = $conn->query("SELECT id, category_name FROM product_categories ORDER BY category_name")->fetch_all(MYSQLI_ASSOC);

// Filter warehouses for warehouse_staff
$current_role = $_SESSION['role'] ?? '';
$user_id = $_SESSION['user_id'] ?? 0;
if ($current_role === 'warehouse_staff') {
    $user_id = $_SESSION['employee_id'] ?? 0;

    $stmt = $conn->prepare("SELECT id, name FROM warehouses WHERE id IN (SELECT warehouse_id FROM warehouse_employees WHERE employee_id = ?) ORDER BY name");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $warehouses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Build query
$sql = "
    SELECT 
        p.id AS product_id,
        p.name AS product_name,
        pc.category_name,
        w.id AS warehouse_id,
        w.name AS warehouse_name,
        COALESCE(ws.quantity, 0) AS quantity,
        COALESCE(ws.reserved, 0) AS reserved,
        COALESCE(ws.quantity, 0) - COALESCE(ws.reserved, 0) AS available
    FROM products p
    LEFT JOIN product_categories pc ON pc.id = p.category_id
    CROSS JOIN warehouses w
    LEFT JOIN warehouse_stock ws ON ws.product_id = p.id AND ws.warehouse_id = w.id
    WHERE 1=1
";

if ($warehouse_id) {
    $sql .= " AND w.id = " . intval($warehouse_id);
}
if ($category_id) {
    $sql .= " AND p.category_id = " . intval($category_id);
}

$sql .= " ORDER BY w.name, pc.category_name, p.name";

$stocks = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Stock Report</title>
<?php require_once __DIR__ . '/../include/head2.php'; ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php $cwd = getcwd(); chdir(__DIR__ . '/..'); include 'include/sidebar.php'; chdir($cwd); ?>
<div id="main-content">
<?php $cwd = getcwd(); chdir(__DIR__ . '/..'); include 'include/navbar.php'; chdir($cwd); ?>

<main class="container-fluid py-4">

<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>📊 Stock Report</h3>
  <a href="../warehousemanagement/dashboard.php" class="btn btn-outline-secondary">Back to Dashboard</a>
</div>

<!-- Filters -->
<div class="card mb-3">
  <div class="card-body">
    <form method="get" class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Warehouse</label>
        <select name="warehouse_id" class="form-select" onchange="this.form.submit()">
          <option value="">All Warehouses</option>
          <?php foreach($warehouses as $w): ?>
            <option value="<?= $w['id'] ?>" <?= $warehouse_id == $w['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($w['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Category</label>
        <select name="category_id" class="form-select" onchange="this.form.submit()">
          <option value="">All Categories</option>
          <?php foreach($categories as $c): ?>
            <option value="<?= $c['id'] ?>" <?= $category_id == $c['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($c['category_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4 d-flex align-items-end">
        <a href="stock_report.php" class="btn btn-secondary">Clear Filters</a>
      </div>
    </form>
  </div>
</div>

<!-- Stock Table -->
<div class="card">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-bordered table-hover">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Warehouse</th>
            <th>Product</th>
            <th>Category</th>
            <th class="text-end">Total Qty</th>
            <th class="text-end">Reserved</th>
            <th class="text-end">Available</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($stocks)): ?>
            <tr>
              <td colspan="7" class="text-center text-muted">No stock data found</td>
            </tr>
          <?php else: ?>
            <?php $i = 1; foreach($stocks as $s): ?>
              <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($s['warehouse_name']) ?></td>
                <td><?= htmlspecialchars($s['product_name']) ?></td>
                <td><?= htmlspecialchars($s['category_name'] ?? '-') ?></td>
                <td class="text-end"><?= number_format($s['quantity'], 0) ?></td>
                <td class="text-end"><?= number_format($s['reserved'], 0) ?></td>
                <td class="text-end <?= $s['available'] <= 0 ? 'text-danger' : 'text-success' ?>">
                  <?= number_format($s['available'], 0) ?>
                </td>
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
