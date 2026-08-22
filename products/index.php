<?php
/**
 * Products Catalog Dashboard
 * Displays products with active search filters, category selectors, and stock statuses.
 */
$page_title = "Manage Products";

require_once '../config/database.php';
require_once '../auth/auth_check.php';
check_auth();

// Fetch Categories for filter dropdown
try {
    $cat_stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
    $categories = $cat_stmt->fetchAll();
} catch (\PDOException $e) {
    error_log("Failed to load categories in products filter: " . $e->getMessage());
    $categories = [];
}

// Get filter inputs
$search      = trim($_GET['search'] ?? '');
$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
$stock_state = trim($_GET['status'] ?? '');

// Build dynamic PDO query
$sql = "SELECT p.*, c.name AS category_name 
        FROM products p 
        JOIN categories c ON p.category_id = c.id 
        WHERE 1=1";
$params = [];

if ($search !== '') {
    $sql .= " AND (p.product_name LIKE :search OR p.product_code LIKE :search)";
    $params['search'] = '%' . $search . '%';
}

if ($category_id > 0) {
    $sql .= " AND p.category_id = :category_id";
    $params['category_id'] = $category_id;
}

if ($stock_state !== '') {
    if ($stock_state === 'in_stock') {
        $sql .= " AND p.quantity > p.minimum_stock";
    } elseif ($stock_state === 'low_stock') {
        $sql .= " AND p.quantity > 0 AND p.quantity <= p.minimum_stock";
    } elseif ($stock_state === 'out_of_stock') {
        $sql .= " AND p.quantity = 0";
    }
}

$sql .= " ORDER BY p.id DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
} catch (\PDOException $e) {
    error_log("Failed to retrieve products: " . $e->getMessage());
    $_SESSION['error'] = "Failed to load product listing.";
    $products = [];
}

// Dynamic stock helper function
function resolve_stock_badge($qty, $min) {
    if ($qty == 0) {
        return ['label' => 'Out of Stock', 'class' => 'bg-danger'];
    } elseif ($qty <= $min) {
        return ['label' => 'Low Stock', 'class' => 'bg-warning text-dark'];
    } else {
        return ['label' => 'In Stock', 'class' => 'bg-success'];
    }
}

$csrf_token = generate_csrf_token();

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 mb-0 text-dark font-weight-bold">
        <i class="bi bi-box me-2 text-primary"></i>Products
    </h2>
    <a href="/inventory-management-system/products/add.php" class="btn btn-sm btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Add Product
    </a>
</div>

<!-- Notifications -->
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

<!-- Search and Filter Panel -->
<div class="card mb-4 bg-white border border-light shadow-sm">
    <div class="card-body py-3">
        <form method="GET" action="index.php" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label for="search" class="form-label small text-muted">Search Product</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><i class="bi bi-search small text-muted"></i></span>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="<?php echo e($search); ?>" placeholder="Name or Code...">
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <label for="category_id" class="form-label small text-muted">Category</label>
                <select class="form-select form-select-sm" id="category_id" name="category_id">
                    <option value="0">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo ($category_id === $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo e($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3 col-sm-6">
                <label for="status" class="form-label small text-muted">Stock Status</label>
                <select class="form-select form-select-sm" id="status" name="status">
                    <option value="">All Statuses</option>
                    <option value="in_stock" <?php echo ($stock_state === 'in_stock') ? 'selected' : ''; ?>>In Stock</option>
                    <option value="low_stock" <?php echo ($stock_state === 'low_stock') ? 'selected' : ''; ?>>Low Stock</option>
                    <option value="out_of_stock" <?php echo ($stock_state === 'out_of_stock') ? 'selected' : ''; ?>>Out of Stock</option>
                </select>
            </div>

            <div class="col-md-2 d-grid gap-2">
                <div class="d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-primary flex-grow-1">
                        <i class="bi bi-filter me-1"></i> Filter
                    </button>
                    <a href="index.php" class="btn btn-sm btn-outline-secondary" title="Reset Filters">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Products Table -->
<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th style="width: 5%;">ID</th>
                <th style="width: 15%;">Product Code</th>
                <th style="width: 25%;">Product Name</th>
                <th style="width: 15%;">Category</th>
                <th style="width: 10%; text-align: right;">Price</th>
                <th style="width: 10%; text-align: right;">Qty</th>
                <th style="width: 10%; text-align: center;">Status</th>
                <th style="width: 10%; text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($products)): ?>
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        <i class="bi bi-box me-1"></i> No matching products found.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($products as $prod): 
                    $badge = resolve_stock_badge($prod['quantity'], $prod['minimum_stock']);
                ?>
                    <tr>
                        <td class="text-secondary small"><?php echo e($prod['id']); ?></td>
                        <td><code class="text-dark font-weight-bold"><?php echo e($prod['product_code']); ?></code></td>
                        <td><strong><?php echo e($prod['product_name']); ?></strong></td>
                        <td class="small text-muted"><?php echo e($prod['category_name']); ?></td>
                        <td style="text-align: right;">$<?php echo number_format($prod['price'], 2); ?></td>
                        <td style="text-align: right;"><?php echo e($prod['quantity']); ?></td>
                        <td style="text-align: center;">
                            <span class="badge <?php echo $badge['class']; ?> rounded-pill small px-2 py-1">
                                <?php echo $badge['label']; ?>
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <div class="btn-group btn-group-xs" role="group">
                                <a href="/inventory-management-system/products/view.php?id=<?php echo $prod['id']; ?>" 
                                   class="btn btn-sm btn-outline-secondary py-0 px-2" title="View Details" style="font-size: 0.75rem;">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="/inventory-management-system/products/edit.php?id=<?php echo $prod['id']; ?>" 
                                   class="btn btn-sm btn-outline-secondary py-0 px-2" title="Edit" style="font-size: 0.75rem;">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="/inventory-management-system/products/delete.php?id=<?php echo $prod['id']; ?>&csrf_token=<?php echo $csrf_token; ?>" 
                                   class="btn btn-sm btn-outline-danger confirm-delete py-0 px-2" 
                                   data-message="Are you sure you want to delete this product?"
                                   title="Delete" style="font-size: 0.75rem;">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>


