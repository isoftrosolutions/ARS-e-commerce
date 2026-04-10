<?php
require_once __DIR__ . '/../includes/functions.php';

// Clear remember_me cookie and DB token if present
if (isset($_COOKIE['remember_me'])) {
    $parts = explode(':', $_COOKIE['remember_me'], 2);
    if (count($parts) === 2 && is_numeric($parts[0])) {
        try {
            $pdo->prepare("UPDATE users SET remember_token = NULL WHERE id = ?")
                ->execute([(int)$parts[0]]);
        } catch (PDOException $e) {
            error_log("Logout: failed to clear remember_token: " . $e->getMessage());
        }
    }
    // Expire the cookie immediately
    $cookieParams = session_get_cookie_params();
    setcookie('remember_me', '', time() - 3600, $cookieParams['path'] ?: '/',
              $cookieParams['domain'], $cookieParams['secure'], true);
}

// Clear session data in memory
$_SESSION = [];

// Expire the session cookie on the client
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
              $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}

session_destroy();

redirect('../index.php', 'You have been logged out.', 'success');

