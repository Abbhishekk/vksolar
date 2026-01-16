<?php

class fun
{
    private $db;
    function __construct($con)
    {
        $this->db = $con;

    }
    public function login($username,$password){
        
        $query    = "SELECT * FROM `user` WHERE `userid`='$username' AND `pass` = '$password'";
        $result = mysqli_query($this->db, $query);
        
        $rows = mysqli_num_rows($result);
        if ($rows == 1) {
           $fetch = mysqli_fetch_assoc($result);
           return $fetch;
        }
        else{
            return null;
        }
    }
   
   public function fetchAllEnquiries(){
    $sql = "SELECT * FROM `enquiry` ORDER BY `id` DESC";
    $fetch = mysqli_query($this->db, $sql);
     return $fetch;
   }
   
   public function fetchEnquiriesResidential(){
    $sql = "SELECT * FROM `enquiry` WHERE `type` = 'Residential' ORDER BY `id` DESC";
    $fetch = mysqli_query($this->db, $sql);
     return $fetch;
   }
   
   public function fetchEnquiriesCommercial(){
    $sql = "SELECT * FROM `enquiry` WHERE `type` = 'Commercial' ORDER BY `id` DESC";
    $fetch = mysqli_query($this->db, $sql);
     return $fetch;
   }
   
   public function fetchEnquiriesCleaning(){
    $sql = "SELECT * FROM `enquiry` WHERE `type` = 'Cleaning service' ORDER BY `id` DESC";
    $fetch = mysqli_query($this->db, $sql);
     return $fetch;
   }
   
   public function fetchActiveEnquiries(){
    $sql = "SELECT * FROM `enquiry` WHERE `status` = '0' ORDER BY `id` DESC";
    $fetch = mysqli_query($this->db, $sql);
     return $fetch;
   }

   public function updateEnquiry($id){
    $sql = "UPDATE `enquiry` SET `status` = '1' WHERE `id` = '$id'";
    $fetch = mysqli_query($this->db, $sql);
     return $fetch;
   }

    public function deleteEnquiry($id){
     $sql = "DELETE FROM `enquiry` WHERE `id` = '$id'";
     $fetch = mysqli_query($this->db, $sql);
      return $fetch;
    }
    
    // Client Management Functions
    
    public function fetchClients() {
        $sql = "SELECT * FROM clients ORDER BY id DESC";
        return mysqli_query($this->db, $sql);
    }

    public function fetchClientById($id) {
        $sql = "SELECT * FROM clients WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result();
    }

 public function addClient($data) {
    $fields = [];
    $placeholders = [];
    $values = [];
    $types = '';
    
    // Build dynamic INSERT query based on provided data
    foreach($data as $field => $value) {
        $fields[] = "`$field`";
        $placeholders[] = '?';
        $values[] = $value;
        $types .= 's'; // assuming all are strings for now
    }
    
    if(empty($fields)) {
        return false;
    }
    
    $sql = "INSERT INTO clients (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
    
    $stmt = $this->db->prepare($sql);
    if (!$stmt) {
        return false;
    }
    
    $stmt->bind_param($types, ...$values);
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

    public function updateClient($id, $data) {
        if (empty($data)) {
            return false;
        }
        
        $setParts = [];
        $params = [];
        $types = '';
        
        // Build dynamic SET clause only for provided fields
        foreach ($data as $field => $value) {
            $setParts[] = "`$field` = ?";
            $params[] = $value;
            $types .= 's'; // assuming all are strings for now
        }
        
        $params[] = $id;
        $types .= 'i'; // id is integer
        
        $sql = "UPDATE clients SET " . implode(', ', $setParts) . " WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return false;
        }
        
        $stmt->bind_param($types, ...$params);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }

    public function deleteClient($id) {
        $sql = "DELETE FROM clients WHERE id = '$id'";
        return mysqli_query($this->db, $sql);
    }

    // Export to Excel function
    public function exportClientsToExcel() {
        $sql = "SELECT * FROM clients ORDER BY id DESC";
        $result = mysqli_query($this->db, $sql);
        
        $clients = [];
        while($row = mysqli_fetch_assoc($result)) {
            $clients[] = $row;
        }
        
        return $clients;
    }
    
    // Panel Management Functions
    public function addSolarPanels($clientId, $panels) {
        foreach($panels as $panel) {
            $sql = "INSERT INTO solar_panels (
                client_id, panel_number, serial_number, wattage, 
                manufacturer, photo_path, installation_date
            ) VALUES (
                '$clientId', '{$panel['panel_number']}', '{$panel['serial_number']}', 
                '{$panel['wattage']}', '{$panel['manufacturer']}', 
                '{$panel['photo_path']}', '{$panel['installation_date']}'
            )";
            
            if(!mysqli_query($this->db, $sql)) {
                return false;
            }
        }
        return true;
    }

    public function getClientPanels($clientId) {
        $sql = "SELECT * FROM solar_panels WHERE client_id = '$clientId' ORDER BY panel_number";
        return mysqli_query($this->db, $sql);
    }

    public function updateSolarPanel($panelId, $data) {
        $sql = "UPDATE solar_panels SET 
                serial_number = '{$data['serial_number']}',
                wattage = '{$data['wattage']}',
                manufacturer = '{$data['manufacturer']}',
                photo_path = '{$data['photo_path']}',
                installation_date = '{$data['installation_date']}'
                WHERE id = '$panelId'";
        
        return mysqli_query($this->db, $sql);
    }

    public function deleteSolarPanel($panelId) {
        $sql = "DELETE FROM solar_panels WHERE id = '$panelId'";
        return mysqli_query($this->db, $sql);
    }

    public function deleteAllClientPanels($clientId) {
        $sql = "DELETE FROM solar_panels WHERE client_id = '$clientId'";
        return mysqli_query($this->db, $sql);
    }

    public function getNextPanelNumber($clientId) {
        $sql = "SELECT MAX(panel_number) as max_number FROM solar_panels WHERE client_id = '$clientId'";
        $result = mysqli_query($this->db, $sql);
        $row = mysqli_fetch_assoc($result);
        return $row['max_number'] ? $row['max_number'] + 1 : 1;
    }
    
    // Dashboard Statistics Functions
    public function fetchRecentClients($days = 7) {
        $sql = "SELECT * FROM clients WHERE created_at >= DATE_SUB(NOW(), INTERVAL $days DAY) ORDER BY created_at DESC";
        return mysqli_query($this->db, $sql);
    }

    public function getPanelStatistics() {
        $stats = [
            'total_panels' => 0,
            'total_capacity' => 0,
            'avg_system_size' => 0,
            'dcr_count' => 0,
            'non_dcr_count' => 0,
            'loan_clients' => 0,
            'completed_installations' => 0
        ];

        // Total panels and capacity
        $sql = "SELECT COUNT(*) as total_panels, SUM(wattage) as total_capacity FROM solar_panels";
        $result = mysqli_query($this->db, $sql);
        $row = mysqli_fetch_assoc($result);
        $stats['total_panels'] = $row['total_panels'] ?? 0;
        $stats['total_capacity'] = $row['total_capacity'] ?? 0;

        // Average system size
        $sql = "SELECT AVG(system_size) as avg_size FROM (
            SELECT client_id, SUM(wattage) as system_size 
            FROM solar_panels 
            GROUP BY client_id
        ) as systems";
        $result = mysqli_query($this->db, $sql);
        $row = mysqli_fetch_assoc($result);
        $stats['avg_system_size'] = $row['avg_size'] ? round($row['avg_size'] / 1000, 1) : 0;

        // Solar type distribution
        $sql = "SELECT solar_type, COUNT(*) as count FROM clients GROUP BY solar_type";
        $result = mysqli_query($this->db, $sql);
        while($row = mysqli_fetch_assoc($result)) {
            if($row['solar_type'] == 'DCR') {
                $stats['dcr_count'] = $row['count'];
            } else {
                $stats['non_dcr_count'] = $row['count'];
            }
        }

        // Loan clients
        $sql = "SELECT COUNT(*) as count FROM clients WHERE bank_loan_status = 'yes'";
        $result = mysqli_query($this->db, $sql);
        $row = mysqli_fetch_assoc($result);
        $stats['loan_clients'] = $row['count'] ?? 0;

        // Completed installations (clients with panels)
        $sql = "SELECT COUNT(DISTINCT client_id) as count FROM solar_panels";
        $result = mysqli_query($this->db, $sql);
        $row = mysqli_fetch_assoc($result);
        $stats['completed_installations'] = $row['count'] ?? 0;

        return $stats;
    }

    public function getCapacityStatistics() {
        $stats = [
            'total_estimate' => 0,
            'avg_estimate' => 0,
            'max_estimate' => 0,
            'min_estimate' => 0
        ];

        $sql = "SELECT 
                SUM(estimate_amount) as total_estimate,
                AVG(estimate_amount) as avg_estimate,
                MAX(estimate_amount) as max_estimate,
                MIN(estimate_amount) as min_estimate
                FROM clients 
                WHERE estimate_amount > 0";
        
        $result = mysqli_query($this->db, $sql);
        $row = mysqli_fetch_assoc($result);
        
        $stats['total_estimate'] = $row['total_estimate'] ?? 0;
        $stats['avg_estimate'] = $row['avg_estimate'] ? round($row['avg_estimate']) : 0;
        $stats['max_estimate'] = $row['max_estimate'] ?? 0;
        $stats['min_estimate'] = $row['min_estimate'] ?? 0;

        return $stats;
    }

    public function getRecentActivities($limit = 10) {
        $sql = "SELECT 
                c.name as client_name,
                'New Installation' as activity_type,
                c.created_at
                FROM clients c
                ORDER BY c.created_at DESC
                LIMIT $limit";
        
        return mysqli_query($this->db, $sql);
    }

    public function getMonthlyInstallations() {
        $sql = "SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                COUNT(*) as installations,
                SUM(estimate_amount) as revenue
                FROM clients 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY month DESC
                LIMIT 12";
        
        return mysqli_query($this->db, $sql);
    }
    // Add these functions to your existing fun.php class

public function getCompleteClients() {
    $sql = "SELECT * FROM clients WHERE 
            name IS NOT NULL AND name != '' AND
            consumer_number IS NOT NULL AND consumer_number != '' AND
            mobile IS NOT NULL AND mobile != '' AND
            email IS NOT NULL AND email != '' AND
            district IS NOT NULL AND district != '' AND
            mahadiscom_email IS NOT NULL AND mahadiscom_email != '' AND
            mahadiscom_user_id IS NOT NULL AND mahadiscom_user_id != '' AND
            load_change_application_number IS NOT NULL AND load_change_application_number != '' AND
            inverter_company_name IS NOT NULL AND inverter_company_name != '' AND
            dcr_certificate_number IS NOT NULL AND dcr_certificate_number != '' AND
            meter_number IS NOT NULL AND meter_number != ''";
    
    return $this->db->query($sql);
}

public function getIncompleteClients() {
    $sql = "SELECT * FROM clients WHERE 
            name IS NULL OR name = '' OR
            consumer_number IS NULL OR consumer_number = '' OR
            mobile IS NULL OR mobile = '' OR
            email IS NULL OR email = '' OR
            district IS NULL OR district = '' OR
            mahadiscom_email IS NULL OR mahadiscom_email = '' OR
            mahadiscom_user_id IS NULL OR mahadiscom_user_id = '' OR
            load_change_application_number IS NULL OR load_change_application_number = '' OR
            inverter_company_name IS NULL OR inverter_company_name = '' OR
            dcr_certificate_number IS NULL OR dcr_certificate_number = '' OR
            meter_number IS NULL OR meter_number = ''";
    
    return $this->db->query($sql);
}
 
}

// quotation functions 
class quote {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    // Dashboard quotation Status count function
    public function getQuotationStats() {
    $sql = "SELECT 
                COUNT(*) AS Total,
                SUM(status = 'approved') AS Accepted,
                SUM(status = 'under_review') AS Pending,
                SUM(status = 'sent') AS Awaiting,
                SUM(status = 'declined') AS Declined
            FROM solar_rooftop_quotations";

    $result = $this->conn->query($sql);

    if (!$result) {
        return [
            'total'    => 0,
            'accepted' => 0,
            'pending'  => 0,
            'awaiting' => 0,
            'declined' => 0
        ];
    }

    $r = $result->fetch_assoc();

    return [
        'total'    => $r['Total'] ?? 0,
        'accepted' => $r['Accepted'] ?? 0,
        'pending'  => $r['Pending'] ?? 0,
        'awaiting' => $r['Awaiting'] ?? 0,
        'declined' => $r['Declined'] ?? 0
    ];
}

}

?>