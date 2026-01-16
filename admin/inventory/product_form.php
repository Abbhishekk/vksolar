<?php
// admin/inventory/product_form.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php';
require_once __DIR__ . '/../connect/auth_middleware.php';
$auth->requireAuth();
$auth->requirePermission('product_management', 'create');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$product = null; $images = []; $existing_serials = [];

if ($id) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ? LIMIT 1");
    $stmt->bind_param('i',$id); $stmt->execute(); $res=$stmt->get_result(); $product=$res->fetch_assoc(); $stmt->close();

    $q = $conn->prepare("SELECT id,filename,is_primary FROM product_images WHERE product_id=? ORDER BY is_primary DESC, id ASC");
    $q->bind_param('i',$id); $q->execute(); $r=$q->get_result(); while($row=$r->fetch_assoc()) $images[]=$row; $q->close();

    // existing serials if serial_tracked
    if (!empty($product) && $product['serial_tracked']) {
        $s = $conn->prepare("SELECT serial_number,status,warehouse_id FROM product_serials WHERE product_id=? ORDER BY id DESC LIMIT 500");
        $s->bind_param('i',$id); $s->execute(); $res=$s->get_result(); while($r=$res->fetch_assoc()) $existing_serials[]=$r; $s->close();
    }
}



?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><title><?= $id ? 'Edit' : 'Add' ?> Product</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <?php $cwd=getcwd(); chdir(__DIR__.'/..'); include 'include/head2.php'; chdir($cwd); ?>
<style>.img-thumb{width:110px;height:80px;object-fit:cover;border-radius:6px;border:1px solid #ddd;margin-right:8px}.images-row{display:flex;gap:8px;align-items:center;flex-wrap:wrap}</style>

</head>
<body>
<?php $cwd=getcwd(); chdir(__DIR__.'/..'); include 'include/sidebar.php'; chdir($cwd); ?>
<div id="main-content">
<?php $cwd=getcwd(); chdir(__DIR__.'/..'); include 'include/navbar.php'; chdir($cwd); ?>
<main class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3><?= $id ? 'Edit Product' : 'Add Product' ?></h3>
    <a href="products.php" class="btn btn-outline-secondary">← Back</a>
  </div>

  <form method="post" action="product_save" enctype="multipart/form-data" class="row g-3">
    <input type="hidden" name="id" value="<?= intval($product['id'] ?? 0) ?>">
    <div class="col-md-4">
      <label class="form-label">SKU</label>
      <input class="form-control" name="sku" required value="<?= htmlspecialchars($product['sku'] ?? '') ?>">
    </div>
    <div class="col-md-8">
      <label class="form-label">Name</label>
      <input class="form-control" name="name" required value="<?= htmlspecialchars($product['name'] ?? '') ?>">
    </div>
    <div class="col-md-4"><label class="form-label">Brand</label><input class="form-control" name="brand" value="<?= htmlspecialchars($product['brand'] ?? '') ?>"></div>
    <div class="col-md-4"><label class="form-label">Type</label><input class="form-control" name="type" value="<?= htmlspecialchars($product['type'] ?? '') ?>"></div>
    <div class="col-md-2"><label class="form-label">Unit</label><input class="form-control" name="unit" value="<?= htmlspecialchars($product['unit'] ?? 'pc') ?>"></div>
    <div class="col-md-2"><label class="form-label">Serial Tracked?</label>
      <select class="form-select" name="serial_tracked">
        <option value="0" <?= empty($product['serial_tracked']) ? 'selected' : '' ?>>No</option>
        <option value="1" <?= !empty($product['serial_tracked']) ? 'selected' : '' ?>>Yes</option>
      </select>
    </div>

    <div class="col-md-3"><label>Purchase Price</label><input type="number" step="0.01" name="default_purchase_price" class="form-control" value="<?= htmlspecialchars($product['default_purchase_price'] ?? '') ?>"></div>
    <div class="col-md-3"><label>Selling Price</label><input type="number" step="0.01" name="default_selling_price" class="form-control" value="<?= htmlspecialchars($product['default_selling_price'] ?? '') ?>"></div>
    <div class="col-md-3"><label>HSN</label><input class="form-control" name="hsn_code" value="<?= htmlspecialchars($product['hsn_code'] ?? '') ?>"></div>
    <div class="col-md-3"><label>Warranty (months)</label><input type="number" class="form-control" name="warranty_months" value="<?= htmlspecialchars($product['warranty_months'] ?? '') ?>"></div>

    <div class="col-12"><label>Description</label><textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($product['description'] ?? '') ?></textarea></div>

    <div class="col-md-6">
      <label>Specs (JSON)</label>
      <textarea name="specs" class="form-control" rows="6"><?= htmlspecialchars($product['specs'] ?? '') ?></textarea>
      <small class="text-muted">Optional JSON object for structured specs (power_w, vmp_v, etc.).</small>
    </div>

    <div class="col-md-6">
      <label>Images (multiple)</label>
      <input type="file" name="images[]" multiple accept="image/*" class="form-control">
      <div class="images-row mt-2">
        <?php foreach($images as $img): ?>
          <div style="position:relative">
            <img class="img-thumb" src="/admin/inventory/uploads/products/<?=htmlspecialchars($img['filename'])?>" alt="">
            <div style="position:absolute;right:6px;top:6px;">
              <a href="product_image_delete.php?id=<?=intval($img['id'])?>&product_id=<?=intval($id)?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete image?')">x</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Serial input area for serial_tracked products -->
    <div class="col-12" id="serialArea" style="<?= (!empty($product['serial_tracked'])? '':'display:none;') ?>">
      <label>Serial Numbers (one per line) — for new inventory add only</label>
      <textarea name="new_serials" class="form-control" rows="5" placeholder="Enter serial numbers, one per line"></textarea>
      <?php if(!empty($existing_serials)): ?>
        <div class="mt-2">
          <strong>Existing serials (latest 500):</strong>
          <div style="max-height:220px; overflow:auto; border:1px solid #eee; padding:8px; background:#fafafa;">
            <?php foreach($existing_serials as $s): ?>
              <div style="font-family:monospace"><?=htmlspecialchars($s['serial_number'])?> — <?=htmlspecialchars($s['status'])?> <?= $s['warehouse_id'] ? ' (W#'.$s['warehouse_id'].')':'' ?></div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <div class="col-12 text-end">
      <a class="btn btn-secondary me-2" href="products.php">Cancel</a>
      <button class="btn btn-primary"><?= $id ? 'Update' : 'Create' ?></button>
    </div>
  </form>
</main>
</div>
<script>
document.querySelector('select[name="serial_tracked"]').addEventListener('change', function(){
  document.getElementById('serialArea').style.display = this.value === '1' ? 'block' : 'none';
});
</script>
</body>
</html>
