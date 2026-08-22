<?php
/**
 * Categories Management Dashboard
 * Lists categories and provides navigation for CRUD operations.
 */
$page_title = "Manage Categories";

require_once '../config/database.php';
require_once '../auth/auth_check.php';
check_auth();

// Fetch all categories from the database
try {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
    $categories = $stmt->fetchAll();
} catch (\PDOException $e) {
    error_log("Failed to fetch categories: " . $e->getMessage());
    $_SESSION['error'] = "Could not retrieve categories. Please check database connectivity.";
    $categories = [];
}

// Generate security token if not set
$csrf_token = generate_csrf_token();

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 mb-0 text-dark font-weight-bold">
        <i class="bi bi-tags me-2 text-primary"></i>Categories
    </h2>
    <a href="/inventory-management-system/categories/add.php" class="btn btn-sm btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Add Category
    </a>
</div>

<!-- Alert notifications -->
<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show small py-2 mb-3" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i><?php echo htmlspecialchars($_SESSION['success']); ?>
        <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show small py-2 mb-3" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($_SESSION['error']); ?>
        <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<!-- Category Table -->
<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th style="width: 10%;">ID</th>
                <th style="width: 30%;">Category Name</th>
                <th style="width: 45%;">Description</th>
                <th style="width: 15%; text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($categories)): ?>
                <tr>
                    <td colspan="4" class="text-center text-muted py-4">
                        <i class="bi bi-inbox me-1"></i> No categories registered in the database.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td class="text-secondary small"><?php echo e($category['id']); ?></td>
                        <td><strong><?php echo e($category['name']); ?></strong></td>
                        <td class="text-muted small"><?php echo e($category['description'] ?: 'No description provided.'); ?></td>
                        <td style="text-align: right;">
                            <a href="/inventory-management-system/categories/edit.php?id=<?php echo $category['id']; ?>" class="btn btn-xs btn-outline-secondary me-1 py-1 px-2 small" style="font-size: 0.8rem;">
                                <i class="bi bi-pencil-square"></i> Edit
                            </a>
                            <a href="/inventory-management-system/categories/delete.php?id=<?php echo $category['id']; ?>&csrf_token=<?php echo $csrf_token; ?>" 
                               class="btn btn-xs btn-outline-danger confirm-delete py-1 px-2 small" 
                               data-message="Are you sure you want to delete this category? Product constraints may apply."
                               style="font-size: 0.8rem;">
                                <i class="bi bi-trash"></i> Delete
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

