<?php
require_once __DIR__ . '/../../connect/auth_middleware.php';

if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../connect/db.php';
$auth->requirePermission('quotation_management', 'create');

/* ---------- client_id ---------- */
$client_id = 0;
if (isset($_POST['client_id']) && is_numeric($_POST['client_id'])) {
    $client_id = (int)$_POST['client_id'];
} elseif (isset($_GET['client_id']) && is_numeric($_GET['client_id'])) {
    $client_id = (int)$_GET['client_id'];
}
if ($client_id <= 0) die('Invalid client');

/* ---------- fetch client ---------- */
$stmt = $conn->prepare("SELECT * FROM clients WHERE id=? LIMIT 1");
$stmt->bind_param("i", $client_id);
$stmt->execute();
$client = $stmt->get_result()->fetch_assoc();
if (!$client) die('Client not found');

/* ---------- fetch client - document ---------- */
$file_path = ''; // default

$stmt = $conn->prepare(
    "SELECT * 
     FROM client_documents 
     WHERE client_id = ? AND document_type = 'aadhar' 
     LIMIT 1"
);
$stmt->bind_param("i", $client_id);
$stmt->execute();

$client_doc_adhar = $stmt->get_result()->fetch_assoc();

/* ---------- fetch client - document ---------- */

$file_path = ''; // default

$stmt = $conn->prepare(
    "SELECT * 
     FROM client_documents 
     WHERE client_id = ? AND document_type = 'client_signature' 
     LIMIT 1"
);
$stmt->bind_param("i", $client_id);
$stmt->execute();

$client_doc_sign = $stmt->get_result()->fetch_assoc();

// print_r($client_doc_sign);
/* ---------- fetch saved WCR ---------- */
$saved = [];
$q = $conn->prepare("SELECT * FROM work_completion_reports WHERE client_id=? LIMIT 1");
$q->bind_param("i", $client_id);
$q->execute();
$r = $q->get_result()->fetch_assoc();
if ($r) $saved = $r;

/* ---------- PREFILL (keys MUST match input IDs) ---------- */
$prefill = array_merge($saved,[
    'name'                 => $client['name'] ?? $saved['name'],
    'consumer_number'      => $client['consumer_number'] ?? $saved['consumer_number'] ?? "",
    'adhar'                => $client['adhar'] ?? $saved['aadhar_number'],
    'address'              => ($client['village'] ?? ''),
    // 'address'              => trim(
    //     ($client['location'] ?? '') . ' ' .
    //     ($client['village'] ?? '') . ' ' .
    //     ($client['taluka'] ?? '') . ' ' .
    //     ($client['district'] ?? '')
    // ),
    'category'             => 'Private',
    'sanction_number'      => $client['rooftop_solar_application_number'] ?? $saved['sanction_number'] ?? ' ',
    'sanctioned_capacity'  => $client['kilo_watt'] ?? $saved['sanctioned_capacity'] ?? ' ',
    'installed_capacity'   => $client['kilo_watt'] ?? $saved['installed_capacity'] ?? ' ',

    'module_make'          => $client['company_name'] ?? '',
    'wattage_per_module'   => $client['wattage'] ?? '',
    'number_of_modules'    => $client['number_of_panels'] ?? '',
    'total_capacity'       => $client['wattage']* $client['number_of_panels'] ?? '',
    'warranty_details'     => '10 Years Product + 25 Years Performance',
    
    /*------inverter variables---------*/
    'inverter_make'        => $client['inverter_company_name'] ?? '',
    'inverter_serial_no'   => $client['inverter_serial_number'] ?? '',
    'inverter_capacity'    =>$client['inverter_capacity'] ?? '',
    'vendor_name'          => 'VK SOLAR ENERGY',
    'report_date'          => date('Y-m-d'),
    
    /*------client-adhar---------*/
    'adhar_path'        => $client_doc_adhar['file_path'] ?? '',
    'client_signature'  => $client_doc_sign['file_path'] ?? ''
    
]);

/* ---------- store client id ---------- */
$_SESSION['wcr_client_id'] = $client_id;
?>

<script>
const wcrData = <?= json_encode($prefill, JSON_UNESCAPED_UNICODE); ?>;
</script>

<?php
require_once __DIR__ . '/../wcr.php'; // UI FILE (UNCHANGED)
