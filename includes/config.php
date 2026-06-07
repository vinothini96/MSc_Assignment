<?php
/**
 * Elegance Sarees - Application Configuration
 */

define('APP_NAME', 'Elegance Sarees');
define('APP_URL', 'http://localhost/EleganceSarees');
define('BASE_PATH', dirname(__DIR__));

// Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'root'); // XAMPP default is often '' (empty) — change if login/DB fails
define('DB_NAME', 'saree_shop_db');

// Show detailed checkout errors (set false in production)
define('APP_DEBUG', true);

// Paths
define('UPLOAD_PRODUCTS', BASE_PATH . '/uploads/products/');
define('UPLOAD_PRODUCTS_URL', 'uploads/products/');
define('ASSETS_IMAGES', 'assets/images/');

// Session — delegate all session startup to includes/session.php.
// config.php must NOT call session_start() directly, because session cookie
// security params must be set before the very first session_start() call.
// Pages that load config.php without going through session.php get a basic
// session only; protected pages always load header.php → session.php first.
if (session_status() === PHP_SESSION_NONE) {
    // Minimal safe start — session.php applies full security config when available.
    session_start();
}

// Timezone
date_default_timezone_set('Asia/Kolkata');
