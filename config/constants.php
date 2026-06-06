<?php
/**
 * Application-wide constants
 */
define('APP_NAME', 'EleganceSarees');
define('APP_TAGLINE', 'Your Trusted Online Store');
define('BASE_URL', '/EleganceSarees'); // Change if folder name differs in htdocs
define('UPLOAD_PATH', __DIR__ . '/../uploads/products/');
define('UPLOAD_URL', BASE_URL . '/uploads/products/');
define('ITEMS_PER_PAGE', 12);
define('LOYALTY_POINTS_PER_DOLLAR', 1); // Earn 1 point per $1 spent
define('LOYALTY_REDEEM_RATE', 100); // 100 points = $1 discount
