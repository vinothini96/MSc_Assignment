<?php
/**
 * AJAX Cart API - add, update, remove items
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/init.php';

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$action = $input['action'] ?? '';
$db = getDB();

// Guest cart in session
if (!isLoggedIn()) {
    if (!isset($_SESSION['guest_cart'])) $_SESSION['guest_cart'] = [];
    
    if ($action === 'add') {
        $pid = (int)($input['product_id'] ?? 0);
        $qty = max(1, (int)($input['quantity'] ?? 1));
        $_SESSION['guest_cart'][$pid] = ($_SESSION['guest_cart'][$pid] ?? 0) + $qty;
        echo json_encode(['success' => true, 'cart_count' => array_sum($_SESSION['guest_cart'])]);
        exit;
    }
    echo json_encode(['success' => false, 'redirect' => BASE_URL . '/login.php', 'message' => 'Please login']);
    exit;
}

$userId = (int)$_SESSION['user_id'];

if ($action === 'add') {
    $productId = (int)($input['product_id'] ?? 0);
    $quantity = max(1, (int)($input['quantity'] ?? 1));
    
    $stmt = $db->prepare('SELECT id, stock FROM products WHERE id = ? AND is_active = 1');
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }
    
    $stmt = $db->prepare('SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?');
    $stmt->execute([$userId, $productId]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        $newQty = $existing['quantity'] + $quantity;
        $db->prepare('UPDATE cart SET quantity = ? WHERE id = ?')->execute([$newQty, $existing['id']]);
    } else {
        $db->prepare('INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)')
            ->execute([$userId, $productId, $quantity]);
    }
    
    echo json_encode(['success' => true, 'cart_count' => getCartCount($db, $userId)]);
    
} elseif ($action === 'update') {
    $cartId = (int)($input['cart_id'] ?? 0);
    $quantity = max(1, (int)($input['quantity'] ?? 1));
    $db->prepare('UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?')
        ->execute([$quantity, $cartId, $userId]);
    echo json_encode(['success' => true]);
    
} elseif ($action === 'remove') {
    $cartId = (int)($input['cart_id'] ?? 0);
    $db->prepare('DELETE FROM cart WHERE id = ? AND user_id = ?')->execute([$cartId, $userId]);
    echo json_encode(['success' => true, 'cart_count' => getCartCount($db, $userId)]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
