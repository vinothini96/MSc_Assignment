<?php
/**
 * Admin panel header with sidebar navigation
 */
requireAdmin();
if (!isset($adminPageTitle)) $adminPageTitle = 'Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($adminPageTitle) ?> | Admin - <?= e(APP_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/admin.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= BASE_URL ?>/admin/index.php"><i class="bi bi-speedometer2"></i> Admin Panel</a>
        <span class="text-light"><?= e($_SESSION['admin_name'] ?? 'Admin') ?></span>
        <a href="<?= BASE_URL ?>/admin/logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
</nav>
<div class="container-fluid">
    <div class="row">
        <nav class="col-md-2 admin-sidebar">
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/index.php"><i class="bi bi-grid"></i> Dashboard</a></li>
                <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'products.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/products.php"><i class="bi bi-box"></i> Products</a></li>
                <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'categories.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/categories.php"><i class="bi bi-tags"></i> Categories</a></li>
                <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'orders.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/orders.php"><i class="bi bi-receipt"></i> Orders</a></li>
                <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'coupons.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/coupons.php"><i class="bi bi-ticket"></i> Coupons</a></li>
                <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'banners.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/banners.php"><i class="bi bi-image"></i> Banners</a></li>
                <li class="nav-item mt-3"><a class="nav-link" href="<?= BASE_URL ?>/index.php" target="_blank"><i class="bi bi-shop"></i> View Store</a></li>
            </ul>
        </nav>
        <main class="col-md-10 py-4 px-4">
        <?php
        $s = getFlash('success'); $e = getFlash('error');
        if ($s): ?><div class="alert alert-success"><?= e($s) ?></div><?php endif;
        if ($e): ?><div class="alert alert-danger"><?= e($e) ?></div><?php endif; ?>
