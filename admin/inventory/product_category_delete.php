<?php
// admin/inventory/product_category_delete.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) { header('Location: product_categories.php'); exit; }

// optionally check for child categories or products referencing this category
// (left simple here)
try {
    $stmt = $conn->prepare("DELETE FROM product_categories WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    $_SESSION['cat_flash'] = 'Category deleted';
} catch (Exception $e) {
    $_SESSION['cat_flash'] = 'Delete failed';
}

header('Location: product_categories.php');
exit;
