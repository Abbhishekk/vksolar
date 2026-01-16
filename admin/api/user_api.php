<?php
// admin/api/user_api.php
session_start();
require_once '../connect/db.php';
require_once '../connect/auth_middleware.php';

$auth = new AuthMiddleware($conn);
$response = ['success' => false, 'message' => ''];

header('Content-Type: application/json');

try {
    // Check authentication
    if (!$auth->isLoggedIn()) {
        throw new Exception("Authentication required");
    }

    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    switch ($action) {
        case 'get_users':
            if (!$auth->checkPermission('user_management', 'view')) {
                throw new Exception("Access denied");
            }

            $query = "SELECT u.*, 
                             creator.username as created_by_name 
                      FROM users u 
                      LEFT JOIN users creator ON u.created_by = creator.id 
                      WHERE 1=1";
            
            $params = [];
            $types = "";
            
            if (isset($_GET['role']) && !empty($_GET['role'])) {
                $query .= " AND u.role = ?";
                $params[] = $_GET['role'];
                $types .= "s";
            }
            
            if (isset($_GET['is_active'])) {
                $query .= " AND u.is_active = ?";
                $params[] = $_GET['is_active'];
                $types .= "i";
            }
            
            $query .= " ORDER BY u.created_at DESC";
            
            $stmt = $conn->prepare($query);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            
            $users = [];
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
            
            $response['success'] = true;
            $response['data'] = $users;
            break;

        case 'add_user':
            if (!$auth->checkPermission('user_management', 'create')) {
                throw new Exception("You don't have permission to add users");
            }

            // Validate required fields
            $required = ['username', 'email', 'full_name', 'role', 'password'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("Field '$field' is required");
                }
            }

            // Check if username already exists
            $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $check_stmt->bind_param("s", $_POST['username']);
            $check_stmt->execute();
            if ($check_stmt->get_result()->num_rows > 0) {
                throw new Exception("Username already exists");
            }

            // Check if email already exists
            $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $check_stmt->bind_param("s", $_POST['email']);
            $check_stmt->execute();
            if ($check_stmt->get_result()->num_rows > 0) {
                throw new Exception("Email already exists");
            }

            // Insert into users table
            $stmt = $conn->prepare("
                INSERT INTO users (
                    username, email, password_hash, full_name, phone, role, 
                    is_active, created_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $phone = $_POST['phone'] ?? null;
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            $created_by = $_SESSION['user_id'];

            $stmt->bind_param(
                "ssssssii",
                $_POST['username'],
                $_POST['email'],
                $password_hash,
                $_POST['full_name'],
                $phone,
                $_POST['role'],
                $is_active,
                $created_by
            );

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = "User created successfully";
                $response['user_id'] = $stmt->insert_id;
            } else {
                throw new Exception("Failed to create user: " . $conn->error);
            }
            break;

        case 'get_user':
            if (!$auth->checkPermission('user_management', 'view')) {
                throw new Exception("You don't have permission to view user details");
            }

            $user_id = $_GET['id'] ?? 0;
            if ($user_id <= 0) {
                throw new Exception("Invalid user ID");
            }

            $stmt = $conn->prepare("
                SELECT u.*, creator.username as created_by_name 
                FROM users u 
                LEFT JOIN users creator ON u.created_by = creator.id 
                WHERE u.id = ?
            ");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                // Don't return password hash
                unset($user['password_hash']);
                $response['success'] = true;
                $response['data'] = $user;
            } else {
                throw new Exception("User not found");
            }
            break;

        case 'update_user':
            if (!$auth->checkPermission('user_management', 'edit')) {
                throw new Exception("You don't have permission to update users");
            }

            $user_id = $_POST['id'] ?? 0;
            if ($user_id <= 0) {
                throw new Exception("Invalid user ID");
            }

            // Check if user exists
            $check_stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
            $check_stmt->bind_param("i", $user_id);
            $check_stmt->execute();
            if ($check_stmt->get_result()->num_rows === 0) {
                throw new Exception("User not found");
            }

            // Build update query
            $updateFields = [];
            $updateParams = [];
            $updateTypes = "";

            $fields = ['username', 'email', 'full_name', 'phone', 'role'];

            foreach ($fields as $field) {
                if (isset($_POST[$field])) {
                    $updateFields[] = "$field = ?";
                    $updateParams[] = $_POST[$field];
                    $updateTypes .= "s";
                }
            }

            // Handle password update if provided
            if (!empty($_POST['password'])) {
                $updateFields[] = "password_hash = ?";
                $updateParams[] = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $updateTypes .= "s";
            }

            // Handle status
            if (isset($_POST['is_active'])) {
                $updateFields[] = "is_active = ?";
                $updateParams[] = $_POST['is_active'] ? 1 : 0;
                $updateTypes .= "i";
            }

            if (empty($updateFields)) {
                throw new Exception("No fields to update");
            }

            $updateParams[] = $user_id;
            $updateTypes .= "i";

            $query = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param($updateTypes, ...$updateParams);

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = "User updated successfully";
            } else {
                throw new Exception("Failed to update user: " . $conn->error);
            }
            break;

        case 'toggle_user_status':
            if (!$auth->checkPermission('user_management', 'edit')) {
                throw new Exception("You don't have permission to change user status");
            }

            $user_id = $_POST['id'] ?? 0;
            $new_status = $_POST['status'] ?? 0;

            if ($user_id <= 0) {
                throw new Exception("Invalid user ID");
            }

            // Don't allow deactivating own account
            if ($user_id == $_SESSION['user_id']) {
                throw new Exception("You cannot deactivate your own account");
            }

            $stmt = $conn->prepare("UPDATE users SET is_active = ? WHERE id = ?");
            $stmt->bind_param("ii", $new_status, $user_id);

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = "User status updated successfully";
            } else {
                throw new Exception("Failed to update user status: " . $conn->error);
            }
            break;

        case 'delete_user':
            if (!$auth->checkPermission('user_management', 'delete')) {
                throw new Exception("You don't have permission to delete users");
            }

            $user_id = $_POST['id'] ?? 0;
            if ($user_id <= 0) {
                throw new Exception("Invalid user ID");
            }

            // Don't allow deleting own account
            if ($user_id == $_SESSION['user_id']) {
                throw new Exception("You cannot delete your own account");
            }

            // Check if user is referenced in other tables
            $check_refs = $conn->prepare("
                SELECT COUNT(*) as ref_count FROM employees WHERE created_by = ?
                UNION ALL
                SELECT COUNT(*) FROM users WHERE created_by = ?
            ");
            $check_refs->bind_param("ii", $user_id, $user_id);
            $check_refs->execute();
            $ref_result = $check_refs->get_result();
            
            $ref_counts = [];
            while ($row = $ref_result->fetch_assoc()) {
                $ref_counts[] = $row['ref_count'];
            }

            if ($ref_counts[0] > 0 || $ref_counts[1] > 0) {
                throw new Exception("Cannot delete user: User is referenced in other records");
            }

            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);

            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $response['success'] = true;
                    $response['message'] = "User deleted successfully";
                } else {
                    throw new Exception("User not found or already deleted");
                }
            } else {
                throw new Exception("Failed to delete user: " . $conn->error);
            }
            break;
            
            // Add these cases to the existing switch statement in user_api.php

        case 'get_permissions':
            if (!$auth->checkPermission('user_management', 'view')) {
                throw new Exception("Access denied");
            }
        
            $stmt = $conn->prepare("SELECT * FROM user_permissions ORDER BY role, module");
            $stmt->execute();
            $result = $stmt->get_result();
            
            $permissions = [];
            while ($row = $result->fetch_assoc()) {
                $permissions[] = $row;
            }
            
            $response['success'] = true;
            $response['data'] = $permissions;
            break;
        
        case 'update_permissions_bulk':
            if (!$auth->checkPermission('user_management', 'edit')) {
                throw new Exception("You don't have permission to update permissions");
            }
        
            $updates = json_decode($_POST['updates'] ?? '[]', true);
            
            if (empty($updates)) {
                throw new Exception("No updates provided");
            }
        
            $conn->begin_transaction();
            
            try {
                foreach ($updates as $update) {
                    $role = $update['role'];
                    $module = $update['module'];
                    $permission_type = $update['permission_type'];
                    $value = $update['value'];
        
                    // Validate permission type
                    $allowed_types = ['view', 'create', 'edit', 'delete'];
                    if (!in_array($permission_type, $allowed_types)) {
                        throw new Exception("Invalid permission type: $permission_type");
                    }
        
                    $column_name = 'can_' . $permission_type;
                    
                    // Use INSERT ... ON DUPLICATE KEY UPDATE to handle missing records
                    $stmt = $conn->prepare("
                        INSERT INTO user_permissions (role, module, {$column_name}) 
                        VALUES (?, ?, ?) 
                        ON DUPLICATE KEY UPDATE {$column_name} = VALUES({$column_name})
                    ");
                    $stmt->bind_param("ssi", $role, $module, $value);
                    
                    if (!$stmt->execute()) {
                        throw new Exception("Failed to update permission: " . $conn->error);
                    }
                }
                
                $conn->commit();
                $response['success'] = true;
                $response['message'] = "Permissions updated successfully";
                
            } catch (Exception $e) {
                $conn->rollback();
                throw new Exception("Failed to update permissions: " . $e->getMessage());
            }
            break;
        
        case 'get_my_profile':
            // Users can always view their own profile
            $user_id = $_SESSION['user_id'];
        
            $stmt = $conn->prepare("
                SELECT u.*, creator.username as created_by_name 
                FROM users u 
                LEFT JOIN users creator ON u.created_by = creator.id 
                WHERE u.id = ?
            ");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
        
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                // Don't return password hash
                unset($user['password_hash']);
                $response['success'] = true;
                $response['data'] = $user;
            } else {
                throw new Exception("User not found");
            }
            break;
        
        case 'update_my_profile':
            // Users can always update their own profile
            $user_id = $_SESSION['user_id'];
        
            // Check if user exists
            $check_stmt = $conn->prepare("SELECT id, password_hash FROM users WHERE id = ?");
            $check_stmt->bind_param("i", $user_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
        
            if ($check_result->num_rows === 0) {
                throw new Exception("User not found");
            }
        
            $current_user = $check_result->fetch_assoc();
        
            // Build update query
            $updateFields = [];
            $updateParams = [];
            $updateTypes = "";
        
            $fields = ['full_name', 'email', 'phone'];
        
            foreach ($fields as $field) {
                if (isset($_POST[$field])) {
                    $updateFields[] = "$field = ?";
                    $updateParams[] = $_POST[$field];
                    $updateTypes .= "s";
                }
            }
        
            // Handle password change if provided
            if (!empty($_POST['new_password'])) {
                // Verify current password
                if (empty($_POST['current_password'])) {
                    throw new Exception("Current password is required to change password");
                }
        
                if (!password_verify($_POST['current_password'], $current_user['password_hash'])) {
                    throw new Exception("Current password is incorrect");
                }
        
                $updateFields[] = "password_hash = ?";
                $updateParams[] = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                $updateTypes .= "s";
            }
        
            if (empty($updateFields)) {
                throw new Exception("No fields to update");
            }
        
            $updateParams[] = $user_id;
            $updateTypes .= "i";
        
            $query = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param($updateTypes, ...$updateParams);
        
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = "Profile updated successfully";
            } else {
                throw new Exception("Failed to update profile: " . $conn->error);
            }
            break;
        
        case 'change_my_password':
            // Users can always change their own password
            $user_id = $_SESSION['user_id'];
        
            $new_password = $_POST['new_password'] ?? '';
            
            if (empty($new_password) ){
                throw new Exception("New password is required");
            }
        
            if (strlen($new_password) < 6) {
                throw new Exception("Password must be at least 6 characters long");
            }
        
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $stmt->bind_param("si", $password_hash, $user_id);
        
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = "Password changed successfully";
            } else {
                throw new Exception("Failed to change password: " . $conn->error);
            }
            break;

        default:
            throw new Exception("Invalid action");
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log("User API Error: " . $e->getMessage());
}

echo json_encode($response);
?>