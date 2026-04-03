<?php
require_once __DIR__ . '/../config/db.php';

try {
    // Add columns to products
    $pdo->exec("ALTER TABLE products ADD COLUMN meta_title VARCHAR(255) AFTER image, ADD COLUMN meta_description TEXT AFTER meta_title, ADD COLUMN meta_keywords VARCHAR(255) AFTER meta_description");
    
    // Add columns to categories
    $pdo->exec("ALTER TABLE categories ADD COLUMN meta_title VARCHAR(255) AFTER slug, ADD COLUMN meta_description TEXT AFTER meta_title, ADD COLUMN meta_keywords VARCHAR(255) AFTER meta_description");
    
    echo "SEO Migration Successful!";
} catch (PDOException $e) {
    echo "Migration failed or columns already exist: " . $e->getMessage();
}
?>
