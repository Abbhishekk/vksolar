<?php
// admin/inventory/product_delete.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php';
$id = intval($_GET['id'] ?? 0);
if (!$id) { header('Location: products.php'); exit; }

// delete image files
$stmt = $conn->prepare("SELECT filename FROM product_images WHERE product_id=?");
$stmt->bind_param('i',$id); $stmt->execute(); $res=$stmt->get_result(); while($r=$res->fetch_assoc()){
    $f = __DIR__.'/uploads/products/'.$r['filename'];
    if (is_file($f)) @unlink($f);
}
$stmt->close();

// delete product (cascade product_images, product_serials if FK ON DELETE CASCADE)
$d = $conn->prepare("DELETE FROM products WHERE id=? LIMIT 1");
$d->bind_param('i',$id);
$ok = $d->execute();
$d->close();

$_SESSION['inv_success'] = $ok ? 'Product deleted' : 'Delete failed';
header('Location: products.php');
exit;
