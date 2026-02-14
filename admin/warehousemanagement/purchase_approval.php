<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../connect/db.php';
require_once __DIR__ . '/../connect/auth_middleware.php';

$auth->requirePermission('inventory_management', 'create');

$return_to = $_GET['return_to'] ?? 'invoice';
$return_warehouse_id = (int)($_GET['warehouse_id'] ?? 0);


$po_id = (int)($_GET['id'] ?? 0);
$user_id = $_SESSION['user_id'] ?? 0;

if ($po_id <= 0) {
if ($return_to === 'warehouse' && $return_warehouse_id > 0) {
    header("Location: warehouse_view.php?id=" . $return_warehouse_id);
} else {
    header("Location: ../invoice/purchase_invoice_list.php");
}

    exit;
}

/* ================= FETCH PURCHASE ORDER ================= */

$stmt = $conn->prepare("
SELECT po.*, 
       pm.manufacturer_name,
       w.name AS warehouse_name
FROM purchase_orders po
LEFT JOIN product_manufacturers pm ON pm.id = po.manufacturer_id
LEFT JOIN warehouses w ON w.id = po.warehouse_id
WHERE po.id = ?
LIMIT 1
");
$stmt->bind_param("i", $po_id);
$stmt->execute();
$po = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$po || $po['status'] !== 'pending') {
    $_SESSION['inv_error'] = "Invalid or already processed PO.";
if ($return_to === 'warehouse' && $return_warehouse_id > 0) {
    header("Location: warehouse_view.php?id=" . $return_warehouse_id);
} else {
    header("Location: ../invoice/purchase_invoice_list.php");
}

    exit;
}

/* ================= FETCH ITEMS ================= */

$stmt = $conn->prepare("
SELECT poi.*, p.name AS product_name
FROM purchase_order_items poi
LEFT JOIN products p ON p.id = poi.product_id
WHERE poi.purchase_order_id = ?
");
$stmt->bind_param("i", $po_id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

/* ================= HANDLE APPROVAL ================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $conn->begin_transaction();

    try {

        foreach ($items as $item) {

            $product_id = $item['product_id'];
            $qty = $item['quantity'];
            $warehouse_id = $po['warehouse_id'];

            /* 1️⃣ Update warehouse_stock */

            $check = $conn->prepare("
                SELECT id, quantity
                FROM warehouse_stock
                WHERE warehouse_id=? AND product_id=?
                LIMIT 1
            ");
            $check->bind_param("ii", $warehouse_id, $product_id);
            $check->execute();
            $res = $check->get_result();

            if ($row = $res->fetch_assoc()) {

                $new_qty = $row['quantity'] + $qty;

                $update = $conn->prepare("
                    UPDATE warehouse_stock
                    SET quantity=?
                    WHERE id=?
                ");
                $update->bind_param("di", $new_qty, $row['id']);
                $update->execute();
                $update->close();

            } else {

                $insert = $conn->prepare("
                    INSERT INTO warehouse_stock
                    (warehouse_id, product_id, quantity)
                    VALUES (?,?,?)
                ");
                $insert->bind_param("iid",
                    $warehouse_id,
                    $product_id,
                    $qty
                );
                $insert->execute();
                $insert->close();
            }

            $check->close();

            /* 2️⃣ Insert stock movement */

            $movement = $conn->prepare("
                INSERT INTO stock_movements
                (movement_type, product_id, warehouse_to, quantity, reference_type, reference_id, created_by)
                VALUES ('purchase', ?, ?, ?, 'retailer', ?, ?)
            ");

            $movement->bind_param("iidii",
                $product_id,
                $warehouse_id,
                $qty,
                $po['manufacturer_id'],
                $user_id
            );
            $movement->execute();
            $movement->close();
        }

        /* 3️⃣ Update PO status */

        $updatePO = $conn->prepare("
            UPDATE purchase_orders
            SET status='approved',
                approved_by=?,
                approved_at=NOW()
            WHERE id=?
        ");
        $updatePO->bind_param("ii", $user_id, $po_id);
        $updatePO->execute();
        $updatePO->close();

        $conn->commit();

        $_SESSION['inv_success'] = "Stock received successfully.";
      if ($return_to === 'warehouse' && $return_warehouse_id > 0) {
    header("Location: warehouse_view.php?id=" . $return_warehouse_id);
} else {
    header("Location: ../invoice/purchase_invoice_list.php");
}
  
        exit;

    } catch (Exception $e) {

        $conn->rollback();
        $_SESSION['inv_error'] = $e->getMessage();
        header("Location: purchase_approval.php?id=".$po_id);
        exit;
    }
}
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Receive Purchase Order</title>
<?php require_once __DIR__ . '/../include/head2.php'; ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include __DIR__ . '/../include/sidebar.php'; ?>
<div id="main-content">
<?php include __DIR__ . '/../include/navbar.php'; ?>

<div class="container-fluid py-4">

<div class="card shadow">
<div class="card-header bg-success text-white">
<h5>Receive Purchase Order</h5>
</div>

<div class="card-body">

<h6>PO Number: <?= htmlspecialchars($po['po_number']) ?></h6>
<p>
<strong>Manufacturer:</strong> <?= htmlspecialchars($po['manufacturer_name']) ?><br>
<strong>Warehouse:</strong> <?= htmlspecialchars($po['warehouse_name']) ?>
</p>

<table class="table table-bordered">
<thead class="table-light">
<tr>
<th>Product</th>
<th width="120">Quantity</th>
</tr>
</thead>
<tbody>
<?php foreach ($items as $item): ?>
<tr>
<td><?= htmlspecialchars($item['product_name']) ?></td>
<td class="text-center fw-bold">
<?= $item['quantity'] ?>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<form method="post">
<button class="btn btn-success">
Confirm & Receive Stock
</button>
<?php if ($return_to === 'warehouse' && $return_warehouse_id > 0): ?>
<a href="warehouse_view.php?id=<?= $return_warehouse_id ?>"
<?php else: ?>
<a href="../invoice/purchase_invoice_list.php"
<?php endif; ?>
class="btn btn-secondary">
Cancel
</a>
</form>

</div>
</div>

</div>
</div>
</body>
</html>
