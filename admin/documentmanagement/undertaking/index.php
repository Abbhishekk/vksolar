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

/* ---------- fetch client documents ---------- */
$stmt = $conn->prepare(
    "SELECT file_path 
     FROM client_documents 
     WHERE client_id=? AND document_type='client_signature'
     LIMIT 1"
);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$client_signature = $stmt->get_result()->fetch_assoc();

/* ---------- fetch saved undertaking ---------- */
$saved = [];
$q = $conn->prepare("SELECT * FROM undertaking_documents WHERE client_id=? LIMIT 1");
$q->bind_param("i", $client_id);
$q->execute();
$r = $q->get_result()->fetch_assoc();
if ($r) $saved = $r;

/* ---------- PREFILL DATA ---------- */
$prefill = array_merge($saved, [

    'consumer_name'      => $client['name'] ?? '',
    'consumer_number'    => $client['consumer_number'] ?? '',
    'project_address'    => trim(
        ($client['village'] ?? '') . ', ' .
        ($client['taluka'] ?? '') . ', ' .
        ($client['district'] ?? '')
    ),

    'system_capacity'    => $client['kilo_watt'] ?? '',
    'undertaking_date'   => date('Y-m-d'),

    'authorized_person'  => 'VK SOLAR ENERGY',
    'designation'        => 'Authorized Signatory',

    'client_signature'   => $client_signature['file_path'] ?? ''
]);

/* ---------- store client id ---------- */
$_SESSION['undertaking_client_id'] = $client_id;
?>

<script>
const undertakingData = <?= json_encode($prefill, JSON_UNESCAPED_UNICODE); ?>;
</script>

<?php
// UI FILE (UNCHANGED)
require_once __DIR__ . '/../undertaking.php';
