<?php
/**
 * Edit Product Page
 * Pre-populates and updates existing product records.
 */
$page_title = "Edit Product";

require_once '../config/database.php';
require_once '../auth/auth_check.php';
check_auth();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    $_SESSION['error'] = "Invalid Product ID.";
    header("Location: /inventory-management-system/products/index.php");
    exit;
}

// Fetch categories for select dropdown
try {
    $cat_stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
    $categories = $cat_stmt->fetchAll();
} catch (\PDOException $e) {
    error_log("Failed to fetch categories: " . $e->getMessage());
    $categories = [];
}

// Fetch product data
try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    if (!$product) {
        $_SESSION['error'] = "Product record not found.";
        header("Location: /inventory-management-system/products/index.php");
        exit;
    }
} catch (\PDOException $e) {
    error_log("Fetch product failed: " . $e->getMessage());
    $_SESSION['error'] = "Database query failure while loading product.";
    header("Location: /inventory-management-system/products/index.php");
    exit;
}

$error         = '';
$product_name  = $product['product_name'];
$product_code  = $product['product_code'];
$category_id   = intval($product['category_id']);
$description   = $product['description'];
$price         = $product['price'];
$quantity      = $product['quantity'];
$minimum_stock = $product['minimum_stock'];
$supplier      = $product['supplier'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name  = trim($_POST['product_name'] ?? '');
    $product_code  = trim($_POST['product_code'] ?? '');
    $category_id   = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
    $description   = trim($_POST['description'] ?? '');
    $price         = trim($_POST['price'] ?? '');
    $quantity      = trim($_POST['quantity'] ?? '');
    $minimum_stock = trim($_POST['minimum_stock'] ?? '');
    $supplier      = trim($_POST['supplier'] ?? '');
    $csrf_token    = $_POST['csrf_token'] ?? '';

    // Validate inputs
    if (!verify_csrf_token($csrf_token)) {
        $error = "Security check failed. Please submit the form again.";
    } elseif (empty($product_name) || empty($product_code) || $category_id <= 0 || $price === '' || $quantity === '' || $minimum_stock === '') {
        $error = "All fields marked with an asterisk (*) are required.";
    } elseif (!is_numeric($price) || floatval($price) < 0) {
        $error = "Price cannot be negative.";
    } elseif (!is_numeric($quantity) || intval($quantity) < 0) {
        $error = "Quantity cannot be negative.";
    } elseif (!is_numeric($minimum_stock) || intval($minimum_stock) < 0) {
        $error = "Minimum stock level cannot be negative.";
    } else {
        try {
            // Check for duplicate product code (excluding current product)
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE product_code = ? AND id != ?");
            $stmt->execute([$product_code, $id]);
            if ($stmt->fetchColumn() > 0) {
                $error = "Product code '" . htmlspecialchars($product_code) . "' is already assigned to another product.";
            } else {
                // Update product table
                $update_stmt = $pdo->prepare("
                    UPDATE products 
                    SET product_code = ?, product_name = ?, category_id = ?, description = ?, price = ?, quantity = ?, minimum_stock = ?, supplier = ?
                    WHERE id = ?
                ");
                $update_stmt->execute([
                    $product_code,
                    $product_name,
                    $category_id,
                    $description ?: null,
                    floatval($price),
                    intval($quantity),
                    intval($minimum_stock),
                    $supplier ?: null,
                    $id
                ]);

                $_SESSION['success'] = "Product '" . htmlspecialchars($product_name) . "' has been updated successfully.";
                header("Location: /inventory-management-system/products/index.php");
                exit;
            }
        } catch (\PDOException $e) {
            error_log("Failed to update product: " . $e->getMessage());
            $error = "A database error occurred. Could not update product.";
        }
    }
}

$csrf_token = generate_csrf_token();

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-7">
        <div class="card shadow-sm border border-light">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="card-title mb-0 text-dark font-weight-bold">
                    <i class="bi bi-pencil-square text-primary me-2"></i>Edit Product
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

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="product_name" class="form-label small">Product Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" id="product_name" name="product_name" 
                                   value="<?php echo e($product_name); ?>" required placeholder="Product Name">
                        </div>
                        <div class="col-md-6">
                            <label for="product_code" class="form-label small">Product Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" id="product_code" name="product_code" 
                                   value="<?php echo e($product_code); ?>" required placeholder="e.g. ELEC-001">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="category_id" class="form-label small">Category <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" id="category_id" name="category_id" required>
                                <option value="" disabled>Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo ($category_id === intval($cat['id'])) ? 'selected' : ''; ?>>
                                        <?php echo e($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="supplier" class="form-label small">Supplier</label>
                            <input type="text" class="form-control form-control-sm" id="supplier" name="supplier" 
                                   value="<?php echo e($supplier); ?>" placeholder="e.g. Logitech Ltd.">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="price" class="form-label small">Price ($) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control form-control-sm" id="price" name="price" 
                                   value="<?php echo e($price); ?>" step="0.01" min="0" required placeholder="0.00">
                        </div>
                        <div class="col-md-4">
                            <label for="quantity" class="form-label small">Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control form-control-sm" id="quantity" name="quantity" 
                                   value="<?php echo e($quantity); ?>" step="1" min="0" required placeholder="0">
                        </div>
                        <div class="col-md-4">
                            <label for="minimum_stock" class="form-label small">Minimum Stock <span class="text-danger">*</span></label>
                            <input type="number" class="form-control form-control-sm" id="minimum_stock" name="minimum_stock" 
                                   value="<?php echo e($minimum_stock); ?>" step="1" min="0" required placeholder="10">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label small">Description</label>
                        <textarea class="form-control form-control-sm" id="description" name="description" 
                                  rows="3" placeholder="Additional product description..."><?php echo e($description); ?></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="bi bi-save me-1"></i> Update Product
                        </button>
                        <a href="/inventory-management-system/products/index.php" class="btn btn-sm btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

