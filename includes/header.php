<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

$cartCount = get_cart_count($pdo);
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' | ' : '' ?><?= e(APP_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
<?php $flash = get_flash(); if ($flash): ?>
<div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show flash-alert" role="alert">
    <?= e($flash['message']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<nav class="navbar navbar-expand-lg navbar-elegance sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="bi bi-gem"></i> <?= e(APP_NAME) ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item"><a class="nav-link <?= $currentPage === 'index' ? 'active' : '' ?>" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link <?= $currentPage === 'shop' ? 'active' : '' ?>" href="shop.php">Shop</a></li>
                <li class="nav-item"><a class="nav-link <?= $currentPage === 'about' ? 'active' : '' ?>" href="about.php">About</a></li>
                <li class="nav-item"><a class="nav-link <?= $currentPage === 'contact' ? 'active' : '' ?>" href="contact.php">Contact</a></li>
            </ul>
            <form class="d-flex search-form me-lg-3 my-2 my-lg-0" action="shop.php" method="get" role="search">
                <input class="form-control form-control-sm" type="search" name="q" placeholder="Search sarees..." value="<?= e($_GET['q'] ?? '') ?>">
                <button class="btn btn-search btn-sm" type="submit"><i class="bi bi-search"></i></button>
            </form>
            <ul class="navbar-nav align-items-lg-center gap-1">
                <li class="nav-item">
                    <a class="nav-link cart-link" href="cart.php">
                        <i class="bi bi-bag"></i> Cart
                        <span class="badge bg-accent" id="cart-badge"><?= $cartCount ?></span>
                    </a>
                </li>
                <?php if (is_logged_in()): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> <?= e($_SESSION['user_name'] ?? 'Account') ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="profile.php">My Profile</a></li>
                        <li><a class="dropdown-item" href="orders.php">Order History</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="actions/logout.php">Logout</a></li>
                    </ul>
                </li>
                <?php else: ?>
                <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                <li class="nav-item"><a class="btn btn-accent btn-sm ms-lg-1" href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<main class="main-content">
