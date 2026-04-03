<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/classes/EmailManager.php';

echo "=== Sending Real Test Email ===\n\n";

$emailManager = new EmailManager($pdo);

$result = $emailManager->queue(
    'mind59024@gmail.com',
    'Test User',
    'welcome_email',
    ['name' => 'Test User']
);

if ($result) {
    echo "Email queued successfully!\n\n";
    echo "Now running cron worker to send...\n";
} else {
    echo "Failed to queue email.\n";
}
?>
