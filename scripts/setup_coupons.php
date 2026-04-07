<?php
require_once __DIR__ . '/../config/db.php';

try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS coupons (
            id INT AUTO_INCREMENT PRIMARY KEY,
            code VARCHAR(50) NOT NULL UNIQUE,
            type ENUM('fixed', 'percent') NOT NULL DEFAULT 'fixed',
            value DECIMAL(10,2) NOT NULL,
            min_order_amount DECIMAL(10,2) DEFAULT 0.00,
            max_uses INT DEFAULT NULL,
            used_count INT DEFAULT 0,
            valid_from DATETIME DEFAULT CURRENT_TIMESTAMP,
            valid_until DATETIME DEFAULT NULL,
            status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Add coupon columns to orders table if they don't exist
    $columns = $pdo->query("SHOW COLUMNS FROM orders")->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('coupon_code', $columns)) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN coupon_code VARCHAR(50) NULL AFTER total_amount");
        $pdo->exec("ALTER TABLE orders ADD COLUMN discount_amount DECIMAL(10,2) DEFAULT 0.00 AFTER coupon_code");
    }

    echo "Coupon tables and columns created successfully!\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
