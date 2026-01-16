<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../connect/db.php';
require_once __DIR__ . '/../inventory/inventory_functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

$product_id = (int)($_GET['product_id'] ?? 0);
$warehouse_id = (int)($_GET['warehouse_id'] ?? 0);

if (!$product_id || !$warehouse_id) {
    echo json_encode(['error' => 'Missing parameters']);
    exit;
}

$stock = getWarehouseProductStock($conn, $product_id, $warehouse_id);

echo json_encode(['stock' => $stock]);
?>