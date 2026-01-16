<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$log_file = '../logs/debug.log';
$timestamp = date('Y-m-d H:i:s');

file_put_contents($log_file, "[$timestamp] TEST Endpoint Called\n", FILE_APPEND | LOCK_EX);
file_put_contents($log_file, "[$timestamp] Method: " . $_SERVER['REQUEST_METHOD'] . "\n", FILE_APPEND | LOCK_EX);
file_put_contents($log_file, "[$timestamp] POST Data: " . print_r($_POST, true) . "\n", FILE_APPEND | LOCK_EX);

echo json_encode([
    'success' => true,
    'message' => 'Test endpoint working',
    'method' => $_SERVER['REQUEST_METHOD'],
    'post_data' => $_POST
]);
?>