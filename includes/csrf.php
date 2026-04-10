<?php
// includes/csrf.php - CSRF Protection System

require_once __DIR__ . '/../config/env.php';

function csrf_token(): string {
    if (!isset($_SESSION['csrf_token']) || 
        !isset($_SESSION['csrf_token_time']) ||
        (time() - $_SESSION['csrf_token_time']) > (int)env('CSRF_TOKEN_LIFETIME', 3600)) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

function validate_csrf($token = null): bool {
    $token = $token ?? ($_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    
    if (empty($token) || !isset($_SESSION['csrf_token'])) {
        return false;
    }
    
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        return false;
    }
    
    if (isset($_SESSION['csrf_token_time']) && 
        (time() - $_SESSION['csrf_token_time']) > (int)env('CSRF_TOKEN_LIFETIME', 3600)) {
        return false;
    }
    
    return true;
}

function require_csrf(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!validate_csrf()) {
            // Regenerate a fresh token so the redirected page works correctly
            unset($_SESSION['csrf_token']);
            unset($_SESSION['csrf_token_time']);
            $referer = $_SERVER['HTTP_REFERER'] ?? 'index.php';
            redirect($referer, 'Your session expired. Please try again.', 'danger');
            exit;
        }
        unset($_SESSION['csrf_token']);
        unset($_SESSION['csrf_token_time']);
    }
}
