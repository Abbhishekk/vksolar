<?php
require_once __DIR__ . '/../connect/auth_middleware.php';

if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../connect/db.php';
$auth->requirePermission('quotation_management', 'create');

/* ======================================================
   1. CLIENT ID VALIDATION
====================================================== */
$client_id = 0;

if (isset($_POST['client_id']) && is_numeric($_POST['client_id'])) {
    $client_id = (int) $_POST['client_id'];
} elseif (isset($_GET['client_id']) && is_numeric($_GET['client_id'])) {
    $client_id = (int) $_GET['client_id'];
}

if ($client_id <= 0) {
    die('Invalid client reference');
}

/* ======================================================
   2. FETCH CLIENT MASTER DATA
====================================================== */
$stmt = $conn->prepare("SELECT * FROM clients WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $client_id);
$stmt->execute();
$client = $stmt->get_result()->fetch_assoc();

if (!$client) {
    die('Client not found');
}

/* ======================================================
   3. FETCH SAVED DECLARATION (IF EXISTS)
====================================================== */
$savedData = [];

$q = $conn->prepare("
    SELECT
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
        declaration_date
    FROM declaration_documents
    WHERE client_id = ?
    LIMIT 1
");
$q->bind_param("i", $client_id);
$q->execute();
$r = $q->get_result()->fetch_assoc();

if ($r) {
    $savedData = $r;
}

/* ======================================================
   4. BUILD PREFILL ARRAY (CLIENT → OVERRIDDEN BY SAVED)
====================================================== */
$prefill = array_merge(
    [
        // ---- defaults from CLIENT table ----
        'company_name'        => 'V K Solar Energy',
        'system_capacity'     => $client['kilo_watt'] ??  $savedData['system_capacity'] ?? '',
        'consumer_name'       => $client['name'] ?? $savedData['consumer_name'] ?? '',
        'project_address'     => $client['village'] ?? $savedData['project_address']     ?? '',
        'application_number'  => $client['rooftop_solar_application_number'] ?? $savedData['application_number']  ?? '',
        'application_date'    => $savedData['application_date']    ?? date('Y-m-d'),
        'discom_name'         => $client['discom_name'] ?? $savedData['discom_name']         ?? '',

        'pv_module_capacity'  => $client['wattage'] ?? $savedData['pv_module_capacity']  ?? '',
        'pv_module_count'     => $client['number_of_panels'] ?? $savedData['pv_module_count']     ?? '',
        'pv_module_make'      => $client['company_name'] ??  $savedData['pv_module_make']      ?? '',
        'inverter_no'         => $client['inverter_serial_number'] ?? $savedData['inverter_no']         ?? '',
        'pannel_serial'       => $client['panel_serial_numbers'] ?? $savedData['panel_serial_numbers'] ?? '',

        'cell_manufacturer'   => $savedData['cell_manufacturer']   ?? '',
        'cell_gst_invoice'    => $savedData['cell_gst_invoice']    ??'',

        'declarant_name'      => $client['name'] ?? $savedData['declarant_name']      ?? '',
        'declarant_designation'=> $savedData['declarant_designation'] ?? 'Authorized Signatory',
        'declarant_phone'     => $client['mobile'] ?? $savedData['declarant_phone']     ?? '',
        'declarant_email'     => $client['email'] ?? $savedData['declarant_email']     ?? '',
        'declaration_date'    => $savedData['declaration_date']    ??date('Y-m-d')
    ]
   
);

/* ======================================================
   5. STORE CLIENT ID FOR SAVE API
====================================================== */
$_SESSION['declaration_client_id'] = $client_id;
?>

<!-- ======================================================
     6. PASS DATA TO DECLARATION UI (JS)
====================================================== -->
<script>
const client = <?= json_encode($prefill, JSON_UNESCAPED_UNICODE); ?>;
</script>

<?php
/* ======================================================
   7. INCLUDE FINAL UI (UNCHANGED)
====================================================== */
require_once __DIR__ . '/../decleration.php';
