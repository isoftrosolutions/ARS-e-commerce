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
        return $this->pdo->prepare($sql)->execute([
            $toEmail, 
            $toName, 
            $subject, 
            $body, 
            $scheduledAt
        ]);
    }

    private function replaceVars($content, $vars) {
        foreach ($vars as $key => $value) {
            $content = str_replace("{{{$key}}}", $value, $content);
        }
        return $content;
    }
}
?>
