<?php
// admin/inventory/supplier_form.php
require_once "connect/auth_middleware.php";
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php';
$auth->requirePermission('product_management', 'create');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$supplier = null;
if ($id) {
    $stmt = $conn->prepare("SELECT * FROM suppliers WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $supplier = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$cwd = getcwd(); chdir(__DIR__ . '/..'); include 'include/sidebar.php'; chdir($cwd);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title><?= $id ? 'Edit' : 'Add' ?> Supplier</title>
  <?php require_once __DIR__ . '/../include/head2.php'; ?>
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php $cwd = getcwd(); chdir(__DIR__ . '/..'); include 'include/sidebar.php'; chdir($cwd); ?>

<div id="main-content">
<?php $cwd = getcwd(); chdir(__DIR__ . '/..'); include 'include/navbar.php'; chdir($cwd); ?>

<main class="container py-4">
  <h3><?= $id ? 'Edit' : 'Add' ?> Supplier</h3>

  <form action="supplier_save" method="post" class="mt-3">
    <input type="hidden" name="id" value="<?= intval($id) ?>">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Supplier Name</label>
        <input name="name" class="form-control" required value="<?= htmlspecialchars($supplier['name'] ?? '') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Contact Person</label>
        <input name="contact_person" class="form-control" value="<?= htmlspecialchars($supplier['contact_person'] ?? '') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Phone</label>
        <input name="phone" class="form-control" value="<?= htmlspecialchars($supplier['phone'] ?? '') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Email</label>
        <input name="email" class="form-control" value="<?= htmlspecialchars($supplier['email'] ?? '') ?>">
      </div>
      <div class="col-12">
        <label class="form-label">Address</label>
        <textarea name="address" class="form-control" rows="3"><?= htmlspecialchars($supplier['address'] ?? '') ?></textarea>
      </div>

      <div class="col-12 text-end">
        <a href="suppliers.php" class="btn btn-secondary">Cancel</a>
        <button class="btn btn-success">Save Supplier</button>
      </div>
    </div>
  </form>
</main>

</div>
</body>
</html>
