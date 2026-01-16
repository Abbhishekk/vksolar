<?php
require_once __DIR__ . '/../connect/auth_middleware.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../connect/db.php';
$auth->requirePermission('quotation_management', 'create');

/* ======================================================
   1. CLIENT ID VALIDATION
====================================================== */
$client_id = 0;

if (isset($_POST['client_id']) && is_numeric($_POST['client_id'])) {
    $client_id = (int)$_POST['client_id'];
} elseif (isset($_GET['client_id']) && is_numeric($_GET['client_id'])) {
    $client_id = (int)$_GET['client_id'];
}

if ($client_id <= 0) {
    die('Invalid client reference');
}
$_SESSION['commissioning_client_id'] = $client_id;

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
   3. FETCH SAVED COMMISSIONING CERTIFICATE (IF EXISTS)
====================================================== */
$savedData = [];

$q = $conn->prepare("
    SELECT
        *
    FROM commissioning_certificates
    WHERE client_id = ?
    LIMIT 1
");

$q->bind_param("i", $client_id);
$q->execute();
$r = $q->get_result()->fetch_assoc();

if ($r) {
    $savedData = $r;
}

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
//print_r($client_doc_sign);

/* ======================================================
   4. BUILD PREFILL ARRAY
   (CLIENT DATA → OVERRIDDEN BY SAVED DATA)
====================================================== */
$prefill = array_merge(

    /* -------- defaults from CLIENT table -------- */
    [
        'consumer_name'        => $client['name'] ?? '',
        'consumer_number'      => $client['consumer_number'] ?? '',
        'mobile_number'        => $client['mobile'] ?? '',
        'email'                => $client['email'] ?? '',
        'installation_address' => trim(
            ($client['location'] ?? '') . ' ' .
            ($client['village'] ?? '') . ' ' .
            ($client['taluka'] ?? '') . ' ' .
            ($client['district'] ?? '')
        ),

        're_arrangement_type'  => 'Grid Connected',
        're_source'            => 'Solar',
        'sanctioned_capacity'  => $client['kilo_watt'] ?? '',
        'capacity_type'        => 'Rooftop',
        'project_model'        => 'CAPEX',
        'installation_date'    => date('Y-m-d'),

        'inverter_capacity'    => $client['kilo_watt'] ?? '',
        'inverter_make'        => $client['inverter_company_name'] ?? '',

        'number_of_modules'    => $client['number_of_panels'] ?? '',
        'module_capacity'      => $client['wattage'] ?? '',
        'module_make'          => $client['company_name'] ?? '',

        'company_name'         => 'VK SOLAR ENERGY',
        'rep_name'             => 'Mr. VILAS KALE',
        'company_phone'        => $client['reference_contact'] ?? '',
        'company_email'        => 'vksolarenergy1989@gmail.com',
        'client_signature'  => $client_doc_sign['file_path'] ?? ''
    ],

    /* -------- override from SAVED COMMISSIONING -------- */
    $savedData ?: []
);

/* ======================================================
   5. STORE CLIENT ID FOR SAVE API
====================================================== */
$_SESSION['commissioning_client_id'] = $client_id;

/* ======================================================
   6. PASS PREFILL DATA TO UI (JS)
====================================================== */
?>
<script>
const commissioningData = <?= json_encode($prefill, JSON_UNESCAPED_UNICODE); ?>;

document.addEventListener('DOMContentLoaded', function () {

    if (typeof commissioningData === 'undefined') return;

    Object.keys(commissioningData).forEach(function (key) {
        const field = document.getElementById(key);
        if (field && commissioningData[key] !== null && commissioningData[key] !== '') {
            field.value = commissioningData[key];
        }
    });

});


</script>


<?php
/* ======================================================
   7. INCLUDE COMMISSIONING UI (UNCHANGED)
====================================================== */
require_once __DIR__ . '/../commissioning.php';
