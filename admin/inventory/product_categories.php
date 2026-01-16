<?php
// admin/inventory/product_categories.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php'; // provides $conn (mysqli)
require_once __DIR__ . '/../connect/auth_middleware.php';
$auth->requireAuth();
$auth->requirePermission('product_management', 'view');

// flash
$flash = $_SESSION['cat_flash'] ?? null;
unset($_SESSION['cat_flash']);

// fetch categories
$cats = [];
$res = $conn->query("SELECT id, name, slug, parent_id, created_at FROM product_categories ORDER BY name ASC");
if ($res) while ($r = $res->fetch_assoc()) $cats[] = $r;

// includes (safe)
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Product Categories</title>
  <?php require_once __DIR__ . '/../include/head2.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
 
</head>
<body>
<?php $cwd = getcwd(); chdir(__DIR__ . '/..'); include 'include/sidebar.php'; chdir($cwd); ?>

<div id="main-content">
<?php $cwd = getcwd(); chdir(__DIR__ . '/..'); include 'include/navbar.php'; chdir($cwd); ?>

<main class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Product Categories</h3>
    <a href="product_category_form.php" class="btn btn-primary">+ Add Category</a>
  </div>

  <?php if ($flash): ?>
    <div class="alert alert-success"><?= htmlspecialchars($flash) ?></div>
  <?php endif; ?>

  <div class="card">
    <div class="card-body p-2">
      <?php if (empty($cats)): ?>
        <p class="m-0">No categories found.</p>
      <?php else: ?>
        <table class="table table-sm table-hover mb-0">
          <thead>
            <tr>
              <th>Name</th>
              <th>Slug</th>
              <th>Parent</th>
              <th>Created</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($cats as $c): 
                $parent = '';
                if ($c['parent_id']) {
                    $q = $conn->prepare("SELECT name FROM product_categories WHERE id = ? LIMIT 1");
                    $q->bind_param('i', $c['parent_id']);
                    $q->execute();
                    $rr = $q->get_result()->fetch_assoc();
                    $parent = $rr['name'] ?? '';
                    $q->close();
                }
            ?>
              <tr>
                <td><?= htmlspecialchars($c['name']) ?></td>
                <td><?= htmlspecialchars($c['slug']) ?></td>
                <td><?= htmlspecialchars($parent) ?></td>
                <td><?= htmlspecialchars($c['created_at']) ?></td>
                <td class="text-end">
                  <a class="btn btn-sm btn-outline-secondary" href="product_category_form.php?id=<?= intval($c['id']) ?>">Edit</a>
                  <a class="btn btn-sm btn-danger" href="product_category_delete.php?id=<?= intval($c['id']) ?>" onclick="return confirm('Delete category?')">Delete</a>
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
