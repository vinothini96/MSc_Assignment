<?php
$pageTitle = 'Product Details';
require_once 'includes/header.php';

$slug = $_GET['slug'] ?? '';
if (!$slug) {
    flash('warning', 'Product not found.');
    redirect('shop.php');
}

$stmt = $pdo->prepare('
    SELECT p.*, c.name AS category_name, c.slug AS category_slug
    FROM products p JOIN categories c ON c.id = p.category_id
    WHERE p.slug = ? AND p.status = "active"
');
$stmt->execute([$slug]);
$product = $stmt->fetch();

if (!$product) {
    flash('warning', 'Product not found.');
    redirect('shop.php');
}

$pageTitle = $product['name'];
$price = $product['discount_price'] ?? $product['price'];
$img = product_image_url($product['image']);

$related = $pdo->prepare('SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON c.id = p.category_id WHERE p.category_id = ? AND p.id != ? AND p.status = "active" LIMIT 4');
$related->execute([$product['category_id'], $product['id']]);
$relatedProducts = $related->fetchAll();
?>

<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb breadcrumb-elegance mb-0">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="shop.php">Shop</a></li>
            <li class="breadcrumb-item"><a href="shop.php?category=<?= urlencode($product['category_slug']) ?>"><?= e($product['category_name']) ?></a></li>
            <li class="breadcrumb-item active"><?= e($product['name']) ?></li>
        </ol>
    </nav>

    <div class="row mt-4 g-4">
        <div class="col-md-6 product-gallery">
            <img src="<?= e($img) ?>" alt="<?= e($product['name']) ?>">
        </div>
        <div class="col-md-6 product-detail-info">
            <span class="badge bg-secondary mb-2"><?= e($product['saree_type']) ?></span>
            <?php if ($product['is_new_arrival']): ?><span class="badge bg-accent text-dark">New Arrival</span><?php endif; ?>
            <h1 class="h3"><?= e($product['name']) ?></h1>
            <p class="text-muted">Category: <a href="shop.php?category=<?= urlencode($product['category_slug']) ?>"><?= e($product['category_name']) ?></a></p>
            <p class="product-price fs-4">
                <?= format_price((float) $price) ?>
                <?php if ($product['discount_price']): ?>
                <span class="old-price fs-6"><?= format_price((float) $product['price']) ?></span>
                <?php endif; ?>
            </p>
            <p><?= nl2br(e($product['description'])) ?></p>
            <p><strong>Stock:</strong> <?= (int) $product['stock'] ?> available</p>
            <div class="d-flex align-items-center gap-2 my-3">
                <label for="qty" class="mb-0">Quantity:</label>
                <input type="number" id="qty" class="form-control form-control-sm" style="width:80px" value="1" min="1" max="<?= (int) $product['stock'] ?>">
            </div>
            <button type="button" class="btn btn-primary-elegance btn-lg add-to-cart-btn" data-product-id="<?= (int) $product['id'] ?>" data-qty-input="qty">
                <i class="bi bi-bag-plus"></i> Add to Cart
            </button>
            <a href="cart.php" class="btn btn-outline-secondary btn-lg ms-2">View Cart</a>
        </div>
    </div>

    <?php if ($relatedProducts): ?>
    <h3 class="section-title mt-5">You May Also Like</h3>
    <div class="row">
        <?php foreach ($relatedProducts as $rp): echo render_product_card($rp); endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php
$extraScripts = ['js/cart.js'];
require_once 'includes/footer.php';
