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

function validate_mobile($mobile): bool {
    $mobile = preg_replace('/[^0-9]/', '', $mobile);
    return strlen($mobile) >= 9 && strlen($mobile) <= 15;
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
