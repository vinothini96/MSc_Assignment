<?php
/**
 * Wishlist Page
 */
$pageTitle = 'Wishlist';
require_once __DIR__ . '/includes/init.php';
requireLogin();
$db = getDB();

if (isset($_GET['remove'])) {
    $db->prepare('DELETE FROM wishlist WHERE user_id = ? AND product_id = ?')
        ->execute([$_SESSION['user_id'], (int)$_GET['remove']]);
    setFlash('success', 'Removed from wishlist.');
    redirect(BASE_URL . '/wishlist.php');
}

$stmt = $db->prepare('SELECT p.* FROM wishlist w JOIN products p ON w.product_id = p.id WHERE w.user_id = ?');
$stmt->execute([$_SESSION['user_id']]);
$products = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
    <h2><i class="bi bi-heart"></i> My Wishlist</h2>
    <?php if (count($products) === 0): ?>
        <div class="alert alert-info mt-3">Wishlist is empty.</div>
    <?php else: ?>
    <div class="row mt-3">
        <?php foreach ($products as $product): ?>
            <?php include __DIR__ . '/includes/product-card.php'; ?>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
