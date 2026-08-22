<?php
/**
 * Delete Category Controller
 * Handles removal of categories with security validation and foreign key constraint handling.
 */
require_once '../config/database.php';
require_once '../auth/auth_check.php';
check_auth();

$id    = isset($_GET['id']) ? intval($_GET['id']) : 0;
$token = $_GET['csrf_token'] ?? '';

if ($id <= 0) {
    $_SESSION['error'] = "Invalid Category ID.";
    header("Location: /inventory-management-system/categories/index.php");
    exit;
}

// Verify URL-based CSRF token to prevent accidental execution
if (!verify_csrf_token($token)) {
    $_SESSION['error'] = "Security token mismatch. Action declined.";
    header("Location: /inventory-management-system/categories/index.php");
    exit;
}

try {
    // Attempt SQL delete query
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$id]);

    $_SESSION['success'] = "Category deleted successfully.";
} catch (\PDOException $e) {
    error_log("Category delete failure: " . $e->getMessage());
    
    // Catch standard foreign key integrity violation (SQLSTATE code '23000')
    if ($e->getCode() == 23000) {
        $_SESSION['error'] = "Cannot delete category because it contains active products. Please reassign or delete the products first.";
    } else {
        $_SESSION['error'] = "A database error occurred. Could not delete the category.";
    }
}

header("Location: /inventory-management-system/categories/index.php");
exit;
?>
