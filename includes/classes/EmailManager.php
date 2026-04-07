<?php

class EmailManager {
    private $pdo;
    private $mailConfig;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->mailConfig = require __DIR__ . '/../../config/mail.php';
    }

    /**
     * Add an email to the queue instead of sending it immediately
     */
    public function queue($toEmail, $toName, $slug, $placeholders = [], $scheduledAt = null) {
        // Fetch template
        $stmt = $this->pdo->prepare("SELECT * FROM email_templates WHERE slug = ?");
        $stmt->execute([$slug]);
        $template = $stmt->fetch();

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
