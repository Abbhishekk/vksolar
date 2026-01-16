<?php
session_start();
require_once __DIR__ . '/../../connect/db.php';

$client_id = $_SESSION['wcr_client_id'] ?? 0;
if ($client_id <= 0) {
    http_response_code(400);
    exit('Invalid session');
}

$fields = [
    'name','consumer_number','address','category',
    'sanction_number','sanctioned_capacity','installed_capacity',
    'module_make','almm_model','wattage_per_module','number_of_modules',
    'total_capacity','warranty_details',
    'inverter_make_model','inverter_rating','charge_controller',
    'inverter_capacity','hpd','manufacturing_year',
    'earthings_count','lightening_arrester',
    'vendor_name','adhar','report_date'
];

$data = [];
foreach ($fields as $f) {
    $data[$f] = $_POST[$f] ?? null;
}

/* ---------- UPSERT ---------- */
$sql = "
INSERT INTO work_completion_reports (
    client_id," . implode(',', $fields) . "
) VALUES (
    ?," . rtrim(str_repeat('?,', count($fields)), ',') . "
)
ON DUPLICATE KEY UPDATE " .
implode(',', array_map(fn($f) => "$f=VALUES($f)", $fields));

$stmt = $conn->prepare($sql);
$types = 'i' . str_repeat('s', count($fields));
$stmt->bind_param($types, $client_id, ...array_values($data));
$stmt->execute();

echo "saved";
