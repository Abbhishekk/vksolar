<?php
// admin/connect/login_process.php
session_start();

require_once 'db.php';

function verifyLogin($conn, $username, $password) {

    $stmt = $conn->prepare("
        SELECT 
            u.id,
            u.username,
            u.password_hash,
            u.role,
            u.is_active,
            u.full_name,
            e.id AS employee_id
        FROM users u
        LEFT JOIN employees e ON e.email = u.email
        WHERE u.username = ? AND u.is_active = 1
        LIMIT 1
    ");

    if (!$stmt) {
        return ['success' => false, 'message' => 'Database error'];
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows !== 1) {
        return ['success' => false, 'message' => 'Invalid username'];
    }

    $user = $res->fetch_assoc();

    if (
        $password === $user['password_hash'] ||
        password_verify($password, $user['password_hash'])
    ) {
        return ['success' => true, 'user' => $user];
    }

    return ['success' => false, 'message' => 'Invalid password'];
}

/* ================= LOGIN HANDLER ================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$username || !$password) {
        $_SESSION['error_message'] = 'Please fill in all fields';
        header('Location: ../../login.php');
        exit;
    }

    $login = verifyLogin($conn, $username, $password);

    if (!$login['success']) {
        $_SESSION['error_message'] = $login['message'];
        header('Location: ../../login.php');
        exit;
    }

    /* ================= STORE COMMON SESSION ================= */

    $_SESSION['user_id']       = $login['user']['id'];
    $_SESSION['username']      = $login['user']['username'];
    $_SESSION['role']          = $login['user']['role'];
    $_SESSION['full_name']     = $login['user']['full_name'];
    $_SESSION['employee_id']   = $login['user']['employee_id'];
    $_SESSION['last_activity'] = time();

    /* ================= WAREHOUSE STAFF ================= */

    if ($_SESSION['role'] === 'warehouse_staff') {

        if (empty($_SESSION['employee_id'])) {
            $_SESSION['error_message'] = 'Employee profile not linked';
            session_destroy();
            header('Location: ../../login.php');
            exit;
        }

        $stmt = $conn->prepare("
            SELECT warehouse_id
            FROM warehouse_employees
            WHERE employee_id = ?
            LIMIT 1
        ");
        $stmt->bind_param("i", $_SESSION['employee_id']);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();

        if (!$row) {
            $_SESSION['error_message'] = 'No warehouse assigned to this user';
            session_destroy();
            header('Location: ../../login.php');
            exit;
        }

        $_SESSION['warehouse_id'] = (int)$row['warehouse_id'];
    }

    $_SESSION['success_message'] =
        'Login successful! Welcome back, ' . $_SESSION['full_name'];

    header('Location: ../');
    exit;
}

header('Location: ../../login.php');
exit;
