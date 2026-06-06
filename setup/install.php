<?php
/**
 * One-time setup: generates placeholder images and resets demo passwords.
 * Run once: http://localhost/EleganceSarees/setup/install.php
 */
require_once dirname(__DIR__) . '/includes/config.php';

$dirs = [
    BASE_PATH . '/assets/images/sarees',
    BASE_PATH . '/assets/images/banners',
    BASE_PATH . '/assets/images/products',
    BASE_PATH . '/assets/images/users',
    BASE_PATH . '/uploads/products',
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

function create_placeholder_image(string $path, string $label, array $rgb): void
{
    if (file_exists($path)) {
        return;
    }
    if (!function_exists('imagecreatetruecolor')) {
        return;
    }
    $w = 600;
    $h = 800;
    $img = imagecreatetruecolor($w, $h);
    $bg = imagecolorallocate($img, $rgb[0], $rgb[1], $rgb[2]);
    $accent = imagecolorallocate($img, 212, 165, 116);
    $text = imagecolorallocate($img, 255, 255, 255);
    imagefill($img, 0, 0, $bg);
    imagerectangle($img, 20, 20, $w - 20, $h - 20, $accent);
    imagestring($img, 5, (int)(($w - strlen($label) * 9) / 2), (int)($h / 2), $label, $text);
    imagejpeg($img, $path, 85);
    imagedestroy($img);
}

$images = [
    ['assets/images/sarees/silk_saree_1.jpg', 'Silk Saree', [139, 34, 82]],
    ['assets/images/sarees/bridal_saree.jpg', 'Bridal Saree', [180, 40, 90]],
    ['assets/images/sarees/cotton_saree.jpg', 'Cotton Saree', [100, 120, 80]],
    ['assets/images/sarees/designer_saree.jpg', 'Designer', [120, 60, 100]],
    ['assets/images/products/default_saree.jpg', 'Elegance', [139, 34, 82]],
    ['assets/images/banners/hero_banner_1.jpg', 'Festive Sale', [107, 26, 64]],
    ['assets/images/banners/hero_banner_2.jpg', 'Bridal Sale', [160, 50, 90]],
    ['assets/images/banners/hero_banner_3.jpg', 'New Arrivals', [90, 50, 70]],
    ['assets/images/banners/about_banner.jpg', 'About Us', [80, 30, 50]],
    ['assets/images/users/default_avatar.jpg', 'User', [200, 180, 190]],
];

foreach ($images as [$rel, $label, $rgb]) {
    create_placeholder_image(BASE_PATH . '/' . $rel, $label, $rgb);
}

$messages = ['Placeholder images created (or already exist).'];

try {
    require_once dirname(__DIR__) . '/includes/db.php';
    $adminHash = password_hash('Admin@123', PASSWORD_DEFAULT);
    $userHash = password_hash('User@123', PASSWORD_DEFAULT);
    $pdo->prepare('UPDATE admins SET password = ? WHERE username = ?')->execute([$adminHash, 'admin']);
    $pdo->prepare('UPDATE users SET password = ?')->execute([$userHash]);
    $messages[] = 'Demo passwords set: Admin = Admin@123, Users = User@123';
} catch (Exception $e) {
    $messages[] = 'Database not ready. Import database/saree_shop_db.sql first, then run this again.';
}

header('Content-Type: text/html; charset=utf-8');
echo '<h1>Elegance Sarees Setup</h1><ul>';
foreach ($messages as $m) {
    echo '<li>' . htmlspecialchars($m) . '</li>';
}
echo '</ul><p><a href="../index.php">Go to website</a></p>';
