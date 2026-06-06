<?php
/**
 * Search autocomplete API
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/init.php';

$q = trim($_GET['q'] ?? '');
if (strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

$db = getDB();
$stmt = $db->prepare('SELECT id, name, price, sale_price FROM products 
    WHERE is_active = 1 AND name LIKE ? LIMIT 8');
$stmt->execute(['%' . $q . '%']);
$results = [];
while ($row = $stmt->fetch()) {
    $row['price'] = number_format(getProductPrice($row), 2);
    $results[] = $row;
}
echo json_encode($results);
