<?php
// admin/inventory/warehouse_save.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php'; // $conn (mysqli)

// Basic upload constants
define('INV_UPLOAD_DIR', __DIR__ . '/uploads/');
define('INV_UPLOAD_URL', '/admin/inventory/uploads/'); // public prefix

// ensure upload subdir exists
$wh_dir = INV_UPLOAD_DIR . 'warehouses/';
if (!is_dir($wh_dir)) {
    @mkdir($wh_dir, 0755, true);
}

function clean($v){ return trim($v ?? ''); }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['inv_error'] = 'Invalid request.';
    header('Location: warehouses.php');
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$name = clean($_POST['name'] ?? '');
$code = clean($_POST['code'] ?? '');
$address = clean($_POST['address'] ?? '');
$city = clean($_POST['city'] ?? '');
$state = clean($_POST['state'] ?? '');
$pincode = clean($_POST['pincode'] ?? '');
$contact_name = clean($_POST['contact_name'] ?? '');
$contact_phone = clean($_POST['contact_phone'] ?? '');
$employees = $_POST['employees'] ?? []; // array of employee ids

$errors = [];
if ($name === '') $errors[] = 'Warehouse name is required.';

$image_db_path = ''; // relative path to store in DB if image uploaded or existing

// handle image upload
if (!empty($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
    $f = $_FILES['image'];
    if ($f['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Error uploading image.';
    } else {
        if ($f['size'] > 5 * 1024 * 1024) $errors[] = 'Image exceeds 5MB.';
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($f['tmp_name']);
        $allowed = ['image/jpeg'=>'jpg','image/jpg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
        if (!isset($allowed[$mime])) $errors[] = 'Only JPG, PNG, WEBP images allowed.';
        if (empty($errors)) {
            $ext = $allowed[$mime];
            $filename = uniqid('wh_', true) . '.' . $ext;
            $dest = $wh_dir . $filename;
            if (!move_uploaded_file($f['tmp_name'], $dest)) {
                $errors[] = 'Failed to move uploaded file.';
            } else {
                // store relative path used by pages: "warehouses/filename.jpg"
                $image_db_path = 'warehouses/' . $filename;
            }
        }
    }
}

// if updating and no new image uploaded, preserve old image if exists
if ($id && $image_db_path === '') {
    $stmt = $conn->prepare("SELECT image FROM warehouses WHERE id = ? LIMIT 1");
    $stmt->bind_param('i',$id);
    $stmt->execute();
    $r = $stmt->get_result();
    if ($r && $row = $r->fetch_assoc()) $image_db_path = $row['image'];
    $stmt->close();
}

if (!empty($errors)) {
    $_SESSION['inv_errors'] = $errors;
    $loc = $id ? 'warehouse_create.php?id=' . $id : 'warehouse_create.php';
    header('Location: ' . $loc);
    exit;
}

// Insert or update
if ($id) {
    $sql = "UPDATE warehouses SET name=?, code=?, address=?, city=?, state=?, pincode=?, contact_name=?, contact_phone=?, image=?, updated_at=NOW() WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssssssssi', $name, $code, $address, $city, $state, $pincode, $contact_name, $contact_phone, $image_db_path, $id);
    $ok = $stmt->execute();
    if (!$ok) {
        $_SESSION['inv_errors'] = ['DB error: ' . $stmt->error];
        header('Location: warehouse_create.php?id=' . $id);
        exit;
    }
    $stmt->close();
    $warehouse_id = $id;
} else {
    $sql = "INSERT INTO warehouses (name, code, address, city, state, pincode, contact_name, contact_phone, image, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssssssss', $name, $code, $address, $city, $state, $pincode, $contact_name, $contact_phone, $image_db_path);
    $ok = $stmt->execute();
    if (!$ok) {
        $_SESSION['inv_errors'] = ['DB error: ' . $stmt->error];
        header('Location: warehouse_create.php');
        exit;
    }
    $warehouse_id = $stmt->insert_id;
    $stmt->close();
}

// update warehouse_employees: simple approach -> delete existing and insert new
$del = $conn->prepare("DELETE FROM warehouse_employees WHERE warehouse_id = ?");
$del->bind_param('i', $warehouse_id);
$del->execute();
$del->close();

if (!empty($employees) && is_array($employees)) {
    $ins = $conn->prepare("INSERT INTO warehouse_employees (warehouse_id, employee_id, assigned_at) VALUES (?, ?, NOW())");
    foreach ($employees as $emp) {
        $empId = intval($emp);
        if ($empId <= 0) continue;
        $ins->bind_param('ii', $warehouse_id, $empId);
        $ins->execute();
    }
    $ins->close();
}

$_SESSION['inv_success'] = 'Warehouse saved successfully.';
header('Location: warehouses.php');
exit;
