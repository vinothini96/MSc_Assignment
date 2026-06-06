<?php
/**
 * Product Details - reviews, add to cart, recently viewed
 */
$pageTitle = 'Product Details';
require_once __DIR__ . '/includes/init.php';
$db = getDB();

$id = (int)($_GET['id'] ?? 0);
$stmt = $db->prepare('SELECT p.*, c.name AS category_name, c.slug AS category_slug 
    FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id = ? AND p.is_active = 1');
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    setFlash('error', 'Product not found.');
    redirect(BASE_URL . '/products.php');
}

$pageTitle = $product['name'];

// Record recently viewed
if (isLoggedIn()) {
    recordRecentlyViewed($db, (int)$_SESSION['user_id'], $id);
}

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review']) && isLoggedIn()) {
    $rating = (int)($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');
    if ($rating >= 1 && $rating <= 5) {
        try {
            $db->prepare('INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)')
                ->execute([$id, $_SESSION['user_id'], $rating, $comment]);
            updateProductRating($db, $id);
            setFlash('success', 'Review submitted!');
        } catch (PDOException $e) {
            setFlash('error', 'You have already reviewed this product.');
        }
        redirect(BASE_URL . '/product-detail.php?id=' . $id);
    }
}

// Refresh product after possible rating update
$stmt->execute([$id]);
$product = $stmt->fetch();

$reviews = $db->prepare('SELECT r.*, u.full_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = ? ORDER BY r.created_at DESC');
$reviews->execute([$id]);
$reviews = $reviews->fetchAll();

$inWishlist = false;
if (isLoggedIn()) {
    $w = $db->prepare('SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?');
    $w->execute([$_SESSION['user_id'], $id]);
    $inWishlist = (bool)$w->fetch();
}

$price = getProductPrice($product);
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/products.php?category=<?= e($product['category_slug']) ?>"><?= e($product['category_name']) ?></a></li>
            <li class="breadcrumb-item active"><?= e($product['name']) ?></li>
        </ol>
    </nav>
    <div class="row">
        <div class="col-md-5">
            <img src="<?= e(productImageUrl($product['image'])) ?>" class="img-fluid product-detail-img w-100" alt="<?= e($product['name']) ?>">
        </div>
        <div class="col-md-7">
            <h2><?= e($product['name']) ?></h2>
            <div class="mb-2"><?= renderStars((float)$product['avg_rating']) ?> (<?= (int)$product['review_count'] ?> reviews)</div>
            <p class="text-muted">Category: <?= e($product['category_name']) ?> | Stock: <?= (int)$product['stock'] ?></p>
            <div class="mb-3">
                <?php if ($product['sale_price']): ?>
                    <span class="price fs-3"><?= formatPrice((float)$product['sale_price']) ?></span>
                    <span class="price-old fs-5"><?= formatPrice((float)$product['price']) ?></span>
                <?php else: ?>
                    <span class="price fs-3"><?= formatPrice($price) ?></span>
                <?php endif; ?>
            </div>
            <p><?= nl2br(e($product['description'])) ?></p>
            <div class="d-flex align-items-center gap-2 mb-3">
                <label>Qty:</label>
                <input type="number" id="quantity" class="form-control qty-input" value="1" min="1" max="<?= (int)$product['stock'] ?>">
                <button class="btn btn-warning btn-add-cart" data-product-id="<?= $id ?>"><i class="bi bi-cart-plus"></i> Add to Cart</button>
                <?php if (isLoggedIn()): ?>
                <button class="btn <?= $inWishlist ? 'btn-danger' : 'btn-outline-danger' ?>" onclick="toggleWishlist(<?= $id ?>, this)">
                    <i class="bi bi-heart<?= $inWishlist ? '-fill' : '' ?>"></i>
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Reviews Section -->
    <div class="row mt-5">
        <div class="col-lg-8">
            <h4>Customer Reviews</h4>
            <?php foreach ($reviews as $r): ?>
            <div class="review-item">
                <strong><?= e($r['full_name']) ?></strong> <?= renderStars((float)$r['rating']) ?>
                <small class="text-muted"><?= date('M d, Y', strtotime($r['created_at'])) ?></small>
                <p class="mb-0 mt-1"><?= e($r['comment']) ?></p>
            </div>
            <?php endforeach; ?>
            <?php if (count($reviews) === 0): ?><p class="text-muted">No reviews yet.</p><?php endif; ?>
        </div>
        <?php if (isLoggedIn()): ?>
        <div class="col-lg-4">
            <div class="card p-3">
                <h5>Write a Review</h5>
                <form method="post" id="reviewForm">
                    <div class="rating-input mb-2">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                        <input type="radio" name="rating" id="star<?= $i ?>" value="<?= $i ?>" class="d-none">
                        <label for="star<?= $i ?>" style="cursor:pointer;color:#ccc;font-size:1.5rem;">★</label>
                        <?php endfor; ?>
                    </div>
                    <textarea name="comment" class="form-control mb-2" rows="3" placeholder="Your review..."></textarea>
                    <button type="submit" name="submit_review" class="btn btn-primary btn-sm">Submit Review</button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
