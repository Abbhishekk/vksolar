<?php
require_once __DIR__ . '/../connect/db.php';

$cat_id = (int)($_GET['category_id'] ?? 0);

$stmt = $conn->prepare("
    SELECT id, name
    FROM products
    WHERE category_id=?
    ORDER BY name
");
$stmt->bind_param("i",$cat_id);
$stmt->execute();
echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
