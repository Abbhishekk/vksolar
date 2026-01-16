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

/* ---------- fetch saved model agreement ---------- */
$saved = [];

$q = $conn->prepare("
    SELECT * 
    FROM model_agreements 
    WHERE client_id=? 
    LIMIT 1
");
$q->bind_param("i", $client_id);
$q->execute();
$r = $q->get_result()->fetch_assoc();

if ($r) {
    $saved = $r;
}

/* ---------- PREFILL (IDs MUST MATCH FORM INPUT IDs) ---------- */
$prefill = array_merge($saved, [

    /* Applicant */
    'applicant_name'    => strtoupper($client['name']) ?? '',
    'consumer_number'   => $client['consumer_number'] ?? '',
    'applicant_address' => strtoupper($client['village']) ?? '',

    /* Vendor defaults */
    'vendor_name'    => 'VK SOLAR ENERGY',
    'vendor_address' => 'NAGPUR , MAHARASHTRA',

    /* System */
    'system_capacity' => $client['kilo_watt'] ?? '',
    'pv_module_make'      => strtoupper($client['company_name']) ?? '',
    'pv_module_capacity'  => $client['wattage'] ?? '',
    'inverter_company_name' => strtoupper($client['inverter_company_name']) ?? '',
    'inverter_no'         => strtoupper($client['inverter_serial_number']) ?? '',
    'inverter_capacity'   => $client['inverter_capacity'] ?? '',

    /* Financial defaults */
    'advance_percentage'    => $saved['advance_percentage'] ?? 10,
    'dispatch_percentage'   => $saved['dispatch_percentage'] ?? 80,
    'completion_percentage' => $saved['completion_percentage'] ?? 10,

    /* Date */
    'agreement_date' => date('Y-m-d')
]);

/* ---------- store client id for save ---------- */
$_SESSION['model_agreement_client_id'] = $client_id;
?>

<script>
const modelAgreementData = <?= json_encode($prefill, JSON_UNESCAPED_UNICODE); ?>;
</script>

<?php
/* ---------- LOAD UI FILE (UNCHANGED) ---------- */
require_once __DIR__ . '/../ModelAgreement.php';
