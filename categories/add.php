<?php
/**
 * Add Category Page
 * Handles form rendering and processing for creating a new product category.
 */
$page_title = "Add Category";

require_once '../config/database.php';
require_once '../auth/auth_check.php';
check_auth();

$error = '';
$name = '';
$description = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $csrf_token  = $_POST['csrf_token'] ?? '';

    // Validate security token
    if (!verify_csrf_token($csrf_token)) {
        $error = "Security token mismatch. Please resubmit the form.";
    } elseif (empty($name)) {
        $error = "Category Name is required.";
    } else {
        try {
            // Check for duplicate category name
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE name = ?");
            $stmt->execute([$name]);
            if ($stmt->fetchColumn() > 0) {
                $error = "A category named '" . htmlspecialchars($name) . "' already exists.";
            } else {
                // Insert category
                $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
                $stmt->execute([$name, $description]);

                $_SESSION['success'] = "Category added successfully.";
                header("Location: /inventory-management-system/categories/index.php");
                exit;
            }
        } catch (\PDOException $e) {
            error_log("Failed to add category: " . $e->getMessage());
            $error = "A database error occurred. Could not add category.";
        }
    }
}

$csrf_token = generate_csrf_token();

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm border border-light">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="card-title mb-0 text-dark font-weight-bold">
                    <i class="bi bi-tag-fill text-primary me-2"></i>Add New Category
                </h5>
            </div>
            <div class="card-body py-4">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show small py-2 mb-3" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="add.php">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                    <div class="mb-3">
                        <label for="name" class="form-label small">Category Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="name" name="name" 
                               value="<?php echo e($name); ?>" required placeholder="e.g. Office Supplies">
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label small">Description</label>
                        <textarea class="form-control form-control-sm" id="description" name="description" 
                                  rows="3" placeholder="Brief details about products in this category"><?php echo e($description); ?></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Save Category
                        </button>
                        <a href="/inventory-management-system/categories/index.php" class="btn btn-sm btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

