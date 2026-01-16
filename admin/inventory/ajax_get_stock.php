<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php';

$product_id   = intval($_GET['product_id'] ?? 0);
$warehouse_id = intval($_GET['warehouse_id'] ?? 0);

$response = [
    'success' => false,
    'quantity' => 0
];

if ($product_id && $warehouse_id) {
    $stmt = $conn->prepare(
        "SELECT quantity 
         FROM warehouse_stock 
         WHERE product_id = ? AND warehouse_id = ? 
         LIMIT 1"
    );
    $stmt->bind_param('ii', $product_id, $warehouse_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $response['success'] = true;
    $response['quantity'] = $row['quantity'] ?? 0;
}

header('Content-Type: application/json');
echo json_encode($response);


