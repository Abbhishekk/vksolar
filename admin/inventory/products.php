<?php
// admin/inventory/products.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php'; // $conn (mysqli)
require_once __DIR__ . '/../connect/auth_middleware.php';
$auth->requireAuth();
$auth->requirePermission('product_management', 'view');

$q = trim($_GET['q'] ?? '');

// fetch products with a primary image
$params = [];
$sql = "SELECT p.id,p.sku,p.name,p.brand,p.type,p.unit,p.default_selling_price,
               (SELECT filename FROM product_images WHERE product_images.product_id = p.id AND is_primary = 1 LIMIT 1) AS primary_image
        FROM products p";

if ($q !== '') {
    $sql .= " WHERE p.name LIKE ? OR p.sku LIKE ? OR p.brand LIKE ?";
    $like = "%$q%";
    $params = [$like,$like,$like];
}
$sql .= " ORDER BY p.id DESC LIMIT 200";

$stmt = $conn->prepare($sql);
if ($stmt === false) die("DB prepare error: ".$conn->error);
if (!empty($params)) $stmt->bind_param(str_repeat('s', count($params)), ...$params);
$stmt->execute();
$res = $stmt->get_result();
$products = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Products - Inventory</title>
  <?php $cwd=getcwd(); chdir(__DIR__.'/..'); include 'include/head2.php'; chdir($cwd); ?>
  <style>
    .product-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:18px}
    .product-card{border-radius:10px;overflow:hidden;box-shadow:0 6px 18px rgba(0,0,0,.08);background:#fff;display:flex;flex-direction:column}
    .media{height:140px;background:#f2f2f2;background-position:center;background-size:cover}
    .body{padding:12px;flex:1}
  </style>
</head>
<body>
<?php $cwd=getcwd(); chdir(__DIR__.'/..'); include 'include/sidebar.php'; chdir($cwd); ?>
<div id="main-content">
<?php $cwd=getcwd(); chdir(__DIR__.'/..'); include 'include/navbar.php'; chdir($cwd); ?>

<main class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Products</h2>
    <a href="product_form.php" class="btn btn-primary">+ Add Product</a>
  </div>

  <form class="row g-2 mb-4" method="get">
    <div class="col-auto"><input class="form-control" name="q" placeholder="Search by name, sku or brand" value="<?=htmlspecialchars($q)?>"></div>
    <div class="col-auto"><button class="btn btn-outline-secondary">Search</button></div>
  </form>

  <?php if(empty($products)): ?>
    <div class="card p-4"><div class="card-body">No products found.</div></div>
  <?php else: ?>
    <div class="product-grid">
      <?php foreach($products as $p):
        $img = $p['primary_image'] ? '/admin/inventory/uploads/products/'.htmlspecialchars($p['primary_image']) : null;
      ?>
        <div class="product-card">
          <div class="media" style="<?= $img ? "background-image:url('{$img}')" : '' ?>">
            <?php if(!$img): ?><div style="display:flex;align-items:center;justify-content:center;height:100%;color:#888">No image</div><?php endif; ?>
          </div>
          <div class="body">
            <div style="display:flex;justify-content:space-between;align-items:start">
              <div>
                <strong><?=htmlspecialchars($p['name'])?></strong><br>
                <div class="small-muted"><?=htmlspecialchars($p['sku'])?> · <?=htmlspecialchars($p['brand']?:'')?></div>
              </div>
              <div style="text-align:right">
                <div class="small-muted"><?=htmlspecialchars($p['type']?:'')?></div>
                <div style="font-weight:700;margin-top:8px;">₹ <?=number_format($p['default_selling_price'],2)?></div>
              </div>
            </div>

            <div class="mt-2 d-flex gap-2">
              <a class="btn btn-sm btn-light" href="product_form.php?id=<?=intval($p['id'])?>">Edit</a>
              <a class="btn btn-sm btn-outline-danger" href="product_delete.php?id=<?=intval($p['id'])?>" onclick="return confirm('Delete product?');">Delete</a>
              <a class="btn btn-sm btn-secondary ms-auto" href="product_view.php?id=<?=intval($p['id'])?>">View</a>
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
