<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../connect/db.php';
require_once __DIR__ . '/../connect/auth_middleware.php';
$auth->requirePermission('invoice_management', 'create');

$title = 'purchase_invoice_create';

/* ================= MASTER DATA ================= */
$products = $conn->query("
    SELECT id, name, sku 
    FROM products 
    ORDER BY name
")->fetch_all(MYSQLI_ASSOC);

$warehouses = $conn->query("
    SELECT id, name 
    FROM warehouses 
    ORDER BY name
")->fetch_all(MYSQLI_ASSOC);

$manufacturers = $conn->query("
    SELECT id, name 
    FROM product_manufacturers 
    WHERE is_active = 1 
    ORDER BY name
")->fetch_all(MYSQLI_ASSOC);

/* ================= HANDLE SUBMIT ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $warehouse_id     = (int)($_POST['warehouse_id'] ?? 0);
    $manufacturer_id  = (int)($_POST['manufacturer_id'] ?? 0);
    $user_id          = $_SESSION['user_id'] ?? 0;

    if ($warehouse_id <= 0) {
        $_SESSION['inv_error'] = 'Please select warehouse';
        header("Location: purchase_invoice_create.php");
        exit;
    }

    if ($manufacturer_id <= 0) {
        $_SESSION['inv_error'] = 'Please select manufacturer';
        header("Location: purchase_invoice_create.php");
        exit;
    }

    if (empty($_POST['items']) || !is_array($_POST['items'])) {
        $_SESSION['inv_error'] = 'Please add at least one product';
        header("Location: purchase_invoice_create.php");
        exit;
    }

    $conn->begin_transaction();

    try {

        /* 1️⃣ CREATE PURCHASE ORDER */
        $po_number = 'PO-' . date('Ymd-His');

        $stmt = $conn->prepare("
            INSERT INTO purchase_orders
            (po_number, manufacturer_id, warehouse_id, status, created_by)
            VALUES (?, ?, ?, 'pending', ?)
        ");
        $stmt->bind_param(
            "siii",
            $po_number,
            $manufacturer_id,
            $warehouse_id,
            $user_id
        );
        $stmt->execute();
        $purchase_order_id = $stmt->insert_id;
        $stmt->close();

        /* 2️⃣ INSERT PURCHASE ITEMS */
        foreach ($_POST['items'] as $item) {

            if (
                empty($item['product']) ||
                empty($item['qty']) ||
                empty($item['price'])
            ) {
                throw new Exception('Invalid item row detected');
            }

            $product_id = (int)$item['product'];
            $qty        = (float)$item['qty'];
            $rate       = (float)$item['price'];

            if ($qty <= 0 || $rate <= 0) {
                throw new Exception('Quantity and price must be greater than zero');
            }

            $total = $qty * $rate;

            $stmt = $conn->prepare("
                INSERT INTO purchase_order_items
                (purchase_order_id, product_id, quantity, rate, total)
                VALUES (?,?,?,?,?)
            ");
            $stmt->bind_param(
                "iiddd",
                $purchase_order_id,
                $product_id,
                $qty,
                $rate,
                $total
            );
            $stmt->execute();
            $stmt->close();
        }

        $conn->commit();

        $_SESSION['inv_success'] = 'Purchase Order Created (Pending Warehouse Approval)';
        header("Location: purchase_invoice_list.php");
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['inv_error'] = $e->getMessage();
        header("Location: purchase_invoice_create.php");
        exit;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Create Purchase Order</title>

<?php require_once __DIR__ . '/../include/head2.php'; ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
<?php
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

<div class="container-fluid py-4">
<div class="card shadow">
<div class="card-header bg-primary text-white">
<h5>Create Purchase Order</h5>
</div>

<div class="card-body">

<?php if (!empty($_SESSION['inv_error'])): ?>
<div class="p-5 alert-danger">
<?= htmlspecialchars($_SESSION['inv_error']); unset($_SESSION['inv_error']); ?>
</div>
<?php endif; ?>

<form method="post" id="purchaseForm">

<div class="row mb-3">

  <div class="col-md-4">
    <label>Manufacturer</label>
    <select name="manufacturer_id" class="form-select" required>
      <option value="">Select Manufacturer</option>
      <?php foreach ($manufacturers as $m): ?>
        <option value="<?= $m['id'] ?>">
          <?= htmlspecialchars($m['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="col-md-4">
    <label>Warehouse</label>
    <select name="warehouse_id" class="form-select" required>
      <option value="">Select Warehouse</option>
      <?php foreach ($warehouses as $w): ?>
        <option value="<?= $w['id'] ?>"><?= htmlspecialchars($w['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="col-md-4 text-end pt-4">
    <a href="/admin/invoice/manufacturer_list.php"
       class="btn btn-sm btn-outline-primary"
       target="_blank">
       ➕ Manage Manufacturers
    </a>
  </div>

</div>

<table class="table table-bordered">
<thead>
<tr>
<th>Product</th>
<th width="120">Qty</th>
<th width="150">Price</th>
<th width="60"></th>
</tr>
</thead>
<tbody id="items"></tbody>
</table>

<button type="button" class="btn btn-sm btn-secondary" onclick="addRow()">+ Add Item</button>
<br><br>
<button class="btn btn-success">Save Purchase Order</button>

</form>

</div>
</div>
</div>
</div>

<script>
const products = <?= json_encode($products) ?>;
let rowIndex = 0;

function addRow() {
  const tr = document.createElement('tr');
  tr.innerHTML = `
    <td>
      <select name="items[${rowIndex}][product]" class="form-select" required>
        ${products.map(p => `<option value="${p.id}">${p.name}</option>`).join('')}
      </select>
    </td>
    <td>
      <input name="items[${rowIndex}][qty]" type="number" class="form-control" min="1" required>
    </td>
    <td>
      <input name="items[${rowIndex}][price]" type="number" step="0.01" class="form-control" required>
    </td>
    <td>
      <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove()">×</button>
    </td>
  `;
  document.getElementById('items').appendChild(tr);
  rowIndex++;
}

document.getElementById('purchaseForm').addEventListener('submit', function(e){
  if (document.querySelectorAll('#items tr').length === 0) {
    alert('Please add at least one product row');
    e.preventDefault();
  }
});
</script>

</body>
</html>
