<?php
// admin/inventory/product_view.php
require_once "connect/auth_middleware.php";
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php';
$auth->requirePermission('inventory_management', 'view');

$id = intval($_GET['id'] ?? 0);
if (!$id) { header('Location: products.php'); exit; }

$stmt = $conn->prepare("SELECT * FROM products WHERE id=? LIMIT 1");
$stmt->bind_param('i',$id); $stmt->execute(); $res=$stmt->get_result(); $product=$res->fetch_assoc(); $stmt->close();
if (!$product) { header('Location: products.php'); exit; }

$imgs = []; $r = $conn->prepare("SELECT id,filename,is_primary,caption FROM product_images WHERE product_id=? ORDER BY is_primary DESC, id ASC");
$r->bind_param('i',$id); $r->execute(); $rr = $r->get_result(); while($row=$rr->fetch_assoc()) $imgs[]=$row; $r->close();

// stock per warehouse
$stocks = [];
$sq = $conn->prepare("SELECT ws.id,ws.warehouse_id,ws.quantity,w.name AS warehouse_name FROM warehouse_stock ws LEFT JOIN warehouses w ON w.id = ws.warehouse_id WHERE ws.product_id = ? ORDER BY ws.id DESC");
$sq->bind_param('i',$id); $sq->execute(); $sr = $sq->get_result(); while($rw=$sr->fetch_assoc()) $stocks[]=$rw; $sq->close();

// serials
$serials = [];
if ($product['serial_tracked']) {
    $sq2 = $conn->prepare("SELECT id,serial_number,status,warehouse_id,created_at FROM product_serials WHERE product_id=? ORDER BY id DESC LIMIT 500");
    $sq2->bind_param('i',$id); $sq2->execute(); $sr2 = $sq2->get_result(); while($r2=$sr2->fetch_assoc()) $serials[]=$r2; $sq2->close();
}

?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><title>Product: <?=htmlspecialchars($product['name'])?></title>
  <?php require_once __DIR__ . '/../include/head2.php'; ?>
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>.img-thumb{width:160px;height:110px;object-fit:cover;border-radius:6px;border:1px solid #ddd;margin-right:8px}</style>
</head>
<body>
    
<?php $cwd = getcwd(); chdir(__DIR__ . '/..'); include 'include/sidebar.php'; chdir($cwd); ?>
<div id="main-content">
<?php $cwd = getcwd(); chdir(__DIR__ . '/..'); include 'include/navbar.php'; chdir($cwd); ?>
<main class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3><?=htmlspecialchars($product['name'])?></h3>
    <div>
      <a href="product_form.php?id=<?=$id?>" class="btn btn-outline-secondary">Edit</a>
      <a href="products.php" class="btn btn-secondary">Back</a>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-md-4">
      <div class="card p-3">
        <h6>Images</h6>
        <div class="d-flex flex-wrap">
          <?php foreach($imgs as $im): ?>
            <div>
              <img class="img-thumb" src="/admin/inventory/uploads/products/<?=htmlspecialchars($im['filename'])?>" alt="">
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="card p-3 mt-3">
        <h6>Stock by Warehouse</h6>
        <?php if(empty($stocks)): ?>
          <div>No stock entries.</div>
        <?php else: ?>
          <ul class="list-group">
            <?php foreach($stocks as $st): ?>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <?=htmlspecialchars($st['warehouse_name'] ?: 'W#'.$st['warehouse_id'])?>
                <span class="badge bg-primary rounded-pill"><?= $st['quantity'] + 0 ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>

    <div class="col-md-8">
      <div class="card p-3 mb-3">
        <h6>Details</h6>
        <table class="table table-sm">
          <tr><th>SKU</th><td><?=htmlspecialchars($product['sku'])?></td></tr>
          <tr><th>Brand</th><td><?=htmlspecialchars($product['brand'])?></td></tr>
          <tr><th>Type</th><td><?=htmlspecialchars($product['type'])?></td></tr>
          <tr><th>Unit</th><td><?=htmlspecialchars($product['unit'])?></td></tr>
          <tr><th>Purchase / Selling Price</th><td>₹ <?=number_format($product['default_purchase_price'],2)?> / ₹ <?=number_format($product['default_selling_price'],2)?></td></tr>
          <tr><th>HSN</th><td><?=htmlspecialchars($product['hsn_code'])?></td></tr>
          <tr><th>Warranty</th><td><?=intval($product['warranty_months'])?> months</td></tr>
        </table>
        <div><strong>Description</strong><div class="mt-2"><?=nl2br(htmlspecialchars($product['description']))?></div></div>
        <div class="mt-3"><strong>Specs (JSON)</strong><pre style="background:#f8f9fa;padding:8px;border-radius:6px"><?=htmlspecialchars($product['specs'])?></pre></div>
      </div>

      <?php if($product['serial_tracked']): ?>
        <div class="card p-3">
          <h6>Serials (latest)</h6>
          <?php if(empty($serials)): ?><div>No serials yet.</div><?php else: ?>
            <table class="table table-sm">
              <thead><tr><th>Serial</th><th>Status</th><th>Warehouse</th><th>Added</th></tr></thead>
              <tbody>
                <?php foreach($serials as $s): ?>
                  <tr><td style="font-family:monospace"><?=htmlspecialchars($s['serial_number'])?></td><td><?=htmlspecialchars($s['status'])?></td><td><?=htmlspecialchars($s['warehouse_id']?:'')?></td><td><?=htmlspecialchars($s['created_at']?:'')?></td></tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </div>
      <?php endif; ?>

    </div>
  </div>
</main>
</div>
</body>
</html>
