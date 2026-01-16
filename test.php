<?php
// quick_test.php - Simple connection test
session_start();
echo "<h2>VK Solar - Quick Connection Test</h2>";

try {
    require_once 'admin/connect/db.php';
    echo "<p style='color: green;'>✓ Database connected successfully!</p>";
    
    // Test users table
    $result = $conn->query("SELECT username, role FROM users WHERE is_active = 1");
    echo "<p>Active users found: " . $result->num_rows . "</p>";
    
    if ($result->num_rows > 0) {
        echo "<ul>";
        while ($user = $result->fetch_assoc()) {
            echo "<li>" . $user['username'] . " (" . $user['role'] . ")</li>";
        }
        echo "</ul>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><strong>Session Status:</strong> " . (isset($_SESSION['user_id']) ? "Logged in as user ID: " . $_SESSION['user_id'] : "Not logged in") . "</p>";
echo "<p><a href='login.php'>Go to Login</a> | <a href='test_connection.php'>Full Test</a></p>";
?>