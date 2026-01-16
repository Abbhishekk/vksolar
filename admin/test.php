<?php
require_once "connect/db1.php";
try {
    $database = new DBConnection();
    $db = $database->getConnection();
    echo "Database connection successful!";
} catch (Exception $e) {
    echo "Database connection failed: " . $e->getMessage();
}
?>