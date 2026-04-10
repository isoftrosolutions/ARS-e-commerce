<?php
require_once __DIR__ . '/config/db.php';

try {
    echo "Starting security migration...\n";

    // Add reset token columns to users table
    $pdo->exec("ALTER TABLE users 
        ADD COLUMN IF NOT EXISTS reset_token VARCHAR(255) NULL AFTER role,
        ADD COLUMN IF NOT EXISTS reset_expires DATETIME NULL AFTER reset_token,
        ADD COLUMN IF NOT EXISTS reset_token_used_at DATETIME NULL AFTER reset_expires");

    echo "Successfully added password reset columns to users table.\n";

    // Add email_verified_at if not exists
    $pdo->exec("ALTER TABLE users 
        ADD COLUMN IF NOT EXISTS email_verified_at TIMESTAMP NULL AFTER reset_token_used_at,
        ADD COLUMN IF NOT EXISTS verification_token VARCHAR(255) NULL AFTER email_verified_at");

    echo "Successfully added email verification columns to users table.\n";
    echo "Migration completed successfully!\n";

} catch (PDOException $e) {
    die("Migration Error: " . $e->getMessage() . "\n");
}
