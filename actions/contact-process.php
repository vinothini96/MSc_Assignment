<?php
require_once dirname(__DIR__) . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../contact.php');
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

if (strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($message) < 10) {
    flash('danger', 'Please fill the contact form correctly.');
    redirect('../contact.php');
}

// In production, send email; for demo we acknowledge
flash('success', 'Thank you, ' . $name . '! We will respond to your message soon.');
redirect('../contact.php');
