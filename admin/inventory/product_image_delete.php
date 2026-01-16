<?php
// admin/inventory/product_image_delete.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php';

$id = intval($_GET['id'] ?? 0);
$product_id = intval($_GET['product_id'] ?? 0);
if (!$id || !$product_id) { header('Location: products.php'); exit; }

$stmt = $conn->prepare("SELECT filename FROM product_images WHERE id=? AND product_id=? LIMIT 1");
$stmt->bind_param('ii',$id,$product_id); $stmt->execute(); $res=$stmt->get_result(); $row=$res->fetch_assoc(); $stmt->close();
if ($row) {
    $f = __DIR__.'/uploads/products/'.$row['filename'];
    if (is_file($f)) @unlink($f);
    $d = $conn->prepare("DELETE FROM product_images WHERE id=? LIMIT 1");
    $d->bind_param('i',$id); $d->execute(); $d->close();
    // ensure a primary exists
    $rp = $conn->query("SELECT id FROM product_images WHERE product_id=$product_id AND is_primary=1 LIMIT 1");
    if ($rp && $rp->num_rows==0) $conn->query("UPDATE product_images SET is_primary=1 WHERE product_id=$product_id ORDER BY id ASC LIMIT 1");
}
header('Location: product_form.php?id='.$product_id);
exit;
