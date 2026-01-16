<?php
header('Content-Type: application/json');

error_log("=== SIMPLE TEST API ===");
error_log("REQUEST METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN'));
error_log("CONTENT TYPE: " . ($_SERVER['CONTENT_TYPE'] ?? 'NOT SET'));

// Get all input methods
$post_data = $_POST;
$get_data = $_GET;
$input_data = file_get_contents('php://input');

error_log("POST data: " . print_r($post_data, true));
error_log("GET data: " . print_r($get_data, true));
error_log("Raw input: " . $input_data);
error_log("REQUEST data: " . print_r($_REQUEST, true));

// Return everything
echo json_encode([
    'success' => true,
    'received' => [
        'post' => $post_data,
        'get' => $get_data,
        'raw_input' => $input_data,
        'request' => $_REQUEST,
        'server' => [
            'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
            'CONTENT_TYPE' => $_SERVER['CONTENT_TYPE'] ?? 'NOT SET'
        ]
    ]
]);
?>