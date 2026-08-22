<?php
/**
 * View Product Details Page
 * Displays a read-only detailed view of a specific product.
 */
$page_title = "Product Details";

require_once '../config/database.php';
require_once '../auth/auth_check.php';
check_auth();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    $_SESSION['error'] = "Invalid Product ID.";
    header("Location: /inventory-management-system/products/index.php");
    exit;
}

try {
    // Fetch product with category name joined
    $stmt = $pdo->prepare("
        SELECT p.*, c.name AS category_name 
        FROM products p 
        JOIN categories c ON p.category_id = c.id 
        WHERE p.id = ?
    ");
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    if (!$product) {
        $_SESSION['error'] = "Product record was not found.";
        header("Location: /inventory-management-system/products/index.php");
        exit;
    }
} catch (\PDOException $e) {
    error_log("Failed to view product: " . $e->getMessage());
    $_SESSION['error'] = "Database query failure while loading product detail.";
    header("Location: /inventory-management-system/products/index.php");
    exit;
}

// Calculate stock status dynamically
function get_stock_badge($qty, $min) {
    if ($qty == 0) {
        return ['label' => 'Out of Stock', 'class' => 'bg-danger'];
    } elseif ($qty <= $min) {
        return ['label' => 'Low Stock', 'class' => 'bg-warning text-dark'];
    } else {
        return ['label' => 'In Stock', 'class' => 'bg-success'];
    }
}

$badge = get_stock_badge($product['quantity'], $product['minimum_stock']);

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-7">
        <div class="card shadow-sm border border-light">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 text-dark font-weight-bold">
                    <i class="bi bi-info-circle text-primary me-2"></i>Product Details
                </h5>
                <span class="badge <?php echo $badge['class']; ?> rounded-pill px-3 py-1.5 small">
                    <?php echo $badge['label']; ?>
                </span>
            </div>
            
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0">
                    <tbody>
                        <tr>
                            <th class="ps-4 py-3" style="width: 35%;">Product ID</th>
                            <td class="py-3 text-secondary"><?php echo e($product['id']); ?></td>
                        </tr>
                        <tr>
                            <th class="ps-4 py-3">Product Code</th>
                            <td class="py-3"><code class="text-dark font-weight-bold"><?php echo e($product['product_code']); ?></code></td>
                        </tr>
                        <tr>
                            <th class="ps-4 py-3">Product Name</th>
                            <td class="py-3"><strong><?php echo e($product['product_name']); ?></strong></td>
                        </tr>
                        <tr>
                            <th class="ps-4 py-3">Category</th>
                            <td class="py-3 text-secondary"><?php echo e($product['category_name']); ?></td>
                        </tr>
                        <tr>
                            <th class="ps-4 py-3">Price</th>
                            <td class="py-3 text-primary font-weight-bold">$<?php echo number_format($product['price'], 2); ?></td>
                        </tr>
                        <tr>
                            <th class="ps-4 py-3">Current Stock Quantity</th>
                            <td class="py-3 font-weight-bold"><?php echo e($product['quantity']); ?> units</td>
                        </tr>
                        <tr>
                            <th class="ps-4 py-3">Minimum Stock Threshold</th>
                            <td class="py-3 text-secondary"><?php echo e($product['minimum_stock']); ?> units</td>
                        </tr>
                        <tr>
                            <th class="ps-4 py-3">Supplier Name</th>
                            <td class="py-3 text-secondary"><?php echo e($product['supplier'] ?: 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <th class="ps-4 py-3">Description</th>
                            <td class="py-3 text-muted small"><?php echo nl2br(e($product['description'] ?: 'No description provided.')); ?></td>
                        </tr>
                        <tr>
                            <th class="ps-4 py-3">Date Added</th>
                            <td class="py-3 text-secondary small"><?php echo date("F d, Y h:i A", strtotime($product['created_at'])); ?></td>
                        </tr>
                        <tr>
                            <th class="ps-4 py-3">Last Updated</th>
                            <td class="py-3 text-secondary small"><?php echo date("F d, Y h:i A", strtotime($product['updated_at'])); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="card-footer bg-white border-top py-3 d-flex justify-content-between">
                <a href="/inventory-management-system/products/index.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Products
                </a>
                <a href="/inventory-management-system/products/edit.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-primary">
                    <i class="bi bi-pencil-square me-1"></i> Edit Product
                </a>
            </div>
        </div>
    </div>
</div>

