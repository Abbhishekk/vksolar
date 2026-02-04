<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php';
require_once __DIR__ . '/../connect/auth_middleware.php';

require_once __DIR__ . '../../inventory/inventory_functions.php';

$auth->requireAnyRole(['warehouse_staff']);

$purchase_order_id = (int)($_GET['id'] ?? 0);
$user_id = $_SESSION['user_id'] ?? 0;

if ($purchase_order_id <= 0) {
    die('Invalid Purchase Order ID');
}

$conn->begin_transaction();

try {

    /* 1️⃣ FETCH PURCHASE ORDER */
    $stmt = $conn->prepare("
        SELECT id, warehouse_id, status
        FROM purchase_orders
        WHERE id = ? AND status = 'pending'
        FOR UPDATE
    ");
    $stmt->bind_param("i", $purchase_order_id);
    $stmt->execute();
    $po = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$po) {
        throw new Exception('Purchase order not found or already processed');
    }

    /* 2️⃣ FETCH PURCHASE ITEMS */
    $stmt = $conn->prepare("
        SELECT product_id, quantity
        FROM purchase_order_items
        WHERE purchase_order_id = ?
    ");
    $stmt->bind_param("i", $purchase_order_id);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (empty($items)) {
        throw new Exception('No items found in purchase order');
    }

    /* 3️⃣ ADD STOCK + MOVEMENT */
    foreach ($items as $item) {

        adjustStock(
            $conn,
            $item['product_id'],
            $po['warehouse_id'],
            $item['quantity'],
            'purchase',
            'Purchase order approved',
            $user_id
        );
    }

    /* 4️⃣ UPDATE PURCHASE ORDER STATUS */
    $stmt = $conn->prepare("
        UPDATE purchase_orders
        SET status = 'approved',
            approved_by = ?,
            approved_at = NOW()
        WHERE id = ?
    ");
    $stmt->bind_param("ii", $user_id, $purchase_order_id);
    $stmt->execute();
    $stmt->close();

    $conn->commit();

    $_SESSION['inv_success'] = 'Purchase Order Approved & Stock Updated';
    header("Location: purchase_requests.php");
    exit;

} catch (Exception $e) {

    $conn->rollback();
    $_SESSION['inv_error'] = $e->getMessage();
    header("Location: purchase_requests.php");
    exit;
}
