<?php
// includes/security.php - Security Helper Functions

require_once __DIR__ . '/../config/env.php';

function sanitize_input($data) {
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    return htmlspecialchars(trim($data ?? ''), ENT_QUOTES, 'UTF-8');
}

function validate_email($email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validate_mobile($mobile): bool {
    $mobile = preg_replace('/[^0-9]/', '', $mobile);
    return strlen($mobile) >= 9 && strlen($mobile) <= 15;
}

function validate_required($data): bool {
    if (is_array($data)) {
        foreach ($data as $value) {
            if (empty(trim($value))) return false;
        }
        return true;
    }
    return !empty(trim($data));
}

function is_valid_price($price): bool {
    return is_numeric($price) && $price >= 0;
}

function is_valid_quantity($qty): bool {
    return is_numeric($qty) && $qty > 0 && $qty == (int)$qty;
}

function secure_redirect($url): void {
    $url = filter_var($url, FILTER_SANITIZE_URL);
    if (filter_var($url, FILTER_VALIDATE_URL) || strpos($url, '/') === 0) {
        header("Location: $url");
        exit();
    }
    header("Location: index.php");
    exit();
}

function generate_random_string($length = 32): string {
    return bin2hex(random_bytes($length / 2));
}

function hash_password($password): string {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

function verify_password($password, $hash): bool {
    return password_verify($password, $hash);
}

function validate_password_strength($password): array {
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter';
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain at least one lowercase letter';
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one number';
    }
    
    return $errors;
}

function get_client_ip(): string {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
}

function mask_email(string $email): string {
    [$local, $domain] = explode('@', $email, 2);
    $visible = min(2, strlen($local));
    return substr($local, 0, $visible) . str_repeat('*', max(0, strlen($local) - $visible)) . '@' . $domain;
}

function send_otp_email(string $toEmail, string $toName, string $otp): bool {
    $vendorBase = __DIR__ . '/../vendor/phpmailer/phpmailer';
    require_once $vendorBase . '/PHPMailer.php';
    require_once $vendorBase . '/SMTP.php';
    require_once $vendorBase . '/Exception.php';

    $config = require __DIR__ . '/../config/mail.php';

    if (empty($config['username'])) {
        error_log("OTP email skipped: SMTP not configured. OTP for {$toEmail}: {$otp}");
        return false;
    }

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = $config['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $config['username'];
        $mail->Password   = $config['password'];
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $config['port'];
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom($config['from_email'], $config['from_name']);
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->Subject = 'Your ARS Shop Password Reset OTP';
        $mail->Body    = "
<!DOCTYPE html>
<html>
<head><meta charset='UTF-8'></head>
<body style='margin:0;padding:0;background:#f5f5f5;font-family:Arial,sans-serif;'>
<table width='100%' cellpadding='0' cellspacing='0' style='background:#f5f5f5;padding:40px 0;'>
  <tr><td align='center'>
    <table width='480' cellpadding='0' cellspacing='0' style='background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.08);'>
      <tr><td style='background:#130c06;padding:32px 40px;text-align:center;'>
        <span style='font-size:24px;font-weight:700;color:#fff;letter-spacing:.02em;'>ARS<span style='color:#ea580c;'>SHOP</span></span>
      </td></tr>
      <tr><td style='padding:40px;text-align:center;'>
        <p style='margin:0 0 8px;font-size:14px;color:#6b5c4e;text-transform:uppercase;letter-spacing:.1em;'>Password Reset</p>
        <h1 style='margin:0 0 24px;font-size:28px;color:#1a0e05;'>Your OTP Code</h1>
        <p style='margin:0 0 32px;font-size:15px;color:#6b5c4e;line-height:1.6;'>Hi {$toName}, use the code below to reset your password. It expires in <strong>10 minutes</strong>.</p>
        <div style='display:inline-block;background:#fdfaf7;border:2px solid #ea580c;border-radius:12px;padding:20px 48px;margin-bottom:32px;'>
          <span style='font-size:40px;font-weight:700;letter-spacing:.25em;color:#130c06;'>{$otp}</span>
        </div>
        <p style='margin:0;font-size:13px;color:#a89688;'>If you did not request this, you can safely ignore this email.</p>
      </td></tr>
      <tr><td style='background:#fdfaf7;padding:20px 40px;text-align:center;border-top:1px solid #e4d9d0;'>
        <p style='margin:0;font-size:12px;color:#a89688;'>Easy Shopping A.R.S &mdash; Nepal</p>
      </td></tr>
    </table>
  </td></tr>
</table>
</body>
</html>";
        $mail->AltBody = "Your ARS Shop OTP: {$otp} (expires in 10 minutes)";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("OTP email failed to {$toEmail}: " . $e->getMessage());
        return false;
    }
}
