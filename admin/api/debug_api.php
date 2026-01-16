<?php
header('Content-Type: application/json');

// Log everything
error_log("=== DEBUG API CALLED ===");
error_log("POST data: " . print_r($_POST, true));
error_log("GET data: " . print_r($_GET, true));
error_log("REQUEST data: " . print_r($_REQUEST, true));
error_log("Headers: " . print_r(getallheaders(), true));
error_log("Raw input: " . file_get_contents('php://input'));

// Return all received data
echo json_encode([
    'success' => true,
    'message' => 'Debug data received',
    'post_data' => $_POST,
    'get_data' => $_GET,
    'request_data' => $_REQUEST,
    'raw_input' => file_get_contents('php://input'),
    'server' => [
        'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
        'CONTENT_TYPE' => $_SERVER['CONTENT_TYPE'] ?? 'NOT_SET'
    ]
]);
?>