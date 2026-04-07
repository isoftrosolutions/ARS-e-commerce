<?php
require_once __DIR__ . '/../config/db.php';

try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS product_reviews (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            user_id INT NOT NULL,
            order_id INT NULL,
            rating TINYINT NOT NULL CHECK (rating >= 1 AND rating <= 5),
            comment TEXT NOT NULL,
            status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    echo "Reviews table created successfully!\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
