<?php
session_start();
date_default_timezone_set('Asia/Kolkata');

function setting(string $name, string $default = ''): string {
    $value = getenv($name);
    return $value === false ? $default : $value;
}

define('DB_HOST', setting('DB_HOST', 'localhost'));
define('DB_NAME', setting('DB_NAME', 'billing_db'));
define('DB_USER', setting('DB_USER'));
define('DB_PASS', setting('DB_PASS'));

define('SHOP_NAME', setting('SHOP_NAME', 'Your Shop'));
define('SHOP_TAG', setting('SHOP_TAG', ''));
define('SHOP_ADDR', setting('SHOP_ADDR', ''));
define('SHOP_PHONE', setting('SHOP_PHONE', ''));

try {
    $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4', DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
} catch (PDOException $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    die('Database connection failed. Check the server configuration.');
}

function money($n){ return '₹' . number_format((float)$n, 2); }
function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// Create the initial admin only when a strong password is supplied in the environment.
$adminName = setting('APP_ADMIN_USERNAME');
$adminPassword = setting('APP_ADMIN_PASSWORD');
if ($adminName !== '' && strlen($adminPassword) >= 12 && $pdo->query("SHOW TABLES LIKE 'users'")->fetch()) {
    $check = $pdo->prepare('SELECT id FROM users WHERE username = ?');
    $check->execute([$adminName]);
    if (!$check->fetch()) {
        $pdo->prepare('INSERT INTO users(username,password) VALUES(?,?)')
            ->execute([$adminName, password_hash($adminPassword, PASSWORD_DEFAULT)]);
    }
}

function require_login(){
    if (empty($_SESSION['user'])) { header('Location: login.php'); exit; }
}
