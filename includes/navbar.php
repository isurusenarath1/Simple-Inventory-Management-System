<?php
/**
 * Global Navbar Template
 * Provides responsive navigation links and active state detection.
 */
$current_script = $_SERVER['SCRIPT_NAME'];

// Define active helper classes
$active_dashboard   = (strpos($current_script, 'dashboard.php') !== false) ? 'active font-weight-bold' : '';
$active_products    = (strpos($current_script, '/products/') !== false && strpos($current_script, '/products/add.php') === false) ? 'active font-weight-bold' : '';
$active_add_product = (strpos($current_script, '/products/add.php') !== false) ? 'active font-weight-bold' : '';
$active_categories  = (strpos($current_script, '/categories/') !== false) ? 'active font-weight-bold' : '';
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom mb-4">
    <div class="container">
        <a class="navbar-brand text-primary" href="/inventory-management-system/dashboard.php">
            <i class="bi bi-box-seam me-2"></i>Inventory System
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo $active_dashboard; ?>" href="/inventory-management-system/dashboard.php">
                        <i class="bi bi-speedometer2 me-1"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $active_products; ?>" href="/inventory-management-system/products/index.php">
                        <i class="bi bi-journal-text me-1"></i> Products
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $active_categories; ?>" href="/inventory-management-system/categories/index.php">
                        <i class="bi bi-tags me-1"></i> Categories
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $active_add_product; ?>" href="/inventory-management-system/products/add.php">
                        <i class="bi bi-plus-circle me-1"></i> Add Product
                    </a>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['full_name'])): ?>
                    <li class="nav-item d-flex align-items-center me-3">
                        <span class="text-muted small">
                            <i class="bi bi-person-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['full_name']); ?> (Admin)
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-sm btn-outline-danger" href="/inventory-management-system/logout.php">
                            <i class="bi bi-box-arrow-right me-1"></i> Logout
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<div class="container mb-5">
