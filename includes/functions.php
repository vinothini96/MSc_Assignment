<?php
/**
 * Shared helper functions
 */
require_once __DIR__ . '/config.php';

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array
{
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

function is_admin_logged_in(): bool
{
    return !empty($_SESSION['admin_id']);
}

function require_login(): void
{
    if (!is_logged_in()) {
        flash('warning', 'Please login to continue.');
        redirect('login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    }
}

function require_admin(): void
{
    if (!is_admin_logged_in()) {
        $inAdmin = strpos($_SERVER['PHP_SELF'] ?? '', '/admin/') !== false;
        redirect($inAdmin ? 'login.php' : 'admin/login.php');
    }
}

function product_image_url(?string $filename): string
{
    // Resolve the absolute root of the project (EleganceSarees/) regardless of which
    // config file was loaded (config.php defines BASE_PATH; constants.php does not).
    $projectRoot = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__);

    if (!$filename) {
        return 'assets/images/products/default_saree.jpg';
    }

    // Check the primary uploads directory first (admin-uploaded images go here)
    $uploadPhysicalPath = $projectRoot . '/uploads/products/' . $filename;
    if (file_exists($uploadPhysicalPath)) {
        return 'uploads/products/' . $filename;
    }

    // Fall back to bundled asset folders (seed/demo images)
    foreach (['sarees', 'products'] as $folder) {
        $assetPath = $projectRoot . '/assets/images/' . $folder . '/' . $filename;
        if (file_exists($assetPath)) {
            return 'assets/images/' . $folder . '/' . $filename;
        }
    }

    // Final fallback to default placeholder
    return 'assets/images/products/default_saree.jpg';
}

/**
 * camelCase alias — used by product-detail.php and product-card.php
 */
function productImageUrl(?string $filename): string
{
    return product_image_url($filename);
}

function format_price(float $amount): string
{
    return 'Rs.' . number_format($amount, 2);
}

function generate_order_number(): string
{
    return 'ES' . date('Ymd') . strtoupper(substr(uniqid(), -6));
}

function get_cart_count(PDO $pdo): int
{
    if (is_logged_in()) {
        $stmt = $pdo->prepare('SELECT COALESCE(SUM(quantity), 0) AS cnt FROM cart WHERE user_id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        return (int) $stmt->fetch()['cnt'];
    }
    $count = 0;
    if (!empty($_SESSION['guest_cart'])) {
        foreach ($_SESSION['guest_cart'] as $item) {
            $count += (int) ($item['quantity'] ?? 0);
        }
    }
    return $count;
}

function sync_guest_cart_to_db(PDO $pdo, int $userId): void
{
    if (empty($_SESSION['guest_cart'])) {
        return;
    }
    foreach ($_SESSION['guest_cart'] as $productId => $item) {
        $stmt = $pdo->prepare('SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?');
        $stmt->execute([$userId, $productId]);
        $existing = $stmt->fetch();
        if ($existing) {
            $upd = $pdo->prepare('UPDATE cart SET quantity = quantity + ? WHERE id = ?');
            $upd->execute([(int) $item['quantity'], $existing['id']]);
        } else {
            $ins = $pdo->prepare('INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)');
            $ins->execute([$userId, $productId, (int) $item['quantity']]);
        }
    }
    unset($_SESSION['guest_cart']);
}

function award_loyalty_points(PDO $pdo, int $userId, int $orderId, float $orderTotal): void
{
    $points = (int) floor($orderTotal / 100);
    if ($points <= 0) {
        return;
    }
    $stmt = $pdo->prepare('INSERT INTO loyalty_points (user_id, order_id, points, description) VALUES (?, ?, ?, ?)');
    $stmt->execute([$userId, $orderId, $points, 'Points earned on order']);
    $upd = $pdo->prepare('UPDATE users SET loyalty_points = loyalty_points + ? WHERE id = ?');
    $upd->execute([$points, $userId]);
}

function get_user_loyalty_total(PDO $pdo, int $userId): int
{
    $stmt = $pdo->prepare('SELECT loyalty_points FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    return (int) ($stmt->fetch()['loyalty_points'] ?? 0);
}

/** @return array<int, array> */
function get_cart_items(PDO $pdo): array
{
    if (is_logged_in()) {
        $stmt = $pdo->prepare('
            SELECT c.id AS cart_id, c.quantity, p.id AS product_id, p.name, p.image, p.price, p.discount_price, p.stock, p.slug
            FROM cart c
            JOIN products p ON p.id = c.product_id
            WHERE c.user_id = ? AND p.status = ?
        ');
        $stmt->execute([$_SESSION['user_id'], 'active']);
        return $stmt->fetchAll();
    }

    $items = [];
    if (empty($_SESSION['guest_cart'])) {
        return $items;
    }
    $ids = array_keys($_SESSION['guest_cart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT id AS product_id, name, image, price, discount_price, stock, slug FROM products WHERE id IN ($placeholders) AND status = 'active'");
    $stmt->execute($ids);
    foreach ($stmt->fetchAll() as $product) {
        $qty = (int) ($_SESSION['guest_cart'][$product['product_id']]['quantity'] ?? 1);
        $items[] = array_merge($product, [
            'cart_id' => $product['product_id'],
            'quantity' => $qty,
        ]);
    }
    return $items;
}

function render_product_card(array $product): string
{
    $price = $product['discount_price'] ?? $product['price'];
    $hasDiscount = !empty($product['discount_price']);
    $img = product_image_url($product['image']);
    $saleBadge = $hasDiscount ? '<span class="badge badge-sale">Sale</span>' : '';
    $newBadge = !empty($product['is_new_arrival']) ? '<span class="badge badge-new">New</span>' : '';
    $oldPrice = $hasDiscount ? '<span class="old-price">' . format_price((float) $product['price']) . '</span>' : '';

    return '<div class="col-sm-6 col-lg-4 col-xl-3 mb-4">
        <div class="card product-card h-100">
            <div class="position-relative">
                ' . $saleBadge . $newBadge . '
                <a href="product.php?slug=' . urlencode($product['slug']) . '">
                    <img src="' . e($img) . '" class="card-img-top" alt="' . e($product['name']) . '">
                </a>
            </div>
            <div class="card-body d-flex flex-column">
                <span class="text-muted small">' . e($product['saree_type'] ?? '') . '</span>
                <h6 class="card-title"><a href="product.php?slug=' . urlencode($product['slug']) . '" class="text-decoration-none text-dark">' . e($product['name']) . '</a></h6>
                <p class="product-price mt-auto">' . $oldPrice . '<br/>' . format_price((float) $price) . '</p>
                <button type="button" class="btn btn-primary-elegance btn-sm w-100 add-to-cart-btn" data-product-id="' . (int) $product['id'] . '">
                    <i class="bi bi-bag-plus"></i> Add to Cart
                </button>
            </div>
        </div>
    </div>';
}
