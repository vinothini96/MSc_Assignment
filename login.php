<?php
$pageTitle = 'Login';
require_once 'includes/header.php';

if (is_logged_in()) {
    redirect('/EleganceSarees/index.php');
}

$redirect = $_GET['redirect'] ?? '/EleganceSarees/index.php';
?>

<div class="container">
    <div class="auth-card">
        <h2 class="text-center mb-4" style="color: var(--primary);">
            Welcome Back
        </h2>

        <form action="/EleganceSarees/actions/login-process.php" 
              method="post" 
              id="login-form" 
              novalidate>

            <input type="hidden" 
                   name="redirect" 
                   value="<?= e($redirect) ?>">

            <div class="mb-3">
                <label class="form-label">Email</label>

                <input type="email"
                       name="email"
                       class="form-control"
                       required
                       data-validate="email">

                <div class="invalid-feedback"></div>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>

                <input type="password"
                       name="password"
                       class="form-control"
                       required
                       minlength="1">

                <div class="invalid-feedback"></div>
            </div>

            <button type="submit" class="btn btn-primary-elegance w-100">
                Login
            </button>
        </form>

        <p class="text-center mt-3 mb-0">
            Don't have an account?
            <a href="/EleganceSarees/register.php">Register</a>
        </p>
    </div>
</div>

<?php
$extraScripts = ['js/validation.js'];
require_once 'includes/footer.php';
?>