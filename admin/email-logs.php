<?php
// admin/email-logs.php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/layout-parts.php';

// Check if email_logs table exists
$has_logs = false;
$logs = [];
try {
    $logs = $pdo->query("SELECT * FROM email_logs ORDER BY created_at DESC LIMIT 50")->fetchAll();
    $has_logs = true;
} catch (PDOException $e) {
    // Table doesn't exist yet — that's fine
}

admin_header('Email Logs', 'email-logs');
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Email Logs</h1>
        <p class="page-subtitle">Track transactional emails sent by your store</p>
    </div>
</div>

<?php if (!$has_logs || empty($logs)): ?>

<div class="card card-body" style="max-width:600px;text-align:center;padding:48px 32px;">
    <div style="width:56px;height:56px;border-radius:var(--r-lg);background:var(--bg-overlay);display:flex;align-items:center;justify-content:center;margin:0 auto 18px;color:var(--text-muted);">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
    </div>
    <h3 style="font-size:16px;font-weight:700;margin-bottom:8px;">Email Logging Not Configured</h3>
    <p style="font-size:13.5px;color:var(--text-secondary);line-height:1.6;margin-bottom:20px;">
        This store currently uses a basic notification system. To enable detailed email logs, integrate an SMTP mailer
        (such as PHPMailer or Mailgun) and create the <code style="font-family:var(--font-mono);font-size:12px;background:var(--bg-card);padding:2px 6px;border-radius:var(--r-sm);">email_logs</code> table.
    </p>

    <div class="alert alert-info" style="text-align:left;margin-bottom:24px;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
        <div class="alert-body">
            <div class="alert-title">Schema for email_logs table</div>
            <pre style="font-family:var(--font-mono);font-size:11px;color:var(--text-secondary);margin-top:6px;white-space:pre-wrap;">CREATE TABLE email_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  recipient VARCHAR(255) NOT NULL,
  subject VARCHAR(500) NOT NULL,
  body TEXT,
  status ENUM('sent','failed') DEFAULT 'sent',
  error TEXT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);</pre>
        </div>
    </div>

    <div style="display:flex;gap:10px;justify-content:center;">
        <a href="settings.php" class="btn btn-primary">Go to Settings</a>
        <a href="dashboard.php" class="btn btn-ghost">Back to Dashboard</a>
    </div>
</div>

<?php else: ?>

<div class="table-wrapper">
    <div class="table-scroll">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Recipient</th>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td style="font-family:var(--font-mono);font-size:12.5px;"><?php echo htmlspecialchars($log['recipient']); ?></td>
                    <td><?php echo htmlspecialchars($log['subject']); ?></td>
                    <td>
                        <span class="badge <?php echo $log['status'] === 'sent' ? 'badge-success' : 'badge-danger'; ?>">
                            <span class="badge-dot"></span><?php echo ucfirst($log['status']); ?>
                        </span>
                        <?php if ($log['status'] === 'failed' && $log['error']): ?>
                        <div style="font-size:11px;color:var(--danger);margin-top:3px;" title="<?php echo htmlspecialchars($log['error']); ?>"><?php echo htmlspecialchars(substr($log['error'], 0, 60)); ?>…</div>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:12px;color:var(--text-secondary);">
                        <?php echo date('M d, Y h:i A', strtotime($log['created_at'])); ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php endif; ?>

<?php admin_footer(); ?>
