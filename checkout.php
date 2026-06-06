<?php
$pageTitle = 'Checkout';
require_once 'includes/header.php';
require_login();

$items = get_cart_items($pdo);
if (!$items) {
    flash('warning', 'Your cart is empty.');
    redirect('cart.php');
}

$subtotal = 0;
foreach ($items as $item) {
    $unit = $item['discount_price'] ?? $item['price'];
    $subtotal += $unit * $item['quantity'];
}

$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
$loyaltyPoints = get_user_loyalty_total($pdo, (int) $_SESSION['user_id']);
?>

<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb breadcrumb-elegance mb-0">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="cart.php">Cart</a></li>
            <li class="breadcrumb-item active">Checkout</li>
        </ol>
    </nav>

    <h1 class="section-title mt-3">Checkout</h1>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="summary-box">
                <h5>Shipping Details</h5>
                <form action="actions/checkout-process.php" method="post" id="checkout-form" novalidate>
                    <div class="mb-3">
                        <label class="form-label">Full Address *</label>
                        <textarea name="shipping_address" class="form-control" rows="3" required><?= e($user['address'] ?? '') ?></textarea>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">City *</label>
                            <input type="text" name="city" class="form-control" value="<?= e($user['city'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">District *</label>
                            <input type="text" name="district" class="form-control" value="<?= e($user['district'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Pincode *</label>
                            <input type="text" name="pincode" class="form-control" pattern="[0-9]{5}" maxlength="5" value="<?= e($user['pincode'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone *</label>
                        <input type="tel" name="phone" class="form-control" value="<?= e($user['phone'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method" class="form-select">
                            <option value="cod">Cash on Delivery</option>
                            <option value="online">Online Payment (Demo)</option>
                        </select>
                    </div>
                    <?php if ($loyaltyPoints > 0): ?>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="use_loyalty_points" id="usePoints">
                        <label class="form-check-label" for="usePoints">
                            Use loyalty points (<?= $loyaltyPoints ?> pts available, up to 10% off)
                        </label>
                    </div>
                    <?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label">Order Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Optional delivery instructions"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary-elegance btn-lg">Place Order</button>
                </form>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="summary-box">
                <h5>Your Order</h5>
                <ul class="list-unstyled">
                    <?php foreach ($items as $item):
                        $unit = $item['discount_price'] ?? $item['price'];
                    ?>
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span><?= e($item['name']) ?> × <?= (int) $item['quantity'] ?></span>
                        <span><?= format_price($unit * $item['quantity']) ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <hr>
                <div class="d-flex justify-content-between fs-5">
                    <strong>Total</strong>
                    <strong class="text-primary"><?= format_price($subtotal) ?></strong>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$extraScripts = ['js/validation.js'];
require_once 'includes/footer.php';
