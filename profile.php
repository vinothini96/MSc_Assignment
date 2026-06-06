<?php
$pageTitle = 'My Profile';
require_once 'includes/header.php';
require_login();

$userId = (int) $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $district = trim($_POST['district'] ?? '');
    $pincode = trim($_POST['pincode'] ?? '');
    $currentPass = $_POST['current_password'] ?? '';
    $newPass = $_POST['new_password'] ?? '';

    $errors = [];
    if (strlen($fullName) < 2) {
        $errors[] = 'Name is required.';
    }
    if (!preg_match('/^[0-9]{10}$/', preg_replace('/\D/', '', $phone))) {
        $errors[] = 'Valid phone required.';
    }

    if ($errors) {
        flash('danger', implode(' ', $errors));
    } else {
        $stmt = $pdo->prepare('UPDATE users SET full_name=?, phone=?, address=?, city=?, district=?, pincode=? WHERE id=?');
        $stmt->execute([$fullName, $phone, $address, $city, $district, $pincode, $userId]);
        $_SESSION['user_name'] = $fullName;

        if ($newPass !== '') {
            $stmt = $pdo->prepare('SELECT password FROM users WHERE id = ?');
            $stmt->execute([$userId]);
            $hash = $stmt->fetch()['password'];
            if (!password_verify($currentPass, $hash)) {
                flash('danger', 'Current password incorrect.');
                redirect('profile.php');
            }
            if (strlen($newPass) < 8) {
                flash('danger', 'New password too weak.');
                redirect('profile.php');
            }
            $pdo->prepare('UPDATE users SET password = ? WHERE id = ?')
                ->execute([password_hash($newPass, PASSWORD_DEFAULT), $userId]);
        }
        flash('success', 'Profile updated successfully.');
        redirect('profile.php');
    }
}

$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();
$loyalty = get_user_loyalty_total($pdo, $userId);
?>

<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb breadcrumb-elegance mb-0">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active">Profile</li>
        </ol>
    </nav>

    <div class="row g-4 mt-2">
        <div class="col-md-4">
            <div class="summary-box text-center">
                <img src="<?= e(ASSETS_IMAGES . 'users/default_avatar.jpg') ?>" alt="Avatar" class="rounded-circle mb-3" width="100" height="100" style="object-fit:cover">
                <h5><?= e($user['full_name']) ?></h5>
                <p class="text-muted mb-2"><?= e($user['email']) ?></p>
                <div class="badge bg-accent text-dark fs-6">
                    <i class="bi bi-star-fill"></i> <?= $loyalty ?> Loyalty Points
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="summary-box">
                <h5>Edit Profile</h5>
                <form method="post" id="profile-form" novalidate>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="full_name" class="form-control" value="<?= e($user['full_name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="<?= e($user['email']) ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="tel" name="phone" class="form-control" value="<?= e($user['phone']) ?>" required data-validate="phone">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="2"><?= e($user['address'] ?? '') ?></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control" value="<?= e($user['city'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">District</label>
                            <input type="text" name="district" class="form-control" value="<?= e($user['district'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Pincode</label>
                            <input type="text" name="pincode" class="form-control" value="<?= e($user['pincode'] ?? '') ?>">
                        </div>
                        <div class="col-12"><hr><h6>Change Password (optional)</h6></div>
                        <div class="col-md-6">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="current_password" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-control" data-validate="password-optional">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary-elegance mt-3">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$extraScripts = ['js/validation.js'];
require_once 'includes/footer.php';
