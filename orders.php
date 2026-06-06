<?php
$pageTitle = 'Order History';
require_once 'includes/header.php';
require_login();

$stmt = $pdo->prepare('
    SELECT o.*, (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) AS item_count
    FROM orders o WHERE o.user_id = ? ORDER BY o.created_at DESC
');
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();
?>

<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb breadcrumb-elegance mb-0">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active">Orders</li>
        </ol>
    </nav>

    <h1 class="section-title mt-3">Order History</h1>

    <?php if (!$orders): ?>
    <div class="alert alert-info">You have not placed any orders yet. <a href="shop.php">Start shopping</a></div>
    <?php else: ?>
    <div class="accordion" id="ordersAccordion">
        <?php foreach ($orders as $i => $order):
            $itemsStmt = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ?');
            $itemsStmt->execute([$order['id']]);
            $orderItems = $itemsStmt->fetchAll();
            $statusClass = match ($order['status']) {
                'delivered' => 'success',
                'cancelled' => 'danger',
                'shipped', 'processing', 'confirmed' => 'primary',
                default => 'warning',
            };
        ?>
        <div class="accordion-item mb-2 border-0 shadow-sm rounded overflow-hidden">
            <h2 class="accordion-header">
                <button class="accordion-button <?= $i > 0 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#order-<?= $order['id'] ?>">
                    <span class="me-3"><strong>#<?= e($order['order_number']) ?></strong></span>
                    <span class="me-3"><?= date('d M Y', strtotime($order['created_at'])) ?></span>
                    <span class="badge bg-<?= $statusClass ?> me-3"><?= ucfirst(e($order['status'])) ?></span>
                    <span class="ms-auto me-3"><?= format_price((float) $order['total_amount']) ?></span>
                </button>
            </h2>
            <div id="order-<?= $order['id'] ?>" class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>" data-bs-parent="#ordersAccordion">
                <div class="accordion-body bg-white">
                    <p class="small text-muted mb-2">
                        <?= e($order['shipping_address']) ?>, <?= e($order['city']) ?>, <?= e($order['state']) ?> - <?= e($order['pincode']) ?>
                    </p>
                    <table class="table table-sm">
                        <thead><tr><th>Item</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr></thead>
                        <tbody>
                            <?php foreach ($orderItems as $oi): ?>
                            <tr>
                                <td><?= e($oi['product_name']) ?></td>
                                <td><?= (int) $oi['quantity'] ?></td>
                                <td><?= format_price((float) $oi['unit_price']) ?></td>
                                <td><?= format_price((float) $oi['subtotal']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if ($order['discount_amount'] > 0): ?>
                    <p class="small text-success">Loyalty discount applied: <?= format_price((float) $order['discount_amount']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php';
