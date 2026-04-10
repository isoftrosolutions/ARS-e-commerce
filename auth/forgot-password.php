<?php
require_once __DIR__ . '/../includes/functions.php';

if (is_logged_in()) {
    redirect('../index.php');
}

$step  = 'request'; // 'request' | 'verify'
$error = null;

// Restore step from session if OTP was already sent and not yet expired
if (isset($_SESSION['otp_user_id'], $_SESSION['otp_sent_at'])) {
    if (time() - $_SESSION['otp_sent_at'] < 600) {
        $step = 'verify';
    } else {
        unset($_SESSION['otp_user_id'], $_SESSION['otp_sent_at'], $_SESSION['otp_masked_contact']);
        $error = "Your OTP expired. Please request a new one.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();

    $action = $_POST['action'] ?? '';

    // ── STEP 1: Request OTP ──────────────────────────────────────
    if ($action === 'request') {
        $emailOrMobile = trim($_POST['email_or_mobile'] ?? '');

        if (empty($emailOrMobile)) {
            $error = "Please enter your email or mobile number.";
        } else {
            try {
                $stmt = $pdo->prepare(
                    "SELECT id, full_name, email, mobile FROM users WHERE mobile = ? OR email = ?"
                );
                $stmt->execute([$emailOrMobile, $emailOrMobile]);
                $user = $stmt->fetch();

                if ($user) {
                    // Rate limit: 1 OTP per 2 minutes
                    // An OTP sent <2 min ago still has >8 min left on its 10-min window
                    $rateStmt = $pdo->prepare(
                        "SELECT 1 FROM users
                         WHERE id = ?
                           AND reset_token IS NOT NULL
                           AND reset_expires > DATE_ADD(NOW(), INTERVAL 8 MINUTE)"
                    );
                    $rateStmt->execute([$user['id']]);

                    if ($rateStmt->fetch()) {
                        $error = "Please wait a moment before requesting another OTP.";
                        $step  = 'verify';
                    } else {
                        $otp        = sprintf('%06d', random_int(100000, 999999));
                        $otpHash    = password_hash($otp, PASSWORD_BCRYPT, ['cost' => 10]);
                        $otpExpires = date('Y-m-d H:i:s', strtotime('+10 minutes'));

                        $pdo->prepare(
                            "UPDATE users
                             SET reset_token = ?, reset_expires = ?, otp_attempts = 0
                             WHERE id = ?"
                        )->execute([$otpHash, $otpExpires, $user['id']]);

                        send_otp_email($user['email'], $user['full_name'], $otp);

                        $_SESSION['otp_user_id']        = $user['id'];
                        $_SESSION['otp_sent_at']        = time();
                        $_SESSION['otp_masked_contact'] = mask_email($user['email']);
                    }
                }

                // Always move to verify step — don't reveal if account exists
                $step = 'verify';

            } catch (PDOException $e) {
                error_log("OTP request error: " . $e->getMessage());
                $error = "Request failed. Please try again.";
            }
        }

    // ── STEP 2: Verify OTP ───────────────────────────────────────
    } elseif ($action === 'verify') {
        $otpDigits = $_POST['otp_digits'] ?? [];
        $otp       = is_array($otpDigits)
            ? implode('', array_map('trim', $otpDigits))
            : trim($_POST['otp_code'] ?? '');

        if (!isset($_SESSION['otp_user_id'])) {
            $step  = 'request';
            $error = "Session expired. Please request a new OTP.";

        } elseif (strlen($otp) !== 6 || !ctype_digit($otp)) {
            $step  = 'verify';
            $error = "Please enter the complete 6-digit OTP.";

        } else {
            try {
                $userId = (int)$_SESSION['otp_user_id'];
                $stmt   = $pdo->prepare(
                    "SELECT reset_token, otp_attempts
                     FROM users
                     WHERE id = ? AND reset_token IS NOT NULL AND reset_expires > NOW()"
                );
                $stmt->execute([$userId]);
                $record = $stmt->fetch();

                if (!$record) {
                    unset($_SESSION['otp_user_id'], $_SESSION['otp_sent_at'], $_SESSION['otp_masked_contact']);
                    $step  = 'request';
                    $error = "Your OTP has expired. Please request a new one.";

                } elseif ((int)$record['otp_attempts'] >= 5) {
                    $pdo->prepare(
                        "UPDATE users SET reset_token = NULL, reset_expires = NULL WHERE id = ?"
                    )->execute([$userId]);
                    unset($_SESSION['otp_user_id'], $_SESSION['otp_sent_at'], $_SESSION['otp_masked_contact']);
                    $step  = 'request';
                    $error = "Too many incorrect attempts. Please request a new OTP.";

                } elseif (!password_verify($otp, $record['reset_token'])) {
                    $pdo->prepare(
                        "UPDATE users SET otp_attempts = otp_attempts + 1 WHERE id = ?"
                    )->execute([$userId]);
                    $remaining = 5 - ((int)$record['otp_attempts'] + 1);
                    $step  = 'verify';
                    $error = "Incorrect OTP. " . $remaining . " attempt" . ($remaining === 1 ? '' : 's') . " remaining.";

                } else {
                    // OTP correct — mark verified in session, redirect to set new password
                    unset($_SESSION['otp_user_id'], $_SESSION['otp_sent_at'], $_SESSION['otp_masked_contact']);
                    $_SESSION['otp_reset_user_id'] = $userId;
                    $_SESSION['otp_reset_verified'] = true;
                    $_SESSION['otp_reset_expires']  = time() + 900; // 15 min window to set password
                    redirect('reset-password.php');
                }

            } catch (PDOException $e) {
                error_log("OTP verify error: " . $e->getMessage());
                $step  = 'verify';
                $error = "Verification failed. Please try again.";
            }
        }

    // ── Resend OTP ───────────────────────────────────────────────
    } elseif ($action === 'resend') {
        unset($_SESSION['otp_user_id'], $_SESSION['otp_sent_at'], $_SESSION['otp_masked_contact']);
        $step = 'request';
    }
}

$maskedContact = $_SESSION['otp_masked_contact'] ?? '';
$page_title    = "Reset Password";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> | <?= SITE_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --ember:      #ea580c;
            --ember-dim:  #c2410c;
            --ember-glow: rgba(234,88,12,.18);
            --void:       #130c06;
            --cream:      #fdfaf7;
            --ink:        #1a0e05;
            --ink-light:  #6b5c4e;
            --ink-muted:  #a89688;
            --border:     #e4d9d0;
            --font-d:     'Cormorant Garamond', Georgia, serif;
            --font-b:     'DM Sans', system-ui, sans-serif;
        }

        html, body { height: 100%; font-family: var(--font-b); background: var(--void); overflow: hidden; }

        /* ── LAYOUT ── */
        .auth-wrap { display: flex; height: 100vh; overflow: hidden; }

        /* ── LEFT PANEL ── */
        .panel-left {
            width: 44%;
            background: var(--void);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            padding: 3.5rem;
            animation: panelLeft .8s cubic-bezier(.22,1,.36,1) both;
        }
        @keyframes panelLeft {
            from { transform: translateX(-100%); }
            to   { transform: translateX(0); }
        }
        .panel-left::before {
            content: '';
            position: absolute; inset: 0;
            background-image:
                linear-gradient(rgba(234,88,12,.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(234,88,12,.04) 1px, transparent 1px);
            background-size: 44px 44px;
            pointer-events: none;
        }
        .panel-left::after {
            content: '';
            position: absolute; top: 0; right: 0;
            width: 1px; height: 100%;
            background: linear-gradient(to bottom, transparent, rgba(234,88,12,.25) 40%, rgba(234,88,12,.25) 60%, transparent);
        }
        .orb { position: absolute; border-radius: 50%; pointer-events: none; filter: blur(70px); }
        .orb-1 {
            width: 380px; height: 380px;
            background: radial-gradient(circle, rgba(234,88,12,.22) 0%, transparent 70%);
            top: -120px; right: -100px;
            animation: orbFloat 7s ease-in-out infinite;
        }
        .orb-2 {
            width: 260px; height: 260px;
            background: radial-gradient(circle, rgba(217,119,6,.14) 0%, transparent 70%);
            bottom: 40px; left: -70px;
            animation: orbFloat 9s ease-in-out infinite reverse;
        }
        @keyframes orbFloat {
            0%, 100% { transform: translateY(0) scale(1); }
            50%       { transform: translateY(-22px) scale(1.04); }
        }
        .ghost-text {
            position: absolute; bottom: -30px; right: -20px;
            font-family: var(--font-d);
            font-size: clamp(110px, 16vw, 190px);
            font-weight: 700; color: transparent;
            -webkit-text-stroke: 1px rgba(234,88,12,.07);
            letter-spacing: -.05em; line-height: 1;
            user-select: none; pointer-events: none;
        }
        .brand {
            display: flex; align-items: center; gap: .875rem;
            text-decoration: none; position: relative; z-index: 1;
            animation: fadeUp .6s .15s both;
        }
        .brand-img {
            width: 46px; height: 46px; border-radius: 12px; overflow: hidden;
            background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.08);
            padding: 5px; flex-shrink: 0;
        }
        .brand-img img { width: 100%; height: 100%; object-fit: contain; }
        .brand-name { font-family: var(--font-d); font-size: 1.7rem; font-weight: 700; color: #fff; letter-spacing: .02em; line-height: 1; }
        .brand-name em { font-style: normal; color: var(--ember); }
        .panel-tagline {
            position: relative; z-index: 1;
            margin-top: auto; margin-bottom: 3.5rem;
            animation: fadeUp .7s .25s both;
        }
        .panel-tagline h2 {
            font-family: var(--font-d);
            font-size: clamp(2rem, 3.2vw, 3rem);
            font-weight: 600; color: #fff; line-height: 1.15; margin-bottom: .75rem;
        }
        .panel-tagline h2 em { font-style: italic; color: var(--ember); }
        .panel-tagline p { color: rgba(255,255,255,.38); font-size: .8rem; letter-spacing: .14em; text-transform: uppercase; }
        .security-list { display: flex; flex-direction: column; gap: .875rem; position: relative; z-index: 1; animation: fadeUp .7s .35s both; }
        .sec-item { display: flex; align-items: center; gap: .75rem; color: rgba(255,255,255,.5); font-size: .82rem; }
        .sec-icon {
            width: 34px; height: 34px;
            background: rgba(234,88,12,.12); border: 1px solid rgba(234,88,12,.2);
            border-radius: 9px; display: flex; align-items: center; justify-content: center;
            color: var(--ember); font-size: .95rem; flex-shrink: 0;
        }

        /* ── RIGHT PANEL ── */
        .panel-right {
            flex: 1; background: var(--cream);
            display: flex; align-items: center; justify-content: center;
            overflow-y: auto; position: relative;
            animation: fadeIn .7s .1s both;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateX(40px); }
            to   { opacity: 1; transform: translateX(0); }
        }
        .form-wrap { width: 100%; max-width: 400px; padding: 2.5rem 2rem; }

        /* ── FORM HEADER ── */
        .form-header { margin-bottom: 2.25rem; animation: fadeUp .55s .3s both; }
        .form-header h1 {
            font-family: var(--font-d); font-size: 3.2rem; font-weight: 700;
            color: var(--ink); line-height: .95; margin-bottom: .5rem;
        }
        .form-header p { color: var(--ink-muted); font-size: .875rem; line-height: 1.5; }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(18px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── ALERT ── */
        .alert-error {
            display: flex; align-items: center; gap: .6rem;
            background: #fff5f5; border: 1px solid #fca5a5; border-left: 3px solid #ef4444;
            color: #dc2626; padding: .75rem 1rem; border-radius: 8px;
            font-size: .85rem; margin-bottom: 1.75rem;
            animation: shake .4s ease, fadeUp .3s ease;
        }
        @keyframes shake {
            0%,100% { transform: translateX(0); }
            20%     { transform: translateX(-6px); }
            40%     { transform: translateX(6px); }
            60%     { transform: translateX(-4px); }
            80%     { transform: translateX(4px); }
        }

        /* ── FLOATING LABEL FIELD ── */
        .field { position: relative; margin-bottom: 2rem; animation: fadeUp .5s .38s both; }
        .field input {
            width: 100%; padding: 1.4rem 0 .45rem;
            background: transparent; border: none;
            border-bottom: 1.5px solid var(--border);
            outline: none; font-family: var(--font-b);
            font-size: .95rem; color: var(--ink);
            transition: border-color .3s; -webkit-appearance: none;
        }
        .field label {
            position: absolute; top: 1.35rem; left: 0;
            font-size: .9rem; color: var(--ink-muted);
            transition: all .22s cubic-bezier(.4,0,.2,1); pointer-events: none;
        }
        .field input:focus ~ label,
        .field input:not(:placeholder-shown) ~ label {
            top: .1rem; font-size: .65rem; color: var(--ember);
            letter-spacing: .1em; text-transform: uppercase; font-weight: 600;
        }
        .field-line {
            position: absolute; bottom: 0; left: 0;
            width: 0; height: 2px; background: var(--ember);
            transition: width .35s cubic-bezier(.4,0,.2,1); pointer-events: none;
        }
        .field:focus-within .field-line { width: 100%; }
        .field-hint { margin-top: .45rem; font-size: .73rem; color: var(--ink-muted); display: flex; align-items: center; gap: .3rem; }

        /* ── OTP BOXES ── */
        .otp-label {
            font-size: .65rem; color: var(--ember); letter-spacing: .12em;
            text-transform: uppercase; font-weight: 600; margin-bottom: .85rem;
            animation: fadeUp .45s .3s both;
        }
        .otp-sent-to {
            font-size: .82rem; color: var(--ink-muted); margin-bottom: 1.75rem;
            animation: fadeUp .45s .35s both;
        }
        .otp-sent-to strong { color: var(--ink-light); }

        .otp-boxes {
            display: flex; gap: .6rem; margin-bottom: 2rem;
            animation: fadeUp .5s .4s both;
        }
        .otp-box {
            flex: 1; aspect-ratio: 1;
            border: 1.5px solid var(--border); border-radius: 10px;
            background: #fff; outline: none;
            font-family: var(--font-d); font-size: 1.6rem; font-weight: 700;
            color: var(--ink); text-align: center;
            transition: border-color .2s, box-shadow .2s;
            -moz-appearance: textfield;
        }
        .otp-box::-webkit-outer-spin-button,
        .otp-box::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        .otp-box:focus {
            border-color: var(--ember);
            box-shadow: 0 0 0 3px var(--ember-glow);
        }
        .otp-box.filled { border-color: var(--ink-light); }
        .otp-box.error  { border-color: #ef4444; box-shadow: 0 0 0 3px rgba(239,68,68,.12); }
        input[type=hidden]#otp_hidden {}

        /* ── TIMER ── */
        .otp-timer {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 1.5rem;
            animation: fadeUp .5s .45s both;
        }
        .timer-text { font-size: .8rem; color: var(--ink-muted); }
        .timer-text span { color: var(--ink-light); font-weight: 600; }
        .resend-link {
            font-size: .8rem; color: var(--ember);
            font-weight: 600; text-decoration: none; cursor: pointer;
            background: none; border: none; padding: 0; font-family: var(--font-b);
        }
        .resend-link:disabled { color: var(--ink-muted); cursor: not-allowed; }

        /* ── SUBMIT BUTTON ── */
        .btn-submit {
            width: 100%; padding: 1rem 1.5rem;
            background: var(--ember); color: #fff;
            border: none; border-radius: 10px;
            font-family: var(--font-b); font-size: .93rem; font-weight: 600;
            cursor: pointer; display: flex; align-items: center;
            justify-content: center; gap: .55rem;
            position: relative; overflow: hidden; letter-spacing: .02em;
            transition: transform .2s, box-shadow .25s;
            animation: fadeUp .5s .5s both;
        }
        .btn-submit::after {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(110deg, transparent 30%, rgba(255,255,255,.18) 50%, transparent 70%);
            transform: translateX(-100%); transition: transform .55s ease;
        }
        .btn-submit:hover::after { transform: translateX(100%); }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 10px 28px rgba(234,88,12,.38); }
        .btn-submit:active { transform: translateY(0); box-shadow: none; }
        .btn-submit:disabled { opacity: .6; cursor: not-allowed; transform: none; box-shadow: none; }
        .btn-submit i { transition: transform .3s; }
        .btn-submit:not(:disabled):hover i { transform: translateX(5px); }

        /* ── FORM FOOTER ── */
        .form-footer { margin-top: 2rem; text-align: center; animation: fadeUp .5s .55s both; }
        .form-footer p { color: var(--ink-muted); font-size: .85rem; margin-bottom: .875rem; }
        .form-footer a { color: var(--ember); font-weight: 600; text-decoration: none; }
        .form-footer a:hover { text-decoration: underline; }
        .back-link {
            display: inline-flex; align-items: center; gap: .35rem;
            color: var(--ink-muted) !important; font-size: .78rem;
            font-weight: 400 !important; transition: color .2s;
        }
        .back-link:hover { color: var(--ink) !important; text-decoration: none !important; }

        /* ── MOBILE ── */
        @media (max-width: 820px) {
            html, body { overflow: auto; }
            .auth-wrap { flex-direction: column; height: auto; min-height: 100vh; }
            .panel-left {
                width: 100%; flex-direction: row; align-items: center;
                justify-content: space-between; padding: 1.25rem 1.5rem;
                animation: none; min-height: 70px;
            }
            .panel-left::after { display: none; }
            .panel-tagline, .security-list, .ghost-text, .orb { display: none; }
            .brand { margin: 0; }
            .panel-right { flex: 1; align-items: flex-start; animation: none; padding-bottom: 2rem; }
            .form-wrap { padding: 2rem 1.5rem 1.5rem; max-width: 100%; }
            .form-header h1 { font-size: 2.5rem; }
            .panel-left .back-link-mobile {
                display: flex; align-items: center; gap: .35rem;
                color: rgba(255,255,255,.45); font-size: .78rem; text-decoration: none;
            }
        }
        @media (min-width: 821px) { .back-link-mobile { display: none; } }
    </style>
</head>
<body>

<div class="auth-wrap">

    <!-- ═══ LEFT PANEL ═══════════════════════════════════════ -->
    <div class="panel-left">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="ghost-text">ARS</div>

        <a href="../index.php" class="brand">
            <div class="brand-img"><img src="../assets/logo.jpeg" alt="ARS Shop"></div>
            <span class="brand-name">ARS<em>SHOP</em></span>
        </a>
        <a href="../index.php" class="back-link-mobile">
            <i class="bi bi-arrow-left"></i> Shop
        </a>

        <div class="panel-tagline">
            <?php if ($step === 'request'): ?>
                <h2>Forgot your<br><em>password?</em><br>We've got<br>you covered.</h2>
            <?php else: ?>
                <h2>Check your<br><em>inbox</em> for<br>the OTP code.</h2>
            <?php endif; ?>
            <p>Secure account recovery</p>
        </div>

        <div class="security-list">
            <div class="sec-item">
                <div class="sec-icon"><i class="bi bi-shield-lock"></i></div>
                <span>One-time code, expires in 10 min</span>
            </div>
            <div class="sec-item">
                <div class="sec-icon"><i class="bi bi-envelope-lock"></i></div>
                <span>Sent to your registered email</span>
            </div>
            <div class="sec-item">
                <div class="sec-icon"><i class="bi bi-arrow-repeat"></i></div>
                <span>Request a new code any time</span>
            </div>
        </div>
    </div>

    <!-- ═══ RIGHT PANEL ══════════════════════════════════════ -->
    <div class="panel-right">
        <div class="form-wrap">

            <?php if ($error): ?>
                <div class="alert-error">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <!-- ── STEP 1: Request ───────────────────────── -->
            <?php if ($step === 'request'): ?>

            <div class="form-header">
                <h1>Reset<br>password.</h1>
                <p>Enter your email or mobile to receive a one-time code.</p>
            </div>

            <form action="forgot-password.php" method="POST" id="requestForm" autocomplete="on">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="request">

                <div class="field">
                    <input type="text" name="email_or_mobile" id="email_or_mobile"
                           placeholder=" " required autocomplete="username" inputmode="text"
                           value="<?= htmlspecialchars($_POST['email_or_mobile'] ?? '') ?>">
                    <label for="email_or_mobile">Email or mobile number</label>
                    <div class="field-line"></div>
                    <div class="field-hint">
                        <i class="bi bi-info-circle"></i>
                        Use the email or number linked to your account
                    </div>
                </div>

                <button type="submit" class="btn-submit" id="sendBtn">
                    <span>Send OTP</span>
                    <i class="bi bi-send"></i>
                </button>
            </form>

            <div class="form-footer">
                <p>Remembered it? <a href="login.php">Sign in</a></p>
                <a href="../index.php" class="back-link"><i class="bi bi-arrow-left"></i>Back to shop</a>
            </div>

            <!-- ── STEP 2: Verify OTP ────────────────────── -->
            <?php else: ?>

            <div class="form-header">
                <h1>Enter<br>the code.</h1>
                <p>We sent a 6-digit code<?php if ($maskedContact): ?> to <strong><?= htmlspecialchars($maskedContact) ?></strong><?php endif; ?>. It expires in 10&nbsp;minutes.</p>
            </div>

            <form action="forgot-password.php" method="POST" id="verifyForm" autocomplete="off">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="verify">
                <input type="hidden" name="otp_digits[]" id="otp_val_1" value="">
                <input type="hidden" name="otp_digits[]" id="otp_val_2" value="">
                <input type="hidden" name="otp_digits[]" id="otp_val_3" value="">
                <input type="hidden" name="otp_digits[]" id="otp_val_4" value="">
                <input type="hidden" name="otp_digits[]" id="otp_val_5" value="">
                <input type="hidden" name="otp_digits[]" id="otp_val_6" value="">

                <div class="otp-label">One-time password</div>
                <?php if ($maskedContact): ?>
                <div class="otp-sent-to">Code sent to <strong><?= htmlspecialchars($maskedContact) ?></strong></div>
                <?php endif; ?>

                <div class="otp-boxes" id="otpBoxes">
                    <input class="otp-box" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" autocomplete="one-time-code" aria-label="Digit 1">
                    <input class="otp-box" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" aria-label="Digit 2">
                    <input class="otp-box" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" aria-label="Digit 3">
                    <input class="otp-box" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" aria-label="Digit 4">
                    <input class="otp-box" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" aria-label="Digit 5">
                    <input class="otp-box" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" aria-label="Digit 6">
                </div>

                <div class="otp-timer">
                    <span class="timer-text">Code expires in <span id="countdown">10:00</span></span>
                    <form action="forgot-password.php" method="POST" style="display:inline;" id="resendForm">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="resend">
                        <button type="submit" class="resend-link" id="resendBtn" disabled>Resend code</button>
                    </form>
                </div>

                <button type="submit" class="btn-submit" id="verifyBtn" disabled>
                    <span>Verify OTP</span>
                    <i class="bi bi-arrow-right"></i>
                </button>
            </form>

            <div class="form-footer" style="margin-top:1.5rem;">
                <a href="../index.php" class="back-link"><i class="bi bi-arrow-left"></i>Back to shop</a>
            </div>

            <?php endif; ?>

        </div>
    </div>

</div>

<script>
// ── Step 1: loading state ─────────────────────────────────
const requestForm = document.getElementById('requestForm');
const sendBtn     = document.getElementById('sendBtn');
if (requestForm && sendBtn) {
    requestForm.addEventListener('submit', () => {
        sendBtn.disabled = true;
        sendBtn.innerHTML = '<span>Sending…</span>';
    });
}

// ── Step 2: OTP boxes logic ───────────────────────────────
const boxes     = document.querySelectorAll('.otp-box');
const verifyBtn = document.getElementById('verifyBtn');
const hiddenIds = ['otp_val_1','otp_val_2','otp_val_3','otp_val_4','otp_val_5','otp_val_6'];

function syncHiddenFields() {
    boxes.forEach((box, i) => {
        const hid = document.getElementById(hiddenIds[i]);
        if (hid) hid.value = box.value;
    });
    const full = [...boxes].every(b => /^[0-9]$/.test(b.value));
    if (verifyBtn) verifyBtn.disabled = !full;
}

boxes.forEach((box, i) => {
    box.addEventListener('input', e => {
        const val = e.target.value.replace(/[^0-9]/g, '');
        e.target.value = val ? val[val.length - 1] : '';
        e.target.classList.toggle('filled', !!e.target.value);
        if (e.target.value && i < boxes.length - 1) boxes[i + 1].focus();
        syncHiddenFields();
    });

    box.addEventListener('keydown', e => {
        if (e.key === 'Backspace' && !box.value && i > 0) {
            boxes[i - 1].value = '';
            boxes[i - 1].classList.remove('filled');
            boxes[i - 1].focus();
            syncHiddenFields();
        }
        if (e.key === 'ArrowLeft' && i > 0)           boxes[i - 1].focus();
        if (e.key === 'ArrowRight' && i < boxes.length - 1) boxes[i + 1].focus();
    });

    box.addEventListener('paste', e => {
        e.preventDefault();
        const data = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '');
        [...data.slice(0, 6)].forEach((ch, j) => {
            if (boxes[j]) {
                boxes[j].value = ch;
                boxes[j].classList.add('filled');
            }
        });
        const next = Math.min(data.length, boxes.length - 1);
        boxes[next].focus();
        syncHiddenFields();
    });
});

// Mark error state on boxes if error present
<?php if ($error && $step === 'verify'): ?>
boxes.forEach(b => b.classList.add('error'));
setTimeout(() => boxes.forEach(b => b.classList.remove('error')), 1500);
<?php endif; ?>

// ── Countdown timer ───────────────────────────────────────
const countdownEl = document.getElementById('countdown');
const resendBtn   = document.getElementById('resendBtn');
const sentAt      = <?= json_encode(isset($_SESSION['otp_sent_at']) ? $_SESSION['otp_sent_at'] : time()) ?>;
const expiresIn   = 600; // 10 min

function updateTimer() {
    const elapsed  = Math.floor(Date.now() / 1000) - sentAt;
    const remaining = Math.max(0, expiresIn - elapsed);
    const m = String(Math.floor(remaining / 60)).padStart(2, '0');
    const s = String(remaining % 60).padStart(2, '0');
    if (countdownEl) countdownEl.textContent = m + ':' + s;
    if (remaining === 0) {
        if (countdownEl) countdownEl.textContent = 'Expired';
        if (resendBtn)   resendBtn.disabled = false;
        clearInterval(timerInterval);
    }
}
updateTimer();
const timerInterval = setInterval(updateTimer, 1000);

// Enable resend after 60s
setTimeout(() => { if (resendBtn) resendBtn.disabled = false; }, 60000);

// Verify button loading state
const verifyForm = document.getElementById('verifyForm');
if (verifyForm && verifyBtn) {
    verifyForm.addEventListener('submit', () => {
        verifyBtn.disabled = true;
        verifyBtn.innerHTML = '<span>Verifying…</span>';
    });
}

// Focus first box on verify step
if (boxes.length) boxes[0].focus();
</script>
</body>
</html>
