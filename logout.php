<?php
// admin/logout.php
session_start();

/* Unset all session variables */
$_SESSION = [];

/* Destroy the session */
session_destroy();

/* Prevent browser back button cache */
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

/* Optional success message */
session_start();
$_SESSION['success_message'] = "You have been logged out successfully.";

/* Redirect to login page */
header("Location: ../login.php");
exit;
