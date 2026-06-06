<?php
require_once dirname(__DIR__) . '/includes/config.php';
session_destroy();
session_start();
header('Location: ../index.php');
exit;
