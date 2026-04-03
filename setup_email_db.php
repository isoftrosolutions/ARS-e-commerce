<?php
require_once __DIR__ . '/config/db.php';

try {
    // 1. Email Templates Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS email_templates (
        id INT AUTO_INCREMENT PRIMARY KEY,
        slug VARCHAR(50) UNIQUE NOT NULL,
        subject VARCHAR(255) NOT NULL,
        content_html TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 2. Email Queue Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS email_queue (
        id INT AUTO_INCREMENT PRIMARY KEY,
        recipient_email VARCHAR(255) NOT NULL,
        recipient_name VARCHAR(255),
        subject VARCHAR(255) NOT NULL,
        body_html TEXT NOT NULL,
        status ENUM('pending', 'sending', 'sent', 'failed') DEFAULT 'pending',
        attempts INT DEFAULT 0,
        max_attempts INT DEFAULT 3,
        error_message TEXT,
        scheduled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        sent_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (status),
        INDEX (scheduled_at)
    )");

    // 3. Email Logs Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS email_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        queue_id INT,
        recipient VARCHAR(255),
        subject VARCHAR(255),
        status VARCHAR(20),
        error_message TEXT,
        sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 4. Seed basic templates
    $templates = [
        [
            'slug' => 'welcome_email',
            'subject' => 'Welcome to ARS Store, {{name}}!',
            'content_html' => '<h1>Welcome!</h1><p>Hi {{name}}, thank you for registering at ARS Store. We are excited to have you!</p>'
        ],
        [
            'slug' => 'order_confirmation',
            'subject' => 'Order Confirmation #{{order_id}}',
            'content_html' => '<h1>Thank you for your order!</h1><p>Hi {{name}}, your order #{{order_id}} has been received and is being processed.</p><p>Total Amount: {{total}}</p>'
        ]
    ];

    $stmt = $pdo->prepare("INSERT IGNORE INTO email_templates (slug, subject, content_html) VALUES (?, ?, ?)");
    foreach ($templates as $t) {
        $stmt->execute([$t['slug'], $t['subject'], $t['content_html']]);
    }

    echo "Email system tables created and seeded successfully.";
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>
