<?php
// admin/inventory/inventory_functions.php
// Helper functions for inventory management.
// Requires $conn (mysqli) to be available.

if (session_status() === PHP_SESSION_NONE) session_start();

/**
 * ensureStockRow - ensures a row exists in warehouse_stock for product+warehouse
 * returns inserted/existing id
 */
function ensureStockRow($conn, $product_id, $warehouse_id) {
    $sel = $conn->prepare("SELECT id FROM warehouse_stock WHERE product_id = ? AND warehouse_id = ? LIMIT 1");
    $sel->bind_param('ii', $product_id, $warehouse_id);
    $sel->execute();
    $r = $sel->get_result()->fetch_assoc();
    $sel->close();
    if ($r && isset($r['id'])) return intval($r['id']);

    $ins = $conn->prepare("INSERT INTO warehouse_stock (product_id, warehouse_id, quantity, reserved, updated_at) VALUES (?, ?, 0, 0, NOW())");
    $ins->bind_param('ii', $product_id, $warehouse_id);
    $ins->execute();
    $id = $ins->insert_id;
    $ins->close();
    return intval($id);
}

/**
 * logStockMovement - records an entry in stock_movements
 */
function logStockMovement($conn, $warehouse_id, $product_id, $qty_change, $type, $note = null, $created_by = null) {
    $stmt = $conn->prepare("INSERT INTO stock_movements (warehouse_id, product_id, qty_change, type, note, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param('iiisss', $warehouse_id, $product_id, $qty_change, $type, $note, $created_by);
    $stmt->execute();
    $stmt->close();
}

/**
 * adjustStock - modify aggregated stock for product at warehouse and log movement.
 * $qty_change can be positive (add) or negative (remove).
 * $type: 'add_stock'|'remove_stock'|'transfer_in'|'transfer_out'|'adjustment'
 */
function adjustStock(
    mysqli $conn,
    int $product_id,
    int $warehouse_id,
    int $qty_change,
    string $type,
    string $note = '',
    ?int $user_id = null,
    ?string $ref_type = null,
    ?int $ref_id = null
) {
    try {
        /* 1️⃣ Update warehouse_stock */
        $chk = $conn->prepare("
            SELECT quantity 
            FROM warehouse_stock 
            WHERE product_id = ? AND warehouse_id = ?
            LIMIT 1
        ");
        $chk->bind_param('ii', $product_id, $warehouse_id);
        $chk->execute();
        $row = $chk->get_result()->fetch_assoc();
        $chk->close();

        if ($row) {
            $newQty = (int)$row['quantity'] + $qty_change;
            if ($newQty < 0) $newQty = 0;

            $upd = $conn->prepare("
                UPDATE warehouse_stock
                SET quantity = ?
                WHERE product_id = ? AND warehouse_id = ?
            ");
            $upd->bind_param('iii', $newQty, $product_id, $warehouse_id);
            $upd->execute();
            $upd->close();
        } else {
            // Create new row if doesn't exist
            $newQty = max(0, $qty_change);
            $ins = $conn->prepare("
                INSERT INTO warehouse_stock (product_id, warehouse_id, quantity, reserved, updated_at)
                VALUES (?, ?, ?, 0, NOW())
            ");
            $ins->bind_param('iii', $product_id, $warehouse_id, $newQty);
            $ins->execute();
            $ins->close();
        }

        /* 2️⃣ Log stock movement */
        $mov = $conn->prepare("
            INSERT INTO stock_movements
            (
                movement_type,
                product_id,
                warehouse_from,
                warehouse_to,
                quantity,
                unit,
                note,
                created_by,
                reference_type,
                reference_id
            )
            VALUES (?, ?, ?, NULL, ?, 'pc', ?, ?, ?, ?)
        ");

        $mov->bind_param(
            'siidssis',
            $type,
            $product_id,
            $warehouse_id,
            $qty_change,
            $note,
            $user_id,
            $ref_type,
            $ref_id
        );
        $mov->execute();
        $mov->close();

        return true;

    } catch (Throwable $e) {
        error_log('adjustStock error: ' . $e->getMessage());
        return false;
    }
}



/**
 * getWarehouseStock($conn, $warehouse_id) - returns array rows for a given warehouse
 */
function getWarehouseStock($conn, $warehouse_id) {
    $rows = [];
    $stmt = $conn->prepare("SELECT ws.id, ws.product_id, ws.quantity, ws.reserved, p.name AS product_name, p.sku, p.serial_tracked
                            FROM warehouse_stock ws
                            LEFT JOIN products p ON p.id = ws.product_id
                            WHERE ws.warehouse_id = ?
                            ORDER BY p.name ASC");
    $stmt->bind_param('i', $warehouse_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) $rows[] = $r;
    $stmt->close();
    return $rows;
}

/**
 * getProductTotalStock($conn, $product_id) - returns total across warehouses
 */
function getProductTotalStock($conn, $product_id) {
    $stmt = $conn->prepare("SELECT COALESCE(SUM(quantity),0) as total FROM warehouse_stock WHERE product_id = ?");
    $stmt->bind_param('i', $product_id);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return intval($r['total'] ?? 0);
}
/**
 * Get available stock of a product in a warehouse
 */
function getWarehouseProductStock(mysqli $conn, int $product_id, int $warehouse_id): int
{
    $stmt = $conn->prepare("
        SELECT quantity 
        FROM warehouse_stock 
        WHERE product_id = ? AND warehouse_id = ?
        LIMIT 1
    ");
    $stmt->bind_param('ii', $product_id, $warehouse_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $res ? (int)$res['quantity'] : 0;
}

