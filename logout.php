<?php
/**
 * Hostel Complaint Management System
 * Logout Processor (logout.php)
 */

session_start();

// Unset all session variables
$_SESSION = [];

// Delete session cookie if active
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy session
session_destroy();

// Redirect back to login page with notice
header("Location: index.php?msg=logged_out");
exit();
?>
