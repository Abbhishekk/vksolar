<?php
// feedback_api.php
require_once "connect/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method.");
}

/* ---------- Validate quotation_id ---------- */
if (!isset($_POST['quotation_id']) || !is_numeric($_POST['quotation_id'])) {
    die("Invalid quotation reference.");
}

$quotation_id = (int) $_POST['quotation_id'];
$status       = $_POST['status'] ?? '';
$remarks      = trim($_POST['remarks'] ?? '');

/* ---------- Allowed statuses ---------- */
$allowed = ['approved', 'declined', 'under_review'];

if (!in_array($status, $allowed, true)) {
    die("Invalid status value.");
}

/* ---------- Update quotation ---------- */
$stmt = $conn->prepare("
    UPDATE solar_rooftop_quotations
    SET 
        status = ?,
        updated_date = NOW()
    WHERE quotation_id = ?
");

$stmt->bind_param("si", $status, $quotation_id);

if (!$stmt->execute()) {
    die("Failed to update quotation status.");
}

/* ---------- Redirect to thank-you page ---------- */
header("Location: quotation_api");
exit;
