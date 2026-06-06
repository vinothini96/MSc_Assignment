<?php
/**
 * Reusable product card partial
 * Expects $product array with id, name, slug, price, sale_price, image, avg_rating, review_count
 */
$price = getProductPrice($product);
$hasSale = $product['sale_price'] && $product['sale_price'] > 0;
?>
<div class="col-sm-6 col-md-4 col-lg-3 mb-4">
    <div class="card product-card h-100 position-relative">
        <?php if ($hasSale): ?><span class="sale-badge">SALE</span><?php endif; ?>
        <a href="<?= BASE_URL ?>/product-detail.php?id=<?= (int)$product['id'] ?>">
            <img src="<?= e(productImageUrl($product['image'])) ?>" class="card-img-top" alt="<?= e($product['name']) ?>">
        </a>
        <div class="card-body d-flex flex-column">
            <h6 class="card-title"><a href="<?= BASE_URL ?>/product-detail.php?id=<?= (int)$product['id'] ?>" class="text-decoration-none text-dark"><?= e($product['name']) ?></a></h6>
            <div class="mb-1"><?= renderStars((float)$product['avg_rating']) ?> <small class="text-muted">(<?= (int)$product['review_count'] ?>)</small></div>
            <div class="mt-auto">
                <?php if ($hasSale): ?>
                    <span class="price"><?= formatPrice((float)$product['sale_price']) ?></span>
                    <span class="price-old ms-1"><?= formatPrice((float)$product['price']) ?></span>
                <?php else: ?>
                    <span class="price"><?= formatPrice($price) ?></span>
                <?php endif; ?>
                <div class="d-flex gap-1 mt-2">
                    <button class="btn btn-warning btn-sm flex-grow-1 btn-add-cart" data-product-id="<?= (int)$product['id'] ?>">
                        <i class="bi bi-cart-plus"></i> Add
                    </button>
                    <?php if (isLoggedIn()): ?>
                    <button class="btn btn-outline-danger btn-sm" onclick="toggleWishlist(<?= (int)$product['id'] ?>, this)">
                        <i class="bi bi-heart"></i>
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
