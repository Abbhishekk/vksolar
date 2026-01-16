<?php
// admin/connect/login_process.php
session_start();

require_once 'db.php';

function verifyLogin($conn, $username, $password) {
    // Query to get user by username only (role will be determined from database)
    $stmt = $conn->prepare("
        SELECT u.id, u.username, u.password_hash, u.role, u.is_active, u.full_name, e.id as employee_id
        FROM users u
        LEFT JOIN employees e on e.email=u.email
        WHERE u.username = ? AND u.is_active = 1
    ");
    
    if (!$stmt) {
        return ['success' => false, 'message' => 'Database error: ' . $conn->error];
    }
    
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Compare plain text passwords
        if ($password === $user['password_hash'] || password_verify($password, $user['password_hash'])) {
            return [
                'success' => true,
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'role' => $user['role'], // Role comes from database
                    'full_name' => $user['full_name'],
                    'employee_id' => $user['employee_id'] ?? ""
                ]
            ];
        } else {
            return ['success' => false, 'message' => 'Invalid password'];
        }
    } else {
        return ['success' => false, 'message' => 'Invalid username'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $_SESSION['error_message'] = 'Please fill in all fields';
        header('Location: ../../login.php');
        exit;
    }
    
    $loginResult = verifyLogin($conn, $username, $password);
    
    if ($loginResult['success']) {
        $_SESSION['user_id'] = $loginResult['user']['id'];
        $_SESSION['username'] = $loginResult['user']['username'];
        $_SESSION['role'] = $loginResult['user']['role']; // Role from database
        $_SESSION['full_name'] = $loginResult['user']['full_name'];
        $_SESSION['last_activity'] = time();
        $_SESSION['employee_id'] = $loginResult['user']['employee_id'];
        
        $_SESSION['success_message'] = 'Login successful! Welcome back, ' . $loginResult['user']['full_name'];
        
        // Redirect to admin dashboard
        header('Location: ../');
        exit;
    } else {
        $_SESSION['error_message'] = $loginResult['message'];
        header('Location: ../../login.php');
        exit;
    }
} else {
    header('Location: ../../login');
    exit;
}
?>