<?php
// admin/invoice/invoice_save.php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../connect/db.php';
require_once __DIR__ . '/../inventory/inventory_functions.php';
require_once __DIR__ . '/../connect/auth_middleware.php';
$auth->requireAuth();
$auth->requirePermission('invoice_management', 'create');

$user_id = $_SESSION['user_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: invoice_create.php');
    exit;
}

/* ---------------- BASIC DATA ---------------- */
$invoice_id    = (int)($_POST['invoice_id'] ?? 0);
$invoice_type  = $_POST['invoice_type']; // client | retailer
$reference_id  = (int)$_POST['reference_id'];
$warehouse_id  = (int)$_POST['warehouse_id'];
$invoice_date  = $_POST['invoice_date'];
$action        = $_POST['action']; // draft | final

$product_ids  = $_POST['product_id'] ?? [];
$qtys         = $_POST['qty'] ?? [];
$rates        = $_POST['rate'] ?? [];

if (!$reference_id || !$warehouse_id || !$product_ids) {
    $_SESSION['inv_error'] = 'Invalid invoice data.';
    header('Location: invoice_create.php');
    exit;
}

$is_update = $invoice_id > 0;

/* ---------------- FINANCIAL YEAR ---------------- */
$year = date('Y', strtotime($invoice_date));
$fy = (date('m', strtotime($invoice_date)) >= 4)
        ? substr($year, 2).'-'.substr($year+1, 2)
        : substr($year-1, 2).'-'.substr($year, 2);

/* ---------------- INVOICE NUMBER ---------------- */
if ($is_update) {
    // Get existing invoice details
    $stmt = $conn->prepare("SELECT invoice_no FROM invoices WHERE id = ? AND status = 'draft' LIMIT 1");
    $stmt->bind_param('i', $invoice_id);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$existing) {
        $_SESSION['inv_error'] = 'Invoice not found or cannot be edited.';
        header('Location: invoices.php');
        exit;
    }
    
    $invoice_no = $existing['invoice_no'];
} else {
    $stmt = $conn->prepare("
        SELECT MAX(id) AS max_id
    FROM invoices
    WHERE financial_year = ?;
    
    ");
    $stmt->bind_param('s', $fy);
    $stmt->execute();
    $count = $stmt->get_result()->fetch_assoc()['max_id'] + 1;
    $stmt->close();
    
    $invoice_no = "VK/$fy/" . str_pad($count, 3, '0', STR_PAD_LEFT);
}

/* ---------------- CALCULATE TOTALS ---------------- */
$subtotal = 0;
foreach ($product_ids as $i => $pid) {
    $subtotal += ((float)$qtys[$i] * (float)$rates[$i]);
}

$cgst = $subtotal * 0.09;
$sgst = $subtotal * 0.09;
$total = $subtotal + $cgst + $sgst;

/* ---------------- SAVE INVOICE ---------------- */
$conn->begin_transaction();

try {

    if ($is_update) {
        // Update existing invoice
        $stmt = $conn->prepare("
            UPDATE invoices
            SET invoice_type = ?, reference_id = ?, invoice_date = ?, warehouse_id = ?,
                subtotal = ?, cgst = ?, sgst = ?, total = ?, status = ?
            WHERE id = ?
        ");
        $status = ($action === 'final') ? 'final' : 'draft';
        
        $stmt->bind_param(
            'sissddddsi',
            $invoice_type,
            $reference_id,
            $invoice_date,
            $warehouse_id,
            $subtotal,
            $cgst,
            $sgst,
            $total,
            $status,
            $invoice_id
        );
        $stmt->execute();
        $stmt->close();
        
        // Delete existing items
        $del = $conn->prepare("DELETE FROM invoice_items WHERE invoice_id = ?");
        $del->bind_param('i', $invoice_id);
        $del->execute();
        $affected_rows = $del->affected_rows;
        $del->close();
        
        error_log("Deleted $affected_rows items for invoice $invoice_id");
        
    } else {
        // Create new invoice
        $stmt = $conn->prepare("
            INSERT INTO invoices
            (invoice_no, invoice_type, reference_id, invoice_date, financial_year,
             warehouse_id, subtotal, cgst, sgst, total, status, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $status = ($action === 'final') ? 'final' : 'draft';

        $stmt->bind_param(
            'ssissiddddsi',
            $invoice_no,
            $invoice_type,
            $reference_id,
            $invoice_date,
            $fy,
            $warehouse_id,
            $subtotal,
            $cgst,
            $sgst,
            $total,
            $status,
            $user_id
        );
        $stmt->execute();
        $invoice_id = $stmt->insert_id;
        $stmt->close();
    }

    /* ---------------- SAVE ITEMS ---------------- */
    foreach ($product_ids as $i => $pid) {

        $qty  = (float)$qtys[$i];
        $rate = (float)$rates[$i];
        if ($qty <= 0 || $rate <= 0) continue; // Skip items with zero or negative quantity/rate

        // Check stock availability for all invoices
        $available = getWarehouseProductStock($conn, $pid, $warehouse_id);
        if ($available < $qty) {
            $product_name = $conn->query("SELECT name FROM products WHERE id = $pid")->fetch_assoc()['name'] ?? "Product ID $pid";
            throw new Exception("Insufficient stock for $product_name. Available: $available, Required: $qty");
        }

        $line_total = $qty * $rate;

        $it = $conn->prepare("
            INSERT INTO invoice_items
            (invoice_id, product_id, warehouse_id, quantity, rate, gst_percent, line_total)
            VALUES (?, ?, ?, ?, ?, 18, ?)
        ");
        $it->bind_param(
            'iiiddd',
            $invoice_id,
            $pid,
            $warehouse_id,
            $qty,
            $rate,
            $line_total
        );
        $it->execute();
        $it->close();

        /* ---------------- STOCK DEDUCTION (ONLY FINAL) ---------------- */
        if ($status === 'final') {
            // deduct stock + movement
            adjustStock(
                $conn,
                $pid,
                $warehouse_id,
                -$qty,
                'sale',
                "Invoice $invoice_no",
                $user_id,
                $invoice_type,
                $reference_id
            );
        }
    }

    $conn->commit();

    $_SESSION['inv_success'] = $is_update ? "Invoice $invoice_no updated successfully." : "Invoice $invoice_no saved successfully.";
    header('Location: invoices.php');
    exit;

} catch (Throwable $e) {

    $conn->rollback();
    error_log('Invoice Save Error: '.$e->getMessage());
    error_log('POST data: ' . print_r($_POST, true));

    $_SESSION['inv_error'] = $is_update ? 'Failed to update invoice: '.$e->getMessage() : 'Failed to save invoice: '.$e->getMessage();
    header('Location: invoice_create.php' . ($is_update ? '?id='.$invoice_id : ''));
    exit;
}
