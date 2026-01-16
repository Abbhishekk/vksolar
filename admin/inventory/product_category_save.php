<?php
// admin/inventory/product_category_save.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php';

function clean($v){ return trim($v); }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: product_categories.php'); exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$name = clean($_POST['name'] ?? '');
$slug = clean($_POST['slug'] ?? '');
$parent_id = !empty($_POST['parent_id']) ? intval($_POST['parent_id']) : null;

if ($name === '') {
    $_SESSION['cat_flash'] = 'Name required';
    header('Location: product_category_form.php' . ($id ? '?id=' . $id : ''));
    exit;
}

try {
    if ($id) {
        $stmt = $conn->prepare("UPDATE product_categories SET name=?, slug=?, parent_id=?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param('ssii', $name, $slug, $parent_id, $id);
        $stmt->execute();
        $stmt->close();
        $_SESSION['cat_flash'] = 'Category updated';
    } else {
        $stmt = $conn->prepare("INSERT INTO product_categories (name, slug, parent_id) VALUES (?, ?, ?)");
        $stmt->bind_param('ssi', $name, $slug, $parent_id);
        $stmt->execute();
        $stmt->close();
        $_SESSION['cat_flash'] = 'Category created';
    }
} catch (Exception $e) {
    error_log('category_save: ' . $e->getMessage());
    $_SESSION['cat_flash'] = 'Server error';
}

header('Location: product_categories.php');
exit;
