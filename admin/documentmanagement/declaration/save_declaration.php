<?php
session_start();
require_once __DIR__ . '/../../connect/db.php';

header('Content-Type: application/json');
// print_r($_POST);
// echo "/n";
// print_r($_SESSION);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$client_id = $_SESSION['declaration_client_id'] ?? 0;
$created_by = $_SESSION['user_id'] ?? null;

if (!$client_id) {
    echo json_encode(['success' => false, 'message' => 'Client not logged in']);
    exit;
}

$sql = "INSERT INTO declaration_documents (
    client_id,
    company_name,
    system_capacity,
    consumer_name,
    project_address,
    application_number,
    application_date,
    discom_name,
    pv_module_capacity,
    pv_module_count,
    inverter_no,
    pv_module_make,
    cell_manufacturer,
    cell_gst_invoice,
    panel_serial_numbers,
    declarant_name,
    declarant_designation,
    declarant_phone,
    declarant_email,
    declaration_date,
    created_by
) VALUES (
    ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?
)
ON DUPLICATE KEY UPDATE
    company_name = VALUES(company_name),
    system_capacity = VALUES(system_capacity),
    consumer_name = VALUES(consumer_name),
    project_address = VALUES(project_address),
    application_number = VALUES(application_number),
    application_date = VALUES(application_date),
    discom_name = VALUES(discom_name),
    pv_module_capacity = VALUES(pv_module_capacity),
    pv_module_count = VALUES(pv_module_count),
    inverter_no = VALUES(inverter_no),
    pv_module_make = VALUES(pv_module_make),
    cell_manufacturer = VALUES(cell_manufacturer),
    cell_gst_invoice = VALUES(cell_gst_invoice),
    panel_serial_numbers = VALUES(panel_serial_numbers),
    declarant_name = VALUES(declarant_name),
    declarant_designation = VALUES(declarant_designation),
    declarant_phone = VALUES(declarant_phone),
    declarant_email = VALUES(declarant_email),
    declaration_date = VALUES(declaration_date),
    updated_at = CURRENT_TIMESTAMP
";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "isdsssssdissssssssssi",
    $client_id,
    $_POST['company_name'],
    $_POST['system_capacity'],
    $_POST['consumer_name'],
    $_POST['project_address'],
    $_POST['application_number'],
    $_POST['application_date'],
    $_POST['discom_name'],
    $_POST['pv_module_capacity'],
    $_POST['pv_module_count'],
    $_POST['inverter_no'],
    $_POST['pv_module_make'],
    $_POST['cell_manufacturer'],
    $_POST['cell_gst_invoice'],
    $_POST['pannel_serial'], // textarea name
    $_POST['declarant_name'],
    $_POST['declarant_designation'],
    $_POST['declarant_phone'],
    $_POST['declarant_email'],
    $_POST['declaration_date'],
    $created_by
);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Declaration saved successfully']);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}

$stmt->close();
$conn->close();
