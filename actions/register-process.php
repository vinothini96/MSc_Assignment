<?php
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../register.php');
}

$fullName = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';
$address = trim($_POST['address'] ?? '');
$city = trim($_POST['city'] ?? '');
$district = trim($_POST['district'] ?? '');
$pincode = trim($_POST['pincode'] ?? '');

$errors = [];

if (strlen($fullName) < 2) {
    $errors[] = 'Full name must be at least 2 characters.';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address.';
}
if (!preg_match('/^[0-9]{10}$/', preg_replace('/\D/', '', $phone))) {
    $errors[] = 'Phone must be 10 digits.';
}
if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
    $errors[] = 'Password must be 8+ chars with upper, lower, and number.';
}
if ($password !== $confirm) {
    $errors[] = 'Passwords do not match.';
}

if ($errors) {
    flash('danger', implode(' ', $errors));
    redirect('../register.php');
}

$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    flash('danger', 'Email already registered.');
    redirect('../register.php');
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $pdo->prepare('INSERT INTO users (full_name, email, phone, password, address, city, district, pincode) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
$stmt->execute([$fullName, $email, $phone, $hash, $address, $city, $district, $pincode]);

$userId = (int) $pdo->lastInsertId();
$_SESSION['user_id'] = $userId;
$_SESSION['user_name'] = $fullName;
$_SESSION['user_email'] = $email;

sync_guest_cart_to_db($pdo, $userId);

flash('success', 'Registration successful! Welcome to Elegance Sarees.');
redirect('../index.php');
