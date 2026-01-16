<?php
// admin/inventory/suppliers.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php';
require_once __DIR__ . '/../connect/auth_middleware.php';
$auth->requireAuth();
$auth->requirePermission('inventory_management', 'view');

// flash
$flash = $_SESSION['sup_flash'] ?? null;
unset($_SESSION['sup_flash']);

// fetch suppliers
$sups = [];
$res = $conn->query("SELECT id, name, contact_person, phone, email, created_at FROM suppliers ORDER BY name ASC");
if ($res) while ($r = $res->fetch_assoc()) $sups[] = $r;

$cwd = getcwd(); chdir(__DIR__ . '/..'); include 'include/sidebar.php'; chdir($cwd);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Suppliers</title>
  <?php require_once __DIR__ . '/../include/head2.php'; ?>
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php $cwd = getcwd(); chdir(__DIR__ . '/..'); include 'include/sidebar.php'; chdir($cwd); ?>

<div id="main-content">
<?php $cwd = getcwd(); chdir(__DIR__ . '/..'); include 'include/navbar.php'; chdir($cwd); ?>

<main class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Suppliers</h3>
    <a href="supplier_form.php" class="btn btn-primary">+ Add Supplier</a>
  </div>

  <?php if ($flash): ?>
    <div class="alert alert-success"><?= htmlspecialchars($flash) ?></div>
  <?php endif; ?>

  <div class="card">
    <div class="card-body p-2">
      <?php if (empty($sups)): ?>
        <p class="m-0">No suppliers found.</p>
      <?php else: ?>
        <table class="table table-sm table-hover mb-0">
          <thead>
            <tr>
              <th>Name</th>
              <th>Contact</th>
              <th>Phone</th>
              <th>Email</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($sups as $s): ?>
              <tr>
                <td><?= htmlspecialchars($s['name']) ?></td>
                <td><?= htmlspecialchars($s['contact_person']) ?></td>
                <td><?= htmlspecialchars($s['phone']) ?></td>
                <td><?= htmlspecialchars($s['email']) ?></td>
                <td class="text-end">
                  <a class="btn btn-sm btn-outline-secondary" href="supplier_form.php?id=<?= intval($s['id']) ?>">Edit</a>
                  <a class="btn btn-sm btn-danger" href="supplier_delete.php?id=<?= intval($s['id']) ?>" onclick="return confirm('Delete supplier?')">Delete</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>
</main>
</div>
</body>
</html>
