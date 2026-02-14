<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../connect/db.php';
require_once __DIR__ . '/../connect/auth_middleware.php';
$auth->requirePermission('invoice_management', 'create');

$title = 'purchase_invoice_create';

/* ================= MASTER DATA ================= */

$categories = $conn->query("
    SELECT id, category_name
    FROM product_categories
    ORDER BY category_name
")->fetch_all(MYSQLI_ASSOC);

$warehouses = $conn->query("
    SELECT id, name
    FROM warehouses
    ORDER BY name
")->fetch_all(MYSQLI_ASSOC);


/* ================= HANDLE SUBMIT ================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $category_id     = (int)($_POST['category_id'] ?? 0);
    $manufacturer_id = (int)($_POST['manufacturer_id'] ?? 0);
    $warehouse_id    = (int)($_POST['warehouse_id'] ?? 0);
    $user_id         = $_SESSION['user_id'] ?? 0;

    if ($category_id <= 0 || $manufacturer_id <= 0 || $warehouse_id <= 0) {
        $_SESSION['inv_error'] = "Please fill all required fields.";
        header("Location: purchase_invoice_create.php");
        exit;
    }

    if (empty($_POST['items'])) {
        $_SESSION['inv_error'] = "Add at least one product.";
        header("Location: purchase_invoice_create.php");
        exit;
    }

    $conn->begin_transaction();

    try {

        $po_number = 'PO-' . date('Ymd-His');

        /* 1️⃣ INSERT PURCHASE ORDER */
        $stmt = $conn->prepare("
            INSERT INTO purchase_orders
            (po_number, manufacturer_id, warehouse_id, status, created_by)
            VALUES (?, ?, ?, 'pending', ?)
        ");
        $stmt->bind_param("siii",
            $po_number,
            $manufacturer_id,
            $warehouse_id,
            $user_id
        );
        $stmt->execute();
        $purchase_order_id = $stmt->insert_id;
        $stmt->close();

        /* 2️⃣ INSERT ITEMS */
        foreach ($_POST['items'] as $item) {

            $product_id = (int)$item['product'];
            $qty        = (float)$item['qty'];
            $rate       = (float)$item['price'];

            if ($product_id <= 0 || $qty <= 0 || $rate <= 0) {
                throw new Exception("Invalid product row.");
            }

            $total = $qty * $rate;

            $stmt = $conn->prepare("
                INSERT INTO purchase_order_items
                (purchase_order_id, product_id, quantity, rate, total)
                VALUES (?,?,?,?,?)
            ");
            $stmt->bind_param("iiddd",
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

        $_SESSION['inv_success'] = "Purchase Order Created Successfully.";
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
<html>
<head>
<meta charset="utf-8">
<title>Create Purchase Order</title>
<?php require_once __DIR__ . '/../include/head2.php'; ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include __DIR__ . '/../include/sidebar.php'; ?>
<div id="main-content">
<?php include __DIR__ . '/../include/navbar.php'; ?>

<div class="container-fluid py-4">

<div class="card shadow">
<div class="card-header bg-primary text-white">
<h5>Create Purchase Order</h5>
</div>

<div class="card-body">

<?php if (!empty($_SESSION['inv_error'])): ?>
<div class="alert alert-danger">
<?= htmlspecialchars($_SESSION['inv_error']); unset($_SESSION['inv_error']); ?>
</div>
<?php endif; ?>

<form method="post" id="purchaseForm">

<div class="row mb-3">

<div class="col-md-3">
<label>Category *</label>
<select name="category_id" id="category" class="form-select" required>
<option value="">Select Category</option>
<?php foreach ($categories as $c): ?>
<option value="<?= $c['id'] ?>">
<?= htmlspecialchars($c['category_name']) ?>
</option>
<?php endforeach; ?>
</select>
</div>

<div class="col-md-3">
<label>Manufacturer *</label>
<select name="manufacturer_id" id="manufacturer" class="form-select" required>
<option value="">Select Manufacturer</option>
</select>
</div>

<div class="col-md-3">
<label>Warehouse *</label>
<select name="warehouse_id" class="form-select" required>
<option value="">Select Warehouse</option>
<?php foreach ($warehouses as $w): ?>
<option value="<?= $w['id'] ?>">
<?= htmlspecialchars($w['name']) ?>
</option>
<?php endforeach; ?>
</select>
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

<button type="button" class="btn btn-secondary btn-sm" onclick="addRow()">+ Add Item</button>
<br><br>
<button class="btn btn-success">Save Purchase Order</button>

</form>
</div>
</div>
</div>

<script>
let products = [];
let rowIndex = 0;

document.getElementById('category').addEventListener('change', function(){

    let catId = this.value;

    fetch('ajax_get_manufacturers.php?category_id=' + catId)
    .then(res => res.json())
    .then(data => {
        let manuSelect = document.getElementById('manufacturer');
        manuSelect.innerHTML = '<option value="">Select Manufacturer</option>';
        data.forEach(m => {
            manuSelect.innerHTML += `<option value="${m.id}">${m.manufacturer_name}</option>`;
        });
    });

    fetch('ajax_get_products.php?category_id=' + catId)
    .then(res => res.json())
    .then(data => {
        products = data;
    });

});

function addRow(){
    if(products.length === 0){
        alert("Select category first.");
        return;
    }

    let tr = document.createElement('tr');
    tr.innerHTML = `
        <td>
        <select name="items[${rowIndex}][product]" class="form-select" required>
        ${products.map(p => `<option value="${p.id}">${p.name}</option>`).join('')}
        </select>
        </td>
        <td><input name="items[${rowIndex}][qty]" type="number" min="1" class="form-control" required></td>
        <td><input name="items[${rowIndex}][price]" type="number" step="0.01" class="form-control" required></td>
        <td><button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove()">×</button></td>
    `;
    document.getElementById('items').appendChild(tr);
    rowIndex++;
}
</script>

</body>
</html>
