<?php
$pageTitle = 'About Us';
require_once 'includes/header.php';
?>

<div class="about-hero">
    <div class="container">
        <h1>About <?= e(APP_NAME) ?></h1>
        <p class="lead mb-0">Weaving tradition with contemporary elegance since 2010</p>
    </div>
</div>

<div class="container py-5">
    <div class="row g-4 mb-5">
        <div class="col-md-6">
            <h2 class="section-title">Our Story</h2>
            <p>Elegance Sarees began as a small boutique in Polonnaruwa with a passion for authentic Sri Lanken textiles. Today, we bring handpicked silk, cotton, designer, and bridal sarees from master weavers across Sri Lanka to your doorstep.</p>
            <p>Every saree in our collection is chosen for its craftsmanship, fabric quality, and timeless appeal. We believe every woman deserves to drape herself in confidence and grace.</p>
        </div>
        <div class="col-md-6">
            <img src="<?= e(ASSETS_IMAGES . 'banners/about_banner.jpg') ?>" alt="Our store" class="img-fluid rounded shadow" style="max-height:320px;width:100%;object-fit:cover">
        </div>
    </div>

    <h2 class="section-title text-center">Why Choose Us</h2>
    <div class="row g-4 text-center">
        <div class="col-md-3 col-6">
            <div class="feature-icon"><i class="bi bi-patch-check"></i></div>
            <h6>Authentic Quality</h6>
            <p class="small text-muted">Certified fabrics from trusted weavers</p>
        </div>
        <div class="col-md-3 col-6">
            <div class="feature-icon"><i class="bi bi-truck"></i></div>
            <h6>Island Wide Delivery</h6>
            <p class="small text-muted">Safe packaging & timely shipping</p>
        </div>
        <div class="col-md-3 col-6">
            <div class="feature-icon"><i class="bi bi-arrow-repeat"></i></div>
            <h6>Easy Returns</h6>
            <p class="small text-muted">7-day return on unused items</p>
        </div>
        <div class="col-md-3 col-6">
            <div class="feature-icon"><i class="bi bi-star"></i></div>
            <h6>Loyalty Rewards</h6>
            <p class="small text-muted">Earn points on every purchase</p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php';
