<?php
/**
 * Root Router
 * Redirects the user depending on authentication status.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    header("Location: /inventory-management-system/dashboard.php");
} else {
    header("Location: /inventory-management-system/login.php");
}
exit;
?>
