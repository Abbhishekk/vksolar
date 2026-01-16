<?php
// admin/inventory/warehouses.php
// Copy-paste this file and replace existing warehouses.php
// NOTES / QUICK TROUBLESHOOT:
// - This script will try to locate images in these public URL prefixes:
//     /admin/inventory/uploads/<filename>
//     /admin/uploads/<filename>
//   It checks actual filesystem existence using $_SERVER['DOCUMENT_ROOT']
// - If your images are in a different folder, update $upload_candidates accordingly.
// - Make sure filenames stored in DB (clients->image) are correct and not empty.
// - If still not showing: check browser devtools network tab for 404 when loading the image URL.

if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php'; // provides $conn (mysqli)
require_once __DIR__ . '/../connect/auth_middleware.php';
$auth->requireAuth();
$auth->requirePermission('inventory_management', 'view');

// flash messages
$flash_success = $_SESSION['inv_success'] ?? null;
$flash_error = $_SESSION['inv_error'] ?? null;
unset($_SESSION['inv_success'], $_SESSION['inv_error']);

// Fetch warehouses - adapt columns to your table schema (id, name, code, address, city, image)
$warehouses = [];

$role = $_SESSION['role'] ?? '';
$userId = $_SESSION['user_id'] ?? null;
$employeeId = $_SESSION['employee_id'] ?? null;
// print_r($_SESSION);
if ($role === 'warehouse_staff') {

    // 🔒 Warehouse staff → only their warehouse
    $stmt = $conn->prepare("
        SELECT w.id, w.name, w.code, w.address, w.city, w.image
        FROM warehouses w
        INNER JOIN warehouse_employees we ON we.warehouse_id = w.id
        WHERE we.employee_id = ?
        LIMIT 1
    ");
    $stmt->bind_param("i", $employeeId);

} else {

    // 👑 Admin / others → all warehouses
    $stmt = $conn->prepare("
        SELECT id, name, code, address, city, image
        FROM warehouses
        ORDER BY id DESC
    ");
}

if ($stmt) {
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $warehouses[] = $row;
    }
    $stmt->close();
}
 else {
    error_log("warehouses.php: prepare failed: " . $conn->error);
}

//
// Helper: resolve image URL by checking candidate locations on disk.
// Returns a URL path (starting with /) or null if not found.
//
function resolveWarehouseImageUrl($filename) {
    if (!$filename) return null;

    // Candidate public URLs (try these in order)
    $candidates = [
        '/admin/inventory/uploads/' . $filename,
        '/admin/uploads/' . $filename,
        '/uploads/' . $filename, // fallback common path
    ];

    foreach ($candidates as $url) {
        $full = $_SERVER['DOCUMENT_ROOT'] . $url;
        if (file_exists($full) && is_file($full)) {
            // return the URL (browser can load)
            return $url;
        }
    }
    // not found
    return null;
}

?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Warehouses - Inventory</title>

  <?php
  // include head (safe chdir pattern used by you)
  $cwd = getcwd();
  chdir(__DIR__ . '/..');
  include 'include/head2.php';
  chdir($cwd);
  ?>

  <style>
    :root{ --card-height:220px; --card-radius:12px; --card-pad:20px; }
    .warehouse-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
      gap: 24px;
      align-items: start;
    }

    .warehouse-card {
      position: relative;
      height: var(--card-height);
      border-radius: var(--card-radius);
      overflow: hidden;
      box-shadow: 0 8px 24px rgba(0,0,0,0.12);
      cursor: pointer;
      transition: transform .15s ease, box-shadow .15s ease;
      display: flex;
      align-items: flex-end;
      color: #fff;
      background-color: #6c757d;
      background-repeat: no-repeat;
      background-position: center;
      background-size: cover; /* ensures image covers entire card */
    }

    .warehouse-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 18px 40px rgba(0,0,0,0.18);
    }

    /* subtle dark overlay for readability */
    .warehouse-card::before{
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(to top, rgba(0,0,0,0.55), rgba(0,0,0,0.18));
      z-index: 1;
    }

    .warehouse-card .card-content{
      position: relative;
      z-index: 2;
      padding: var(--card-pad);
      width: 100%;
    }

    .warehouse-card h5 {
      margin: 0 0 6px 0;
      font-weight: 700;
      font-size: 1.15rem;
    }
    .warehouse-card .meta {
      font-size: .95rem;
      opacity: .95;
      margin-bottom: 10px;
    }

    .card-actions {
      display:flex;
      gap:10px;
      align-items:center;
    }
    .card-actions .btn { font-size:.90rem; padding:.45rem .75rem; }

    /* fallback when image missing - a simple textured panel */
    .warehouse-card.no-image {
      background: linear-gradient(180deg,#7a7f84,#5f6468);
      color:#fff;
    }

    .page-actions { margin-bottom: 22px; display:flex; gap:12px; align-items:center; justify-content:space-between; }

    @media (max-width:600px){
      :root{ --card-height:200px; }
    }
  </style>
</head>
<body>

<?php
// includes using safe chdir trick so include/sidebar.php works relative to admin/
$cwd = getcwd();
chdir(__DIR__ . '/..');
include 'include/sidebar.php';
chdir($cwd);
?>

<div id="main-content">
  <?php
  $cwd = getcwd();
  chdir(__DIR__ . '/..');
  include 'include/navbar.php';
  chdir($cwd);
  ?>

  <main class="main container-fluid py-4">
    <div class="page-actions">
      <h2 style="margin:0;">Warehouses</h2>
      <div>
        <a href="warehouse_create.php" class="btn btn-primary">+ Add Warehouse</a>
      </div>
    </div>

    <?php if ($flash_success): ?>
      <div class="alert alert-success"><?= htmlspecialchars($flash_success) ?></div>
    <?php endif; ?>
    <?php if ($flash_error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($flash_error) ?></div>
    <?php endif; ?>

    <?php if (empty($warehouses)): ?>
      <div class="card p-4">
        <div class="card-body">
          <p>No warehouses yet. Create one.</p>
        </div>
      </div>
    <?php else: ?>
      <div class="warehouse-grid">
        <?php foreach ($warehouses as $w):
            // make sure keys exist in DB row (adjust if different column names)
            $id   = intval($w['id']);
            $name = $w['name'] ?? 'Unnamed';
            $code = $w['code'] ?? '';
            $city = $w['city'] ?? '';
            $addr = $w['address'] ?? '';
            $imgFile = $w['image'] ?? '';

            // resolve image URL robustly
            $imgUrl = resolveWarehouseImageUrl($imgFile);
            $style = $imgUrl ? "background-image: url('" . htmlspecialchars($imgUrl, ENT_QUOTES) . "');" : '';
            $hasImage = (bool)$imgUrl;
        ?>
          <div class="warehouse-card <?= $hasImage ? '' : 'no-image' ?>" style="<?= $style ?>">
            <div class="card-content">
              <div>
                <h5><?= htmlspecialchars($name) ?></h5>
                <div class="meta"><?= $code ? htmlspecialchars($code) . ' • ' : '' ?><?= htmlspecialchars($city) ?></div>
                <?php if ($addr): ?>
                  <div class="small" style="opacity:.9; margin-bottom:8px;"><?= htmlspecialchars($addr) ?></div>
                <?php endif; ?>
              </div>

              <div class="d-flex justify-content-between align-items-center" style="margin-top:8px;">
                <a class="btn btn-light" href="warehouse_view.php?id=<?= $id ?>">Open</a>

                <div class="card-actions">
                  <a class="btn btn-outline-light" href="warehouse_create.php?id=<?= $id ?>">Edit</a>
                  <a class="btn btn-danger" href="warehouse_delete?id=<?= $id ?>"
                     onclick="return confirm('Delete warehouse? This will remove assignments and stock.');">Delete</a>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>
</div>

</body>
</html>
