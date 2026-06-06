<?php
/**
 * Cart AJAX / form handler
 */
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$productId = (int) ($_POST['product_id'] ?? $_GET['product_id'] ?? 0);
$quantity = max(1, (int) ($_POST['quantity'] ?? 1));

if ($productId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product.']);
    exit;
}

$stmt = $pdo->prepare('SELECT id, name, stock, status FROM products WHERE id = ? AND status = ?');
$stmt->execute([$productId, 'active']);
$product = $stmt->fetch();

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not found.']);
    exit;
}

switch ($action) {
    case 'add':
        if ($quantity > $product['stock']) {
            echo json_encode(['success' => false, 'message' => 'Insufficient stock.']);
            exit;
        }
        if (is_logged_in()) {
            $stmt = $pdo->prepare('SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?');
            $stmt->execute([$_SESSION['user_id'], $productId]);
            $row = $stmt->fetch();
            if ($row) {
                $newQty = min($product['stock'], $row['quantity'] + $quantity);
                $pdo->prepare('UPDATE cart SET quantity = ? WHERE id = ?')->execute([$newQty, $row['id']]);
            } else {
                $pdo->prepare('INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)')
                    ->execute([$_SESSION['user_id'], $productId, $quantity]);
            }
        } else {
            if (!isset($_SESSION['guest_cart'])) {
                $_SESSION['guest_cart'] = [];
            }
            $current = $_SESSION['guest_cart'][$productId]['quantity'] ?? 0;
            $_SESSION['guest_cart'][$productId] = [
                'product_id' => $productId,
                'quantity' => min($product['stock'], $current + $quantity),
            ];
        }
        echo json_encode(['success' => true, 'message' => 'Added to cart.', 'cart_count' => get_cart_count($pdo)]);
        break;

    case 'update':
        if (is_logged_in()) {
            $cartId = (int) ($_POST['cart_id'] ?? 0);
            if ($quantity > $product['stock']) {
                echo json_encode(['success' => false, 'message' => 'Max stock: ' . $product['stock']]);
                exit;
            }
            $pdo->prepare('UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?')
                ->execute([$quantity, $cartId, $_SESSION['user_id']]);
        } else {
            if (isset($_SESSION['guest_cart'][$productId])) {
                $_SESSION['guest_cart'][$productId]['quantity'] = min($product['stock'], $quantity);
            }
        }
        echo json_encode(['success' => true, 'cart_count' => get_cart_count($pdo)]);
        break;

    case 'remove':
        if (is_logged_in()) {
            $cartId = (int) ($_POST['cart_id'] ?? 0);
            $pdo->prepare('DELETE FROM cart WHERE id = ? AND user_id = ?')->execute([$cartId, $_SESSION['user_id']]);
        } else {
            unset($_SESSION['guest_cart'][$productId]);
        }
        echo json_encode(['success' => true, 'message' => 'Item removed.', 'cart_count' => get_cart_count($pdo)]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
}
