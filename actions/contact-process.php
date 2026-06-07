<?php
/**
 * Contact Form Processor
 *
 * Validates the submitted contact form and saves the message to the
 * contact_messages table in the database.
 *
 * - Saves user_id if the visitor is logged in (enables reply tracking).
 * - Marks the message as 'unread' so the admin inbox badge works.
 * - All queries use PDO prepared statements.
 */

require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../contact.php');
}

// ── Sanitise inputs ───────────────────────────────────────────────────────────
$name    = trim($_POST['name']    ?? '');
$email   = trim($_POST['email']   ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

// ── Server-side validation ────────────────────────────────────────────────────
$errors = [];

if (strlen($name) < 2) {
    $errors[] = 'Please enter your name (at least 2 characters).';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address.';
}
if (strlen($message) < 10) {
    $errors[] = 'Message must be at least 10 characters.';
}

if ($errors) {
    flash('danger', implode(' ', $errors));
    redirect('../contact.php');
}

// ── Save to database ──────────────────────────────────────────────────────────
// Link to the logged-in user so they can view the reply on the contact page.
$userId = is_logged_in() ? (int) $_SESSION['user_id'] : null;

$stmt = $pdo->prepare(
    'INSERT INTO contact_messages (user_id, name, email, subject, message, status)
     VALUES (?, ?, ?, ?, ?, ?)'
);
$stmt->execute([
    $userId,
    $name,
    $email,
    $subject ?: null,   // store NULL if no subject entered
    $message,
    'unread',
]);

// ── Success response ──────────────────────────────────────────────────────────
if (is_logged_in()) {
    flash('success',
        'Thank you, ' . $name . '! Your message has been sent. ' .
        'You can track the reply below under "My Messages".'
    );
} else {
    flash('success',
        'Thank you, ' . $name . '! We have received your message and will ' .
        'respond to ' . $email . ' as soon as possible.'
    );
}

redirect('../contact.php');
