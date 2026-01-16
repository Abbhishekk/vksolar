<?php
// admin/inventory/product_serials.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php';
require_once __DIR__ . '/../connect/auth_middleware.php';
$auth->requireAuth();
$auth->requirePermission('inventory_management', 'view');

// safe includes so sidebar/navbar work regardless of cwd


// Simple flash messages
$flash = ['success' => $_SESSION['inv_success'] ?? null, 'error' => $_SESSION['inv_error'] ?? null];
unset($_SESSION['inv_success'], $_SESSION['inv_error']);

// Handle POST actions (add / delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // simple helper
    function redirect_here($msg = null, $type = 'success') {
        if ($msg) $_SESSION[$type === 'error' ? 'inv_error' : 'inv_success'] = $msg;
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }

    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $product_id = intval($_POST['product_id'] ?? 0);
        $warehouse_id = intval($_POST['warehouse_id'] ?? 0) ?: null;
        $serial_number = trim($_POST['serial_number'] ?? '');
        $status = trim($_POST['status'] ?? 'in_stock');

        if (!$product_id || $serial_number === '') {
            redirect_here('Product and serial number are required.', 'error');
        }

        // insert
        $sql = "INSERT INTO product_serials (product_id, warehouse_id, serial_number, status, created_at) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        if (!$stmt) redirect_here('DB prepare failed: ' . $conn->error, 'error');
        // if warehouse_id is null we still bind as i (use null)
        if ($warehouse_id === null) {
            // bind param with null -> use s for serial and status, but product_id int
            $stmt->bind_param('iiss', $product_id, $warehouse_id, $serial_number, $status);
        } else {
            $stmt->bind_param('iiss', $product_id, $warehouse_id, $serial_number, $status);
        }
        if (!$stmt->execute()) {
            // unique key violation? show friendly message
            $err = $stmt->error;
            $stmt->close();
            redirect_here('Failed to add serial: ' . $err, 'error');
        }
        $stmt->close();
        redirect_here('Serial added successfully.');
    }

    if ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if (!$id) redirect_here('Invalid serial id.', 'error');
        // delete
        $stmt = $conn->prepare("DELETE FROM product_serials WHERE id = ? LIMIT 1");
        if (!$stmt) redirect_here('DB prepare failed: ' . $conn->error, 'error');
        $stmt->bind_param('i', $id);
        if (!$stmt->execute()) {
            $err = $stmt->error;
            $stmt->close();
            redirect_here('Delete failed: ' . $err, 'error');
        }
        $stmt->close();
        redirect_here('Serial deleted.');
    }
}

// ----- Filters & pagination -----
$filter_product = intval($_GET['product_id'] ?? 0);
$filter_warehouse = intval($_GET['warehouse_id'] ?? 0);
$filter_status = trim($_GET['status'] ?? '');
$search = trim($_GET['q'] ?? '');
$limit = intval($_GET['limit'] ?? 50);
if ($limit <= 0) $limit = 50;
$page = max(1, intval($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;

// fetch products and warehouses for selects
$products = $conn->query("SELECT id, name, sku FROM products ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$warehouses = $conn->query("SELECT id, name FROM warehouses ORDER BY name")->fetch_all(MYSQLI_ASSOC);

// build where & params
$where = [];
$types = '';
$params = [];

if ($filter_product) { $where[] = "ps.product_id = ?"; $types .= 'i'; $params[] = $filter_product; }
if ($filter_warehouse) { $where[] = "ps.warehouse_id = ?"; $types .= 'i'; $params[] = $filter_warehouse; }
if ($filter_status) { $where[] = "ps.status = ?"; $types .= 's'; $params[] = $filter_status; }
if ($search !== '') { $where[] = "ps.serial_number LIKE ?"; $types .= 's'; $params[] = '%' . $search . '%'; }

$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// count total
$count_sql = "SELECT COUNT(*) as c FROM product_serials ps $where_sql";
$stmt = $conn->prepare($count_sql);
if ($stmt) {
    if ($types !== '') {
        // bind dynamically
        $refs = [];
        $refs[] = & $types;
        foreach ($params as $i => $p) $refs[] = & $params[$i];
        call_user_func_array([$stmt, 'bind_param'], $refs);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    $total = ($res && $row = $res->fetch_assoc()) ? intval($row['c']) : 0;
    $stmt->close();
} else {
    $total = 0;
}

// fetch rows (with product & warehouse names)
$sql = "SELECT ps.*, p.name AS product_name, p.sku AS product_sku, w.name AS warehouse_name
        FROM product_serials ps
        LEFT JOIN products p ON p.id = ps.product_id
        LEFT JOIN warehouses w ON w.id = ps.warehouse_id
        $where_sql
        ORDER BY ps.created_at DESC
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    $rows = [];
    $errorMsg = "Prepare failed: " . $conn->error;
} else {
    // bind params + limit + offset
    // build bind list
    $bindTypes = $types . 'ii';
    $bindParams = array_merge($params, [$limit, $offset]);
    $refs = [];
    $refs[] = & $bindTypes;
    for ($i = 0; $i < count($bindParams); $i++) $refs[] = & $bindParams[$i];
    call_user_func_array([$stmt, 'bind_param'], $refs);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    $errorMsg = null;
}

// simple helper for url building
function qurl($overrides = []) {
    $params = $_GET;
    foreach ($overrides as $k => $v) {
        if ($v === null) unset($params[$k]);
        else $params[$k] = $v;
    }
    return strtok($_SERVER["REQUEST_URI"], '?') . '?' . http_build_query($params);
}

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Product Serials - Inventory</title>
  <?php require_once __DIR__ . '/../include/head2.php'; ?>
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .small-muted { font-size: .85rem; color: #6c757d; }
    .serial-row .actions { white-space: nowrap; }
    .table-fixed { table-layout: fixed; word-wrap: break-word; }
  </style>
</head>
<body>
<?php $cwd = getcwd(); chdir(__DIR__ . '/..'); include 'include/sidebar.php'; chdir($cwd); ?>
<div id="main-content">
<?php $cwd = getcwd(); chdir(__DIR__ . '/..'); include 'include/navbar.php'; chdir($cwd); ?>
  <main class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h3 class="mb-0">Product Serials</h3>
      <div>
        <a href="products" class="btn btn-outline-secondary">Products</a>
        <a href="warehouses" class="btn btn-outline-secondary">Warehouses</a>
      </div>
    </div>

    <?php if ($flash['success']): ?>
      <div class="alert alert-success"><?= htmlspecialchars($flash['success']) ?></div>
    <?php endif; ?>
    <?php if ($flash['error']): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($flash['error']) ?></div>
    <?php endif; ?>
    <?php if (!empty($errorMsg)): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($errorMsg) ?></div>
    <?php endif; ?>

    <div class="card mb-3">
      <div class="card-body">
        <form class="row g-2" method="get" action="">
          <div class="col-md-3">
            <select name="product_id" class="form-select">
              <option value="">All products</option>
              <?php foreach ($products as $p): ?>
                <option value="<?= intval($p['id']) ?>" <?= $filter_product == $p['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($p['name'].' ('.$p['sku'].')') ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <select name="warehouse_id" class="form-select">
              <option value="">All warehouses</option>
              <?php foreach ($warehouses as $w): ?>
                <option value="<?= intval($w['id']) ?>" <?= $filter_warehouse == $w['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($w['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-2">
            <select name="status" class="form-select">
              <option value="">All statuses</option>
              <?php foreach (['in_stock','sold','transferred','reserved','damaged','returned'] as $s): ?>
                <option value="<?= $s ?>" <?= $filter_status === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-2">
            <input type="search" name="q" class="form-control" placeholder="Search serial" value="<?= htmlspecialchars($search) ?>">
          </div>
          <div class="col-md-1">
            <input type="number" name="limit" class="form-control" min="1" value="<?= intval($limit) ?>">
          </div>
          <div class="col-md-1 d-grid">
            <button class="btn btn-primary">Filter</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Add single serial -->
    <div class="card mb-3">
      <div class="card-body">
        <h5 class="card-title">Add Serial</h5>
        <form method="post" class="row g-2">
          <input type="hidden" name="action" value="add">
          <div class="col-md-3">
            <select name="product_id" class="form-select" required>
              <option value="">Select product</option>
              <?php foreach ($products as $p): ?>
                <option value="<?= intval($p['id']) ?>"><?= htmlspecialchars($p['name'].' ('.$p['sku'].')') ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <select name="warehouse_id" class="form-select">
              <option value="">Select warehouse (optional)</option>
              <?php foreach ($warehouses as $w): ?>
                <option value="<?= intval($w['id']) ?>"><?= htmlspecialchars($w['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <input type="text" name="serial_number" class="form-control" placeholder="Serial number" required>
          </div>
          <div class="col-md-2">
            <select name="status" class="form-select">
              <option value="in_stock">In Stock</option>
              <option value="sold">Sold</option>
              <option value="transferred">Transferred</option>
              <option value="reserved">Reserved</option>
              <option value="damaged">Damaged</option>
              <option value="returned">Returned</option>
            </select>
          </div>
          <div class="col-md-1 d-grid">
            <button class="btn btn-success">Add</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Serial list -->
    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-sm table-striped table-fixed">
            <thead>
              <tr>
                <th style="width:6%">#</th>
                <th style="width:18%">Serial</th>
                <th style="width:26%">Product</th>
                <th style="width:16%">Warehouse</th>
                <th style="width:10%">Status</th>
                <th style="width:14%">Added</th>
                <th style="width:10%">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($rows)): ?>
                <tr><td colspan="7" class="text-center">No serials found.</td></tr>
              <?php else: foreach ($rows as $i => $r): ?>
                <tr class="serial-row">
                  <td><?= intval($offset + $i + 1) ?></td>
                  <td><?= htmlspecialchars($r['serial_number']) ?></td>
                  <td>
                    <?= htmlspecialchars($r['product_name'] ?? ('#'.$r['product_id'])) ?>
                    <div class="small-muted"><?= htmlspecialchars($r['product_sku'] ?? '') ?></div>
                  </td>
                  <td><?= htmlspecialchars($r['warehouse_name'] ?? ($r['warehouse_id'] ? '#'.$r['warehouse_id'] : '—')) ?></td>
                  <td><?= htmlspecialchars(ucfirst($r['status'])) ?></td>
                  <td><?= htmlspecialchars($r['created_at'] ?? $r['updated_at'] ?? '—') ?></td>
                  <td class="actions">
                    <a class="btn btn-sm btn-outline-primary" href="product_serial_view.php?id=<?= intval($r['id']) ?>">View</a>
                    <a class="btn btn-sm btn-outline-secondary" href="stock_transfer.php?serial_id=<?= intval($r['id']) ?>">Transfer</a>
                    <form method="post" style="display:inline" onsubmit="return confirm('Delete serial <?= htmlspecialchars(addslashes($r['serial_number'])) ?>?');">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="<?= intval($r['id']) ?>">
                      <button class="btn btn-sm btn-danger" type="submit">Delete</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>

        <!-- pagination -->
        <?php
          $pages = max(1, ceil($total / $limit));
          $baseUrl = strtok($_SERVER["REQUEST_URI"], '?');
          $queryParams = $_GET;
        ?>
        <?php if ($pages > 1): ?>
          <nav class="mt-3">
            <ul class="pagination pagination-sm">
              <?php for ($p = 1; $p <= $pages; $p++): 
                $queryParams['page'] = $p;
                $queryParams['limit'] = $limit;
                $u = $baseUrl . '?' . http_build_query($queryParams);
              ?>
                <li class="page-item <?= $p == $page ? 'active' : '' ?>"><a class="page-link" href="<?= $u ?>"><?= $p ?></a></li>
              <?php endfor; ?>
            </ul>
          </nav>
        <?php endif; ?>

      </div>
    </div>

  </main>
</div>

</body>
</html>
