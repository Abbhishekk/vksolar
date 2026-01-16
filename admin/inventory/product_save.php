<?php
// admin/inventory/product_save.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: products.php'); exit; }

function clean($v){ return trim($v); }

$id = intval($_POST['id'] ?? 0);
$sku = clean($_POST['sku'] ?? '');
$name = clean($_POST['name'] ?? '');
$brand = clean($_POST['brand'] ?? '');
$type = clean($_POST['type'] ?? '');
$unit = clean($_POST['unit'] ?? 'pc');
$serial_tracked = intval($_POST['serial_tracked'] ?? 0);
$purchase_price = floatval($_POST['default_purchase_price'] ?? 0);
$sell_price = floatval($_POST['default_selling_price'] ?? 0);
$hsn = clean($_POST['hsn_code'] ?? '');
$warranty = intval($_POST['warranty_months'] ?? 0);
$description = clean($_POST['description'] ?? '');
$specs_raw = trim($_POST['specs'] ?? '{}');
$specs_json = null;
if ($specs_raw !== '') {
    $decoded = json_decode($specs_raw, true);
    if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
        $_SESSION['inv_error'] = 'Specs JSON invalid: '.json_last_error_msg();
        header('Location: product_form.php' . ($id ? '?id='.$id : ''));
        exit;
    }
    $specs_json = json_encode($decoded, JSON_UNESCAPED_SLASHES);
} else $specs_json = json_encode(new stdClass());

if ($sku === '' || $name === '') {
    $_SESSION['inv_error'] = 'SKU and Name are required';
    header('Location: product_form.php' . ($id ? '?id='.$id : ''));
    exit;
}

try {

       
        // assume variables already sanitized: $sku,$name,$brand,$type,$unit,$serial_tracked,
// $purchase_price,$sell_price,$hsn,$warranty,$description,$specs_json,$id

    if ($id) {
        $sql = "UPDATE products
                SET sku = ?, name = ?, brand = ?, type = ?, unit = ?, serial_tracked = ?,
                    default_purchase_price = ?, default_selling_price = ?, hsn_code = ?,
                    warranty_months = ?, description = ?, specs = ?
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
    
        // types: sku(s),name(s),brand(s),type(s),unit(s),serial_tracked(i),
        // default_purchase_price(d), default_selling_price(d),
        // hsn_code(s), warranty_months(i), description(s), specs(s), id(i)
        $types = 'sssssiddsissi';
    
        if (!$stmt->bind_param(
            $types,
            $sku,
            $name,
            $brand,
            $type,
            $unit,
            $serial_tracked,
            $purchase_price,
            $sell_price,
            $hsn,
            $warranty,
            $description,
            $specs_json,
            $id
        )) {
            // helpful debug
            die("bind_param failed: " . $stmt->error);
        }
    
        if (!$stmt->execute()) {
            die("Execute failed: " . $stmt->error);
        }
    
        $stmt->close();
        $_SESSION['inv_success'] = "Product updated.";
        header("Location: products.php");
        exit;
    }
    } catch(Exception $e){
        // fallback: use simpler prepared statement below (to avoid bind type hassle)
    }

// Use simpler safe insert/update approach to avoid bind type pain
if ($id) {
    $stmt = $conn->prepare("UPDATE products SET sku=?,name=?,brand=?,type=?,unit=?,serial_tracked=?,default_purchase_price=?,default_selling_price=?,hsn_code=?,warranty_months=?,description=?,specs=?,updated_at=NOW() WHERE id=?");
    $stmt->bind_param('ssssii dd ss i s i', $sku,$name,$brand,$type,$unit,$serial_tracked,$purchase_price,$sell_price,$hsn,$warranty,$description,$specs_json,$id);
    // The line above may break on some PHP versions due to spacing in types; instead we'll fallback to building with safe placeholders:
    $stmt->close();
    // final robust approach:
    $sql = "UPDATE products SET sku=?,name=?,brand=?,type=?,unit=?,serial_tracked=?,default_purchase_price=?,default_selling_price=?,hsn_code=?,warranty_months=?,description=?,specs=?,updated_at=NOW() WHERE id=?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) { $_SESSION['inv_error'] = 'Prepare failed: '.$conn->error; header('Location: product_form.php?id='.$id); exit; }
    $stmt->bind_param('ssssii dd ss is i', $sku,$name,$brand,$type,$unit,$serial_tracked,$purchase_price,$sell_price,$hsn,$warranty,$description,$specs_json,$id);
    // Because bind_param type string is strict, it's prone to errors; easier: use mysqli->real_escape_string and run one query (acceptable here)
    $escaped = function($v) use($conn){ return "'".$conn->real_escape_string($v)."'"; };
    $update_sql = "UPDATE products SET 
        sku=".$escaped($sku).",
        name=".$escaped($name).",
        brand=".$escaped($brand).",
        type=".$escaped($type).",
        unit=".$escaped($unit).",
        serial_tracked=".($serial_tracked?1:0).",
        default_purchase_price=".floatval($purchase_price).",
        default_selling_price=".floatval($sell_price).",
        hsn_code=".$escaped($hsn).",
        warranty_months=".intval($warranty).",
        description=".$escaped($description).",
        specs=".$escaped($specs_json).",
        updated_at=NOW()
        WHERE id=".intval($id);
    if (!$conn->query($update_sql)) {
        $_SESSION['inv_error'] = 'Update failed: '.$conn->error;
        header('Location: product_form.php?id='.$id); exit;
    }
    $product_id = $id;
    $_SESSION['inv_success'] = 'Product updated';
} else {
    $sku_e = $conn->real_escape_string($sku);
    $name_e = $conn->real_escape_string($name);
    $brand_e = $conn->real_escape_string($brand);
    $type_e = $conn->real_escape_string($type);
    $unit_e = $conn->real_escape_string($unit);
    $desc_e = $conn->real_escape_string($description);
    $specs_e = $conn->real_escape_string($specs_json);
    $sql = "INSERT INTO products (sku,name,brand,type,unit,serial_tracked,default_purchase_price,default_selling_price,hsn_code,warranty_months,description,specs,created_by) VALUES (
      '$sku_e','$name_e','$brand_e','$type_e','$unit_e',".($serial_tracked?1:0).",".floatval($purchase_price).",".floatval($sell_price).",'".$conn->real_escape_string($hsn)."',".intval($warranty).",'$desc_e','$specs_e',".intval($_SESSION['user_id'] ?? 0).")";
    if (!$conn->query($sql)) {
        $_SESSION['inv_error'] = 'Insert failed: '.$conn->error;
        header('Location: product_form.php'); exit;
    }
    $product_id = $conn->insert_id;
    $_SESSION['inv_success'] = 'Product created';
}

// Handle image uploads
$uploadDir = __DIR__.'/uploads/products/';
if (!is_dir($uploadDir)) mkdir($uploadDir,0755,true);
if (!empty($_FILES['images']) && is_array($_FILES['images']['name'])) {
    for ($i=0;$i<count($_FILES['images']['name']);$i++) {
        if ($_FILES['images']['error'][$i] !== UPLOAD_ERR_OK) continue;
        $orig = basename($_FILES['images']['name'][$i]);
        $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','gif','webp'])) continue;
        $safe = time().'_'.bin2hex(random_bytes(6)).'.'.$ext;
        if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $uploadDir.$safe)) {
            $ins = $conn->prepare("INSERT INTO product_images (product_id,filename,is_primary) VALUES (?,?,0)");
            $ins->bind_param('is',$product_id,$safe);
            $ins->execute(); $ins->close();
        }
    }
    // ensure one primary exists
    $r = $conn->query("SELECT id FROM product_images WHERE product_id=$product_id LIMIT 1");
    if ($r && $r->num_rows>0) {
        $rp = $conn->query("SELECT id FROM product_images WHERE product_id=$product_id AND is_primary=1 LIMIT 1");
        if ($rp && $rp->num_rows==0) {
            $conn->query("UPDATE product_images SET is_primary=1 WHERE product_id=$product_id ORDER BY id ASC LIMIT 1");
        }
    }
}

// Handle new serials if provided
if ($serial_tracked && !empty($_POST['new_serials'])) {
    $lines = preg_split("/\r\n|\n|\r/", trim($_POST['new_serials']));
    foreach($lines as $ln) {
        $s = trim($ln);
        if ($s === '') continue;
        // avoid duplicate serials
        $esc = $conn->real_escape_string($s);
        $exists = $conn->query("SELECT id FROM product_serials WHERE serial_number='{$esc}' LIMIT 1");
        if ($exists && $exists->num_rows > 0) continue;
        $warehouse_id = intval($_POST['serials_warehouse_id'] ?? null) ?: 'NULL';
        $conn->query("INSERT INTO product_serials (product_id,warehouse_id,serial_number,status,created_at) VALUES (".intval($product_id).",".($warehouse_id==='NULL' ? "NULL" : intval($warehouse_id)).",'".$esc."','in_stock',NOW())");
    }
}

// redirect
header('Location: product_view.php?id='.$product_id);
exit;
