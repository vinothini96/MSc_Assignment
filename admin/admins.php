<?php
/**
 * Admin Account Management
 *
 * Allows the currently logged-in admin to:
 *   - View all admin accounts
 *   - Create a new admin account (with bcrypt-hashed password)
 *   - Edit an existing admin's name, email, and username
 *   - Change an admin's password
 *   - Delete an admin account (cannot delete own account)
 *
 * Security notes:
 *   - Passwords are hashed with password_hash(PASSWORD_DEFAULT) — bcrypt
 *   - An admin cannot delete their own account (prevents lockout)
 *   - Username and email uniqueness enforced at DB and application level
 *   - All output escaped with e() to prevent XSS
 *   - All queries use PDO prepared statements to prevent SQL injection
 */

$pageTitle = 'Admin Accounts';
require_once 'includes/admin-header.php';

$currentAdminId = (int) $_SESSION['admin_id'];

// ── DELETE ────────────────────────────────────────────────────────────────────
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $deleteId = (int) $_GET['delete'];

    // Safety: an admin cannot delete their own account
    if ($deleteId === $currentAdminId) {
        flash('danger', 'You cannot delete your own account.');
        redirect('admins.php');
    }

    // Safety: do not delete the last remaining admin (prevents full lockout)
    $adminCount = (int) $pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn();
    if ($adminCount <= 1) {
        flash('danger', 'Cannot delete the last admin account.');
        redirect('admins.php');
    }

    $pdo->prepare('DELETE FROM admins WHERE id = ?')->execute([$deleteId]);
    flash('success', 'Admin account deleted.');
    redirect('admins.php');
}

// ── CREATE (POST with action=create) ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $username  = trim($_POST['username'] ?? '');
    $fullName  = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm_password'] ?? '';

    $errors = [];

    // ── Validation ────────────────────────────────────────────────────────────
    if (strlen($username) < 3 || !preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = 'Username must be at least 3 characters and contain only letters, numbers, and underscores.';
    }
    if (strlen($fullName) < 2) {
        $errors[] = 'Full name must be at least 2 characters.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (strlen($password) < 8
        || !preg_match('/[A-Z]/', $password)
        || !preg_match('/[a-z]/', $password)
        || !preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must be at least 8 characters and include uppercase, lowercase, and a number.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    // ── Uniqueness checks ─────────────────────────────────────────────────────
    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id FROM admins WHERE username = ?');
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $errors[] = 'Username is already taken.';
        }

        $stmt = $pdo->prepare('SELECT id FROM admins WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Email address is already registered.';
        }
    }

    if ($errors) {
        flash('danger', implode(' ', $errors));
        // Preserve form data so the user does not retype everything
        $_SESSION['form_data'] = compact('username', 'fullName', 'email');
        redirect('admins.php?action=create');
    }

    // ── Hash password and insert ──────────────────────────────────────────────
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $pdo->prepare(
        'INSERT INTO admins (username, email, password, full_name) VALUES (?, ?, ?, ?)'
    )->execute([$username, $email, $hash, $fullName]);

    flash('success', 'Admin account created successfully.');
    redirect('admins.php');
}

// ── EDIT (POST with action=edit) ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'edit') {
    $editId   = (int) ($_POST['admin_id'] ?? 0);
    $username = trim($_POST['username'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $email    = trim($_POST['email'] ?? '');

    $errors = [];

    if (strlen($username) < 3 || !preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = 'Username must be at least 3 characters (letters, numbers, underscores only).';
    }
    if (strlen($fullName) < 2) {
        $errors[] = 'Full name must be at least 2 characters.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    // Uniqueness: exclude the current record being edited
    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id FROM admins WHERE username = ? AND id != ?');
        $stmt->execute([$username, $editId]);
        if ($stmt->fetch()) {
            $errors[] = 'Username is already taken by another admin.';
        }

        $stmt = $pdo->prepare('SELECT id FROM admins WHERE email = ? AND id != ?');
        $stmt->execute([$email, $editId]);
        if ($stmt->fetch()) {
            $errors[] = 'Email is already used by another admin.';
        }
    }

    if ($errors) {
        flash('danger', implode(' ', $errors));
        redirect('admins.php?edit=' . $editId);
    }

    $pdo->prepare('UPDATE admins SET username=?, full_name=?, email=? WHERE id=?')
        ->execute([$username, $fullName, $email, $editId]);

    // Update session display name if the admin edited their own account
    if ($editId === $currentAdminId) {
        $_SESSION['admin_name'] = $fullName;
    }

    flash('success', 'Admin account updated.');
    redirect('admins.php');
}

// ── CHANGE PASSWORD (POST with action=change_password) ───────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'change_password') {
    $targetId    = (int) ($_POST['admin_id'] ?? 0);
    $newPassword = $_POST['new_password'] ?? '';
    $confirm     = $_POST['confirm_password'] ?? '';

    $errors = [];

    // If changing own password, require current password verification
    if ($targetId === $currentAdminId) {
        $currentPassword = $_POST['current_password'] ?? '';
        $stmt = $pdo->prepare('SELECT password FROM admins WHERE id = ?');
        $stmt->execute([$currentAdminId]);
        $row = $stmt->fetch();
        if (!$row || !password_verify($currentPassword, $row['password'])) {
            $errors[] = 'Current password is incorrect.';
        }
    }

    if (strlen($newPassword) < 8
        || !preg_match('/[A-Z]/', $newPassword)
        || !preg_match('/[a-z]/', $newPassword)
        || !preg_match('/[0-9]/', $newPassword)) {
        $errors[] = 'New password must be at least 8 characters with uppercase, lowercase, and a number.';
    }
    if ($newPassword !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    if ($errors) {
        flash('danger', implode(' ', $errors));
        redirect('admins.php?edit=' . $targetId);
    }

    $pdo->prepare('UPDATE admins SET password = ? WHERE id = ?')
        ->execute([password_hash($newPassword, PASSWORD_DEFAULT), $targetId]);

    flash('success', 'Password updated successfully.');
    redirect('admins.php');
}

// ── LOAD DATA ─────────────────────────────────────────────────────────────────
$admins = $pdo->query('SELECT * FROM admins ORDER BY created_at ASC')->fetchAll();

// Admin being edited (if ?edit=ID in URL)
$editAdmin = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM admins WHERE id = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $editAdmin = $stmt->fetch();
}

// Repopulate create form on validation failure
$formData = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']);

$showCreateForm = isset($_GET['action']) && $_GET['action'] === 'create';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0" style="color:var(--primary)">
        <i class="bi bi-shield-lock me-2"></i>Admin Accounts
    </h1>
    <?php if (!$showCreateForm && !$editAdmin): ?>
    <a href="admins.php?action=create" class="btn btn-primary-elegance">
        <i class="bi bi-plus-lg me-1"></i> Add Admin
    </a>
    <?php endif; ?>
</div>

<?php if ($showCreateForm): ?>
<!-- ══════════════════════════════════════════════════════ CREATE FORM ══ -->
<div class="chart-container mb-4">
    <h5 class="mb-4"><i class="bi bi-person-plus me-2"></i>Create New Admin Account</h5>
    <form method="post" action="admins.php" novalidate>
        <input type="hidden" name="action" value="create">
        <div class="row g-3">

            <div class="col-md-6">
                <label class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                <input type="text"
                       name="username"
                       class="form-control"
                       value="<?= e($formData['username'] ?? '') ?>"
                       placeholder="e.g. john_admin"
                       pattern="[a-zA-Z0-9_]+"
                       minlength="3"
                       required
                       autocomplete="off">
                <div class="form-text">Letters, numbers and underscores only. Min 3 characters.</div>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                <input type="text"
                       name="full_name"
                       class="form-control"
                       value="<?= e($formData['fullName'] ?? '') ?>"
                       placeholder="e.g. John Smith"
                       minlength="2"
                       required>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                <input type="email"
                       name="email"
                       class="form-control"
                       value="<?= e($formData['email'] ?? '') ?>"
                       placeholder="admin@elegancesarees.com"
                       required
                       autocomplete="off">
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                <input type="password"
                       name="password"
                       id="new-admin-password"
                       class="form-control"
                       minlength="8"
                       required
                       autocomplete="new-password">
                <!-- Password strength bar -->
                <div class="mt-1" style="height:4px;border-radius:2px;background:#e9ecef;overflow:hidden;">
                    <div id="pwd-strength-bar" style="height:100%;width:0;transition:width .3s,background .3s;"></div>
                </div>
                <div class="form-text">Min 8 chars · uppercase · lowercase · number</div>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                <input type="password"
                       name="confirm_password"
                       id="confirm-admin-password"
                       class="form-control"
                       minlength="8"
                       required
                       autocomplete="new-password">
                <div id="confirm-feedback" class="form-text"></div>
            </div>

            <div class="col-12 d-flex gap-2 mt-2">
                <button type="submit" class="btn btn-primary-elegance">
                    <i class="bi bi-person-check me-1"></i> Create Admin
                </button>
                <a href="admins.php" class="btn btn-secondary">Cancel</a>
            </div>

        </div>
    </form>
</div>

<?php elseif ($editAdmin): ?>
<!-- ══════════════════════════════════════════════════════ EDIT FORM ══ -->
<div class="row g-4 mb-4">

    <!-- Edit profile details -->
    <div class="col-lg-6">
        <div class="chart-container h-100">
            <h5 class="mb-4"><i class="bi bi-pencil-square me-2"></i>Edit Account Details</h5>
            <form method="post" action="admins.php" novalidate>
                <input type="hidden" name="action"   value="edit">
                <input type="hidden" name="admin_id" value="<?= (int) $editAdmin['id'] ?>">

                <div class="mb-3">
                    <label class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                    <input type="text"
                           name="username"
                           class="form-control"
                           value="<?= e($editAdmin['username']) ?>"
                           pattern="[a-zA-Z0-9_]+"
                           minlength="3"
                           required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                    <input type="text"
                           name="full_name"
                           class="form-control"
                           value="<?= e($editAdmin['full_name']) ?>"
                           minlength="2"
                           required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                    <input type="email"
                           name="email"
                           class="form-control"
                           value="<?= e($editAdmin['email']) ?>"
                           required>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary-elegance">
                        <i class="bi bi-check-lg me-1"></i> Save Changes
                    </button>
                    <a href="admins.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Change password -->
    <div class="col-lg-6">
        <div class="chart-container h-100">
            <h5 class="mb-4"><i class="bi bi-key me-2"></i>Change Password</h5>
            <form method="post" action="admins.php" novalidate>
                <input type="hidden" name="action"   value="change_password">
                <input type="hidden" name="admin_id" value="<?= (int) $editAdmin['id'] ?>">

                <?php if ($editAdmin['id'] === $currentAdminId): ?>
                <!-- Current password required only when changing own password -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Current Password <span class="text-danger">*</span></label>
                    <input type="password"
                           name="current_password"
                           class="form-control"
                           required
                           autocomplete="current-password">
                </div>
                <?php else: ?>
                <div class="alert alert-info py-2 small">
                    <i class="bi bi-info-circle me-1"></i>
                    You are resetting another admin's password. Current password verification is not required.
                </div>
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label fw-semibold">New Password <span class="text-danger">*</span></label>
                    <input type="password"
                           name="new_password"
                           id="edit-new-password"
                           class="form-control"
                           minlength="8"
                           required
                           autocomplete="new-password">
                    <div class="mt-1" style="height:4px;border-radius:2px;background:#e9ecef;overflow:hidden;">
                        <div id="edit-pwd-strength-bar" style="height:100%;width:0;transition:width .3s,background .3s;"></div>
                    </div>
                    <div class="form-text">Min 8 chars · uppercase · lowercase · number</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Confirm New Password <span class="text-danger">*</span></label>
                    <input type="password"
                           name="confirm_password"
                           id="edit-confirm-password"
                           class="form-control"
                           minlength="8"
                           required
                           autocomplete="new-password">
                    <div id="edit-confirm-feedback" class="form-text"></div>
                </div>
                <button type="submit" class="btn btn-warning">
                    <i class="bi bi-shield-check me-1"></i> Update Password
                </button>
            </form>
        </div>
    </div>

</div>

<?php endif; ?>

<!-- ══════════════════════════════════════════════════════ ADMINS TABLE ══ -->
<div class="chart-container">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($admins as $admin): ?>
                <tr <?= $admin['id'] === $currentAdminId ? 'class="table-active"' : '' ?>>
                    <td><?= (int) $admin['id'] ?></td>
                    <td>
                        <span class="fw-semibold"><?= e($admin['username']) ?></span>
                        <?php if ($admin['id'] === $currentAdminId): ?>
                            <span class="badge bg-success ms-1">You</span>
                        <?php endif; ?>
                    </td>
                    <td><?= e($admin['full_name']) ?></td>
                    <td><?= e($admin['email']) ?></td>
                    <td><?= date('d M Y', strtotime($admin['created_at'])) ?></td>
                    <td>
                        <a href="admins.php?edit=<?= (int) $admin['id'] ?>"
                           class="btn btn-sm btn-outline-primary"
                           title="Edit">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <?php if ($admin['id'] !== $currentAdminId): ?>
                        <a href="admins.php?delete=<?= (int) $admin['id'] ?>"
                           class="btn btn-sm btn-outline-danger"
                           title="Delete"
                           onclick="return confirm('Delete admin account for <?= e(addslashes($admin['username'])) ?>? This cannot be undone.')">
                            <i class="bi bi-trash"></i>
                        </a>
                        <?php else: ?>
                        <!-- Cannot delete own account — show disabled button with tooltip -->
                        <button class="btn btn-sm btn-outline-danger"
                                disabled
                                title="You cannot delete your own account">
                            <i class="bi bi-trash"></i>
                        </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p class="text-muted small mt-2 mb-0">
        <i class="bi bi-info-circle me-1"></i>
        Your current account is highlighted. You cannot delete your own account or the last remaining admin.
    </p>
</div>

<script>
/**
 * Password strength meter and confirm-match indicator.
 * Runs on both the Create form and the Edit/Change-password form.
 */
(function () {
    function strength(pw) {
        var score = 0;
        if (pw.length >= 8)           score++;
        if (/[A-Z]/.test(pw))         score++;
        if (/[a-z]/.test(pw))         score++;
        if (/[0-9]/.test(pw))         score++;
        if (/[^A-Za-z0-9]/.test(pw))  score++;
        return score;
    }

    var colors = ['', '#dc3545', '#fd7e14', '#ffc107', '#20c997', '#198754'];
    var widths  = ['0%', '20%', '40%', '60%', '80%', '100%'];

    function bindStrength(inputId, barId) {
        var input = document.getElementById(inputId);
        var bar   = document.getElementById(barId);
        if (!input || !bar) return;
        input.addEventListener('input', function () {
            var s = strength(input.value);
            bar.style.width      = widths[s];
            bar.style.background = colors[s];
        });
    }

    function bindConfirm(passwordId, confirmId, feedbackId) {
        var pw      = document.getElementById(passwordId);
        var confirm = document.getElementById(confirmId);
        var fb      = document.getElementById(feedbackId);
        if (!pw || !confirm || !fb) return;
        function check() {
            if (!confirm.value) { fb.textContent = ''; return; }
            if (confirm.value === pw.value) {
                fb.textContent  = '✓ Passwords match';
                fb.style.color  = '#198754';
            } else {
                fb.textContent  = '✗ Passwords do not match';
                fb.style.color  = '#dc3545';
            }
        }
        pw.addEventListener('input', check);
        confirm.addEventListener('input', check);
    }

    // Create form
    bindStrength('new-admin-password',    'pwd-strength-bar');
    bindConfirm ('new-admin-password',    'confirm-admin-password',  'confirm-feedback');

    // Edit / change-password form
    bindStrength('edit-new-password',     'edit-pwd-strength-bar');
    bindConfirm ('edit-new-password',     'edit-confirm-password',   'edit-confirm-feedback');
})();
</script>

<?php require_once 'includes/admin-footer.php'; ?>
