<?php
// includes/security.php - Security Helper Functions

require_once __DIR__ . '/../config/env.php';

function sanitize_input($data) {
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    return htmlspecialchars(trim($data ?? ''), ENT_QUOTES, 'UTF-8');
}

function validate_email($email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Normalize a mobile number to a canonical digit-only local format.
 * Strips all non-digits; strips Nepal +977 country code if present (13-digit E.164).
 * Examples:
 *   '+9779811144402' → '9811144402'
 *   '977-981-114-4402' → '9811144402'
 *   '9811144402'      → '9811144402'
 */
function normalize_mobile(string $mobile): string {
    // Remove all non-digit characters (+, -, spaces, parentheses)
    $digits = preg_replace('/[^0-9]/', '', $mobile);
    // Nepal E.164: 13 digits starting with 977 → strip country code
    if (strlen($digits) === 13 && strpos($digits, '977') === 0) {
        $digits = substr($digits, 3);
    }
    return $digits;
}

function validate_mobile($mobile): bool {
    $normalized = normalize_mobile($mobile);
    return strlen($normalized) >= 9 && strlen($normalized) <= 15;
}

function validate_required($data): bool {
    if (is_array($data)) {
        foreach ($data as $value) {
            if (empty(trim($value))) return false;
        }
        return true;
    }
    return !empty(trim($data));
}

function is_valid_price($price): bool {
    return is_numeric($price) && $price >= 0;
}

function is_valid_quantity($qty): bool {
    return is_numeric($qty) && $qty > 0 && $qty == (int)$qty;
}

function secure_redirect($url): void {
    $url = filter_var($url, FILTER_SANITIZE_URL);
    if (filter_var($url, FILTER_VALIDATE_URL) || strpos($url, '/') === 0) {
        header("Location: $url");
        exit();
    }
    header("Location: index.php");
    exit();
}

function generate_random_string($length = 32): string {
    return bin2hex(random_bytes($length / 2));
}

function hash_password($password): string {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

function verify_password($password, $hash): bool {
    return password_verify($password, $hash);
}

function validate_password_strength($password): array {
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter';
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain at least one lowercase letter';
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one number';
    }
    
    return $errors;
}

function get_client_ip(): string {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
}

function mask_email(string $email): string {
    if (empty($email)) return '';
    if (!str_contains($email, '@')) return $email;
    [$local, $domain] = explode('@', $email, 2);
    $visible = min(2, strlen($local));
    return substr($local, 0, $visible) . str_repeat('*', max(0, strlen($local) - $visible)) . '@' . $domain;
}

function send_otp_email(string $toEmail, string $toName, string $otp): bool {
    global $pdo;
    require_once __DIR__ . '/classes/EmailManager.php';
    $emailManager = new EmailManager($pdo);
    
    return $emailManager->sendNow($toEmail, $toName, 'otp_email', [
        'name' => $toName,
        'otp'  => $otp
    ]);
}

function send_password_reset_notification(string $toEmail, string $toName): bool {
    global $pdo;
    require_once __DIR__ . '/classes/EmailManager.php';
    $emailManager = new EmailManager($pdo);
    
    // Use queue instead of sendNow for non-critical notification
    return $emailManager->queue($toEmail, $toName, 'password_reset_success', [
        'name' => $toName,
        'date' => date('Y-m-d H:i:s')
    ]);
}
