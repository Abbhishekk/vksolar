<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../connect/db.php';

/* ---------- client_id ---------- */
$client_id = 6;
// if (isset($_POST['client_id']) && is_numeric($_POST['client_id'])) {
//     $client_id = (int)$_POST['client_id'];
// } elseif (isset($_GET['client_id']) && is_numeric($_GET['client_id'])) {
//     $client_id = (int)$_GET['client_id'];
// }
// if ($client_id <= 0) die('Invalid client');

/* ---------- fetch client ---------- */

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

echo $client_doc_adhar['file_path'];
echo $client_doc_sign['file_path'];
$saved = [];
$prefill = array_merge($saved, [
    'adhar_path'        => $client_doc_adhar['file_path'] ?? '',
    'client_signature' => $client_doc_sign['file_path'] ?? ''
]);
echo $prefill['adhar_path'];
echo $prefill['client_signature'];
?>
