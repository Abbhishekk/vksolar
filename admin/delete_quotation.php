<?php
// delete_quotation.php
session_start();
require_once 'connect/auth_middleware.php';
$auth->requireAuth();
$auth->requirePermission('quotation_management', 'delete');

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "connect/db.php"; // $conn from Database class

// 1. Validate quote_id
if (!isset($_GET['quote_id']) || !is_numeric($_GET['quote_id'])) {
    $_SESSION['flash_error'] = "Invalid quotation ID.";
    header("Location: view_quotations.php");
    exit;
}

$quotationId = (int) $_GET['quote_id'];

// 2. Optional: check if record exists (good practice)
$checkSql = "SELECT quotation_id, quote_number FROM solar_rooftop_quotations WHERE quotation_id = $quotationId";
$checkRes = $conn->query($checkSql);

if (!$checkRes || $checkRes->num_rows === 0) {
    $_SESSION['flash_error'] = "Quotation not found or already deleted.";
    header("Location: view_quotations.php");
    exit;
}

$row = $checkRes->fetch_assoc();
$quoteNumber = $row['quote_number'];

// 3. DELETE record
$delSql = "DELETE FROM solar_rooftop_quotations WHERE quotation_id = $quotationId";

if (!$conn->query($delSql)) {
    $_SESSION['flash_error'] = "Failed to delete quotation. Error: " . $conn->error;
    header("Location: view_quotations.php");
    exit;
}

// 4. Success message
$_SESSION['flash_success'] = "Quotation {$quoteNumber} deleted successfully.";

header("Location: view_quotations.php");
exit;
