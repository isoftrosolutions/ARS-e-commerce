<?php
// config/env.php - Environment Configuration Loader

if (!defined('BASE_PATH')) {
    define('BASE_PATH', __DIR__ . '/..');
}

function loadEnv($filePath) {
    if (!file_exists($filePath)) {
        return [];
    }
    
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $env = [];
    
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            if (!empty($key)) {
                $env[$key] = $value;
            }
        }
    }
    
    return $env;
}

$envFile = BASE_PATH . '/.env';
$_ENV = array_merge($_ENV, loadEnv($envFile));

function env($key, $default = null) {
    return $_ENV[$key] ?? getenv($key) ?: $default;
}
