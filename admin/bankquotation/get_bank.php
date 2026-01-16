<?php
require_once __DIR__ . '/../connect/db.php';

if (!isset($_GET['bank_id']) || !is_numeric($_GET['bank_id'])) {
    echo json_encode(['status' => 'error']);
    exit;
}

$bank_id = (int) $_GET['bank_id'];

$stmt = $conn->prepare("
    SELECT bank_name, branch_name, account_number, account_type, ifsc_code, bank_gst
    FROM company_bank_details
    WHERE id = ? AND is_active = 1
    LIMIT 1
");
$stmt->bind_param("i", $bank_id);
$stmt->execute();

$data = $stmt->get_result()->fetch_assoc();

echo json_encode($data ?: []);
