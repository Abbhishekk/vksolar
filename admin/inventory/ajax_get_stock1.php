<?php
// admin/inventory/ajax_get_stock.php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../connect/db.php';

$product_id   = (int)($_GET['product_id'] ?? 0);
$warehouse_id = (int)($_GET['warehouse_id'] ?? 0);

header('Content-Type: application/json');

if (!$product_id || !$warehouse_id) {
    echo json_encode(['stock' => 0]);
    exit;
}

$stmt = $conn->prepare("
    SELECT quantity 
    FROM warehouse_stock 
    WHERE product_id = ? AND warehouse_id = ?
    LIMIT 1
");
$stmt->bind_param('ii', $product_id, $warehouse_id);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$stmt->close();

$stock = $res ? (int)$res['quantity'] : 0;

echo json_encode(['stock' => $stock]);
