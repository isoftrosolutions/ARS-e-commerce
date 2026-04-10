<?php

class EmailManager {
    private $pdo;
    private $mailConfig;
    private $templates;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->mailConfig = require __DIR__ . '/../../config/mail.php';
        $this->templates = require __DIR__ . '/../../config/email_templates.php';
    }

    /**
     * Add an email to the queue instead of sending it immediately
     */
    public function queue($toEmail, $toName, $slug, $placeholders = [], $scheduledAt = null) {
        // Fetch template
        $template = $this->getTemplate($slug);
        if (!$template) return false;

        // Process placeholders
        $subject = $this->replaceVars($template['subject'], $placeholders);
        $body = $this->replaceVars($template['content_html'], $placeholders);

        // Schedule time defaults to now
        $scheduledAt = $scheduledAt ?? date('Y-m-d H:i:s');

        // Insert into Queue
        $sql = "INSERT INTO email_queue (recipient_email, recipient_name, subject, body_html, scheduled_at) 
                VALUES (?, ?, ?, ?, ?)";
        $result = $this->pdo->prepare($sql)->execute([
            $toEmail, 
            $toName, 
            $subject, 
            $body, 
            $scheduledAt
        ]);

        if ($result && strtotime($scheduledAt) <= time()) {
            $this->triggerAsyncWorker();
        }

        return $result;
    }

    /**
     * Send an email immediately using the template system
     */
    public function sendNow($toEmail, $toName, $slug, $placeholders = []) {
        $template = $this->getTemplate($slug);
        if (!$template) return false;

        $subject = $this->replaceVars($template['subject'], $placeholders);
        $body    = $this->replaceVars($template['content_html'], $placeholders);

        $phpmailerDir = __DIR__ . '/../../vendor/phpmailer/phpmailer/';
        $files = [
            $phpmailerDir . 'PHPMailer.php',
            $phpmailerDir . 'SMTP.php',
            $phpmailerDir . 'Exception.php',
        ];

        foreach ($files as $file) {
            if (!file_exists($file)) {
                error_log("EmailManager: PHPMailer file missing: {$file}");
                return false;
            }
            require_once $file;
        }

        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = $this->mailConfig['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->mailConfig['username'];
            $mail->Password   = $this->mailConfig['password'];
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $this->mailConfig['port'];
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom($this->mailConfig['from_email'], $this->mailConfig['from_name']);
            $mail->addAddress($toEmail, $toName);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            $mail->send();
            return true;
        } catch (\Throwable $e) {
            // Catches both Exception (SMTP errors) and Error (class not found, etc.)
            error_log("Email immediate send failed to {$toEmail}: " . $e->getMessage());
            return false;
        }
    }

    private function getTemplate($slug) {
        return $this->templates[$slug] ?? null;
    }

    private function triggerAsyncWorker() {
        if (isset($_SERVER['HTTP_HOST'])) {
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
            $url = $protocol . "://" . $_SERVER['HTTP_HOST'] . "/ARS/process_queue_async.php";
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 1);
            curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
            curl_exec($ch);
            curl_close($ch);
        }
    }

    private function replaceVars($content, $vars) {
        foreach ($vars as $key => $value) {
            $content = str_replace("{{{$key}}}", $value, $content);
        }
        return $content;
    }
}
?>
