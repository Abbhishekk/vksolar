<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__.'/../connect/db.php';
require_once __DIR__.'/../connect/auth_middleware.php';
$auth->requireAuth();
$auth->requirePermission('invoice_management', 'create');

$title = 'invoice_create';

// Check if editing existing invoice
$edit_id = (int)($_GET['id'] ?? 0);
$invoice = null;
$items = [];

if ($edit_id) {
    // Fetch invoice for editing
    $stmt = $conn->prepare("SELECT * FROM invoices WHERE id = ? AND status = 'draft' LIMIT 1");
    $stmt->bind_param('i', $edit_id);
    $stmt->execute();
    $invoice = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$invoice) {
        $_SESSION['inv_error'] = 'Invoice not found or cannot be edited.';
        header('Location: invoices.php');
        exit;
    }
    
    // Fetch invoice items
    $stmt = $conn->prepare("SELECT * FROM invoice_items WHERE invoice_id = ?");
    $stmt->bind_param('i', $edit_id);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$products   = $conn->query("SELECT id,name,sku FROM products ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$warehouses = $conn->query("SELECT id,name FROM warehouses ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$clients    = $conn->query("SELECT id,name FROM clients ORDER BY name")->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Create Invoice</title>
<?php require_once __DIR__.'/../include/head2.php'; ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php $cwd = getcwd(); chdir(__DIR__ . '/..'); include 'include/sidebar.php'; chdir($cwd); ?>
<div id="main-content">
<?php $cwd = getcwd(); chdir(__DIR__ . '/..'); include 'include/navbar.php'; chdir($cwd); ?>

<main class="container-fluid py-4">
<?php 
  if(isset($_SESSION['success'])) {
    echo '<div class="alert alert-success">'.$_SESSION['success'].'</div>';
    unset($_SESSION['success']);
  }
  if(isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger">'.$_SESSION['error'].'</div>';
    unset($_SESSION['error']);
  }
  if(isset($_SESSION['inv_error'])) {
    echo '<div class="alert-danger">'.$_SESSION['inv_error'].'</div>';
    unset($_SESSION['inv_error']);
  }
?>
<h3>🧾 <?= $invoice ? 'Edit Invoice' : 'Create Invoice' ?></h3>

<form method="post" action="invoice_save">

<?php if ($invoice): ?>
<input type="hidden" name="invoice_id" value="<?= $invoice['id'] ?>">
<?php endif; ?>

<div class="row mb-3">
  <div class="col-md-3">
    <label>Invoice Type</label>
    <select name="invoice_type" class="form-select" required>
      <option value="client" <?= ($invoice && $invoice['invoice_type'] === 'client') ? 'selected' : '' ?>>Customer</option>
      <option value="retailer" <?= ($invoice && $invoice['invoice_type'] === 'retailer') ? 'selected' : '' ?>>Retailer</option>
    </select>
  </div>

  <div class="col-md-3">
    <label>Customer / Retailer</label>
    <select name="reference_id" class="form-select" required>
      <?php foreach($clients as $c): ?>
        <option value="<?= $c['id'] ?>" <?= ($invoice && $invoice['reference_id'] == $c['id']) ? 'selected' : '' ?>>
          <?= htmlspecialchars($c['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="col-md-3">
    <label>Warehouse</label>
    <select name="warehouse_id" class="form-select" required>
      <?php foreach($warehouses as $w): ?>
        <option value="<?= $w['id'] ?>" <?= ($invoice && $invoice['warehouse_id'] == $w['id']) ? 'selected' : '' ?>>
          <?= htmlspecialchars($w['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="col-md-3">
    <label>Invoice Date</label>
    <input type="date" name="invoice_date" class="form-control" value="<?= $invoice ? $invoice['invoice_date'] : date('Y-m-d') ?>">
  </div>
</div>

<hr>

<table class="table table-bordered" id="itemsTable">
<thead class="table-light">
<tr>
  <th>Product</th>
  <th width="120">Qty</th>
  <th width="150">Rate</th>
  <th width="100">Stock</th>
  <th width="100">GST</th>
  <th width="100">SGST</th>
  <th width="100">CGST</th>
  <th width="150">Total</th>
  <th width="50"></th>
</tr>
</thead>
<tbody>
<?php if ($items): ?>
  <?php foreach ($items as $item): ?>
  <tr>
    <td>
      <select name="product_id[]" class="form-select product-select">
        <?php foreach($products as $p): ?>
          <option value="<?= $p['id'] ?>" <?= ($item['product_id'] == $p['id']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($p['name'].' ('.$p['sku'].')') ?>
          </option>
        <?php endforeach; ?>
      </select>
    </td>
    <td><input type="number" step="0.01" name="qty[]" class="form-control qty" value="<?= $item['quantity'] ?>"></td>
    <td><input type="number" step="0.01" name="rate[]" class="form-control rate" value="<?= $item['rate'] ?>"></td>
    <td><span class="stock-display text-muted">-</span></td>
    <td>
      <select name="gst" class="form-select product-select" onchange="splitGST(this)">
        <option value="">Select GST</option>
        <option value="5">5%</option>
        <option value="18">18%</option>
      </select>
    </td>
    
    <td>
      <input type="text" class="form-control" id="cgst" placeholder="CGST %" readonly>
    </td>
    
    <td>
      <input type="text" class="form-control" id="sgst" placeholder="SGST %" readonly>
    </td>
    <td class="line-total text-end"><?= number_format($item['line_total'], 2) ?></td>
    <td><button type="button" class="btn btn-danger btn-sm removeRow">×</button></td>
  </tr>
  <?php endforeach; ?>
<?php else: ?>
<tr>
  <td>
    <select name="product_id[]" class="form-select product-select">
      <?php foreach($products as $p): ?>
        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name'].' ('.$p['sku'].')') ?></option>
      <?php endforeach; ?>
    </select>
  </td>
  <td><input type="number" step="0.01" name="qty[]" class="form-control qty"></td>
  <td><input type="number" step="0.01" name="rate[]" class="form-control rate"></td>
  <td><span class="stock-display text-muted">-</span></td>
  <td>
      <select name="gst" class="form-select product-select" onchange="splitGST(this)">
        <option value="">Select GST</option>
        <option value="5">5%</option>
        <option value="18">18%</option>
      </select>
    </td>
    
    <td>
      <input type="text" class="form-control" id="cgst" placeholder="CGST %" readonly>
    </td>
    
    <td>
      <input type="text" class="form-control" id="sgst" placeholder="SGST %" readonly>
    </td>

  <td class="line-total text-end">0.00</td>
  
  <td><button type="button" class="btn btn-danger btn-sm removeRow">×</button></td>
</tr>
<?php endif; ?>
</tbody>
</table>

<button type="button" class="btn btn-secondary btn-sm" id="addRow">+ Add Item</button>

<hr>

<div class="row">
  <div class="col-md-4 ms-auto">
    <div class="mb-2">Subtotal: ₹ <span id="subtotal">0.00</span></div>
    <div class="mb-2">CGST (9%): ₹ <span id="cgst">0.00</span></div>
    <div class="mb-2">SGST (9%): ₹ <span id="sgst">0.00</span></div>
    <h5>Total: ₹ <span id="grandTotal">0.00</span></h5>
  </div>
</div>

<hr>

<button name="action" value="draft" class="btn btn-warning"><?= $invoice ? 'Update Draft' : 'Save Draft' ?></button>
<!--<button name="action" value="final" class="btn btn-success">Finalize Invoice</button>-->

</form>

</main>
</div>

<script>
function calc() {
  let subtotal = 0;
  document.querySelectorAll('#itemsTable tbody tr').forEach(tr => {
    let q = parseFloat(tr.querySelector('.qty').value||0);
    let r = parseFloat(tr.querySelector('.rate').value||0);
    let t = q*r;
    tr.querySelector('.line-total').innerText = t.toFixed(2);
    subtotal += t;
  });
  let cgst = subtotal*0.09;
  let sgst = subtotal*0.09;
  document.getElementById('subtotal').innerText = subtotal.toFixed(2);
  document.getElementById('cgst').innerText = cgst.toFixed(2);
  document.getElementById('sgst').innerText = sgst.toFixed(2);
  document.getElementById('grandTotal').innerText = (subtotal+cgst+sgst).toFixed(2);
}

function updateStock(row) {
  const warehouseId = document.querySelector('[name="warehouse_id"]').value;
  const productId = row.querySelector('.product-select').value;
  const stockDisplay = row.querySelector('.stock-display');
  
  if (!warehouseId || !productId) {
    stockDisplay.textContent = '-';
    return;
  }
  
  fetch(`get_stock?product_id=${productId}&warehouse_id=${warehouseId}`)
    .then(response => response.json())
    .then(data => {
      if (data.error) {
        stockDisplay.textContent = 'Error';
      } else {
        stockDisplay.textContent = data.stock;
        stockDisplay.className = data.stock > 0 ? 'stock-display text-success' : 'stock-display text-danger';
      }
    })
    .catch(() => {
      stockDisplay.textContent = 'Error';
    });
}

function updateAllStock() {
  document.querySelectorAll('#itemsTable tbody tr').forEach(updateStock);
}

document.addEventListener('input', e=>{
  if(e.target.classList.contains('qty') || e.target.classList.contains('rate')) calc();
});

document.addEventListener('change', e=>{
  if(e.target.classList.contains('product-select')) {
    updateStock(e.target.closest('tr'));
  }
  if(e.target.name === 'warehouse_id') {
    updateAllStock();
  }
});

document.getElementById('addRow').onclick = ()=>{
  let row = document.querySelector('#itemsTable tbody tr').cloneNode(true);
  row.querySelectorAll('input').forEach(i=>i.value='');
  row.querySelector('.stock-display').textContent = '-';
  row.querySelector('.stock-display').className = 'stock-display text-muted';
  document.querySelector('#itemsTable tbody').appendChild(row);
  updateStock(row);
};

document.addEventListener('click', e=>{
  if(e.target.classList.contains('removeRow')){
    const tbody = document.querySelector('#itemsTable tbody');
    if(tbody.children.length > 1) {
      e.target.closest('tr').remove();
    } else {
      // If it's the last row, clear it instead of removing
      const row = e.target.closest('tr');
      row.querySelectorAll('input').forEach(i => i.value = '');
      row.querySelector('.product-select').selectedIndex = 0;
      row.querySelector('.stock-display').textContent = '-';
      row.querySelector('.stock-display').className = 'stock-display text-muted';
      row.querySelector('.line-total').textContent = '0.00';
    }
    calc();
  }
});

// Update stock on page load
document.addEventListener('DOMContentLoaded', updateAllStock);
</script>
<script>
function splitGST(select) {
    const gst = parseFloat(select.value);

    if (!isNaN(gst)) {
        const half = gst / 2;
        document.getElementById('cgst').value = half + '%';
        document.getElementById('sgst').value = half + '%';
    } else {
        document.getElementById('cgst').value = '';
        document.getElementById('sgst').value = '';
    }
}
</script>


</body>
</html>
