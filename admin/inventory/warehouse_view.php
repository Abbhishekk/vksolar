<?php
// admin/inventory/warehouse_view.php
// View a single warehouse and its stock (robust against missing batch_no column)
// Copy-paste this file to /admin/inventory/warehouse_view.php
require_once __DIR__ . "/../connect/auth_middleware.php";

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../connect/db.php'; // $conn (mysqli)
$auth->requirePermission('inventory_management', 'view');

// sanitize input
$warehouse_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($warehouse_id <= 0) {
    // redirect back to list if invalid id
    header('Location: warehouses.php');
    exit;
}

// fetch warehouse (select all columns to be tolerant of schema)
$warehouse = null;
if ($stmt = $conn->prepare("SELECT * FROM warehouses WHERE id = ? LIMIT 1")) {
    $stmt->bind_param('i', $warehouse_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $warehouse = $res ? $res->fetch_assoc() : null;
    $stmt->close();
}
if (!$warehouse) {
    $_SESSION['inv_error'] = "Warehouse not found.";
    header('Location: warehouses.php');
    exit;
}

// image prefix (adjust if your upload path differs)
$upload_url_prefix = '/admin/inventory/uploads/'; // change if needed
$warehouse_image = !empty($warehouse['image']) ? $upload_url_prefix . $warehouse['image'] : null;

// --- LOAD STOCK (Primary) ---
// Try to read from warehouse_stock table that has columns: warehouse_id, product_id, quantity
$stock_rows = [];
$stock_query_error = null;

$primary_sql = "SELECT ws.product_id, ws.quantity, p.name AS product_name, p.sku
                FROM warehouse_stock ws
                LEFT JOIN products p ON p.id = ws.product_id
                WHERE ws.warehouse_id = ?
                ORDER BY p.name ASC";

$stmt = @$conn->prepare($primary_sql);
if ($stmt) {
    // prepared ok — try to execute
    if ($stmt->bind_param('i', $warehouse_id) && $stmt->execute()) {
        $res = $stmt->get_result();
        $stock_rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    } else {
        $stock_query_error = "Execute failed: " . $stmt->error;
        error_log("warehouse_view primary execute error: " . $stmt->error);
    }
    $stmt->close();
} else {
    // prepare failed, probably table/columns mismatch (e.g. batch_no referenced elsewhere)
    $stock_query_error = "Primary stock query prepare failed: " . $conn->error;
    error_log("warehouse_view primary prepare failed: " . $conn->error . " — SQL: " . $primary_sql);
}

// --- FALLBACK #1: If no rows and primary failed or returned nothing, try reading product_serials grouped by product_id ---
// This is useful if you are tracking serials (product_serials) and not a warehouse_stock table.
if (empty($stock_rows)) {
    // check product_serials table existence
    $check = $conn->query("SHOW TABLES LIKE 'product_serials'");
    if ($check && $check->num_rows > 0) {
        $sql = "SELECT ps.product_id, COUNT(*) AS quantity, p.name AS product_name, p.sku
                FROM product_serials ps
                LEFT JOIN products p ON p.id = ps.product_id
                WHERE ps.warehouse_id = ?
                GROUP BY ps.product_id
                ORDER BY p.name";
        if ($stmt2 = $conn->prepare($sql)) {
            if ($stmt2->bind_param('i', $warehouse_id) && $stmt2->execute()) {
                $res2 = $stmt2->get_result();
                $stock_rows = $res2 ? $res2->fetch_all(MYSQLI_ASSOC) : [];
            } else {
                error_log("warehouse_view product_serials execute failed: " . $stmt2->error);
            }
            $stmt2->close();
        } else {
            error_log("warehouse_view product_serials prepare failed: " . $conn->error . " — SQL: " . $sql);
        }
    } else {
        // product_serials table not present — leave stock_rows empty
        error_log("warehouse_view fallback: product_serials table not found.");
    }
}

// --- FALLBACK #2: If still empty, try a generic query reading any table that looks like stock (best-effort) ---
// (optional — you can remove or extend this if you have other stock table names)
// We won't aggressively guess; instead we'll leave helpful message to user.


// Safe includes for admin header/sidebar/navbar (use chdir trick to avoid path troubles)


?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Warehouse: <?php echo htmlspecialchars($warehouse['name'] ?? 'Warehouse'); ?></title>
  <?php require_once __DIR__ . '/../include/head2.php'; ?>
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .warehouse-hero {
      border-radius: 10px;
      overflow: hidden;
      position: relative;
      height: 220px;
      background-color: #6c757d;
      background-position: center;
      background-size: cover;
      display:flex;
      align-items:flex-end;
      color: #fff;
      box-shadow: 0 6px 18px rgba(0,0,0,0.12);
    }
    .warehouse-hero .meta {
      padding: 18px;
      background: linear-gradient(to top, rgba(0,0,0,0.45), rgba(0,0,0,0.0));
      width: 100%;
    }
    .warehouse-hero h2 { margin:0; font-size:1.5rem; }
    .warehouse-actions { display:flex; gap:8px; margin-top:10px; }
    .stock-table td, .stock-table th { vertical-align: middle; }
  </style>
</head>
<body>
<?php $cwd = getcwd(); chdir(__DIR__ . '/..'); include 'include/sidebar.php'; chdir($cwd); ?>
<div id="main-content">
<?php $cwd = getcwd(); chdir(__DIR__ . '/..'); include 'include/navbar.php'; chdir($cwd); ?>
  <main class="container py-4">
    <div class="d-flex justify-content-between align-items-start mb-3">
      <div style="flex:1">
        <div class="warehouse-hero" style="<?php if ($warehouse_image) echo "background-image:url('".htmlspecialchars($warehouse_image)."');"; ?>">
          <div class="meta">
            <h2><?php echo htmlspecialchars($warehouse['name'] ?? 'Warehouse'); ?></h2>
            <div class="small text-light">
              <?php echo htmlspecialchars($warehouse['code'] ?? ''); ?>
              <?php if (!empty($warehouse['city'])) echo ' • ' . htmlspecialchars($warehouse['city']); ?>
            </div>
            <div class="warehouse-actions">
              <a href="warehouse_create.php?id=<?php echo intval($warehouse_id); ?>" class="btn btn-sm btn-light">Edit</a>
              <a href="warehouse_delete.php?id=<?php echo intval($warehouse_id); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete warehouse? This will remove assignments and stock.')">Delete</a>
              <a href="warehouse_stock_add.php?warehouse_id=<?php echo intval($warehouse_id); ?>" class="btn btn-sm btn-primary">Add Stock</a>
            </div>
          </div>
        </div>

        <div class="card mt-3">
          <div class="card-body">
            <h5 class="card-title">Details</h5>
            <div class="row">
              <div class="col-md-6">
                <p><strong>Code:</strong> <?php echo htmlspecialchars($warehouse['code'] ?? ''); ?></p>
                <p><strong>Address:</strong> <?php echo nl2br(htmlspecialchars($warehouse['address'] ?? '')); ?></p>
              </div>
              <div class="col-md-6">
                <p><strong>City:</strong> <?php echo htmlspecialchars($warehouse['city'] ?? ''); ?></p>
                <p><strong>Contact / Note:</strong> <?php echo nl2br(htmlspecialchars($warehouse['note'] ?? '')); ?></p>
              </div>
            </div>
          </div>
        </div>

        <div class="card mt-3">
          <div class="card-body">
            <h5 class="card-title">Stock</h5>

            <?php if (!empty($stock_rows)): ?>
              <div class="table-responsive">
                <table class="table table-sm table-striped stock-table">
                  <thead>
                    <tr>
                      <th>Product</th>
                      <th>SKU</th>
                      <th class="text-end">Quantity</th>
                      <th class="text-end">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($stock_rows as $r): ?>
                      <tr>
                        <td><?php echo htmlspecialchars($r['product_name'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($r['sku'] ?? '—'); ?></td>
                        <td class="text-end"><?php echo htmlspecialchars($r['quantity'] ?? 0); ?></td>
                        <td class="text-end">
                          <a class="btn btn-sm btn-outline-primary" href="product_view.php?id=<?php echo intval($r['product_id'] ?? 0); ?>">View</a>
                          <a class="btn btn-sm btn-outline-success" href="warehouse_stock_add.php?warehouse_id=<?php echo intval($warehouse_id); ?>&product_id=<?php echo intval($r['product_id'] ?? 0); ?>">Adjust</a>
                          <a class="btn btn-sm btn-outline-danger"  href="warehouse_stock_delete.php?warehouse_id=<?= $warehouse_id ?>&product_id=<?= $r['product_id'] ?>"onclick="return confirm('Remove this stock?');">Remove</a>
                          
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>

            <?php else: ?>

              <div class="alert alert-info">
                <?php
                // display a helpful message with debug hints (server log has more)
                if ($stock_query_error) {
                    echo "Stock not loaded: " . htmlspecialchars($stock_query_error) . "<br>";
                    echo "<small>If your stock table uses different column names (e.g. batch_no), update the warehouse_view query accordingly.</small>";
                } else {
                    echo "No stock records found for this warehouse.";
                }
                ?>
              </div>

            <?php endif; ?>

          </div>
        </div>

      </div>
    </div>
  </main>
</div>
</body>
</html>
