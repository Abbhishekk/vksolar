<?php
// admin/api/employee_api.php
session_start();
require_once '../connect/db.php';
require_once '../connect/auth_middleware.php';

$auth = new AuthMiddleware($conn);
$response = ['success' => false, 'message' => ''];

header('Content-Type: application/json');

// Create uploads directory if it doesn't exist
$uploadDir = __DIR__ . '/../uploads/employees/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

try {
    // Check authentication
    if (!$auth->isLoggedIn()) {
        throw new Exception("Authentication required");
    }

    // Check permission for employee management
    if (!$auth->checkPermission('employee_management', 'view')) {
        throw new Exception("Access denied");
    }

    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    switch ($action) {
        case 'add_employee':
            if (!$auth->checkPermission('employee_management', 'create')) {
                throw new Exception("You don't have permission to add employees");
            }

            // Validate required fields
            $required = ['full_name', 'employee_id', 'email', 'phone', 'role', 'username', 'password'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("Field '$field' is required");
                }
            }

            // Check if employee ID already exists
            $check_stmt = $conn->prepare("SELECT id FROM employees WHERE employee_id = ?");
            $check_stmt->bind_param("s", $_POST['employee_id']);
            $check_stmt->execute();
            if ($check_stmt->get_result()->num_rows > 0) {
                throw new Exception("Employee ID already exists");
            }

            // Check if email already exists
            $check_stmt = $conn->prepare("SELECT id FROM employees WHERE email = ?");
            $check_stmt->bind_param("s", $_POST['email']);
            $check_stmt->execute();
            if ($check_stmt->get_result()->num_rows > 0) {
                throw new Exception("Email already exists");
            }

            // Check if username already exists in users table
            $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $check_stmt->bind_param("s", $_POST['username']);
            $check_stmt->execute();
            if ($check_stmt->get_result()->num_rows > 0) {
                throw new Exception("Username already exists");
            }

            // Handle profile picture upload
            $profilePicturePath = null;
            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['profile_picture'];
                
                // Validate file type
                $allowedTypes = ['image/jpeg', 'image/png'];
                $fileType = mime_content_type($file['tmp_name']);
                if (!in_array($fileType, $allowedTypes)) {
                    throw new Exception("Invalid file type. Only JPG and PNG images are allowed.");
                }
                
                // Validate file size (1MB)
                if ($file['size'] > 1048576) {
                    throw new Exception("Image size must be less than 1MB.");
                }
                
                // Generate unique filename
                $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $fileName = 'emp_' . time() . '_' . uniqid() . '.' . $fileExtension;
                $filePath = $uploadDir . $fileName;
                
                // Resize and save image
                if (resizeImage($file['tmp_name'], $filePath, 300, 300)) {
                    $profilePicturePath = 'uploads/employees/' . $fileName;
                } else {
                    throw new Exception("Failed to process image upload.");
                }
            }

            // Insert into employees table
            $stmt = $conn->prepare("
                INSERT INTO employees (
                    employee_id, profile_picture, full_name, email, phone, role, department, position,
                    salary, joining_date, address, city, state, pincode,
                    emergency_contact, emergency_contact_name, created_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $salary = !empty($_POST['salary']) ? $_POST['salary'] : null;
            $joining_date = !empty($_POST['joining_date']) ? $_POST['joining_date'] : null;
            $created_by = $_SESSION['user_id'];

            $stmt->bind_param(
                "sssssssdssssssssi",
                $_POST['employee_id'],
                $profilePicturePath,
                $_POST['full_name'],
                $_POST['email'],
                $_POST['phone'],
                $_POST['role'],
                $_POST['department'],
                $_POST['position'],
                $salary,
                $joining_date,
                $_POST['address'],
                $_POST['city'],
                $_POST['state'],
                $_POST['pincode'],
                $_POST['emergency_contact'],
                $_POST['emergency_contact_name'],
                $created_by
            );

            if ($stmt->execute()) {
                $employee_id = $stmt->insert_id;

                // Create user account for system access
                $user_stmt = $conn->prepare("
                    INSERT INTO users (
                        username, email, password_hash, full_name, phone, role, created_by
                    ) VALUES (?, ?, ?, ?, ?, ?, ?)
                ");

                $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
                
                $user_stmt->bind_param(
                    "ssssssi",
                    $_POST['username'],
                    $_POST['email'],
                    $password_hash,
                    $_POST['full_name'],
                    $_POST['phone'],
                    $_POST['role'],
                    $created_by
                );

                if ($user_stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = "Employee added successfully and system access created";
                    $response['employee_id'] = $employee_id;
                } else {
                    // Rollback employee insertion if user creation fails
                    $conn->query("DELETE FROM employees WHERE id = $employee_id");
                    throw new Exception("Failed to create system access: " . $conn->error);
                }
            } else {
                throw new Exception("Failed to add employee: " . $conn->error);
            }
            break;

        case 'get_employees':
            $query = "SELECT e.*, u.username 
                     FROM employees e 
                     LEFT JOIN users u ON e.email = u.email 
                     WHERE 1=1";
            
            // Add filters if provided
            $params = [];
            $types = "";
            
            if (isset($_GET['role']) && !empty($_GET['role'])) {
                $query .= " AND e.role = ?";
                $params[] = $_GET['role'];
                $types .= "s";
            }
            
            if (isset($_GET['department']) && !empty($_GET['department'])) {
                $query .= " AND e.department LIKE ?";
                $params[] = "%" . $_GET['department'] . "%";
                $types .= "s";
            }
            
            if (isset($_GET['is_active'])) {
                $query .= " AND e.is_active = ?";
                $params[] = $_GET['is_active'];
                $types .= "i";
            }
            
            $query .= " ORDER BY e.created_at DESC";
            
            $stmt = $conn->prepare($query);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            
            $employees = [];
            while ($row = $result->fetch_assoc()) {
                $employees[] = $row;
            }
            
            $response['success'] = true;
            $response['data'] = $employees;
            break;
             case 'get_employee':
            if (!$auth->checkPermission('employee_management', 'view')) {
                throw new Exception("You don't have permission to view employee details");
            }

            $employee_id = $_GET['id'] ?? 0;
            if ($employee_id <= 0) {
                throw new Exception("Invalid employee ID");
            }

            $stmt = $conn->prepare("
                SELECT e.*, u.username 
                FROM employees e 
                LEFT JOIN users u ON e.email = u.email 
                WHERE e.id = ?
            ");
            $stmt->bind_param("i", $employee_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $employee = $result->fetch_assoc();
                $response['success'] = true;
                $response['data'] = $employee;
            } else {
                throw new Exception("Employee not found");
            }
            break;

        case 'update_employee':
            if (!$auth->checkPermission('employee_management', 'edit')) {
                throw new Exception("You don't have permission to update employees");
            }

            $employee_id = $_POST['id'] ?? 0;
            if ($employee_id <= 0) {
                throw new Exception("Invalid employee ID");
            }

            // Check if employee exists
            $check_stmt = $conn->prepare("SELECT id FROM employees WHERE id = ?");
            $check_stmt->bind_param("i", $employee_id);
            $check_stmt->execute();
            if ($check_stmt->get_result()->num_rows === 0) {
                throw new Exception("Employee not found");
            }

            // Handle profile picture upload if provided
            $profilePicturePath = null;
            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['profile_picture'];
                
                // Validate file type
                $allowedTypes = ['image/jpeg', 'image/png'];
                $fileType = mime_content_type($file['tmp_name']);
                if (!in_array($fileType, $allowedTypes)) {
                    throw new Exception("Invalid file type. Only JPG and PNG images are allowed.");
                }
                
                // Validate file size (1MB)
                if ($file['size'] > 1048576) {
                    throw new Exception("Image size must be less than 1MB.");
                }
                
                // Generate unique filename
                $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $fileName = 'emp_' . time() . '_' . uniqid() . '.' . $fileExtension;
                $filePath = '../uploads/employees/' . $fileName;
                
                // Resize and save image
                if (resizeImage($file['tmp_name'], $filePath, 300, 300)) {
                    $profilePicturePath = 'uploads/employees/' . $fileName;
                    
                    // Delete old profile picture if exists
                    $old_pic_stmt = $conn->prepare("SELECT profile_picture FROM employees WHERE id = ?");
                    $old_pic_stmt->bind_param("i", $employee_id);
                    $old_pic_stmt->execute();
                    $old_pic_result = $old_pic_stmt->get_result();
                    if ($old_pic_result->num_rows === 1) {
                        $old_employee = $old_pic_result->fetch_assoc();
                        if ($old_employee['profile_picture'] && file_exists('../' . $old_employee['profile_picture'])) {
                            unlink('../' . $old_employee['profile_picture']);
                        }
                    }
                } else {
                    throw new Exception("Failed to process image upload.");
                }
            }

            // Build update query
            $updateFields = [];
            $updateParams = [];
            $updateTypes = "";

            $fields = [
                'full_name', 'email', 'phone', 'role', 'department', 'position',
                'salary', 'joining_date', 'address', 'city', 'state', 'pincode',
                'emergency_contact', 'emergency_contact_name'
            ];

            foreach ($fields as $field) {
                if (isset($_POST[$field])) {
                    $updateFields[] = "$field = ?";
                    $updateParams[] = $_POST[$field];
                    $updateTypes .= "s";
                }
            }

            if ($profilePicturePath) {
                $updateFields[] = "profile_picture = ?";
                $updateParams[] = $profilePicturePath;
                $updateTypes .= "s";
            }

            if (empty($updateFields)) {
                throw new Exception("No fields to update");
            }

            $updateParams[] = $employee_id;
            $updateTypes .= "i";

            $query = "UPDATE employees SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param($updateTypes, ...$updateParams);

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = "Employee updated successfully";
            } else {
                throw new Exception("Failed to update employee: " . $conn->error);
            }
            break;

        case 'toggle_status':
            if (!$auth->checkPermission('employee_management', 'edit')) {
                throw new Exception("You don't have permission to change employee status");
            }
        
            $employee_id = $_POST['id'] ?? 0;
            $new_status = $_POST['status'] ?? 0;
        
            if ($employee_id <= 0) {
                throw new Exception("Invalid employee ID");
            }
        
            $stmt = $conn->prepare("UPDATE employees SET is_active = ? WHERE id = ?");
            $stmt->bind_param("ii", $new_status, $employee_id);
        
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = "Employee status updated successfully";
            } else {
                throw new Exception("Failed to update employee status: " . $conn->error);
            }
            break;

     case 'delete_employee':
            if (!$auth->checkPermission('employee_management', 'delete')) {
                throw new Exception("You don't have permission to delete employees");
            }
        
            $employee_id = $_POST['id'] ?? 0;
            if ($employee_id <= 0) {
                throw new Exception("Invalid employee ID");
            }
        
            // Get employee details for cleanup
            $employee_stmt = $conn->prepare("SELECT profile_picture, email, created_by FROM employees WHERE id = ?");
            $employee_stmt->bind_param("i", $employee_id);
            $employee_stmt->execute();
            $employee_result = $employee_stmt->get_result();
        
            if ($employee_result->num_rows === 0) {
                throw new Exception("Employee not found");
            }
        
            $employee = $employee_result->fetch_assoc();
        
            // Start transaction
            $conn->begin_transaction();
        
            try {
                // First, check if this employee is referenced as created_by in other records
                $check_references_stmt = $conn->prepare("SELECT COUNT(*) as ref_count FROM employees WHERE created_by = ?");
                $check_references_stmt->bind_param("i", $employee_id);
                $check_references_stmt->execute();
                $ref_result = $check_references_stmt->get_result();
                $ref_count = $ref_result->fetch_assoc()['ref_count'];
                
                if ($ref_count > 0) {
                    // Update the created_by references to the current admin or super_admin
                    $update_ref_stmt = $conn->prepare("UPDATE employees SET created_by = ? WHERE created_by = ?");
                    $current_admin_id = $_SESSION['user_id']; // Current logged-in admin
                    $update_ref_stmt->bind_param("ii", $current_admin_id, $employee_id);
                    $update_ref_stmt->execute();
                }
        
                // Delete from users table first
                $user_stmt = $conn->prepare("DELETE FROM users WHERE email = ?");
                $user_stmt->bind_param("s", $employee['email']);
                $user_stmt->execute();
        
                // Delete from employees table
                $emp_stmt = $conn->prepare("DELETE FROM employees WHERE id = ?");
                $emp_stmt->bind_param("i", $employee_id);
                $emp_stmt->execute();
        
                // Verify employee was deleted
                if ($emp_stmt->affected_rows === 0) {
                    throw new Exception("Failed to delete employee record");
                }
        
                // Delete profile picture if exists
                if ($employee['profile_picture'] && file_exists('../' . $employee['profile_picture'])) {
                    if (!unlink('../' . $employee['profile_picture'])) {
                        error_log("Warning: Failed to delete profile picture: " . '../' . $employee['profile_picture']);
                    }
                }
        
                $conn->commit();
                $response['success'] = true;
                $response['message'] = "Employee deleted successfully";
                
            } catch (Exception $e) {
                $conn->rollback();
                throw new Exception("Failed to delete employee: " . $e->getMessage());
            }
            break;
        default:
            throw new Exception("Invalid action");
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log("Employee API Error: " . $e->getMessage());
}

echo json_encode($response);

/**
 * Resize and compress image
 */
function resizeImage($sourcePath, $destinationPath, $maxWidth, $maxHeight) {
    list($sourceWidth, $sourceHeight, $sourceType) = getimagesize($sourcePath);
    
    switch ($sourceType) {
        case IMAGETYPE_JPEG:
            $sourceImage = imagecreatefromjpeg($sourcePath);
            break;
        case IMAGETYPE_PNG:
            $sourceImage = imagecreatefrompng($sourcePath);
            break;
        default:
            return false;
    }
    
    // Calculate new dimensions while maintaining aspect ratio
    $aspectRatio = $sourceWidth / $sourceHeight;
    
    if ($sourceWidth > $maxWidth || $sourceHeight > $maxHeight) {
        if ($maxWidth / $maxHeight > $aspectRatio) {
            $newWidth = $maxHeight * $aspectRatio;
            $newHeight = $maxHeight;
        } else {
            $newWidth = $maxWidth;
            $newHeight = $maxWidth / $aspectRatio;
        }
    } else {
        $newWidth = $sourceWidth;
        $newHeight = $sourceHeight;
    }
    
    $newImage = imagecreatetruecolor($newWidth, $newHeight);
    
    // Preserve transparency for PNG
    if ($sourceType == IMAGETYPE_PNG) {
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
        $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
        imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
    }
    
    imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $sourceWidth, $sourceHeight);
    
    // Save image with compression
    switch ($sourceType) {
        case IMAGETYPE_JPEG:
            $result = imagejpeg($newImage, $destinationPath, 85); // 85% quality
            break;
        case IMAGETYPE_PNG:
            $result = imagepng($newImage, $destinationPath, 8); // Compression level 8
            break;
        default:
            $result = false;
    }
    
    imagedestroy($sourceImage);
    imagedestroy($newImage);
    
    return $result;
}
?>