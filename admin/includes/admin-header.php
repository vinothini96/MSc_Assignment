<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__, 2) . '/includes/db.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';

$adminPage = basename($_SERVER['PHP_SELF'], '.php');
$isLoginPage = ($adminPage === 'login');

if (!$isLoginPage) {
    require_admin();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' | ' : '' ?>Admin - <?= e(APP_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>
<?php if (!$isLoginPage): ?>
<div class="admin-layout">
    <aside class="admin-sidebar py-3">
        <div class="px-3 mb-4">
            <a href="index.php" class="text-white text-decoration-none fw-bold">
                <i class="bi bi-gem"></i> Admin Panel
            </a>
        </div>
        <nav class="nav flex-column">
            <a class="nav-link <?= $adminPage === 'index' ? 'active' : '' ?>" href="index.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
            <a class="nav-link <?= in_array($adminPage, ['products', 'product-form']) ? 'active' : '' ?>" href="products.php"><i class="bi bi-box-seam me-2"></i> Products</a>
            <a class="nav-link <?= $adminPage === 'categories' ? 'active' : '' ?>" href="categories.php"><i class="bi bi-tags me-2"></i> Categories</a>
            <a class="nav-link <?= $adminPage === 'orders' ? 'active' : '' ?>" href="orders.php"><i class="bi bi-receipt me-2"></i> Orders</a>
            <a class="nav-link <?= $adminPage === 'users' ? 'active' : '' ?>" href="users.php"><i class="bi bi-people me-2"></i> Users</a>
            <hr class="border-secondary mx-3">
            <a class="nav-link" href="../index.php" target="_blank"><i class="bi bi-shop me-2"></i> View Store</a>
            <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
        </nav>
    </aside>
    <div class="admin-content">
        <?php $flash = get_flash(); if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show">
            <?= e($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
<?php endif; ?>
