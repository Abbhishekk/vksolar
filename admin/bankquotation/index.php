<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../connect/db.php';
require_once __DIR__ . '/../connect/auth_middleware.php';

$auth->requireAuth();
$auth->requireAnyRole(['super_admin', 'admin', 'office_staff']);
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
if(isset($_GET["quotation_id"])){
    $quotation_id = $_GET["quotation_id"];
}else{
    $quotation_id=null;
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
   3. FETCH SAVED BANK QUOTATION (IF EXISTS)
====================================================== */
$savedData = [];
if($quotation_id!=null){
    $q = $conn->prepare("
    SELECT
    id,
    quotation_number,
    quotation_date,
    validity_date,
    customer_name,
    customer_address,
    pin_code,
    customer_phone,
    customer_email,
    project_location,
    plant_capacity,
    system_type,
    estimated_generation,
    system_description,
    total_amount,
    subsidy,
    final_amount,
    bank_id
FROM bank_quotations
WHERE id = ?
ORDER BY id DESC
LIMIT 1

");
$q->bind_param("i", $quotation_id);
$q->execute();
$r = $q->get_result()->fetch_assoc();
}else{
$q = $conn->prepare("
    SELECT
    id,
    quotation_number,
    quotation_date,
    validity_date,
    customer_name,
    customer_address,
    pin_code,
    customer_phone,
    customer_email,
    project_location,
    plant_capacity,
    system_type,
    estimated_generation,
    system_description,
    total_amount,
    subsidy,
    final_amount,
    bank_id
FROM bank_quotations
WHERE client_id = ?
ORDER BY id DESC
LIMIT 1

");
$q->bind_param("i", $client_id);
$q->execute();
$r = $q->get_result()->fetch_assoc();
}


if ($r) {
    $savedData = $r;
}
/* =========================
   FETCH DEFAULT BANK (IF NO SAVED DATA)
========================= */
$defaultBank = null;

$bankQ = $conn->query("
    SELECT id, bank_name, branch_name, account_number, account_type, ifsc_code, bank_gst
    FROM company_bank_details
    WHERE is_active = 1
    ORDER BY id ASC
    LIMIT 1
");

if ($bankQ && $bankQ->num_rows > 0) {
    $defaultBank = $bankQ->fetch_assoc();
}


$products = [];

if (!empty($savedData['id'])) {
    $pq = $conn->prepare("
        SELECT description, quantity, unit_price AS unitPrice
        FROM bank_quotation_products
        WHERE quotation_id = ?
    ");
    $pq->bind_param("i", $savedData['id']);
    $pq->execute();
    $products = $pq->get_result()->fetch_all(MYSQLI_ASSOC);
}


/* ======================================================
   4. BUILD PREFILL ARRAY (CLIENT → OVERRIDDEN BY SAVED)
====================================================== */
$prefill = [
    // ---- Customer Info ----
    'customer_name'      => $savedData['customer_name']      ?? $client['name'] ??  '',
    'customer_address' => $savedData['customer_address']
                        ?? ($client['address'] 
                        ?? $client['village'] 
                        ?? ''),

    'pin_code'         => $savedData['pin_code']
                        ?? ($client['pin_code']
                        ?? $client['pincode']
                        ?? ''),
    'customer_phone'     => $savedData['customer_phone']     ?? $client['mobile'] ?? '',
    'customer_email'     => $savedData['customer_email']     ?? $client['email'] ?? '',

    // ---- Quotation Meta ----
    'quotation_number'   => $savedData['quotation_number']   ?? '',
    'quotation_date'     => $savedData['quotation_date']     ?? date('Y-m-d'),
    'validity_date'      => $savedData['validity_date']      ?? date('Y-m-d', strtotime('+3 months')),

    // ---- Project Details ----
    'project_location'   => $savedData['project_location']   ?? $client['village'] ?? '',
    'plant_capacity'     => $savedData['plant_capacity']     ?? ($client['kilo_watt'] ?? '') . ' kWp',
    'system_type'        => $savedData['system_type']        ?? 'On Grid',
    'estimated_generation'=> $savedData['estimated_generation'] ?? '',
    'system_description' => $savedData['system_description'] ?? 'Sale of solar power generating system including supply, installation, and commissioning of 3kW On-Grid solar PV system, complete with solar panels, 3kW inverter, all other accessories, net meter and connecting cables, including elevated standard structures.',

    // ---- Financial ----
    'total_amount'       => $savedData['total_amount']       ?? 0,
    'subsidy'            => $savedData['subsidy']            ?? 0,
    'final_amount'       => $savedData['final_amount']       ?? 0,
    
    /* ---------------- BANK PREFILL ---------------- */
        'bank_id' => $savedData['bank_id'] ?? ($defaultBank['id'] ?? ''),

        'bank' => [
            'bank_name'      => $savedData['bank_name'] ?? ($defaultBank['bank_name'] ?? ''),
            'branch_name'    => $savedData['branch_name'] ?? ($defaultBank['branch_name'] ?? ''),
            'account_number' => $savedData['account_number'] ?? ($defaultBank['account_number'] ?? ''),
            'account_type'   => $savedData['account_type'] ?? ($defaultBank['account_type'] ?? ''),
            'ifsc_code'      => $savedData['ifsc_code'] ?? ($defaultBank['ifsc_code'] ?? ''),
            'bank_gst'       => $savedData['bank_gst'] ?? ($defaultBank['bank_gst'] ?? '')
        ],

    // ---- Products ----
   'products' => !empty($products)
    ? $products
    : [
        [
            'description' => 'Sale of solar power generating system including supply, installation, and commissioning of 3kW On-Grid solar PV system',
            'quantity'    => 1,
            'unitPrice'   => 0
        ]
    ]


];

/* ======================================================
   5. STORE CLIENT ID FOR SAVE API
====================================================== */
$_SESSION['bank_quotation_client_id'] = $client_id;

/* ======================================================
   6. PASS DATA TO UI (JS PREFILL)
====================================================== */
?>
<script>
const prefillData = <?= json_encode($prefill, JSON_UNESCAPED_UNICODE); ?>;
</script>

<?php
/* ======================================================
   7. INCLUDE FINAL BANK QUOTATION UI
====================================================== */
require_once __DIR__ . '/bank_quotation.php';
