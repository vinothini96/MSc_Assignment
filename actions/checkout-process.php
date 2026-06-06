<?php
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../checkout.php');
}

$address = trim($_POST['shipping_address'] ?? '');
$city = trim($_POST['city'] ?? '');
$district = trim($_POST['district'] ?? '');
$pincode = trim($_POST['pincode'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$payment = $_POST['payment_method'] ?? 'cod';
$notes = trim($_POST['notes'] ?? '');
$usePoints = isset($_POST['use_loyalty_points']);

$userId = (int) $_SESSION['user_id'];

if ($address === '' || $city === '' || $district === '' || !preg_match('/^[0-9]{5}$/', $pincode) || !preg_match('/^[0-9]{10}$/', preg_replace('/\D/', '', $phone))) {
    flash('danger', 'Please fill all shipping details correctly.');

    redirect('../checkout.php');
}

$stmt = $pdo->prepare('
    SELECT c.id AS cart_id, c.quantity, p.id, p.name, p.price, p.discount_price, p.stock
    FROM cart c
    JOIN products p ON p.id = c.product_id
    WHERE c.user_id = ? AND p.status = ?
');
$stmt->execute([$userId, 'active']);
$items = $stmt->fetchAll();

if (!$items) {
    flash('warning', 'Your cart is empty.');
    redirect('../cart.php');
}

$subtotal = 0;
foreach ($items as $item) {
    if ($item['quantity'] > $item['stock']) {
        flash('danger', $item['name'] . ' has insufficient stock.');
        redirect('../cart.php');
    }
    $price = $item['discount_price'] ?? $item['price'];
    $subtotal += $price * $item['quantity'];
}

$discount = 0;
if ($usePoints) {
    $points = get_user_loyalty_total($pdo, $userId);
    $discount = min($subtotal * 0.1, $points); // max 10% or points value (1 point = Rs.1)
    if ($discount > 0) {
        $pdo->prepare('UPDATE users SET loyalty_points = loyalty_points - ? WHERE id = ?')
            ->execute([(int) $discount, $userId]);
        $pdo->prepare('INSERT INTO loyalty_points (user_id, points, description) VALUES (?, ?, ?)')
            ->execute([$userId, -(int) $discount, 'Redeemed on checkout']);
    }
}

$total = max(0, $subtotal - $discount);
$orderNumber = generate_order_number();

try {
    $pdo->beginTransaction();

    $orderStmt = $pdo->prepare('
        INSERT INTO orders (user_id, order_number, total_amount, discount_amount, shipping_address, city, district, pincode, phone, payment_method, notes, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $orderStmt->execute([
        $userId, $orderNumber, $total, $discount, $address, $city, $district, $pincode, $phone, $payment, $notes, 'pending'
    ]);
    $orderId = (int) $pdo->lastInsertId();

    foreach ($items as $item) {
        $unitPrice = $item['discount_price'] ?? $item['price'];
        $lineTotal = $unitPrice * $item['quantity'];
        $pdo->prepare('INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?, ?)')
            ->execute([$orderId, $item['id'], $item['name'], $item['quantity'], $unitPrice, $lineTotal]);
        $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ?')
            ->execute([$item['quantity'], $item['id']]);
    }

    $pdo->prepare('DELETE FROM cart WHERE user_id = ?')->execute([$userId]);

    award_loyalty_points($pdo, $userId, $orderId, $total);

    $pdo->commit();

    flash('success', 'Order placed successfully! Order #' . $orderNumber);
    redirect('../orders.php');
} catch (Exception $e) {
    $pdo->rollBack();
    flash('danger', 'Could not place order. Please try again.');
    redirect('../checkout.php');
}
