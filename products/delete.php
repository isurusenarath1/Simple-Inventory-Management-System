<?php
/**
 * Delete Product Controller
 * Safely removes a product from the database after security validation.
 */
require_once '../config/database.php';
require_once '../auth/auth_check.php';
check_auth();

$id    = isset($_GET['id']) ? intval($_GET['id']) : 0;
$token = $_GET['csrf_token'] ?? '';

if ($id <= 0) {
    $_SESSION['error'] = "Invalid Product ID.";
    header("Location: /inventory-management-system/products/index.php");
    exit;
}

// Verify URL-based security token to prevent CSRF attacks on GET requests
if (!verify_csrf_token($token)) {
    $_SESSION['error'] = "Security token validation failed. Deletion cancelled.";
    header("Location: /inventory-management-system/products/index.php");
    exit;
}

try {
    // Execute DB deletion
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);

    $_SESSION['success'] = "Product has been successfully deleted from the inventory.";
} catch (\PDOException $e) {
    error_log("Product delete failure: " . $e->getMessage());
    $_SESSION['error'] = "A database error occurred. Could not remove product.";
}

header("Location: /inventory-management-system/products/index.php");
exit;
?>
