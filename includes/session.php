<?php
/**
 * Secure session initialization with idle timeout and expiry enforcement.
 *
 * Constants (can be overridden before this file is included):
 *   SESSION_IDLE_TIMEOUT   — seconds of inactivity before the session expires (default 30 min)
 *   SESSION_ABSOLUTE_TTL   — maximum session lifetime regardless of activity (default 8 hours)
 *   SESSION_REGEN_INTERVAL — how often the session ID is regenerated (default 5 min)
 */
define('SESSION_IDLE_TIMEOUT',   defined('SESSION_IDLE_TIMEOUT')   ? SESSION_IDLE_TIMEOUT   : 120);  // 2 minutes
define('SESSION_ABSOLUTE_TTL',   defined('SESSION_ABSOLUTE_TTL')   ? SESSION_ABSOLUTE_TTL   : 28800); // 8 hours
define('SESSION_REGEN_INTERVAL', defined('SESSION_REGEN_INTERVAL') ? SESSION_REGEN_INTERVAL : 60);   // 1 minute

if (session_status() === PHP_SESSION_NONE) {
    // Apply security cookie params BEFORE session_start() so they take effect.
    // lifetime=0 means the cookie dies when the browser closes (no persistent cookie),
    // but we enforce server-side idle + absolute timeouts below.
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => false, // Set to true when serving over HTTPS
        'httponly' => true,  // Cookie not accessible via JavaScript (XSS protection)
        'samesite' => 'Lax', // Sent on same-site and top-level navigations (CSRF protection)
    ]);
    session_start();
}

$now = time();

// ── Idle timeout ────────────────────────────────────────────────────────────
// If the user has not made any request within SESSION_IDLE_TIMEOUT seconds,
// treat the session as expired and force a fresh one.
if (isset($_SESSION['last_activity']) && ($now - $_SESSION['last_activity']) > SESSION_IDLE_TIMEOUT) {
    // Wipe all session data, destroy the server-side file, and expire the cookie.
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', $now - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
    session_start(); // Fresh, empty session
}

// ── Absolute session TTL ─────────────────────────────────────────────────────
// Even if a user keeps clicking around, force logout after SESSION_ABSOLUTE_TTL
// seconds from when they first logged in. Prevents indefinitely-lived sessions.
if (isset($_SESSION['session_start_time']) && ($now - $_SESSION['session_start_time']) > SESSION_ABSOLUTE_TTL) {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', $now - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
    session_start();
}

// ── Initialise timestamps on brand-new sessions ──────────────────────────────
if (!isset($_SESSION['session_start_time'])) {
    $_SESSION['session_start_time'] = $now;
}

// Update last activity on every request so the idle timer resets correctly.
$_SESSION['last_activity'] = $now;

// ── Periodic session ID regeneration (anti-fixation) ────────────────────────
// Rotate the session ID every SESSION_REGEN_INTERVAL seconds.
// This limits the window an attacker has to use a stolen/fixated session ID.
if (!isset($_SESSION['_regen_time'])) {
    $_SESSION['_regen_time'] = $now;
} elseif (($now - $_SESSION['_regen_time']) > SESSION_REGEN_INTERVAL) {
    session_regenerate_id(true); // true = delete the old session file immediately
    $_SESSION['_regen_time'] = $now;
}
