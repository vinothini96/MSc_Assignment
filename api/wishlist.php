<?php
/**
 * Wishlist toggle API
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/init.php';

if (!isLoggedIn()) {
    echo json_encode(['redirect' => BASE_URL . '/login.php']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$productId = (int)($input['product_id'] ?? 0);
$userId = (int)$_SESSION['user_id'];
$db = getDB();

$stmt = $db->prepare('SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?');
$stmt->execute([$userId, $productId]);
$exists = $stmt->fetch();

if ($exists) {
    $db->prepare('DELETE FROM wishlist WHERE id = ?')->execute([$exists['id']]);
    echo json_encode(['success' => true, 'added' => false]);
} else {
    $db->prepare('INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)')->execute([$userId, $productId]);
    echo json_encode(['success' => true, 'added' => true]);
}
