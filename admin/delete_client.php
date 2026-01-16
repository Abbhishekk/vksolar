<?php
include "../../connect/db.php";
include "../../connect/fun.php";
include 'include/auth_session.php';

// Add CSRF protection
if(!verify_csrf_token()) {
    header("Location: view_clients.php?error=invalid_token");
    exit();
}

$connect = new connect();
$fun = new fun($connect->dbconnect());

$id = $_POST['id'] ?? 0; // Use POST instead of GET for destructive operations

if($id) {
    // Verify client exists and user has permission
    $client = $fun->fetchClientById($id);
    if($client && mysqli_num_rows($client) > 0) {
        $client_data = mysqli_fetch_assoc($client);
        
        // Log the deletion
        log_action($_SESSION['user_id'], "delete_client", $id);
        
        if($fun->deleteClient($id)) {
            $message = "Client deleted successfully!";
            $msg_type = "success";
        } else {
            $message = "Error deleting client!";
            $msg_type = "error";
        }
    } else {
        $message = "Client not found!";
        $msg_type = "error";
    }
} else {
    $message = "Invalid client ID!";
    $msg_type = "error";
}

header("Location: view_clients.php?msg=" . urlencode($message) . "&type=" . $msg_type);
exit();
?>