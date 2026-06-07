<?php
$pageTitle = 'Admin Login';
require_once 'includes/admin-header.php';

if (is_admin_logged_in()) {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $stmt = $pdo->prepare('SELECT * FROM admins WHERE username = ?');
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id']   = (int) $admin['id'];
            $_SESSION['admin_name'] = $admin['full_name'];

            // Stamp session timestamps for idle timeout and absolute TTL tracking.
            $_SESSION['session_start_time'] = time();
            $_SESSION['last_activity']      = time();
            $_SESSION['_regen_time']        = time();

            redirect('index.php');
        }
    }
    $loginError = 'Invalid username or password.';
}
?>
<div class="container d-flex align-items-center justify-content-center" style="min-height:100vh;background:var(--cream)">
    <div class="auth-card">
        <h2 class="text-center mb-4" style="color:var(--primary)">Admin Login</h2>
        <?php if (!empty($loginError)): ?>
        <div class="alert alert-danger"><?= e($loginError) ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary-elegance w-100">Login</button>
        </form>
        <p class="text-center"><a href="../index.php">← Back to Store</a></p>
    </div>
</div>
<?php require_once 'includes/admin-footer.php';
