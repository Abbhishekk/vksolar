<?php
// admin/warehousemanagement/warehouses_cards.php

if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php';
require_once __DIR__ . '/../connect/auth_middleware.php';

$auth->requireAuth();
$auth->requirePermission('inventory_management', 'view');

$flash_success = $_SESSION['inv_success'] ?? null;
$flash_error   = $_SESSION['inv_errors'] ?? null;
unset($_SESSION['inv_success'], $_SESSION['inv_errors']);

$warehouses = [];

$current_role = $_SESSION['role'] ?? '';
$user_id = $_SESSION['user_id'] ?? 0;

if ($current_role === 'warehouse_staff') {
    $user_id = $_SESSION['employee_id'] ?? 0;

    $stmt = $conn->prepare("
        SELECT id, name, code, address, city, image
        FROM warehouses
        WHERE id IN (SELECT warehouse_id FROM warehouse_employees WHERE employee_id = ?)
        ORDER BY id DESC
    ");
    $stmt->bind_param('i', $user_id);
} else {
    $stmt = $conn->prepare("
        SELECT id, name, code, address, city, image
        FROM warehouses
        ORDER BY id DESC
    ");
}

$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $warehouses[] = $row;
}
$stmt->close();

/* ===== Resolve image from uploads folder ===== */
function resolveWarehouseImage($filename) {
    if (!$filename) return null;

    $path = __DIR__ . '/uploads/' . $filename;
    if (file_exists($path)) {
        return 'uploads/' . $filename;
    }
    return null;
}
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Warehouses</title>

<?php require_once __DIR__ . '/../include/head2.php'; ?>

<style>
.warehouse-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 24px;
}

.warehouse-card {
    height: 220px;
    border-radius: 14px;
    overflow: hidden;
    display: flex;
    align-items: flex-end;
    background-size: cover;
    background-position: center;
    position: relative;
    box-shadow: 0 12px 30px rgba(0,0,0,0.15);
    transition: all .2s ease;
    color:#fff;
}
.warehouse-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 20px 45px rgba(0,0,0,0.25);
}

.warehouse-card::before {
    content:'';
    position:absolute;
    inset:0;
    background: linear-gradient(to top, rgba(0,0,0,.65), rgba(0,0,0,.15));
}

.card-content {
    position:relative;
    z-index:2;
    padding:20px;
    width:100%;
}

.no-image {
    background: linear-gradient(180deg,#6c757d,#495057);
}
</style>
</head>

<body>

<?php include __DIR__ . '/../include/sidebar.php'; ?>
<div id="main-content">
<?php include __DIR__ . '/../include/navbar.php'; ?>

<main class="container-fluid py-4">

<div class="d-flex justify-content-between align-items-center mb-4">
<h3 class="mb-0">Warehouses</h3>
<a href="warehouses.php" class="btn btn-primary">+ Manage Warehouses</a>
</div>

<?php if ($flash_success): ?>
<div class="alert alert-success"><?= htmlspecialchars($flash_success) ?></div>
<?php endif; ?>

<?php if ($flash_error): ?>
<div class="alert alert-danger">
<?php foreach ($flash_error as $err) echo htmlspecialchars($err)."<br>"; ?>
</div>
<?php endif; ?>

<?php if (empty($warehouses)): ?>
<div class="card p-4">
No warehouses available.
</div>
<?php else: ?>

<div class="warehouse-grid">
<?php foreach ($warehouses as $w):

$id   = $w['id'];
$name = $w['name'];
$code = $w['code'];
$city = $w['city'];
$addr = $w['address'];
$img  = resolveWarehouseImage($w['image']);
?>

<div class="warehouse-card <?= $img ? '' : 'no-image' ?>"
     style="<?= $img ? "background-image:url('$img');" : '' ?>">

<div class="card-content">

<h5><?= htmlspecialchars($name) ?></h5>

<div class="small mb-2">
<?= htmlspecialchars($code) ?>
<?php if ($city): ?> • <?= htmlspecialchars($city) ?><?php endif; ?>
</div>

<?php if ($addr): ?>
<div class="small mb-3">
<?= htmlspecialchars($addr) ?>
</div>
<?php endif; ?>

<div class="d-flex justify-content-between">

<a href="warehouse_view.php?id=<?= $id ?>"
class="btn btn-light btn-sm">
Open
</a>

<div>
<a href="warehouses.php?edit=<?= $id ?>"
class="btn btn-outline-light btn-sm">
Edit
</a>

<a href="warehouses.php?delete=<?= $id ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('Delete this warehouse?')">
Delete
</a>
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
