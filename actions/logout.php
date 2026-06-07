<?php
/**
 * Customer logout — full session teardown.
 */
require_once dirname(__DIR__) . '/includes/config.php';

// Clear all session variables.
$_SESSION = [];

// Expire the session cookie in the browser.
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}

// Destroy the server-side session file.
session_destroy();

header('Location: ../index.php');
exit;
