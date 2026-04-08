<?php
// admin/settings-action.php
require_once __DIR__ . '/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('settings.php');
}

$allowed_keys = [
    'site_name', 'site_email', 'contact_mobile', 'currency',
    'shipping_fee', 'free_shipping_threshold',
    'facebook_url', 'instagram_url', 'maintenance_mode',
];

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS site_settings (
        `key` VARCHAR(100) PRIMARY KEY,
        `value` TEXT NOT NULL
    )");

    $stmt = $pdo->prepare("INSERT INTO site_settings (`key`, `value`) VALUES (?, ?)
        ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)");

    foreach ($allowed_keys as $key) {
        if ($key === 'maintenance_mode') {
            $value = isset($_POST[$key]) ? '1' : '0';
        } else {
            $value = trim($_POST[$key] ?? '');
        }
        $stmt->execute([$key, $value]);
    }

    redirect('settings.php', 'Settings saved successfully.');
} catch (PDOException $e) {
    redirect('settings.php', 'Failed to save settings: ' . $e->getMessage(), 'danger');
}
