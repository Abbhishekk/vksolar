<?php
// admin/connect/auth_middleware.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class AuthMiddleware {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    public function requireAuth() {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
            $this->redirectToLogin();
        }
        
        if (!$this->verifyUserActive($_SESSION['user_id'])) {
            $this->logout();
            $this->redirectToLogin();
        }
        
        $_SESSION['last_activity'] = time();
        
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
            $this->logout();
            $this->redirectToLogin();
        }
    }
    
    public function requireRole($requiredRole) {
        $this->requireAuth();
        
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== $requiredRole) {
            $this->redirectToUnauthorized();
        }
    }
    
    public function requireAnyRole($allowedRoles) {
        $this->requireAuth();
        
        if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowedRoles)) {
            $this->redirectToUnauthorized();
        }
    }
    
    public function checkPermission($module, $action = 'view') {
        $this->requireAuth();
        
        $role = $_SESSION['role'];
        $permission = $this->getUserPermission($role, $module);
        
        switch ($action) {
            case 'view': return $permission['can_view'];
            case 'create': return $permission['can_create'];
            case 'edit': return $permission['can_edit'];
            case 'delete': return $permission['can_delete'];
            default: return false;
        }
    }
    
    public function requirePermission($module, $action = 'view') {
        if (!$this->checkPermission($module, $action)) {
            $this->redirectToUnauthorized();
        }
    }
    
    private function getUserPermission($role, $module) {
        $stmt = $this->conn->prepare("
            SELECT can_view, can_create, can_edit, can_delete 
            FROM user_permissions 
            WHERE role = ? AND module = ?
        ");
        $stmt->bind_param("ss", $role, $module);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return ['can_view' => false, 'can_create' => false, 'can_edit' => false, 'can_delete' => false];
    }
    
    private function verifyUserActive($userId) {
        $stmt = $this->conn->prepare("
            SELECT id, username, role, is_active 
            FROM users 
            WHERE id = ? AND is_active = 1
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            return true;
        }
        
        return false;
    }
    
    private function redirectToLogin() {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        // FIXED PATH: Redirect to login in root
        header('Location: ../../login.php');
        exit;
    }
    
    private function redirectToUnauthorized() {
        // FIXED PATH: Redirect to unauthorized in root
        header('Location: ../../unauthorized');
        exit;
    }
    
    public function logout() {
        session_unset();
        session_destroy();
        session_start();
    }
    
    public function getCurrentUser() {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }
        
        $stmt = $this->conn->prepare("
            SELECT u.*, up.profile_picture 
            FROM users u 
            LEFT JOIN user_profiles up ON u.id = up.user_id 
            WHERE u.id = ?
        ");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->num_rows > 0 ? $result->fetch_assoc() : null;
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['user_id']) && isset($_SESSION['username']);
    }
    
    public function getCurrentRole() {
        return $_SESSION['role'] ?? null;
    }
}

// FIXED PATH: Include database from same directory
require_once 'db.php';

// Create AuthMiddleware instance
$auth = new AuthMiddleware($conn);

// Useful global variables
$current_user_id = $_SESSION['user_id'] ?? null;
$current_username = $_SESSION['username'] ?? null;
$current_role = $_SESSION['role'] ?? null;
?>