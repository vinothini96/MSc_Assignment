<?php
/**
 * Admin logout — wipe the entire session and expire the cookie.
 * Unsetting only admin keys is not sufficient: the session file would remain
 * on the server and a shared-browser attacker could restore it via the cookie.
 */
require_once dirname(__DIR__) . '/includes/config.php';

// Clear all session data (removes both admin and user keys).
$_SESSION = [];

// Expire the session cookie in the browser so it is not sent on future requests.
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}

// Destroy the server-side session file.
session_destroy();

header('Location: login.php');
exit;
