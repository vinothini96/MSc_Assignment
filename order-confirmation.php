<?php
/**
 * Order Confirmation Page
 */
$pageTitle = 'Order Confirmed';
require_once __DIR__ . '/includes/init.php';
requireLogin();

$orderNumber = $_GET['order'] ?? $_SESSION['last_order'] ?? '';
if (!$orderNumber) redirect(BASE_URL . '/orders.php');

$db = getDB();
$stmt = $db->prepare('SELECT * FROM orders WHERE order_number = ? AND user_id = ?');
$stmt->execute([$orderNumber, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    setFlash('error', 'Order not found.');
    redirect(BASE_URL . '/orders.php');
}

$items = $db->prepare('SELECT * FROM order_items WHERE order_id = ?');
$items->execute([$order['id']]);
$items = $items->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5 text-center">
    <div class="order-success-icon"><i class="bi bi-check-circle-fill"></i></div>
    <h2 class="mt-3">Thank You for Your Order!</h2>
    <p class="lead">Order <strong>#<?= e($order['order_number']) ?></strong> has been placed successfully.</p>
    <div class="card mx-auto mt-4" style="max-width:500px">
        <div class="card-body text-start">
            <p><strong>Total:</strong> <?= formatPrice((float)$order['total_amount']) ?></p>
            <p><strong>Payment:</strong> <?= e(ucwords(str_replace('_', ' ', $order['payment_method']))) ?></p>
            <p><strong>Status:</strong> <span class="badge bg-warning"><?= e($order['order_status']) ?></span></p>
            <hr>
            <h6>Items:</h6>
            <?php foreach ($items as $item): ?>
            <p class="mb-1 small"><?= e($item['product_name']) ?> x<?= (int)$item['quantity'] ?> — <?= formatPrice((float)$item['line_total']) ?></p>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="mt-4">
        <a href="<?= BASE_URL ?>/orders.php" class="btn btn-primary">View Order History</a>
        <a href="<?= BASE_URL ?>/products.php" class="btn btn-outline-secondary">Continue Shopping</a>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
