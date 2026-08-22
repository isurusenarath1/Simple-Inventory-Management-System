<?php
/**
 * Logout script
 * Destroys session variables and redirect to login page.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Unset all session variables
$_SESSION = [];

// Destroy session cookies
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// Destroy session state
session_destroy();

// Redirect to admin login
header("Location: /inventory-management-system/login.php");
exit;
?>
