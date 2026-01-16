<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../connect/db.php';

/* ---------- validate session ---------- */
if (!isset($_SESSION['commissioning_client_id'])) {
    http_response_code(400);
    echo "Client not set";
    exit;
}

$client_id = (int) $_SESSION['commissioning_client_id'];

/* ---------- helper ---------- */
function post($key) {
    return isset($_POST[$key]) ? trim($_POST[$key]) : null;
}

/* ---------- collect data ---------- */
$data = [
    'consumer_name'        => post('consumer_name'),
    'consumer_number'      => post('consumer_number'),
    'mobile_number'        => post('mobile_number'),
    'email'                => post('email'),
    'installation_address' => post('installation_address'),

    're_arrangement_type'  => post('re_arrangement_type'),
    're_source'            => post('re_source'),
    'sanctioned_capacity'  => post('sanctioned_capacity'),
    'capacity_type'        => post('capacity_type'),
    'project_model'        => post('project_model'),
    'installation_date'    => post('installation_date'),

    'inverter_capacity'    => post('inverter_capacity'),
    'inverter_make'        => post('inverter_make'),

    'number_of_modules'    => post('number_of_modules'),
    'module_capacity'      => post('module_capacity'),
    'module_make'          => post('module_make'),

    'company_name'         => post('company_name'),
    'rep_name'             => post('rep_name'),
    'company_phone'        => post('company_phone'),
    'company_email'        => post('company_email'),
    'leter_no'             => post('leter_no')
];

/* ---------- normalize numeric fields (CRITICAL FIX) ---------- */
$data['sanctioned_capacity'] =
    ($data['sanctioned_capacity'] !== null && $data['sanctioned_capacity'] !== '')
        ? (float)$data['sanctioned_capacity'] : 0;

$data['module_capacity'] =
    ($data['module_capacity'] !== null && $data['module_capacity'] !== '')
        ? (float)$data['module_capacity'] : 0;

$data['number_of_modules'] =
    ($data['number_of_modules'] !== null && $data['number_of_modules'] !== '')
        ? (int)$data['number_of_modules'] : 0;

/* ---------- check if record exists ---------- */
$check = $conn->prepare(
    "SELECT id FROM commissioning_certificates WHERE client_id=?"
);
$check->bind_param("i", $client_id);
$check->execute();
$exists = $check->get_result()->num_rows > 0;

/* ================================================================= */
/* ============================ UPDATE ============================== */
/* ================================================================= */
if ($exists) {
 $sql = "UPDATE commissioning_certificates SET
    consumer_name=?,
    consumer_number=?,
    mobile_number=?,
    email=?,
    installation_address=?,
    re_arrangement_type=?,
    re_source=?,
    sanctioned_capacity=?,
    capacity_type=?,
    project_model=?,
    installation_date=?,
    inverter_capacity=?,
    inverter_make=?,
    number_of_modules=?,
    module_capacity=?,
    module_make=?,
    company_name=?,
    rep_name=?,
    company_phone=?,
    company_email=?,
    leter_no=?
WHERE client_id=?";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "sssssssdsssssidssssssi",
    $data['consumer_name'],        // 1 s
    $data['consumer_number'],      // 2 s
    $data['mobile_number'],        // 3 s
    $data['email'],                // 4 s
    $data['installation_address'], // 5 s
    $data['re_arrangement_type'],  // 6 s
    $data['re_source'],            // 7 s
    $data['sanctioned_capacity'],  // 8 d
    $data['capacity_type'],        // 9 s
    $data['project_model'],        // 10 s
    $data['installation_date'],    // 11 s
    $data['inverter_capacity'],    // 12 s
    $data['inverter_make'],        // 13 s
    $data['number_of_modules'],    // 14 i
    $data['module_capacity'],      // 15 d
    $data['module_make'],          // 16 s
    $data['company_name'],         // 17 s
    $data['rep_name'],             // 18 s
    $data['company_phone'],        // 19 s
    $data['company_email'],        // 20 s
    $data['leter_no'],             // 21 s
    $client_id                     // 22 i
);

}
/* ================================================================= */
/* ============================ INSERT ============================== */
/* ======================= (UNCHANGED) ============================== */
/* ================================================================= */
else {

    $sql = "INSERT INTO commissioning_certificates (
        client_id,
        consumer_name, consumer_number, mobile_number, email, installation_address,
        re_arrangement_type, re_source, sanctioned_capacity, capacity_type, project_model, installation_date,
        inverter_capacity, inverter_make,
        number_of_modules, module_capacity, module_make,
        company_name, rep_name, company_phone, company_email, leter_no
    ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "isssssssdssssissssssss",
        $client_id,
        $data['consumer_name'],
        $data['consumer_number'],
        $data['mobile_number'],
        $data['email'],
        $data['installation_address'],
        $data['re_arrangement_type'],
        $data['re_source'],
        $data['sanctioned_capacity'],
        $data['capacity_type'],
        $data['project_model'],
        $data['installation_date'],
        $data['inverter_capacity'],
        $data['inverter_make'],
        $data['number_of_modules'],
        $data['module_capacity'],
        $data['module_make'],
        $data['company_name'],
        $data['rep_name'],
        $data['company_phone'],
        $data['company_email'],
        $data['leter_no']
    );
}

/* ---------- execute & handle errors ---------- */
if (!$stmt->execute()) {
    http_response_code(500);
    echo "Database error: " . $stmt->error;
    exit;
}
if (!$stmt->execute()) {
    die("SQL ERROR: " . $stmt->error);
}

echo "Affected rows: " . $stmt->affected_rows;
exit;

echo "saved";
