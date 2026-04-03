<?php
require_once __DIR__ . '/config/db.php';

echo "=== ARS Email System Test ===\n\n";

echo "1. Testing database connection... ";
try {
    $pdo->query("SELECT 1");
    echo "OK\n";
} catch (Exception $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n2. Checking email tables...\n";
$tables = ['email_templates', 'email_queue', 'email_logs'];
foreach ($tables as $table) {
    $exists = $pdo->query("SHOW TABLES LIKE '$table'")->rowCount() > 0;
    echo "   - $table: " . ($exists ? "EXISTS" : "MISSING") . "\n";
}

echo "\n3. Checking email templates...\n";
$templates = $pdo->query("SELECT slug, subject FROM email_templates")->fetchAll();
foreach ($templates as $t) {
    echo "   - {$t['slug']}: {$t['subject']}\n";
}

echo "\n4. Queuing a test email...\n";
$testEmail = 'test@example.com';
$testName = 'Test User';
$stmt = $pdo->prepare("INSERT INTO email_queue (recipient_email, recipient_name, subject, body_html, status) VALUES (?, ?, ?, ?, 'pending')");
$result = $stmt->execute([$testEmail, $testName, 'Test Email from ARS', '<h1>Test</h1><p>This is a test email.</p>']);
echo "   Queue result: " . ($result ? "SUCCESS" : "FAILED") . "\n";

if ($result) {
    $queueId = $pdo->lastInsertId();
    echo "   Queue ID: $queueId\n";
}

echo "\n5. Testing SMTP configuration...\n";
$mailConfig = require __DIR__ . '/config/mail.php';
echo "   Host: {$mailConfig['host']}\n";
echo "   Port: {$mailConfig['port']}\n";
echo "   From: {$mailConfig['from_email']}\n";

echo "\n=== Test Complete ===\n";
echo "Visit admin/email-logs.php to see queued emails.\n";
echo "Run php scripts/cron_worker.php to send queued emails.\n";
?>
