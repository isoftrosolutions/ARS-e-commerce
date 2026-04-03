<?php
// config/db.php
$host = 'localhost';
$dbname = 'ars_ecommerce';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    // Set PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Use associative arrays for results
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // In production, log error instead of die
    die("Database Connection Error: " . $e->getMessage());
}

// Start global session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define some site-wide constants
define('SITE_NAME', 'Nepal E-Shop');
define('CURRENCY', 'Rs. ');
define('UPLOAD_DIR', 'uploads/');
?>
