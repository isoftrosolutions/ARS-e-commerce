<?php
require_once __DIR__ . '/config/db.php';

echo "=== Email System Status ===\n\n";

echo "Email Queue:\n";
$queue = $pdo->query("SELECT * FROM email_queue ORDER BY id DESC LIMIT 5")->fetchAll();
foreach ($queue as $q) {
    echo "  ID {$q['id']}: {$q['recipient_email']} | Status: {$q['status']} | Attempts: {$q['attempts']}\n";
}

echo "\nEmail Logs:\n";
$logs = $pdo->query("SELECT * FROM email_logs ORDER BY id DESC LIMIT 5")->fetchAll();
if (count($logs) > 0) {
    foreach ($logs as $l) {
        echo "  {$l['recipient']} | {$l['subject']} | {$l['status']}\n";
    }
} else {
    echo "  (no logs yet)\n";
}
?>
