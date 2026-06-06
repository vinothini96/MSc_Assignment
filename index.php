<?php
$pageTitle = 'Home';
require_once 'includes/header.php';

$banners = $pdo->query('SELECT * FROM banners WHERE is_active = 1 ORDER BY sort_order')->fetchAll();

$featured = $pdo->query('SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON c.id = p.category_id WHERE p.is_featured = 1 AND p.status = "active" LIMIT 8')->fetchAll();
$newArrivals = $pdo->query('SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON c.id = p.category_id WHERE p.is_new_arrival = 1 AND p.status = "active" LIMIT 4')->fetchAll();
$categories = $pdo->query('SELECT * FROM categories WHERE status = "active" LIMIT 5')->fetchAll();
?>

<div id="heroCarousel" class="carousel slide hero-carousel" data-bs-ride="carousel">
    <div class="carousel-indicators">
        <?php foreach ($banners as $i => $b): ?>
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="<?= $i ?>" <?= $i === 0 ? 'class="active"' : '' ?>></button>
        <?php endforeach; ?>
    </div>
    <div class="carousel-inner">
        <?php foreach ($banners as $i => $banner):
            $imgPath = ASSETS_IMAGES . 'banners/' . ($banner['image'] ?? 'hero_banner_1.jpg');
        ?>
        <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
            <img src="<?= e($imgPath) ?>" class="d-block w-100" alt="<?= e($banner['title']) ?>">
            <div class="carousel-caption hero-caption">
                <?php if ($banner['discount_text']): ?><span class="discount-badge"><?= e($banner['discount_text']) ?></span><?php endif; ?>
                <h2><?= e($banner['title']) ?></h2>
                <p><?= e($banner['subtitle']) ?></p>
                <a href="<?= e($banner['link_url'] ?? 'shop.php') ?>" class="btn btn-accent">Shop Now</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon"></span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon"></span>
    </button>
</div>

<div class="container py-5">
    <div class="promo-banner text-center">
        <h3 class="mb-2">Grand Festive Offer</h3>
        <p class="mb-3">Flat 15% off on orders above Rs.5,000 | Earn loyalty points on every purchase</p>
        <a href="shop.php" class="btn btn-accent">Explore Collection</a>
    </div>

    <h2 class="section-title">Shop by Category</h2>
    <div class="row g-3 mb-5">
        <?php foreach ($categories as $cat): ?>
        <div class="col-6 col-md-4 col-lg">
            <a href="shop.php?category=<?= urlencode($cat['slug']) ?>" class="text-decoration-none">
                <div class="card product-card text-center p-3">
                    <i class="bi bi-collection display-6 text-primary" style="color: var(--primary)!important;"></i>
                    <h6 class="mt-2 mb-0 text-dark"><?= e($cat['name']) ?></h6>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="section-title mb-0">Featured Sarees</h2>
        <a href="shop.php?filter=featured" class="btn btn-outline-secondary btn-sm">View All</a>
    </div>
    <div class="row">
        <?php foreach ($featured as $product): echo render_product_card($product); endforeach; ?>
    </div>

    <?php if ($newArrivals): ?>
    <h2 class="section-title mt-5">New Arrivals</h2>
    <div class="row">
        <?php foreach ($newArrivals as $product): echo render_product_card($product); endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php
$extraScripts = ['js/cart.js'];
require_once 'includes/footer.php';
