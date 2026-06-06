<?php
$pageTitle = 'Products';
require_once 'includes/admin-header.php';

// Delete product
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $stmt = $pdo->prepare('SELECT image FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    $pdo->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
    if ($row && $row['image'] && file_exists(dirname(__DIR__, 2) . '/uploads/products/' . $row['image'])) {
        @unlink(dirname(__DIR__, 2) . '/uploads/products/' . $row['image']);
    }
    flash('success', 'Product deleted.');
    redirect('products.php');
}

$products = $pdo->query('
    SELECT p.*, c.name AS category_name FROM products p
    JOIN categories c ON c.id = p.category_id ORDER BY p.id DESC
')->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0" style="color:var(--primary)">Product Management</h1>
    <a href="product-form.php" class="btn btn-primary-elegance"><i class="bi bi-plus-lg"></i> Add Product</a>
</div>

<div class="chart-container">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Type</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                <tr>
                    <td><img src="../<?= e(product_image_url($p['image'])) ?>" width="50" height="50" style="object-fit:cover;border-radius:6px" alt=""></td>
                    <td><?= e($p['name']) ?></td>
                    <td><?= e($p['category_name']) ?></td>
                    <td><?= e($p['saree_type']) ?></td>
                    <td><?= format_price((float) ($p['discount_price'] ?? $p['price'])) ?></td>
                    <td><?= (int) $p['stock'] ?></td>
                    <td><span class="badge bg-<?= $p['status'] === 'active' ? 'success' : 'secondary' ?>"><?= e($p['status']) ?></span></td>
                    <td>
                        <a href="product-form.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <a href="products.php?delete=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this product?')"><i class="bi bi-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/admin-footer.php';
