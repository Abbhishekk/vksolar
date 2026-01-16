<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../connect/db.php';

$connect = new connect();
$db = $connect->dbconnect();

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        // Read client(s)
        if(isset($_GET['id'])) {
            // Get single client
            $id = intval($_GET['id']);
            $sql = "SELECT * FROM clients WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $client = $result->fetch_assoc();
            
            if($client) {
                echo json_encode(['success' => true, 'data' => $client]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Client not found']);
            }
        } else {
            // Get all clients
            $sql = "SELECT * FROM clients ORDER BY id DESC";
            $result = $db->query($sql);
            $clients = [];
            while($row = $result->fetch_assoc()) {
                $clients[] = $row;
            }
            echo json_encode(['success' => true, 'data' => $clients]);
        }
        break;

    case 'POST':
        // Create client
        $data = json_decode(file_get_contents('php://input'), true);
        
        $sql = "INSERT INTO clients (
            name, consumer_number, billing_unit, mobile, email, email_password, 
            mahadiscom_user_id, mahadiscom_password, application_no_sanction, 
            application_no_load_change, application_no_name_change, kilo_watt, 
            load_change_status, pm_suryaghar_app_id, bank_loan_status, bank_name, 
            bank_application_no, loan_amount, final_loan_amount, bank_loan_1st_installment, 
            bank_loan_2nd_installment, remaining_amount, reference_name, district, 
            block, taluka, village, location, geo_tagging_photo
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $db->prepare($sql);
        $stmt->bind_param(
            "sssssssssssdsssssdddsddssssssss", 
            $data['name'], $data['consumer_number'], $data['billing_unit'], 
            $data['mobile'], $data['email'], $data['email_password'], 
            $data['mahadiscom_user_id'], $data['mahadiscom_password'], 
            $data['application_no_sanction'], $data['application_no_load_change'], 
            $data['application_no_name_change'], $data['kilo_watt'], 
            $data['load_change_status'], $data['pm_suryaghar_app_id'], 
            $data['bank_loan_status'], $data['bank_name'], $data['bank_application_no'], 
            $data['loan_amount'], $data['final_loan_amount'], 
            $data['bank_loan_1st_installment'], $data['bank_loan_2nd_installment'], 
            $data['remaining_amount'], $data['reference_name'], $data['district'], 
            $data['block'], $data['taluka'], $data['village'], $data['location'], 
            $data['geo_tagging_photo']
        );
        
        if($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Client created successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error creating client']);
        }
        break;

    case 'PUT':
        // Update client
        parse_str(file_get_contents("php://input"), $put_vars);
        $data = json_decode(key($put_vars), true);
        
        $sql = "UPDATE clients SET 
            name=?, consumer_number=?, billing_unit=?, mobile=?, email=?, 
            email_password=?, mahadiscom_user_id=?, mahadiscom_password=?, 
            application_no_sanction=?, application_no_load_change=?, 
            application_no_name_change=?, kilo_watt=?, load_change_status=?, 
            pm_suryaghar_app_id=?, bank_loan_status=?, bank_name=?, 
            bank_application_no=?, loan_amount=?, final_loan_amount=?, 
            bank_loan_1st_installment=?, bank_loan_2nd_installment=?, 
            remaining_amount=?, reference_name=?, district=?, block=?, 
            taluka=?, village=?, location=?, geo_tagging_photo=? 
            WHERE id=?";
        
        $stmt = $db->prepare($sql);
        $stmt->bind_param(
            "sssssssssssdsssssdddsddssssssssi", 
            $data['name'], $data['consumer_number'], $data['billing_unit'], 
            $data['mobile'], $data['email'], $data['email_password'], 
            $data['mahadiscom_user_id'], $data['mahadiscom_password'], 
            $data['application_no_sanction'], $data['application_no_load_change'], 
            $data['application_no_name_change'], $data['kilo_watt'], 
            $data['load_change_status'], $data['pm_suryaghar_app_id'], 
            $data['bank_loan_status'], $data['bank_name'], $data['bank_application_no'], 
            $data['loan_amount'], $data['final_loan_amount'], 
            $data['bank_loan_1st_installment'], $data['bank_loan_2nd_installment'], 
            $data['remaining_amount'], $data['reference_name'], $data['district'], 
            $data['block'], $data['taluka'], $data['village'], $data['location'], 
            $data['geo_tagging_photo'], $data['id']
        );
        
        if($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Client updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating client']);
        }
        break;

    case 'DELETE':
        // Delete client
        parse_str(file_get_contents("php://input"), $delete_vars);
        $data = json_decode(key($delete_vars), true);
        $id = intval($data['id']);
        
        $sql = "DELETE FROM clients WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Client deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error deleting client']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}
?>