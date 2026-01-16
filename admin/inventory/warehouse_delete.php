<?php
// admin/inventory/warehouse_delete.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    $_SESSION['inv_error'] = "Invalid warehouse id.";
    header("Location: warehouses.php");
    exit;
}

// you can add permission check here, e.g. $auth->checkPermission(...)
try {
    // Use transaction to be safe
    $conn->begin_transaction();

    // Delete warehouse (FKs in DB will cascade/set null as configured in dump)
    $stmt = $conn->prepare("DELETE FROM warehouses WHERE id = ? LIMIT 1");
    if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
    $stmt->bind_param('i', $id);
    if (!$stmt->execute()) throw new Exception("Execute failed: " . $stmt->error);
    $affected = $stmt->affected_rows;
    $stmt->close();

    $conn->commit();

    if ($affected > 0) {
        $_SESSION['inv_success'] = "Warehouse deleted successfully.";
    } else {
        $_SESSION['inv_error'] = "Warehouse not found or already deleted.";
    }
} catch (Exception $e) {
    $conn->rollback();
    error_log("warehouse_delete error: " . $e->getMessage());
    $_SESSION['inv_error'] = "Failed to delete warehouse. " . $e->getMessage();
}

header("Location: warehouses.php");
exit;
