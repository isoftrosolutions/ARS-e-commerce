<?php
// migrate_security.php - Security & Feature Migrations
require_once __DIR__ . '/config/db.php';

echo "Running security and feature migrations...\n\n";

try {
    // 1. Create rate_limits table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS rate_limits (
            id INT AUTO_INCREMENT PRIMARY KEY,
            identifier VARCHAR(255) NOT NULL,
            action VARCHAR(50) NOT NULL,
            attempts INT DEFAULT 0,
            last_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            locked_until TIMESTAMP NULL,
            INDEX idx_identifier_action (identifier, action),
            INDEX idx_locked_until (locked_until)
        )
    ");
    echo "[OK] rate_limits table created\n";

    // 2. Create wishlist table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS wishlist (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            product_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_user_product (user_id, product_id),
            INDEX idx_user_id (user_id),
            INDEX idx_product_id (product_id),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        )
    ");
    echo "[OK] wishlist table created\n";

    // 3. Create product_reviews table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS product_reviews (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            user_id INT,
            order_id INT,
            rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
            comment TEXT,
            status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_product_id (product_id),
            INDEX idx_user_id (user_id),
            INDEX idx_status (status),
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        )
    ");
    echo "[OK] product_reviews table created\n";

    // 4. Add missing indexes to orders table
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_orders_user_id ON orders(user_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_orders_status ON orders(delivery_status)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_orders_created ON orders(created_at)");
    echo "[OK] orders indexes added\n";

    // 5. Add index to products table
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_products_category ON products(category_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_products_featured ON products(is_featured)");
    echo "[OK] products indexes added\n";

    // 6. Add index to email_queue
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_email_queue_status ON email_queue(status)");
    echo "[OK] email_queue indexes added\n";

    // 7. Create payments uploads directory
    $uploadDir = __DIR__ . '/uploads/payments';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
        echo "[OK] uploads/payments directory created\n";
    }

    echo "\n========================================\n";
    echo "All migrations completed successfully!\n";
    echo "========================================\n";

} catch (PDOException $e) {
    echo "[ERROR] Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
