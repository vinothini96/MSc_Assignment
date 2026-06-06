<?php
$pageTitle = 'Users';
require_once 'includes/admin-header.php';

if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $id = (int) $_GET['toggle'];
    $stmt = $pdo->prepare('SELECT status FROM users WHERE id = ?');
    $stmt->execute([$id]);
    $u = $stmt->fetch();
    if ($u) {
        $newStatus = $u['status'] === 'active' ? 'blocked' : 'active';
        $pdo->prepare('UPDATE users SET status = ? WHERE id = ?')->execute([$newStatus, $id]);
        flash('success', 'User status updated.');
    }
    redirect('users.php');
}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
    flash('success', 'User deleted.');
    redirect('users.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user_id'])) {
    $id = (int) $_POST['edit_user_id'];
    $fullName = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $loyalty = (int) ($_POST['loyalty_points'] ?? 0);
    if (strlen($fullName) >= 2) {
        $pdo->prepare('UPDATE users SET full_name=?, phone=?, loyalty_points=? WHERE id=?')
            ->execute([$fullName, $phone, $loyalty, $id]);
        flash('success', 'User updated.');
    }
    redirect('users.php');
}

$users = $pdo->query('SELECT * FROM users ORDER BY created_at DESC')->fetchAll();
$editUser = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $editUser = $stmt->fetch();
}
?>

<h1 class="h3 mb-4" style="color:var(--primary)">User Management</h1>

<?php if ($editUser): ?>
<div class="chart-container mb-4">
    <h6>Edit User: <?= e($editUser['email']) ?></h6>
    <form method="post" class="row g-3">
        <input type="hidden" name="edit_user_id" value="<?= $editUser['id'] ?>">
        <div class="col-md-4">
            <label class="form-label">Full Name</label>
            <input type="text" name="full_name" class="form-control" value="<?= e($editUser['full_name']) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control" value="<?= e($editUser['phone']) ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Loyalty Points</label>
            <input type="number" name="loyalty_points" class="form-control" value="<?= (int) $editUser['loyalty_points'] ?>">
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-primary-elegance">Update</button>
            <a href="users.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
<?php endif; ?>

<div class="chart-container">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Points</th><th>Status</th><th>Joined</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><?= e($u['full_name']) ?></td>
                    <td><?= e($u['email']) ?></td>
                    <td><?= e($u['phone']) ?></td>
                    <td><?= (int) $u['loyalty_points'] ?></td>
                    <td><span class="badge bg-<?= $u['status'] === 'active' ? 'success' : 'danger' ?>"><?= e($u['status']) ?></span></td>
                    <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                    <td>
                        <a href="users.php?edit=<?= $u['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <a href="users.php?toggle=<?= $u['id'] ?>" class="btn btn-sm btn-outline-warning" title="Toggle status"><i class="bi bi-shield"></i></a>
                        <a href="users.php?delete=<?= $u['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete user?')"><i class="bi bi-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/admin-footer.php';
