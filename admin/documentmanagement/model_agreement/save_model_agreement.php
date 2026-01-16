<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../connect/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Unauthorized');
}

/* ---------- client_id ---------- */
$client_id = $_SESSION['model_agreement_client_id'] ?? 0;
if ($client_id <= 0) {
    http_response_code(400);
    exit('Invalid client');
}

/* ---------- collect POST ---------- */
$data = [
    'applicant_name'        => $_POST['applicant_name'] ?? '',
    'consumer_number'       => $_POST['consumer_number'] ?? '',
    'applicant_address'     => $_POST['applicant_address'] ?? '',
    'agreement_date'        => $_POST['agreement_date'] ?? '',

    'vendor_name'           => $_POST['vendor_name'] ?? '',
    'vendor_address'        => $_POST['vendor_address'] ?? '',

    'system_capacity'       => $_POST['system_capacity'] ?? '',
    'pv_module_make'        => $_POST['pv_module_make'] ?? '',
    'pv_module_capacity'    => $_POST['pv_module_capacity'] ?? '',
    'panel_efficiency'      => $_POST['panel_efficiency'] ?? '',

    'inverter_company_name' => $_POST['inverter_company_name'] ?? '',
    'inverter_capacity'     => $_POST['inverter_capacity'] ?? '',

    'system_cost'           => $_POST['system_cost'] ?? '',
    'advance_percentage'    => $_POST['advance_percentage'] ?? '',
    'dispatch_percentage'   => $_POST['dispatch_percentage'] ?? '',
    'completion_percentage' => $_POST['completion_percentage'] ?? ''
];

/* ---------- validation ---------- */
foreach ($data as $key => $value) {
    if ($value === '') {
        http_response_code(422);
        exit("Missing field: $key");
    }
}

/* ---------- check existing ---------- */
$check = $conn->prepare(
    "SELECT id FROM model_agreements WHERE client_id = ? LIMIT 1"
);
$check->bind_param("i", $client_id);
$check->execute();
$exists = $check->get_result()->fetch_assoc();

/* ---------- INSERT / UPDATE ---------- */
if ($exists) {

    $stmt = $conn->prepare("
        UPDATE model_agreements SET
            applicant_name=?,
            consumer_number=?,
            applicant_address=?,
            agreement_date=?,
            vendor_name=?,
            vendor_address=?,
            system_capacity=?,
            pv_module_make=?,
            pv_module_capacity=?,
            panel_efficiency=?,
            inverter_company_name=?,
            inverter_capacity=?,
            system_cost=?,
            advance_percentage=?,
            dispatch_percentage=?,
            completion_percentage=?,
            updated_at=NOW()
        WHERE client_id=?
    ");

    $stmt->bind_param(
        "sssssssdsssssdiiii",
        $data['applicant_name'],
        $data['consumer_number'],
        $data['applicant_address'],
        $data['agreement_date'],
        $data['vendor_name'],
        $data['vendor_address'],
        $data['system_capacity'],
        $data['pv_module_make'],
        $data['pv_module_capacity'],
        $data['panel_efficiency'],
        $data['inverter_company_name'],
        $data['inverter_capacity'],
        $data['system_cost'],
        $data['advance_percentage'],
        $data['dispatch_percentage'],
        $data['completion_percentage'],
        $client_id
    );

} else {

    $stmt = $conn->prepare("
        INSERT INTO model_agreements (
            client_id,
            applicant_name,
            consumer_number,
            applicant_address,
            agreement_date,
            vendor_name,
            vendor_address,
            system_capacity,
            pv_module_make,
            pv_module_capacity,
            panel_efficiency,
            inverter_company_name,
            inverter_capacity,
            system_cost,
            advance_percentage,
            dispatch_percentage,
            completion_percentage,
            created_by
        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
    ");

    $stmt->bind_param(
        "issssssssssssdiiii",
        $client_id,
        $data['applicant_name'],
        $data['consumer_number'],
        $data['applicant_address'],
        $data['agreement_date'],
        $data['vendor_name'],
        $data['vendor_address'],
        $data['system_capacity'],
        $data['pv_module_make'],
        $data['pv_module_capacity'],
        $data['panel_efficiency'],
        $data['inverter_company_name'],
        $data['inverter_capacity'],
        $data['system_cost'],
        $data['advance_percentage'],
        $data['dispatch_percentage'],
        $data['completion_percentage'],
        $_SESSION['user_id']
    );
}

/* ---------- execute ---------- */
$stmt->execute();

/* ---------- response ---------- */
echo json_encode([
    'status' => 'success',
    'message' => 'Model Agreement saved successfully'
]);
