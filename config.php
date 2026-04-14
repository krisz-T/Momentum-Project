<?php
// config.php
session_start();

$host = 'localhost';
$db   = 'kry_momentum_db';
$user = 'kry_momentum_user_db'; // Change for production
$pass = 'Sty*al$_oAGh';     // Change for production
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Database connection failed. Please configure config.php.");
}

function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

function require_admin() {
    if (($_SESSION['role'] ?? '') !== 'Admin') {
        die('Forbidden: Admin access required.');
    }
}

function set_flash_message($type, $message) {
    $_SESSION['flash'][$type] = $message;
}

function get_flash_message($type) {
    if (isset($_SESSION['flash'][$type])) {
        $msg = $_SESSION['flash'][$type];
        unset($_SESSION['flash'][$type]);
        return $msg;
    }
    return null;
}
?>
