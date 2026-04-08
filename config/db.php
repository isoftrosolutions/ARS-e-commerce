<?php
// config/db.php
require_once __DIR__ . '/env.php';


$host = 'localhost';
$dbname = 'ektamultp_ars-ecommerce';
$username = 'ektamultp_user';
$password = 'U@retheone1';

$db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database Connection Error: " . $e->getMessage());
    die("Unable to connect to database. Please try again later.");
}

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Strict');
    session_start();
    
    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > env('SESSION_LIFETIME', 7200))) {
        session_unset();
        session_destroy();
        session_start();
    }
    $_SESSION['LAST_ACTIVITY'] = time();
}

define('SITE_NAME', env('SITE_NAME', 'ARS Shop'));
define('SITE_URL', env('SITE_URL', 'http://localhost/ARS'));
define('CURRENCY', env('CURRENCY', 'Rs. '));
define('UPLOAD_DIR', env('UPLOAD_DIR', 'uploads/'));
define('FREE_SHIPPING_THRESHOLD', (float)env('FREE_SHIPPING_THRESHOLD', 1000));
define('SHIPPING_FEE', (float)env('SHIPPING_FEE', 150));
