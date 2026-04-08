<?php
// admin/coupon-action.php
require_once __DIR__ . '/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('coupons.php');
}

$action = $_POST['action'] ?? '';

// Ensure table exists
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS coupons (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(50) UNIQUE NOT NULL,
        type ENUM('fixed', 'percentage') DEFAULT 'fixed',
        value DECIMAL(10,2) NOT NULL,
        min_cart_amount DECIMAL(10,2) DEFAULT 0,
        max_discount DECIMAL(10,2) DEFAULT NULL,
        max_uses INT DEFAULT NULL,
        used_count INT DEFAULT 0,
        expires_at DATETIME DEFAULT NULL,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (PDOException $e) {
    redirect('coupons.php', 'Database error: ' . $e->getMessage(), 'danger');
}

if ($action === 'delete') {
    $id = (int)$_POST['id'];
    $stmt = $pdo->prepare("DELETE FROM coupons WHERE id = ?");
    if ($stmt->execute([$id])) {
        redirect('coupons.php', 'Coupon deleted.');
    } else {
        redirect('coupons.php', 'Failed to delete coupon.', 'danger');
    }
}

if ($action === 'create' || $action === 'update') {
    $code         = strtoupper(trim($_POST['code'] ?? ''));
    $type         = in_array($_POST['type'], ['fixed', 'percentage']) ? $_POST['type'] : 'fixed';
    $value        = max(0, (float)($_POST['value'] ?? 0));
    $min_cart     = max(0, (float)($_POST['min_cart_amount'] ?? 0));
    $max_discount = !empty($_POST['max_discount']) ? (float)$_POST['max_discount'] : null;
    $max_uses     = !empty($_POST['max_uses'])     ? (int)$_POST['max_uses']       : null;
    $expires_at   = !empty($_POST['expires_at'])   ? date('Y-m-d H:i:s', strtotime($_POST['expires_at'])) : null;
    $status       = in_array($_POST['status'], ['active', 'inactive']) ? $_POST['status'] : 'active';

    if (empty($code) || $value <= 0) {
        redirect('coupons.php', 'Code and value are required.', 'danger');
    }

    if ($type === 'percentage' && $value > 100) {
        redirect('coupons.php', 'Percentage discount cannot exceed 100%.', 'danger');
    }

    try {
        if ($action === 'create') {
            $stmt = $pdo->prepare("INSERT INTO coupons (code, type, value, min_cart_amount, max_discount, max_uses, expires_at, status) VALUES (?,?,?,?,?,?,?,?)");
            $stmt->execute([$code, $type, $value, $min_cart, $max_discount, $max_uses, $expires_at, $status]);
            redirect('coupons.php', "Coupon <strong>{$code}</strong> created.");
        } else {
            $id = (int)$_POST['id'];
            $stmt = $pdo->prepare("UPDATE coupons SET code=?, type=?, value=?, min_cart_amount=?, max_discount=?, max_uses=?, expires_at=?, status=? WHERE id=?");
            $stmt->execute([$code, $type, $value, $min_cart, $max_discount, $max_uses, $expires_at, $status, $id]);
            redirect('coupons.php', "Coupon <strong>{$code}</strong> updated.");
        }
    } catch (PDOException $e) {
        $msg = str_contains($e->getMessage(), 'Duplicate') ? "Coupon code <strong>{$code}</strong> already exists." : $e->getMessage();
        redirect('coupons.php', $msg, 'danger');
    }
}

redirect('coupons.php');
