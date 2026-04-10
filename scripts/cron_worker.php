<?php
/**
 * CRON WORKER: Run this script every minute
 * Command: * * * * * php /path/to/project/scripts/cron_worker.php
 */

require_once __DIR__ . '/../config/db.php';
// IMPORTANT: Ensure you have PHPMailer installed via Composer
// If not using composer, require the PHPMailer files manually here.
require_once __DIR__ . '/../vendor/autoload.php'; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mailConfig = require __DIR__ . '/../config/mail.php';

// 1. Get pending emails that are scheduled to be sent now or in the past
$stmt = $pdo->prepare("SELECT * FROM email_queue WHERE status = 'pending' AND scheduled_at <= NOW() AND attempts < max_attempts LIMIT 10");
$stmt->execute();
$jobs = $stmt->fetchAll();

foreach ($jobs as $job) {
    // 2. Mark as sending to prevent race conditions
    $pdo->prepare("UPDATE email_queue SET status = 'sending', attempts = attempts + 1 WHERE id = ?")
        ->execute([$job['id']]);

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = $mailConfig['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $mailConfig['username'];
        $mail->Password   = $mailConfig['password'];
        $mail->SMTPSecure = ($mailConfig['port'] == 465) ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $mailConfig['port'];

        // Recipients
        $mail->setFrom($mailConfig['from_email'], $mailConfig['from_name']);
        $mail->addAddress($job['recipient_email'], $job['recipient_name']);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $job['subject'];
        $mail->Body    = $job['body_html'];

        $mail->send();

        // 3. Update status to sent
        $pdo->prepare("UPDATE email_queue SET status = 'sent', sent_at = NOW() WHERE id = ?")
            ->execute([$job['id']]);

        // 4. Log successful send
        $pdo->prepare("INSERT INTO email_logs (queue_id, recipient, subject, status) VALUES (?, ?, ?, 'sent')")
            ->execute([$job['id'], $job['recipient_email'], $job['subject']]);

    } catch (Exception $e) {
        // 5. Handle failure
        $errorMessage = $mail->ErrorInfo;
        $pdo->prepare("UPDATE email_queue SET status = 'pending', error_message = ? WHERE id = ?")
            ->execute([$errorMessage, $job['id']]);

        // Log failure
        $pdo->prepare("INSERT INTO email_logs (queue_id, recipient, subject, status, error_message) VALUES (?, ?, ?, 'failed', ?)")
            ->execute([$job['id'], $job['recipient_email'], $job['subject'], $errorMessage]);
            
        echo "Error sending to {$job['recipient_email']}: {$errorMessage}\n";
    }
}

// 6. Database Maintenance: Clear expired reset tokens and OTP data
try {
    $pdo->prepare("
        UPDATE users 
        SET reset_token = NULL, 
            reset_expires = NULL, 
            otp_attempts = 0, 
            otp_issued_at = NULL 
        WHERE reset_expires < NOW() 
          AND reset_token IS NOT NULL
    ")->execute();
} catch (PDOException $e) {
    error_log("Cron database maintenance failed: " . $e->getMessage());
}
?>
