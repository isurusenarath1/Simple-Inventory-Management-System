<?php
/**
 * Authentication and Security Helper Functions
 * Manages sessions, authorization checks, CSRF protection, and input sanitization.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if the user is authenticated.
 * Redirects to the login page if session user_id is missing.
 */
function check_auth() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /inventory-management-system/login.php");
        exit;
    }
}

/**
 * Generate CSRF token for forms to prevent Cross-Site Request Forgery.
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify if the submitted CSRF token matches the session token.
 */
function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Shorthand helper for escaping HTML output to prevent XSS.
 */
function e($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}
?>
