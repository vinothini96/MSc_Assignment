<?php
$pageTitle = 'Categories';
require_once 'includes/admin-header.php';

// Delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $check = $pdo->prepare('SELECT COUNT(*) FROM products WHERE category_id = ?');
    $check->execute([$id]);
    if ((int) $check->fetchColumn() > 0) {
        flash('danger', 'Cannot delete category with products.');
    } else {
        $pdo->prepare('DELETE FROM categories WHERE id = ?')->execute([$id]);
        flash('success', 'Category deleted.');
    }
    redirect('categories.php');
}

// Create / Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? 'active';
    $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $name));

    if (strlen($name) < 2) {
        flash('danger', 'Category name required.');
    } else {
        if ($id) {
            $pdo->prepare('UPDATE categories SET name=?, slug=?, description=?, status=? WHERE id=?')
                ->execute([$name, $slug, $description, $status, $id]);
            flash('success', 'Category updated.');
        } else {
            $pdo->prepare('INSERT INTO categories (name, slug, description, status) VALUES (?, ?, ?, ?)')
                ->execute([$name, $slug, $description, $status]);
            flash('success', 'Category created.');
        }
        redirect('categories.php');
    }
}

$categories = $pdo->query('SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id) AS product_count FROM categories c ORDER BY c.name')->fetchAll();
$editCat = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM categories WHERE id = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $editCat = $stmt->fetch();
}
?>

<h1 class="h3 mb-4" style="color:var(--primary)">Category Management</h1>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="chart-container">
            <h6><?= $editCat ? 'Edit' : 'Add' ?> Category</h6>
            <form method="post">
                <input type="hidden" name="id" value="<?= (int) ($editCat['id'] ?? 0) ?>">
                <div class="mb-3">
                    <label class="form-label">Name *</label>
                    <input type="text" name="name" class="form-control" value="<?= e($editCat['name'] ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3"><?= e($editCat['description'] ?? '') ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" <?= ($editCat['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= ($editCat['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary-elegance w-100">Save</button>
                <?php if ($editCat): ?><a href="categories.php" class="btn btn-link w-100">Cancel Edit</a><?php endif; ?>
            </form>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="chart-container">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead><tr><th>Name</th><th>Slug</th><th>Products</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php foreach ($categories as $c): ?>
                        <tr>
                            <td><?= e($c['name']) ?></td>
                            <td><?= e($c['slug']) ?></td>
                            <td><?= (int) $c['product_count'] ?></td>
                            <td><span class="badge bg-<?= $c['status'] === 'active' ? 'success' : 'secondary' ?>"><?= e($c['status']) ?></span></td>
                            <td>
                                <a href="categories.php?edit=<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                <a href="categories.php?delete=<?= $c['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete?')"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/admin-footer.php';
