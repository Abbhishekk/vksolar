<?php
header('Content-Type: application/json');
require_once '../../connect/db.php';

$connect = new connect();
$db = $connect->dbconnect();

$search = isset($_GET['search']) ? $_GET['search'] : '';

if (!empty($search)) {
    $sql = "SELECT id, name, consumer_number, mobile, email, district, block, taluka, village, location 
            FROM clients 
            WHERE name LIKE ? OR consumer_number LIKE ? OR mobile LIKE ?
            ORDER BY name 
            LIMIT 20";
    
    $stmt = $db->prepare($sql);
    $searchTerm = "%$search%";
    $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $clients = [];
    while($row = $result->fetch_assoc()) {
        $clients[] = $row;
    }
    
    echo json_encode(['success' => true, 'data' => $clients]);
} else {
    echo json_encode(['success' => false, 'message' => 'Search term is required']);
}
?>