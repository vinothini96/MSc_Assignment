<?php
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/EleganceSarees/login.php');
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Default redirect
$redirect = $_POST['redirect'] ?? '/EleganceSarees/index.php';

// Make sure redirect always starts with /
if (strpos($redirect, '/') !== 0) {
    $redirect = '/EleganceSarees/index.php';
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
    flash('danger', 'Invalid email or password.');
    redirect('/EleganceSarees/login.php');
}

$stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? AND status = ?');
$stmt->execute([$email, 'active']);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    flash('danger', 'Invalid email or password.');

    redirect('/EleganceSarees/login.php?redirect=' . urlencode($redirect));
}

$_SESSION['user_id']    = (int) $user['id'];
$_SESSION['user_name']  = $user['full_name'];
$_SESSION['user_email'] = $user['email'];

// Stamp session timestamps so idle timeout and absolute TTL work correctly.
// These are read by includes/session.php on every subsequent request.
$_SESSION['session_start_time'] = time();
$_SESSION['last_activity']      = time();
$_SESSION['_regen_time']        = time();

// Sync guest cart
sync_guest_cart_to_db($pdo, (int) $user['id']);

flash('success', 'Welcome back, ' . $user['full_name'] . '!');

// Final redirect
redirect($redirect);
?>