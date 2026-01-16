<?php
// admin/inventory/warehouse_stock_delete.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php';
require_once __DIR__ . '/inventory_functions.php';

// get parameters
$warehouse_id = intval($_GET['warehouse_id'] ?? 0);
$product_id   = intval($_GET['product_id'] ?? 0);

if (!$warehouse_id || !$product_id) {
    $_SESSION['inv_error'] = "Invalid request – missing warehouse or product.";
    header("Location: warehouses.php"); exit;
}

// fetch stock row
$stmt = $conn->prepare("SELECT * FROM warehouse_stock WHERE warehouse_id = ? AND product_id = ? LIMIT 1");
$stmt->bind_param("ii", $warehouse_id, $product_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
    $_SESSION['inv_error'] = "Stock record not found.";
    header("Location: warehouse_view.php?id=" . $warehouse_id);
    exit;
}

$current_qty = intval($row['quantity']);
$user_id = $_SESSION['user_id'] ?? null;

// STEP 1: If quantity > 0 → record movement and reduce stock to zero
if ($current_qty > 0) {
    adjustStock(
        $conn,
        $product_id,
        $warehouse_id,
        -$current_qty,
        "delete_stock",
        "Stock row deleted",
        $user_id
    );
}

// STEP 2: Delete row entirely
$delete = $conn->prepare("DELETE FROM warehouse_stock WHERE warehouse_id = ? AND product_id = ? LIMIT 1");
$delete->bind_param("ii", $warehouse_id, $product_id);
$delete->execute();
$delete->close();

$_SESSION['inv_success'] = "Stock removed successfully.";

header("Location: warehouse_view.php?id=" . $warehouse_id);
exit;
?>
