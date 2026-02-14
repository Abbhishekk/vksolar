<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php';
require_once __DIR__ . '/../connect/auth_middleware.php';

$auth->requireAuth();
$auth->requirePermission('inventory_management', 'create');

$errors = [];

$id            = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$name          = trim($_POST['name'] ?? '');
$code          = trim($_POST['code'] ?? '');
$city          = trim($_POST['city'] ?? '');
$address       = trim($_POST['address'] ?? '');
$state         = trim($_POST['state'] ?? '');
$pincode       = trim($_POST['pincode'] ?? '');
$contact_name  = trim($_POST['contact_name'] ?? '');
$contact_phone = trim($_POST['contact_phone'] ?? '');
$employees     = $_POST['employees'] ?? [];
$created_by    = $_SESSION['user_id'] ?? null;

/* ================= VALIDATION ================= */

if ($name === '') {
    $errors[] = "Warehouse name is required.";
}

if ($code !== '') {
    $stmt = $conn->prepare("SELECT id FROM warehouses WHERE code=? AND id!=?");
    $stmt->bind_param("si", $code, $id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $errors[] = "Warehouse code already exists.";
    }
    $stmt->close();
}

/* ================= IMAGE UPLOAD ================= */

$imageName = null;

if (!empty($_FILES['image']['name'])) {

    if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
        $errors[] = "Image size must be under 5MB.";
    }

    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','webp'];

    if (!in_array($ext, $allowed)) {
        $errors[] = "Invalid image format.";
    }

    if (!$errors) {
        $uploadDir = __DIR__ . '/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $imageName = 'wh_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName);
    }
}

/* ================= IF ERRORS ================= */

if ($errors) {
    $_SESSION['inv_errors'] = $errors;
    header("Location: warehouses.php" . ($id ? "?edit=$id" : ""));
    exit;
}

/* ================= SAVE WITH TRANSACTION ================= */

$conn->begin_transaction();

try {

    if ($id) {
        // UPDATE
        if ($imageName) {
            $stmt = $conn->prepare("
                UPDATE warehouses
                SET name=?, code=?, city=?, address=?, state=?, pincode=?, contact_name=?, contact_phone=?, image=?
                WHERE id=?
            ");
            $stmt->bind_param(
                "sssssssssi",
                $name, $code, $city, $address, $state, $pincode,
                $contact_name, $contact_phone, $imageName, $id
            );
        } else {
            $stmt = $conn->prepare("
                UPDATE warehouses
                SET name=?, code=?, city=?, address=?, state=?, pincode=?, contact_name=?, contact_phone=?
                WHERE id=?
            ");
            $stmt->bind_param(
                "ssssssssi",
                $name, $code, $city, $address, $state, $pincode,
                $contact_name, $contact_phone, $id
            );
        }

        $stmt->execute();
        $stmt->close();

        $warehouse_id = $id;

    } else {
        // INSERT
        $stmt = $conn->prepare("
            INSERT INTO warehouses
            (name, code, city, address, state, pincode, contact_name, contact_phone, image, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "sssssssssi",
            $name, $code, $city, $address, $state, $pincode,
            $contact_name, $contact_phone, $imageName, $created_by
        );
        $stmt->execute();
        $warehouse_id = $stmt->insert_id;
        $stmt->close();
    }

    /* ================= EMPLOYEE SYNC ================= */

    // Remove old assignments
    $del = $conn->prepare("DELETE FROM warehouse_employees WHERE warehouse_id=?");
    $del->bind_param("i", $warehouse_id);
    $del->execute();
    $del->close();

    // Insert new assignments
    if (!empty($employees)) {
        $ins = $conn->prepare("
            INSERT INTO warehouse_employees (warehouse_id, employee_id)
            VALUES (?, ?)
        ");
        foreach ($employees as $emp_id) {
            $emp_id = (int)$emp_id;
            $ins->bind_param("ii", $warehouse_id, $emp_id);
            $ins->execute();
        }
        $ins->close();
    }

    $conn->commit();

    $_SESSION['inv_success'] = $id
        ? "Warehouse updated successfully."
        : "Warehouse created successfully.";

} catch (Exception $e) {

    $conn->rollback();
    $_SESSION['inv_errors'] = ["Something went wrong. Please try again."];
}

header("Location: warehouses.php");
exit;

if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php';
require_once __DIR__ . '/../connect/auth_middleware.php';

$auth->requireAuth();
$auth->requirePermission('inventory_management', 'create');

$errors = [];

$id            = (int)($_POST['id'] ?? 0);
$name          = trim($_POST['name'] ?? '');
$code          = trim($_POST['code'] ?? '');
$address       = trim($_POST['address'] ?? '');
$city          = trim($_POST['city'] ?? '');
$state         = trim($_POST['state'] ?? '');
$pincode       = trim($_POST['pincode'] ?? '');
$contact_name  = trim($_POST['contact_name'] ?? '');
$contact_phone = trim($_POST['contact_phone'] ?? '');
$employees     = $_POST['employees'] ?? [];
$created_by    = $_SESSION['user_id'] ?? null;

if ($name === '') $errors[] = "Warehouse name required.";

if ($code !== '') {
    $stmt = $conn->prepare("SELECT id FROM warehouses WHERE code=? AND id!=?");
    $stmt->bind_param("si",$code,$id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) $errors[] = "Warehouse code already exists.";
    $stmt->close();
}

$imageName = null;

if (!empty($_FILES['image']['name'])) {
    if ($_FILES['image']['size'] > 5*1024*1024)
        $errors[] = "Image must be under 5MB.";

    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','webp'];
    if (!in_array($ext,$allowed))
        $errors[] = "Invalid image format.";

    if (!$errors) {
        $uploadDir = __DIR__ . '/uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir,0755,true);
        $imageName = 'wh_'.time().'.'.$ext;
        move_uploaded_file($_FILES['image']['tmp_name'],$uploadDir.$imageName);
    }
}

if ($errors) {
    $_SESSION['inv_errors'] = $errors;
    header("Location: warehouses.php");
    exit;
}

$conn->begin_transaction();

try {

if ($id) {
    if ($imageName) {
        $stmt = $conn->prepare("
        UPDATE warehouses
        SET name=?,code=?,address=?,city=?,state=?,pincode=?,contact_name=?,contact_phone=?,image=?
        WHERE id=?");
        $stmt->bind_param("sssssssssi",
        $name,$code,$address,$city,$state,$pincode,$contact_name,$contact_phone,$imageName,$id);
    } else {
        $stmt = $conn->prepare("
        UPDATE warehouses
        SET name=?,code=?,address=?,city=?,state=?,pincode=?,contact_name=?,contact_phone=?
        WHERE id=?");
        $stmt->bind_param("ssssssssi",
        $name,$code,$address,$city,$state,$pincode,$contact_name,$contact_phone,$id);
    }
    $stmt->execute();
    $warehouse_id = $id;
} else {
    $stmt = $conn->prepare("
    INSERT INTO warehouses
    (name,code,address,city,state,pincode,contact_name,contact_phone,image,created_by)
    VALUES (?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param("sssssssssi",
    $name,$code,$address,$city,$state,$pincode,$contact_name,$contact_phone,$imageName,$created_by);
    $stmt->execute();
    $warehouse_id = $stmt->insert_id;
}
$stmt->close();

$del = $conn->prepare("DELETE FROM warehouse_employees WHERE warehouse_id=?");
$del->bind_param("i",$warehouse_id);
$del->execute();
$del->close();

if (!empty($employees)) {
    $ins = $conn->prepare("INSERT INTO warehouse_employees (warehouse_id,employee_id) VALUES (?,?)");
    foreach ($employees as $emp) {
        $emp = (int)$emp;
        $ins->bind_param("ii",$warehouse_id,$emp);
        $ins->execute();
    }
    $ins->close();
}

$conn->commit();
$_SESSION['inv_success'] = $id ? "Warehouse updated." : "Warehouse created.";

} catch (Exception $e) {
$conn->rollback();
$_SESSION['inv_errors'] = ["Something went wrong."];
}

header("Location: warehouses.php");
exit;
