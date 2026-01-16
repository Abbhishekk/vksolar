<?php
// admin/api/quotation_api.php
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

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? $_POST['action'] ?? $_GET['action'] ?? '';

    switch ($action) {
case 'save_quotation':
    if (!$auth->checkPermission('quotation_management', 'create')) {
        throw new Exception("You don't have permission to create quotations");
    }

    // Validate required fields
    $required = [
        'customer_name', 'customer_phone', 'customer_address',
        'property_type', 'roof_type', 'meter_type', 'system_size',
        'panel_company', 'inverter_company', 'panel_model', 'system_type',
        'monthly_bill', 'total_cost', 'subsidy', 'final_cost',
        'monthly_savings', 'yearly_savings', 'payback_period'
    ];

    $missing_fields = [];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            $missing_fields[] = $field;
        }
    }

    if (!empty($missing_fields)) {
        throw new Exception("Missing required fields: " . implode(', ', $missing_fields));
    }

    // Generate unique quotation number
    $quotation_number = 'VKS-Q-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    // Check if quotation number already exists
    $check_stmt = $conn->prepare("SELECT id FROM quotations WHERE quotation_number = ?");
    $check_stmt->bind_param("s", $quotation_number);
    $check_stmt->execute();
    if ($check_stmt->get_result()->num_rows > 0) {
        // Regenerate if exists
        $quotation_number = 'VKS-Q-' . date('YmdHis') . '-' . rand(1000, 9999);
    }

    // Insert quotation
    $stmt = $conn->prepare("
        INSERT INTO quotations (
            quotation_number, customer_name, customer_phone, customer_email, 
            customer_address, property_type, roof_type, meter_type, roof_area,
            system_size, panel_company, inverter_company, panel_model, system_type,
            monthly_bill, battery_backup, monitoring_system, maintenance_package,
            total_cost, subsidy, final_cost, monthly_savings, yearly_savings,
            payback_period, created_by, assigned_to, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $created_by = $_SESSION['user_id'] ?? 0;
    $assigned_to = $_SESSION['user_id'] ?? 0;

    // Convert boolean to integer for database
    $battery_backup = $input['battery_backup'] ? 1 : 0;
    $monitoring_system = $input['monitoring_system'] ? 1 : 0;
    $maintenance_package = $input['maintenance_package'] ? 1 : 0;

    // Convert values to proper types for database
    $roof_area = floatval($input['roof_area'] ?? 0);
    $system_size = floatval($input['system_size']);
    $monthly_bill = floatval($input['monthly_bill']);
    $total_cost = floatval($input['total_cost']);
    $subsidy = floatval($input['subsidy']);
    $final_cost = floatval($input['final_cost']);
    $monthly_savings = floatval($input['monthly_savings']);
    $yearly_savings = floatval($input['yearly_savings']);
    $payback_period = floatval($input['payback_period']);

    // CORRECTED bind_param type string:
    // s=string, d=double, i=integer
    // 27 parameters: 16 strings + 9 doubles + 2 integers
    $bind_result = $stmt->bind_param(
        "ssssssssddssssdddddddddddiis", // CORRECTED TYPE STRING
        $quotation_number,                      // s
        $input['customer_name'],                // s
        $input['customer_phone'],               // s
        $input['customer_email'] ?? '',         // s
        $input['customer_address'],             // s
        $input['property_type'],                // s
        $input['roof_type'],                    // s
        $input['meter_type'],                   // s
        $roof_area,                             // d (DECIMAL)
        $system_size,                           // d (DECIMAL)
        $input['panel_company'],                // s
        $input['inverter_company'],             // s
        $input['panel_model'],                  // s
        $input['system_type'],                  // s
        $monthly_bill,                          // d (DECIMAL)
        $battery_backup,                        // i (TINYINT)
        $monitoring_system,                     // i (TINYINT)
        $maintenance_package,                   // i (TINYINT)
        $total_cost,                            // d (DECIMAL)
        $subsidy,                               // d (DECIMAL)
        $final_cost,                            // d (DECIMAL)
        $monthly_savings,                       // d (DECIMAL)
        $yearly_savings,                        // d (DECIMAL)
        $payback_period,                        // d (DECIMAL)
        $created_by,                            // i (INT)
        $assigned_to,                           // i (INT)
        $input['status']                        // s
    );

    if (!$bind_result) {
        throw new Exception("Bind failed: " . $stmt->error);
    }

    $execute_result = $stmt->execute();

    if ($execute_result) {
        $quotation_id = $stmt->insert_id;
        
        // Record status history
        $history_stmt = $conn->prepare("
            INSERT INTO quotation_status_history (quotation_id, old_status, new_status, changed_by, notes)
            VALUES (?, NULL, ?, ?, 'Quotation created')
        ");
        
        if ($history_stmt) {
            $history_stmt->bind_param("isi", $quotation_id, $input['status'], $created_by);
            $history_stmt->execute();
            $history_stmt->close();
        }
        
        $stmt->close();
        
        $response['success'] = true;
        $response['message'] = "Quotation saved successfully";
        $response['quotation_number'] = $quotation_number;
        $response['quotation_id'] = $quotation_id;
    } else {
        throw new Exception("Failed to save quotation: " . $stmt->error);
    }
    break;

        case 'get_quotations':
            if (!$auth->checkPermission('quotation_management', 'view')) {
                throw new Exception("Access denied");
            }

            // Get current user info for role-based filtering
            $current_user_id = $_SESSION['user_id'] ?? 0;
            $current_user_role = $_SESSION['user_role'] ?? 'guest';

            // Build query with role-based filtering
            $query = "
                SELECT 
                    q.*,
                    creator.username as creator_name,
                    creator.role as creator_role,
                    assigned.username as assigned_name
                FROM quotations q
                LEFT JOIN users creator ON q.created_by = creator.id
                LEFT JOIN users assigned ON q.assigned_to = assigned.id
                WHERE 1=1
            ";

            $params = [];
            $types = "";

            // Role-based filtering
            if ($current_user_role === 'sales_marketing') {
                $query .= " AND (q.created_by = ? OR q.assigned_to = ?)";
                $params[] = $current_user_id;
                $params[] = $current_user_id;
                $types .= "ii";
            }

            // Add filters from request
            if (isset($_GET['status']) && !empty($_GET['status'])) {
                $query .= " AND q.status = ?";
                $params[] = $_GET['status'];
                $types .= "s";
            }

            if (isset($_GET['user_id']) && !empty($_GET['user_id']) && 
                ($current_user_role === 'admin' || $current_user_role === 'super_admin' || $current_user_role === 'office_staff')) {
                $query .= " AND (q.created_by = ? OR q.assigned_to = ?)";
                $params[] = $_GET['user_id'];
                $params[] = $_GET['user_id'];
                $types .= "ii";
            }

            // Search filter
            if (isset($_GET['search']) && !empty($_GET['search'])) {
                $search = "%" . $_GET['search'] . "%";
                $query .= " AND (q.customer_name LIKE ? OR q.customer_phone LIKE ? OR q.quotation_number LIKE ?)";
                $params[] = $search;
                $params[] = $search;
                $params[] = $search;
                $types .= "sss";
            }

            $query .= " ORDER BY q.created_at DESC";

            $stmt = $conn->prepare($query);
            
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }

            $stmt->execute();
            $result = $stmt->get_result();
            
            $quotations = [];
            while ($row = $result->fetch_assoc()) {
                $quotations[] = $row;
            }
            
            $response['success'] = true;
            $response['data'] = $quotations;
            break;

        case 'update_quotation_status':
            if (!$auth->checkPermission('quotation_management', 'edit')) {
                throw new Exception("You don't have permission to update quotation status");
            }

            $quotation_id = $input['quotation_id'] ?? 0;
            $new_status = $input['new_status'] ?? '';
            $notes = $input['notes'] ?? '';

            if ($quotation_id <= 0) {
                throw new Exception("Invalid quotation ID");
            }

            $allowed_statuses = ['draft', 'sent', 'viewed', 'negotiation', 'accepted', 'rejected'];
            if (!in_array($new_status, $allowed_statuses)) {
                throw new Exception("Invalid status");
            }

            // Get current quotation status
            $current_stmt = $conn->prepare("SELECT status FROM quotations WHERE id = ?");
            $current_stmt->bind_param("i", $quotation_id);
            $current_stmt->execute();
            $current_result = $current_stmt->get_result();

            if ($current_result->num_rows === 0) {
                throw new Exception("Quotation not found");
            }

            $current_quotation = $current_result->fetch_assoc();
            $old_status = $current_quotation['status'];

            // Start transaction
            $conn->begin_transaction();

            try {
                // Update quotation status
                $update_stmt = $conn->prepare("UPDATE quotations SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $update_stmt->bind_param("si", $new_status, $quotation_id);
                
                if (!$update_stmt->execute()) {
                    throw new Exception("Failed to update quotation status");
                }

                // Update timestamps based on status
                if ($new_status === 'sent') {
                    $conn->query("UPDATE quotations SET sent_at = CURRENT_TIMESTAMP WHERE id = $quotation_id");
                } elseif ($new_status === 'accepted') {
                    $conn->query("UPDATE quotations SET accepted_at = CURRENT_TIMESTAMP WHERE id = $quotation_id");
                } elseif ($new_status === 'rejected') {
                    $conn->query("UPDATE quotations SET rejected_at = CURRENT_TIMESTAMP WHERE id = $quotation_id");
                }

                // Record status history
                $changed_by = $_SESSION['user_id'];
                $history_notes = $notes ?: "Status changed from $old_status to $new_status";
                
                $history_stmt = $conn->prepare("
                    INSERT INTO quotation_status_history (quotation_id, old_status, new_status, changed_by, notes)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $history_stmt->bind_param("issis", $quotation_id, $old_status, $new_status, $changed_by, $history_notes);
                $history_stmt->execute();

                $conn->commit();
                $response['success'] = true;
                $response['message'] = "Quotation status updated successfully";
                $response['old_status'] = $old_status;
                $response['new_status'] = $new_status;

            } catch (Exception $e) {
                $conn->rollback();
                throw new Exception("Failed to update quotation status: " . $e->getMessage());
            }
            break;

        case 'transfer_to_customer':
            // Only admin, super_admin, and office_staff can transfer to customers
            $current_user_role = $_SESSION['user_role'];
            if (!in_array($current_user_role, ['admin', 'super_admin', 'office_staff'])) {
                throw new Exception("You don't have permission to transfer quotations to customers");
            }

            $quotation_id = $input['quotation_id'] ?? 0;

            if ($quotation_id <= 0) {
                throw new Exception("Invalid quotation ID");
            }

            // Get quotation details
            $quotation_stmt = $conn->prepare("
                SELECT * FROM quotations 
                WHERE id = ? AND status = 'accepted'
            ");
            $quotation_stmt->bind_param("i", $quotation_id);
            $quotation_stmt->execute();
            $quotation_result = $quotation_stmt->get_result();

            if ($quotation_result->num_rows === 0) {
                throw new Exception("Quotation not found or not accepted");
            }

            $quotation = $quotation_result->fetch_assoc();

            // Check if customer already exists
            $customer_check = $conn->prepare("SELECT id FROM customers WHERE customer_phone = ?");
            $customer_check->bind_param("s", $quotation['customer_phone']);
            $customer_check->execute();
            
            if ($customer_check->get_result()->num_rows > 0) {
                throw new Exception("Customer with this phone number already exists");
            }

            // Start transaction
            $conn->begin_transaction();

            try {
                // Insert into customers table
                $customer_stmt = $conn->prepare("
                    INSERT INTO customers (
                        customer_name, customer_phone, customer_email, customer_address,
                        property_type, meter_type, source_quotation_id, created_by
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");

                $created_by = $_SESSION['user_id'];
                
                $customer_stmt->bind_param(
                    "ssssssii",
                    $quotation['customer_name'],
                    $quotation['customer_phone'],
                    $quotation['customer_email'],
                    $quotation['customer_address'],
                    $quotation['property_type'],
                    $quotation['meter_type'],
                    $quotation_id,
                    $created_by
                );

                if (!$customer_stmt->execute()) {
                    throw new Exception("Failed to create customer record");
                }

                $customer_id = $customer_stmt->insert_id;

                // Update quotation to mark as transferred
                $update_quotation = $conn->prepare("UPDATE quotations SET notes = CONCAT(IFNULL(notes, ''), ' Transferred to customers.') WHERE id = ?");
                $update_quotation->bind_param("i", $quotation_id);
                $update_quotation->execute();

                $conn->commit();
                $response['success'] = true;
                $response['message'] = "Customer transferred successfully";
                $response['customer_id'] = $customer_id;
                $response['customer_name'] = $quotation['customer_name'];

            } catch (Exception $e) {
                $conn->rollback();
                throw new Exception("Failed to transfer to customer: " . $e->getMessage());
            }
            break;

        case 'get_quotation_details':
            if (!$auth->checkPermission('quotation_management', 'view')) {
                throw new Exception("Access denied");
            }

            $quotation_id = $_GET['id'] ?? 0;

            if ($quotation_id <= 0) {
                throw new Exception("Invalid quotation ID");
            }

            // Get quotation details with creator info
            $stmt = $conn->prepare("
                SELECT 
                    q.*,
                    creator.username as creator_name,
                    creator.role as creator_role,
                    assigned.username as assigned_name,
                    assigned.role as assigned_role
                FROM quotations q
                LEFT JOIN users creator ON q.created_by = creator.id
                LEFT JOIN users assigned ON q.assigned_to = assigned.id
                WHERE q.id = ?
            ");

            $stmt->bind_param("i", $quotation_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                throw new Exception("Quotation not found");
            }

            $quotation = $result->fetch_assoc();

            // Get status history
            $history_stmt = $conn->prepare("
                SELECT 
                    h.*,
                    u.username as changed_by_name
                FROM quotation_status_history h
                LEFT JOIN users u ON h.changed_by = u.id
                WHERE h.quotation_id = ?
                ORDER BY h.created_at DESC
            ");
            $history_stmt->bind_param("i", $quotation_id);
            $history_stmt->execute();
            $history_result = $history_stmt->get_result();

            $status_history = [];
            while ($row = $history_result->fetch_assoc()) {
                $status_history[] = $row;
            }

            $response['success'] = true;
            $response['data'] = $quotation;
            $response['status_history'] = $status_history;
            break;

        default:
            throw new Exception("Invalid action");
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log("Quotation API Error: " . $e->getMessage());
}

echo json_encode($response);
?>