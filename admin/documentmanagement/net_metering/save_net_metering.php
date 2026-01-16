<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../connect/db.php';

header('Content-Type: application/json');

$client_id  = $_SESSION['net_metering_client_id'] ?? 0;
$created_by = $_SESSION['user_id'] ?? null;

if ($client_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid client']);
    exit;
}

/* ---------- Collect fields ---------- */
$consumer_name        = trim($_POST['consumer_name'] ?? '');
$consumer_number      = trim($_POST['consumer_number'] ?? '');
$address              = trim($_POST['address'] ?? '');
$location             = trim($_POST['location'] ?? '');
$system_capacity      = trim($_POST['system_capacity'] ?? '');
$vendor_name          = trim($_POST['vendor_name'] ?? '');
$agreement_date       = trim($_POST['agreement_date'] ?? '');
$msedcl_rep           = trim($_POST['msedcl_representative'] ?? '');
$msedcl_designation   = trim($_POST['msedcl_designation'] ?? '');
$witness_vendor       = trim($_POST['witness1_vendor'] ?? '');
$witness_msedcl       = trim($_POST['witness1_msedcl'] ?? '');

/* ---------- Validation ---------- */
if (
    !$consumer_name || !$consumer_number || !$address || !$location ||
    !$system_capacity || !$vendor_name || !$agreement_date ||
    !$msedcl_rep || !$msedcl_designation || !$witness_vendor || !$witness_msedcl
) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

/* ---------- UPSERT ---------- */
$sql = "
INSERT INTO net_metering_agreements (
    client_id, consumer_name, consumer_number, address, location,
    system_capacity, vendor_name, agreement_date,
    msedcl_representative, msedcl_designation,
    witness1_vendor, witness1_msedcl, created_by
) VALUES (
    ?,?,?,?,?,?,?,?,?,?,?,?,?
)
ON DUPLICATE KEY UPDATE
    consumer_name = VALUES(consumer_name),
    consumer_number = VALUES(consumer_number),
    address = VALUES(address),
    location = VALUES(location),
    system_capacity = VALUES(system_capacity),
    vendor_name = VALUES(vendor_name),
    agreement_date = VALUES(agreement_date),
    msedcl_representative = VALUES(msedcl_representative),
    msedcl_designation = VALUES(msedcl_designation),
    witness1_vendor = VALUES(witness1_vendor),
    witness1_msedcl = VALUES(witness1_msedcl),
    updated_at = CURRENT_TIMESTAMP
";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "issssdsdssssi",
    $client_id,
    $consumer_name,
    $consumer_number,
    $address,
    $location,
    $system_capacity,
    $vendor_name,
    $agreement_date,
    $msedcl_rep,
    $msedcl_designation,
    $witness_vendor,
    $witness_msedcl,
    $created_by
);

$stmt->execute();
echo json_encode(['success' => true]);
