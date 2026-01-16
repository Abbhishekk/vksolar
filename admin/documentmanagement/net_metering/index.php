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

/* ---------- fetch saved net metering ---------- */
$saved = [];
$q = $conn->prepare("SELECT * FROM net_metering_agreements WHERE client_id=? LIMIT 1");
$q->bind_param("i", $client_id);
$q->execute();
$r = $q->get_result()->fetch_assoc();
if ($r) $saved = $r;

/* ---------- PREFILL ---------- */
$prefill = array_merge($saved, [
    'consumer_name'   => $client['name'] ?? '',
    'consumer_number' => $client['consumer_number'] ?? '',
    'address'         => trim(
        ($client['location'] ?? '') . ' ' .
        ($client['village'] ?? '') . ' ' .
        ($client['taluka'] ?? '') . ' ' .
        ($client['district'] ?? '')
    ),
    'location'        => $client['district'] ?? '',
    'system_capacity' => $client['kilo_watt'] ?? '',
    'vendor_name'     => 'VK SOLAR ENERGY',
    'agreement_date'  => date('Y-m-d')
]);

$_SESSION['net_metering_client_id'] = $client_id;
?>

<script>
const netMeteringData = <?= json_encode($prefill, JSON_UNESCAPED_UNICODE); ?>;
</script>

<?php
require_once __DIR__ . '/../NetMeteringAgreement.php';
