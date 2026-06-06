<?php
$pageTitle = 'Shopping Cart';
require_once 'includes/header.php';

$items = get_cart_items($pdo);
$subtotal = 0;
foreach ($items as &$item) {
    $unit = $item['discount_price'] ?? $item['price'];
    $item['line_total'] = $unit * $item['quantity'];
    $subtotal += $item['line_total'];
}
unset($item);
?>

<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb breadcrumb-elegance mb-0">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active">Cart</li>
        </ol>
    </nav>

    <h1 class="section-title mt-3">Shopping Cart</h1>

    <?php if (!$items): ?>
    <div class="text-center py-5">
        <i class="bi bi-bag display-1 text-muted"></i>
        <p class="mt-3">Your cart is empty.</p>
        <a href="shop.php" class="btn btn-primary-elegance">Continue Shopping</a>
    </div>
    <?php else: ?>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="table-responsive bg-white rounded shadow-sm p-3">
                <table class="table cart-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th>Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item):
                            $unit = $item['discount_price'] ?? $item['price'];
                            $img = product_image_url($item['image']);
                        ?>
                        <tr data-cart-id="<?= (int) $item['cart_id'] ?>" data-product-id="<?= (int) $item['product_id'] ?>">
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <img src="<?= e($img) ?>" alt="">
                                    <div>
                                        <a href="product.php?slug=<?= urlencode($item['slug']) ?>" class="fw-semibold text-decoration-none"><?= e($item['name']) ?></a>
                                    </div>
                                </div>
                            </td>
                            <td><?= format_price((float) $unit) ?></td>
                            <td>
                                <input type="number" class="form-control form-control-sm cart-qty-input" style="width:70px" value="<?= (int) $item['quantity'] ?>" min="1" max="<?= (int) $item['stock'] ?>">
                            </td>
                            <td class="line-total"><?= format_price((float) $item['line_total']) ?></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-danger remove-cart-item"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="summary-box">
                <h5>Order Summary</h5>
                <hr>
                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal</span>
                    <strong id="cart-subtotal"><?= format_price($subtotal) ?></strong>
                </div>
                <p class="small text-muted">Shipping calculated at checkout. Earn loyalty points on every order!</p>
                <a href="checkout.php" class="btn btn-primary-elegance w-100 mt-3">Proceed to Checkout</a>
                <a href="shop.php" class="btn btn-link w-100">Continue Shopping</a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
$extraScripts = ['js/cart.js'];
require_once 'includes/footer.php';
