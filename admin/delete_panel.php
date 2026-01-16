<?php
include "../../connect/db.php";
include "../../connect/fun.php";
include 'include/auth_session.php';

$connect = new connect();
$fun = new fun($connect->dbconnect());

$panelId = $_GET['id'] ?? 0;
$clientId = $_GET['client_id'] ?? 0;

if($panelId && $clientId) {
    if($fun->deleteSolarPanel($panelId)) {
        $message = "Panel deleted successfully!";
    } else {
        $message = "Error deleting panel!";
    }
} else {
    $message = "Invalid panel or client ID!";
}

header("Location: edit_client.php?id=" . $clientId . "&msg=" . urlencode($message));
exit();
?>