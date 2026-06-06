<?php
$pageTitle = 'Product Form';
require_once 'includes/admin-header.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$product = null;
$categories = $pdo->query('SELECT * FROM categories WHERE status = "active" ORDER BY name')->fetchAll();
$sareeTypes = ['Silk', 'Cotton', 'Designer', 'Bridal', 'Banarasi', 'Chiffon', 'Georgette'];

if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    if (!$product) {
        flash('danger', 'Product not found.');
        redirect('products.php');
    }
    $pageTitle = 'Edit Product';
} else {
    $pageTitle = 'Add Product';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $sareeType = $_POST['saree_type'] ?? '';
    $price = (float) ($_POST['price'] ?? 0);
    $discountPrice = $_POST['discount_price'] !== '' ? (float) $_POST['discount_price'] : null;
    $stock = (int) ($_POST['stock'] ?? 0);
    $status = $_POST['status'] ?? 'active';
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $isNew = isset($_POST['is_new_arrival']) ? 1 : 0;

    $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $name));
    $slug = trim($slug, '-');

    $errors = [];
    if (strlen($name) < 2) {
        $errors[] = 'Name required.';
    }
    if ($categoryId <= 0) {
        $errors[] = 'Category required.';
    }
    if ($price <= 0) {
        $errors[] = 'Valid price required.';
    }
    if (!in_array($sareeType, $sareeTypes, true)) {
        $errors[] = 'Invalid saree type.';
    }

    $imageName = $product['image'] ?? null;
    if (!empty($_FILES['image']['name'])) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            $errors[] = 'Invalid image format.';
        } else {
            $imageName = $slug . '_' . time() . '.' . $ext;
            $dest = dirname(__DIR__, 2) . '/uploads/products/' . $imageName;
            if (!is_dir(dirname($dest))) {
                mkdir(dirname($dest), 0755, true);
            }
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                $errors[] = 'Image upload failed.';
            }
        }
    }

    if ($errors) {
        flash('danger', implode(' ', $errors));
    } else {
        if ($id) {
            $stmt = $pdo->prepare('
                UPDATE products SET category_id=?, name=?, slug=?, description=?, saree_type=?, price=?, discount_price=?, stock=?, image=?, is_featured=?, is_new_arrival=?, status=? WHERE id=?
            ');
            $stmt->execute([$categoryId, $name, $slug, $description, $sareeType, $price, $discountPrice, $stock, $imageName, $isFeatured, $isNew, $status, $id]);
            flash('success', 'Product updated.');
        } else {
            $stmt = $pdo->prepare('
                INSERT INTO products (category_id, name, slug, description, saree_type, price, discount_price, stock, image, is_featured, is_new_arrival, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ');
            $stmt->execute([$categoryId, $name, $slug, $description, $sareeType, $price, $discountPrice, $stock, $imageName, $isFeatured, $isNew, $status]);
            flash('success', 'Product created.');
        }
        redirect('products.php');
    }
}
?>

<h1 class="h3 mb-4" style="color:var(--primary)"><?= $id ? 'Edit' : 'Add' ?> Product</h1>

<div class="chart-container">
    <form method="post" enctype="multipart/form-data">
        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label">Product Name *</label>
                <input type="text" name="name" class="form-control" value="<?= e($product['name'] ?? $_POST['name'] ?? '') ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Category *</label>
                <select name="category_id" class="form-select" required>
                    <option value="">Select</option>
                    <?php foreach ($categories as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($product['category_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4"><?= e($product['description'] ?? '') ?></textarea>
            </div>
            <div class="col-md-4">
                <label class="form-label">Saree Type *</label>
                <select name="saree_type" class="form-select" required>
                    <?php foreach ($sareeTypes as $t): ?>
                    <option value="<?= $t ?>" <?= ($product['saree_type'] ?? '') === $t ? 'selected' : '' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Price (Rs.) *</label>
                <input type="number" step="0.01" name="price" class="form-control" value="<?= e($product['price'] ?? '') ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Discount Price (Rs.)</label>
                <input type="number" step="0.01" name="discount_price" class="form-control" value="<?= e($product['discount_price'] ?? '') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Stock *</label>
                <input type="number" name="stock" class="form-control" value="<?= e($product['stock'] ?? '0') ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="active" <?= ($product['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($product['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Image</label>
                <input type="file" name="image" class="form-control" accept="image/*">
            </div>
            <div class="col-12">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="is_featured" id="featured" <?= !empty($product['is_featured']) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="featured">Featured</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="is_new_arrival" id="newarr" <?= !empty($product['is_new_arrival']) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="newarr">New Arrival</label>
                </div>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary-elegance">Save Product</button>
                <a href="products.php" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </form>
</div>

<?php require_once 'includes/admin-footer.php';
