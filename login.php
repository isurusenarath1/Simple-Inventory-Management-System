<?php
/**
 * Administrator Login Page
 * Handles admin verification using PHP sessions, PDO, and CSRF protection.
 */
$page_title = "Admin Login";

require_once 'config/database.php';
require_once 'auth/auth_check.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: /inventory-management-system/dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username   = trim($_POST['username'] ?? '');
    $password   = trim($_POST['password'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';

    // Validate CSRF token
    if (!verify_csrf_token($csrf_token)) {
        $error = "CSRF security check failed.";
    } elseif (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        try {
            // Retrieve user credentials
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Successful verification, initialize session
                session_regenerate_id(true);
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['username']  = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                
                header("Location: /inventory-management-system/dashboard.php");
                exit;
            } else {
                $error = "Invalid username or password.";
            }
        } catch (\PDOException $e) {
            error_log("Login query failed: " . $e->getMessage());
            $error = "A database error occurred. Please contact the administrator.";
        }
    }
}

// Generate new CSRF token for the login form session
$csrf_token = generate_csrf_token();

// Load header
require_once 'includes/header.php';
?>
<div class="login-container">
    <div class="login-card">
        <div class="text-center mb-4">
            <h4 class="text-primary font-weight-bold">
                <i class="bi bi-box-seam me-1"></i> Inventory System
            </h4>
            <p class="text-muted small">Academic Management Panel</p>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show small py-2" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php" autocomplete="off">
            <!-- CSRF Protection Field -->
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

            <div class="mb-3">
                <label for="username" class="form-label small">Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted small"><i class="bi bi-person"></i></span>
                    <input type="text" class="form-control form-control-sm" id="username" name="username" required placeholder="Enter username">
                </div>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label small">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted small"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control form-control-sm" id="password" name="password" required placeholder="Enter password">
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
                </button>
            </div>
        </form>

        <div class="text-center mt-4">
            <hr class="text-muted opacity-25">
            <span class="text-muted text-xs small">Admins Only</span>
        </div>
    </div>
</div>
<?php
// Load footer which will close the login-container div
require_once 'includes/footer.php';
?>
