<?php
$pageTitle = 'Orders';
require_once 'includes/admin-header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $orderId = (int) $_POST['order_id'];
    $status = $_POST['status'];
    $allowed = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
    if (in_array($status, $allowed, true)) {
        $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute([$status, $orderId]);
        flash('success', 'Order status updated.');
    }
    redirect('orders.php' . ($orderId ? '?id=' . $orderId : ''));
}

$viewId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$orders = $pdo->query('
    SELECT o.*, u.full_name, u.email FROM orders o
    JOIN users u ON u.id = o.user_id ORDER BY o.created_at DESC
')->fetchAll();

$viewOrder = null;
$viewItems = [];
if ($viewId) {
    $stmt = $pdo->prepare('SELECT o.*, u.full_name, u.email, u.phone AS user_phone FROM orders o JOIN users u ON u.id = o.user_id WHERE o.id = ?');
    $stmt->execute([$viewId]);
    $viewOrder = $stmt->fetch();
    if ($viewOrder) {
        $itemsStmt = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ?');
        $itemsStmt->execute([$viewId]);
        $viewItems = $itemsStmt->fetchAll();
    }
}
?>

<h1 class="h3 mb-4" style="color:var(--primary)">Order Management</h1>

<?php if ($viewOrder): ?>
<div class="chart-container mb-4">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h5>Order #<?= e($viewOrder['order_number']) ?></h5>
            <p class="mb-0 text-muted"><?= e($viewOrder['full_name']) ?> — <?= e($viewOrder['email']) ?></p>
            <p class="small"><?= e($viewOrder['shipping_address']) ?>, <?= e($viewOrder['city']) ?>, <?= e($viewOrder['district']) ?> <?= e($viewOrder['pincode']) ?></p>
        </div>
        <form method="post" class="d-flex gap-2 align-items-center">
            <input type="hidden" name="order_id" value="<?= $viewOrder['id'] ?>">
            <select name="status" class="form-select form-select-sm">
                <?php foreach (['pending','confirmed','processing','shipped','delivered','cancelled'] as $s): ?>
                <option value="<?= $s ?>" <?= $viewOrder['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-sm btn-primary-elegance">Update</button>
        </form>
    </div>
    <table class="table mt-3">
        <thead><tr><th>Product</th><th>Qty</th><th>Unit</th><th>Subtotal</th></tr></thead>
        <tbody>
            <?php foreach ($viewItems as $item): ?>
            <tr>
                <td><?= e($item['product_name']) ?></td>
                <td><?= $item['quantity'] ?></td>
                <td><?= format_price((float) $item['unit_price']) ?></td>
                <td><?= format_price((float) $item['subtotal']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr><th colspan="3" class="text-end">Total</th><th><?= format_price((float) $viewOrder['total_amount']) ?></th></tr>
        </tfoot>
    </table>
    <a href="orders.php" class="btn btn-sm btn-secondary">← All Orders</a>
</div>
<?php endif; ?>

<div class="chart-container">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr><th>Order #</th><th>Customer</th><th>Total</th><th>Payment</th><th>Status</th><th>Date</th><th></th></tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $o): ?>
                <tr>
                    <td><?= e($o['order_number']) ?></td>
                    <td><?= e($o['full_name']) ?></td>
                    <td><?= format_price((float) $o['total_amount']) ?></td>
                    <td><?= strtoupper(e($o['payment_method'])) ?></td>
                    <td><span class="badge bg-secondary"><?= e($o['status']) ?></span></td>
                    <td><?= date('d M Y H:i', strtotime($o['created_at'])) ?></td>
                    <td><a href="orders.php?id=<?= $o['id'] ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/admin-footer.php';
