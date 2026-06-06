<?php
require_once dirname(__DIR__) . '/includes/config.php';
unset($_SESSION['admin_id'], $_SESSION['admin_name']);
header('Location: login.php');
exit;
