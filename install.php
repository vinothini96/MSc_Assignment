<?php
/**
 * One-time setup script - run after importing database
 * Visit: http://localhost/EleganceSarees/install.php
 * DELETE this file after setup for security!
 */
require_once __DIR__ . '/config/database.php';

$messages = [];
try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set demo passwords
    $adminHash = password_hash('admin123', PASSWORD_DEFAULT);
    $userHash = password_hash('user123', PASSWORD_DEFAULT);
    
    $pdo->prepare('UPDATE admin SET password = ? WHERE username = ?')->execute([$adminHash, 'admin']);
    $pdo->prepare('UPDATE users SET password = ?')->execute([$userHash]);
    
    $messages[] = 'Passwords updated successfully!';
    $messages[] = 'Admin login: admin / admin123';
    $messages[] = 'User login: john@example.com / user123';
    $messages[] = 'IMPORTANT: Delete install.php after setup!';
} catch (PDOException $e) {
    $messages[] = 'Error: ' . $e->getMessage();
    $messages[] = 'Import database/saree_shop_db.sql first via phpMyAdmin.';
}
?>
<!DOCTYPE html>
<html><head><title>EleganceSarees Install</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-5">
<div class="container" style="max-width:600px">
    <h2>EleganceSarees Installation</h2>
    <?php foreach ($messages as $m): ?>
    <div class="alert alert-info"><?= htmlspecialchars($m) ?></div>
    <?php endforeach; ?>
    <a href="index.php" class="btn btn-primary">Go to Store</a>
    <a href="admin/login.php" class="btn btn-warning">Admin Login</a>
</div>
</body></html>
