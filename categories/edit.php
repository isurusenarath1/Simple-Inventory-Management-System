<?php
/**
 * Edit Category Page
 * Pre-populates and updates existing category rows.
 */
$page_title = "Edit Category";

require_once '../config/database.php';
require_once '../auth/auth_check.php';
check_auth();

// Fetch target ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    $_SESSION['error'] = "Invalid Category ID.";
    header("Location: /inventory-management-system/categories/index.php");
    exit;
}

try {
    // Retrieve target category
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    $category = $stmt->fetch();

    if (!$category) {
        $_SESSION['error'] = "Requested category was not found.";
        header("Location: /inventory-management-system/categories/index.php");
        exit;
    }
} catch (\PDOException $e) {
    error_log("Fetch category failed: " . $e->getMessage());
    $_SESSION['error'] = "Database query failure while loading category.";
    header("Location: /inventory-management-system/categories/index.php");
    exit;
}

$error       = '';
$name        = $category['name'];
$description = $category['description'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $csrf_token  = $_POST['csrf_token'] ?? '';

    // Verify security token
    if (!verify_csrf_token($csrf_token)) {
        $error = "Security validation failed. Please resubmit.";
    } elseif (empty($name)) {
        $error = "Category Name is required.";
    } else {
        try {
            // Check duplicate name excluding current ID
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE name = ? AND id != ?");
            $stmt->execute([$name, $id]);
            if ($stmt->fetchColumn() > 0) {
                $error = "A category named '" . htmlspecialchars($name) . "' already exists.";
            } else {
                // Perform database update
                $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
                $stmt->execute([$name, $description, $id]);

                $_SESSION['success'] = "Category updated successfully.";
                header("Location: /inventory-management-system/categories/index.php");
                exit;
            }
        } catch (\PDOException $e) {
            error_log("Failed to update category: " . $e->getMessage());
            $error = "Could not update category due to database error.";
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
                    <i class="bi bi-pencil-square text-primary me-2"></i>Edit Category
                </h5>
            </div>
            <div class="card-body py-4">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show small py-2 mb-3" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="edit.php?id=<?php echo $id; ?>">
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
                            <i class="bi bi-save me-1"></i> Update Category
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

