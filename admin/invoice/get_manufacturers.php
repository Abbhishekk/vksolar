<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__.'/../connect/db.php';

header('Content-Type: application/json');

$product_id = (int)($_GET['product_id'] ?? 0);

if (!$product_id) {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("
    SELECT pm.id, pm.manufacturer_name 
    FROM product_manufacturers pm
    JOIN products p ON p.category_id = pm.category_id
    WHERE p.id = ?
    ORDER BY pm.manufacturer_name
");
$stmt->bind_param('i', $product_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode($result);
