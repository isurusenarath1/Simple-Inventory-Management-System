<?php
/**
 * Main Administrator Dashboard
 * Displays system metrics and key inventory alerts.
 */
$page_title = "Dashboard";

require_once 'config/database.php';
require_once 'auth/auth_check.php';
check_auth();

// Fetch metrics
try {
    // 1. Total Products
    $total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();

    // 2. Total Stock Quantity
    $total_stock = $pdo->query("SELECT SUM(quantity) FROM products")->fetchColumn();
    $total_stock = $total_stock ? intval($total_stock) : 0;

    // 3. Low Stock Count (Qty > 0 and Qty <= Min)
    $low_stock_count = $pdo->query("SELECT COUNT(*) FROM products WHERE quantity > 0 AND quantity <= minimum_stock")->fetchColumn();

    // 4. Out of Stock Count (Qty == 0)
    $out_of_stock_count = $pdo->query("SELECT COUNT(*) FROM products WHERE quantity = 0")->fetchColumn();

    // 5. Fetch Recent Products (Limit 5)
    $recent_stmt = $pdo->query("
        SELECT p.*, c.name AS category_name 
        FROM products p 
        JOIN categories c ON p.category_id = c.id 
        ORDER BY p.id DESC 
        LIMIT 5
    ");
    $recent_products = $recent_stmt->fetchAll();

    // 6. Fetch Low/Out of Stock Products
    $alert_stmt = $pdo->query("
        SELECT p.*, c.name AS category_name 
        FROM products p 
        JOIN categories c ON p.category_id = c.id 
        WHERE p.quantity <= p.minimum_stock 
        ORDER BY p.quantity ASC
    ");
    $alert_products = $alert_stmt->fetchAll();

} catch (\PDOException $e) {
    error_log("Dashboard query failed: " . $e->getMessage());
    $_SESSION['error'] = "Could not load complete dashboard metrics.";
    $total_products = $total_stock = $low_stock_count = $out_of_stock_count = 0;
    $recent_products = $alert_products = [];
}

// Badge styling helper
function get_stock_badge_data($qty, $min) {
    if ($qty == 0) {
        return ['label' => 'Out of Stock', 'class' => 'bg-danger'];
    } elseif ($qty <= $min) {
        return ['label' => 'Low Stock', 'class' => 'bg-warning text-dark'];
    } else {
        return ['label' => 'In Stock', 'class' => 'bg-success'];
    }
}

require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<div class="mb-4">
    <h2 class="h4 text-dark font-weight-bold">Inventory Management System</h2>
    <p class="text-muted small">Dashboard demonstrating real-time database statistics.</p>
</div>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <!-- Total Products Card -->
    <div class="col-6 col-lg-3">
        <div class="card metric-card p-3 border-light border">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="card-title text-muted mb-1 small text-uppercase">Total Products</h6>
                    <span class="card-value"><?php echo $total_products; ?></span>
                </div>
                <div class="text-primary fs-3">
                    <i class="bi bi-box"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Total Stock Card -->
    <div class="col-6 col-lg-3">
        <div class="card metric-card p-3 border-light border">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="card-title text-muted mb-1 small text-uppercase">Total Stock</h6>
                    <span class="card-value"><?php echo number_format($total_stock); ?></span>
                </div>
                <div class="text-success fs-3">
                    <i class="bi bi-boxes"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Low Stock Card -->
    <div class="col-6 col-lg-3">
        <div class="card metric-card p-3 border-light border">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="card-title text-muted mb-1 small text-uppercase">Low Stock</h6>
                    <span class="card-value <?php echo $low_stock_count > 0 ? 'text-warning' : ''; ?>"><?php echo $low_stock_count; ?></span>
                </div>
                <div class="text-warning fs-3">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Out of Stock Card -->
    <div class="col-6 col-lg-3">
        <div class="card metric-card p-3 border-light border">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="card-title text-muted mb-1 small text-uppercase">Out of Stock</h6>
                    <span class="card-value <?php echo $out_of_stock_count > 0 ? 'text-danger' : ''; ?>"><?php echo $out_of_stock_count; ?></span>
                </div>
                <div class="text-danger fs-3">
                    <i class="bi bi-slash-circle"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Products Table -->
    <div class="col-lg-6">
        <div class="card shadow-sm border-light">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 text-dark font-weight-bold h6">
                    <i class="bi bi-clock-history me-2 text-primary"></i>Recent Products
                </h5>
                <a href="/inventory-management-system/products/index.php" class="text-decoration-none small">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive border-0 p-0">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.9rem;">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th style="text-align: right;">Qty</th>
                                <th style="text-align: center; width: 30%;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recent_products)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">No products created yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recent_products as $prod): 
                                    $badge = get_stock_badge_data($prod['quantity'], $prod['minimum_stock']);
                                ?>
                                    <tr>
                                        <td><strong><?php echo e($prod['product_name']); ?></strong><br><small class="text-muted"><?php echo e($prod['product_code']); ?></small></td>
                                        <td class="text-secondary"><?php echo e($prod['category_name']); ?></td>
                                        <td style="text-align: right;"><?php echo e($prod['quantity']); ?></td>
                                        <td style="text-align: center;">
                                            <span class="badge <?php echo $badge['class']; ?> rounded-pill small py-1 px-2" style="font-size: 0.75rem;">
                                                <?php echo $badge['label']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Alerts Table -->
    <div class="col-lg-6">
        <div class="card shadow-sm border-light">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="card-title mb-0 text-dark font-weight-bold h6">
                    <i class="bi bi-bell me-2 text-danger"></i>Low & Out of Stock Alerts
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive border-0 p-0">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.9rem;">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th style="text-align: right;">Qty</th>
                                <th style="text-align: center; width: 30%;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($alert_products)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-success py-3">
                                        <i class="bi bi-check-circle me-1"></i> All products are well stocked!
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($alert_products as $prod): 
                                    $badge = get_stock_badge_data($prod['quantity'], $prod['minimum_stock']);
                                ?>
                                    <tr>
                                        <td><strong><?php echo e($prod['product_name']); ?></strong><br><small class="text-muted"><?php echo e($prod['product_code']); ?></small></td>
                                        <td class="text-secondary"><?php echo e($prod['category_name']); ?></td>
                                        <td style="text-align: right;" class="text-danger font-weight-bold"><?php echo e($prod['quantity']); ?></td>
                                        <td style="text-align: center;">
                                            <span class="badge <?php echo $badge['class']; ?> rounded-pill small py-1 px-2" style="font-size: 0.75rem;">
                                                <?php echo $badge['label']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
