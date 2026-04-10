<?php
// config/db.php
date_default_timezone_set('Asia/Kathmandu');
require_once __DIR__ . '/env.php';

try {
    $pdo = new PDO(
        "mysql:host=" . env('DB_HOST', 'localhost') . ";dbname=" . env('DB_NAME', 'ars_ecommerce') . ";charset=utf8mb4",
        env('DB_USER', 'root'),
        env('DB_PASSWORD', '')
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Sync MySQL timezone with PHP (Asia/Kathmandu: +05:45)
    $pdo->exec("SET time_zone = '+05:45'");
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

// ── Remember-Me Auto-Login ────────────────────────────────────────
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
    $parts = explode(':', $_COOKIE['remember_me'], 2);
    if (count($parts) === 2 && is_numeric($parts[0])) {
        $userId    = (int)$parts[0];
        $rawToken  = $parts[1];
        $hashedTok = hash('sha256', $rawToken);
        try {
            $rmStmt = $pdo->prepare(
                "SELECT id, full_name, email, role
                 FROM users
                 WHERE id = ? AND remember_token = ?"
            );
            $rmStmt->execute([$userId, $hashedTok]);
            $rmUser = $rmStmt->fetch();
            if ($rmUser) {
                session_regenerate_id(true);
                $_SESSION['user_id']    = $rmUser['id'];
                $_SESSION['user_name']  = $rmUser['full_name'];
                $_SESSION['user_email'] = $rmUser['email'];
                $_SESSION['role']       = $rmUser['role'];
                $_SESSION['login_time'] = time();
                $_SESSION['LAST_ACTIVITY'] = time();
            } else {
                // Invalid token — clear the bad cookie
                $cookieParams = session_get_cookie_params();
                setcookie('remember_me', '', time() - 3600,
                          $cookieParams['path'] ?: '/',
                          $cookieParams['domain'], $cookieParams['secure'], true);
            }
        } catch (PDOException $e) {
            error_log("Remember-me auto-login error: " . $e->getMessage());
        }
    }
}

define('SITE_NAME', env('SITE_NAME', 'ARS Shop'));
define('SITE_URL', env('SITE_URL', 'http://localhost/ARS/'));
define('CURRENCY', env('CURRENCY', 'Rs. '));
define('UPLOAD_DIR', env('UPLOAD_DIR', 'uploads/'));
define('FREE_SHIPPING_THRESHOLD', (float)env('FREE_SHIPPING_THRESHOLD', 1000));
define('SHIPPING_FEE', (float)env('SHIPPING_FEE', 150));
