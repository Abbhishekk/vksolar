<?php
// admin/inventory/product_category_form.php


if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php';
require_once __DIR__ . '/../connect/auth_middleware.php';
$auth->requirePermission('product_management', 'view');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$cat = null;
if ($id) {
    $stmt = $conn->prepare("SELECT * FROM product_categories WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $cat = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// fetch categories for parent select (exclude self)
$parents = [];
$qr = $conn->prepare("SELECT id, name FROM product_categories WHERE id != ? ORDER BY name");
$qr->bind_param('i', $id);
$qr->execute();
$res = $qr->get_result();
while ($r = $res->fetch_assoc()) $parents[] = $r;
$qr->close();

$cwd = getcwd(); chdir(__DIR__ . '/..'); include 'include/sidebar.php'; chdir($cwd);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title><?= $id ? 'Edit' : 'Add' ?> Category</title>
  <?php require_once __DIR__ . '/../include/head2.php'; ?>
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php $cwd = getcwd(); chdir(__DIR__ . '/..'); include 'include/sidebar.php'; chdir($cwd); ?>

<div id="main-content">
<?php $cwd = getcwd(); chdir(__DIR__ . '/..'); include 'include/navbar.php'; chdir($cwd); ?>

<main class="container py-4">
  <h3><?= $id ? 'Edit' : 'Add' ?> Category</h3>

  <form action="product_category_save" method="post" class="mt-3">
    <input type="hidden" name="id" value="<?= intval($id) ?>">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Name</label>
        <input name="name" class="form-control" required value="<?= htmlspecialchars($cat['name'] ?? '') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Slug</label>
        <input name="slug" class="form-control" value="<?= htmlspecialchars($cat['slug'] ?? '') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Parent Category</label>
        <select name="parent_id" class="form-select">
          <option value="">-- none --</option>
          <?php foreach ($parents as $p): ?>
            <option value="<?= $p['id'] ?>" <?= (isset($cat['parent_id']) && $cat['parent_id']==$p['id'])?'selected':'' ?>><?= htmlspecialchars($p['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-12 text-end">
        <a href="product_categories.php" class="btn btn-secondary">Cancel</a>
        <button class="btn btn-success">Save</button>
      </div>
    </div>
  </form>
</main>
</div>
</body>
</html>
