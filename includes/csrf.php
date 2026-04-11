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

/**
 */
function require_csrf(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!validate_csrf()) {
            // Clear stale token so the next page load generates a fresh one.
            unset($_SESSION['csrf_token'], $_SESSION['csrf_token_time']);

            // Build a safe redirect target — never a POST endpoint.
            $origin = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
            $safe = $_SERVER['HTTP_REFERER'] ?? '';
            if ($safe === '' || strpos($safe, $origin) !== 0) {
                // Derive fallback from SITE_URL constant if available, otherwise guess.
                $basePath = defined('SITE_URL')
                    ? rtrim(parse_url(SITE_URL, PHP_URL_PATH), '/') . '/auth/login.php'
                    : '/auth/login.php';
                $safe = $origin . $basePath;
            }
            redirect($safe, 'Your session expired. Please try again.', 'danger');
            exit;
        }
        // Token is VALID — leave it in place so users can retry the same form.
        // Callers that change server state should call csrf_rotate() afterwards.
    }
}

/**
 * Explicitly rotate the CSRF token after a successful state-changing POST
 * (login, order placement, password reset, etc.).
 */
function csrf_rotate(): void {
    unset($_SESSION['csrf_token'], $_SESSION['csrf_token_time']);
    // Fresh token will be generated on next call to csrf_token() / csrf_field().
}
