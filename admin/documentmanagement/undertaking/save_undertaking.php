<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../connect/db.php';

header('Content-Type: application/json');

/* ---------- Allow POST only ---------- */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

/* ---------- Client context ---------- */
$client_id  = $_SESSION['undertaking_client_id'] ?? 0;
$created_by = $_SESSION['user_id'] ?? null;

if ($client_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid client context'
    ]);
    exit;
}

/* ---------- Collect form data (ONLY table columns) ---------- */
$consumer_name      = trim($_POST['consumer_name'] ?? '');
$consumer_number    = trim($_POST['consumer_number'] ?? '');
$project_address    = trim($_POST['consumer_address'] ?? ''); // form field name
$system_capacity    = trim($_POST['system_load'] ?? '');      // form field name
$undertaking_date   = trim($_POST['installation_date'] ?? ''); // form field name
$authorized_person  = trim($_POST['agent_name'] ?? '');
$designation        = 'Authorized Signatory'; // static (or change if you add input)

/* ---------- Validation ---------- */
if (
    $consumer_name === '' ||
    $consumer_number === '' ||
    $project_address === '' ||
    $system_capacity === '' ||
    $undertaking_date === '' ||
    $authorized_person === ''
) {
    echo json_encode([
        'success' => false,
        'message' => 'Please fill all required fields'
    ]);
    exit;
}

/* ---------- INSERT or UPDATE (one undertaking per client) ---------- */
$sql = "
INSERT INTO undertaking_documents (
    client_id,
    consumer_name,
    consumer_number,
    project_address,
    system_capacity,
    undertaking_date,
    authorized_person,
    designation,
    created_by
) VALUES (
    ?,?,?,?,?,?,?,?,?
)
ON DUPLICATE KEY UPDATE
    consumer_name     = VALUES(consumer_name),
    consumer_number   = VALUES(consumer_number),
    project_address   = VALUES(project_address),
    system_capacity   = VALUES(system_capacity),
    undertaking_date  = VALUES(undertaking_date),
    authorized_person = VALUES(authorized_person),
    designation       = VALUES(designation),
    updated_at        = CURRENT_TIMESTAMP
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode([
        'success' => false,
        'message' => $conn->error
    ]);
    exit;
}

$stmt->bind_param(
    "isssdsssi",
    $client_id,
    $consumer_name,
    $consumer_number,
    $project_address,
    $system_capacity,
    $undertaking_date,
    $authorized_person,
    $designation,
    $created_by
);

/* ---------- Execute ---------- */
if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Undertaking saved successfully'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => $stmt->error
    ]);
}

$stmt->close();
$conn->close();
