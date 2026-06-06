<?php
$pageTitle = 'Register';
require_once 'includes/header.php';

if (is_logged_in()) {
    redirect('index.php');
}
?>

<div class="container">
    <div class="auth-card" style="max-width: 560px;">
        <h2 class="text-center mb-4" style="color: var(--primary);">Create Account</h2>
        <form action="actions/register-process.php" method="post" id="register-form" novalidate>
            <div class="mb-3">
                <label class="form-label">Full Name *</label>
                <input type="text" name="full_name" class="form-control" required minlength="2">
                <div class="invalid-feedback"></div>
            </div>
            <div class="mb-3">
                <label class="form-label">Email *</label>
                <input type="email" name="email" class="form-control" required data-validate="email">
                <div class="invalid-feedback"></div>
            </div>
            <div class="mb-3">
                <label class="form-label">Phone *</label>
                <input type="tel" name="phone" class="form-control" required data-validate="phone" placeholder="10 digit mobile">
                <div class="invalid-feedback"></div>
            </div>
            <div class="mb-3">
                <label class="form-label">Password *</label>
                <input type="password" name="password" id="reg-password" class="form-control" required data-validate="password">
                <div class="password-strength"><div class="password-strength-bar" id="pwd-strength-bar"></div></div>
                <small class="text-muted">Min 8 chars, uppercase, lowercase, and number</small>
                <div class="invalid-feedback"></div>
            </div>
            <div class="mb-3">
                <label class="form-label">Confirm Password *</label>
                <input type="password" name="confirm_password" class="form-control" required data-validate="confirm" data-match="#reg-password">
                <div class="invalid-feedback"></div>
            </div>
            <div class="row g-2">
                <div class="col-md-6 mb-3">
                    <label class="form-label">City</label>
                    <input type="text" name="city" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">District</label>
                    <input type="text" name="district" class="form-control">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Address</label>
                <textarea name="address" class="form-control" rows="2"></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Pincode</label>
                <input type="text" name="pincode" class="form-control" pattern="[0-9]{6}" maxlength="6">
            </div>
            <button type="submit" class="btn btn-primary-elegance w-100">Register</button>
        </form>
        <p class="text-center mt-3 mb-0">Already have an account? <a href="login.php">Login</a></p>
    </div>
</div>

<?php
$extraScripts = ['js/validation.js'];
require_once 'includes/footer.php';
