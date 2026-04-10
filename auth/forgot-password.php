<?php
require_once __DIR__ . '/../includes/functions.php';

if (is_logged_in()) {
    redirect('../index.php');
}

$error   = null;
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();

    $emailOrMobile = trim($_POST['email_or_mobile'] ?? '');

    if (empty($emailOrMobile)) {
        $error = "Please enter your email or mobile number.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, full_name, email, mobile FROM users WHERE mobile = ? OR email = ?");
            $stmt->execute([$emailOrMobile, $emailOrMobile]);
            $user = $stmt->fetch();

            $resetToken   = bin2hex(random_bytes(32));
            $resetExpires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $resetTokenHash = password_hash($resetToken, PASSWORD_DEFAULT);

            if ($user) {
                $updateStmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
                $updateStmt->execute([$resetTokenHash, $resetExpires, $user['id']]);

                $resetUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
                    . '://' . $_SERVER['HTTP_HOST']
                    . dirname($_SERVER['REQUEST_URI'])
                    . '/reset-password.php?token=' . $resetToken;

                require_once __DIR__ . '/../includes/classes/EmailManager.php';
                $emailMgr = new EmailManager($pdo);
                $emailMgr->send($user['email'], $user['full_name'], 'password_reset', [
                    'name'      => $user['full_name'],
                    'reset_url' => $resetUrl,
                ]);

                error_log("Password reset requested for {$user['email']}");
            }

            $success = true;

        } catch (PDOException $e) {
            error_log("Password reset error: " . $e->getMessage());
            $error = "Request failed. Please try again.";
        }
    }
}

$page_title = "Reset Password";
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
            --ember:     #ea580c;
            --ember-dim: #c2410c;
            --void:      #130c06;
            --cream:     #fdfaf7;
            --ink:       #1a0e05;
            --ink-light: #6b5c4e;
            --ink-muted: #a89688;
            --border:    #e4d9d0;
            --font-d:    'Cormorant Garamond', Georgia, serif;
            --font-b:    'DM Sans', system-ui, sans-serif;
        }

        html, body {
            height: 100%;
            font-family: var(--font-b);
            background: var(--void);
            overflow: hidden;
        }

        /* ── LAYOUT ──────────────────────────────────────── */
        .auth-wrap {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* ── LEFT PANEL ──────────────────────────────────── */
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
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(234,88,12,.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(234,88,12,.04) 1px, transparent 1px);
            background-size: 44px 44px;
            pointer-events: none;
        }

        .panel-left::after {
            content: '';
            position: absolute;
            top: 0; right: 0;
            width: 1px; height: 100%;
            background: linear-gradient(to bottom,
                transparent,
                rgba(234,88,12,.22) 40%,
                rgba(234,88,12,.22) 60%,
                transparent);
        }

        /* orb glows */
        .orb {
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
            filter: blur(70px);
        }
        .orb-1 {
            width: 380px; height: 380px;
            background: radial-gradient(circle, rgba(234,88,12,.2) 0%, transparent 70%);
            top: -120px; right: -100px;
            animation: orbFloat 7s ease-in-out infinite;
        }
        .orb-2 {
            width: 240px; height: 240px;
            background: radial-gradient(circle, rgba(217,119,6,.13) 0%, transparent 70%);
            bottom: 40px; left: -70px;
            animation: orbFloat 9s ease-in-out infinite reverse;
        }
        @keyframes orbFloat {
            0%,100% { transform: translateY(0) scale(1); }
            50%      { transform: translateY(-22px) scale(1.04); }
        }

        /* ghost brand watermark */
        .ghost-text {
            position: absolute;
            bottom: -30px; right: -20px;
            font-family: var(--font-d);
            font-size: clamp(110px, 16vw, 190px);
            font-weight: 700;
            color: transparent;
            -webkit-text-stroke: 1px rgba(234,88,12,.07);
            letter-spacing: -.05em;
            line-height: 1;
            user-select: none;
            pointer-events: none;
        }

        /* brand */
        .brand {
            display: flex;
            align-items: center;
            gap: .875rem;
            text-decoration: none;
            position: relative;
            z-index: 1;
            animation: fadeUp .6s .15s both;
        }
        .brand-img {
            width: 46px; height: 46px;
            border-radius: 12px;
            overflow: hidden;
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.08);
            padding: 5px; flex-shrink: 0;
        }
        .brand-img img { width: 100%; height: 100%; object-fit: contain; }
        .brand-name {
            font-family: var(--font-d);
            font-size: 1.7rem;
            font-weight: 700;
            color: #fff;
            letter-spacing: .02em;
            line-height: 1;
        }
        .brand-name em { font-style: normal; color: var(--ember); }

        /* tagline */
        .panel-tagline {
            position: relative;
            z-index: 1;
            margin-top: auto;
            margin-bottom: 3.5rem;
            animation: fadeUp .7s .25s both;
        }
        .panel-tagline h2 {
            font-family: var(--font-d);
            font-size: clamp(2rem, 3.2vw, 2.9rem);
            font-weight: 600;
            color: #fff;
            line-height: 1.15;
            margin-bottom: .75rem;
        }
        .panel-tagline h2 em { font-style: italic; color: var(--ember); }
        .panel-tagline p {
            color: rgba(255,255,255,.38);
            font-size: .78rem;
            letter-spacing: .14em;
            text-transform: uppercase;
        }

        /* security notes */
        .security-list {
            display: flex;
            flex-direction: column;
            gap: .875rem;
            position: relative;
            z-index: 1;
            animation: fadeUp .7s .35s both;
        }
        .sec-item {
            display: flex;
            align-items: center;
            gap: .75rem;
            color: rgba(255,255,255,.48);
            font-size: .82rem;
        }
        .sec-icon {
            width: 34px; height: 34px;
            background: rgba(234,88,12,.12);
            border: 1px solid rgba(234,88,12,.2);
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--ember);
            font-size: .9rem;
            flex-shrink: 0;
        }

        /* ── RIGHT PANEL ─────────────────────────────────── */
        .panel-right {
            flex: 1;
            background: var(--cream);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-y: auto;
            position: relative;
            animation: fadeIn .7s .1s both;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateX(40px); }
            to   { opacity: 1; transform: translateX(0); }
        }

        .form-wrap {
            width: 100%;
            max-width: 400px;
            padding: 2.5rem 2rem;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(18px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── FORM STATE ──────────────────────────────────── */
        .form-header {
            margin-bottom: 2.25rem;
            animation: fadeUp .55s .3s both;
        }
        .form-header h1 {
            font-family: var(--font-d);
            font-size: 3.2rem;
            font-weight: 700;
            color: var(--ink);
            line-height: .95;
            margin-bottom: .5rem;
        }
        .form-header p {
            color: var(--ink-muted);
            font-size: .875rem;
            line-height: 1.5;
        }

        /* error */
        .alert-error {
            display: flex;
            align-items: center;
            gap: .6rem;
            background: #fff5f5;
            border: 1px solid #fca5a5;
            border-left: 3px solid #ef4444;
            color: #dc2626;
            padding: .75rem 1rem;
            border-radius: 8px;
            font-size: .85rem;
            margin-bottom: 1.75rem;
            animation: shake .4s ease, fadeUp .3s ease;
        }
        @keyframes shake {
            0%,100% { transform: translateX(0); }
            20%      { transform: translateX(-6px); }
            40%      { transform: translateX(6px); }
            60%      { transform: translateX(-4px); }
            80%      { transform: translateX(4px); }
        }

        /* ── FLOATING LABEL FIELD ────────────────────────── */
        .field {
            position: relative;
            margin-bottom: 2.25rem;
            animation: fadeUp .5s .4s both;
        }

        .field input {
            width: 100%;
            padding: 1.4rem 0 0.45rem;
            background: transparent;
            border: none;
            border-bottom: 1.5px solid var(--border);
            outline: none;
            font-family: var(--font-b);
            font-size: .95rem;
            color: var(--ink);
            transition: border-color .3s;
            -webkit-appearance: none;
        }

        .field label {
            position: absolute;
            top: 1.35rem; left: 0;
            font-size: .9rem;
            color: var(--ink-muted);
            transition: all .22s cubic-bezier(.4,0,.2,1);
            pointer-events: none;
        }

        .field input:focus ~ label,
        .field input:not(:placeholder-shown) ~ label {
            top: .1rem;
            font-size: .65rem;
            color: var(--ember);
            letter-spacing: .1em;
            text-transform: uppercase;
            font-weight: 600;
        }

        .field-line {
            position: absolute;
            bottom: 0; left: 0;
            width: 0; height: 2px;
            background: var(--ember);
            transition: width .35s cubic-bezier(.4,0,.2,1);
            pointer-events: none;
        }
        .field:focus-within .field-line { width: 100%; }

        /* hint text */
        .field-hint {
            margin-top: .45rem;
            font-size: .73rem;
            color: var(--ink-muted);
            display: flex;
            align-items: center;
            gap: .3rem;
        }

        /* ── SUBMIT BUTTON ───────────────────────────────── */
        .btn-submit {
            width: 100%;
            padding: 1rem 1.5rem;
            background: var(--ember);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-family: var(--font-b);
            font-size: .93rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .55rem;
            position: relative;
            overflow: hidden;
            letter-spacing: .02em;
            transition: transform .2s, box-shadow .25s, background .2s;
            animation: fadeUp .5s .5s both;
        }
        .btn-submit::after {
            content: '';
            position: absolute; inset: 0;
            background: linear-gradient(110deg, transparent 30%, rgba(255,255,255,.18) 50%, transparent 70%);
            transform: translateX(-100%);
            transition: transform .55s ease;
        }
        .btn-submit:hover::after { transform: translateX(100%); }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 28px rgba(234,88,12,.38);
        }
        .btn-submit:active { transform: translateY(0); box-shadow: none; }
        .btn-submit:disabled {
            opacity: .7;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        .btn-submit i { transition: transform .3s; }
        .btn-submit:not(:disabled):hover i { transform: translateX(5px); }

        /* spinner inside button */
        .btn-spinner {
            width: 16px; height: 16px;
            border: 2px solid rgba(255,255,255,.4);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .6s linear infinite;
            flex-shrink: 0;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .btn-spinner { display: none; }
        .btn-submit.loading .btn-spinner { display: block; }
        .btn-submit.loading .btn-text { opacity: .8; }
        .btn-submit.loading .btn-icon { display: none; }

        /* form footer */
        .form-footer {
            margin-top: 2rem;
            text-align: center;
            animation: fadeUp .5s .55s both;
        }
        .form-footer p {
            color: var(--ink-muted);
            font-size: .85rem;
            margin-bottom: .875rem;
        }
        .form-footer a {
            color: var(--ember);
            font-weight: 600;
            text-decoration: none;
        }
        .form-footer a:hover { text-decoration: underline; }
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            color: var(--ink-muted) !important;
            font-size: .78rem;
            font-weight: 400 !important;
            transition: color .2s;
        }
        .back-link:hover { color: var(--ink) !important; text-decoration: none !important; }

        /* ── SUCCESS STATE ───────────────────────────────── */
        .success-wrap {
            text-align: center;
            animation: fadeUp .6s .2s both;
        }

        .success-icon-ring {
            width: 88px; height: 88px;
            border-radius: 50%;
            background: rgba(234,88,12,.08);
            border: 1.5px solid rgba(234,88,12,.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            position: relative;
            animation: ringPop .5s .3s cubic-bezier(.34,1.56,.64,1) both;
        }
        @keyframes ringPop {
            from { transform: scale(0); opacity: 0; }
            to   { transform: scale(1); opacity: 1; }
        }

        /* animated envelope icon */
        .success-icon-ring .env-icon {
            font-size: 2.2rem;
            color: var(--ember);
            animation: iconBounce .6s .6s cubic-bezier(.34,1.56,.64,1) both;
        }
        @keyframes iconBounce {
            from { transform: translateY(6px); opacity: 0; }
            to   { transform: translateY(0); opacity: 1; }
        }

        /* pulse ring */
        .success-icon-ring::before {
            content: '';
            position: absolute;
            inset: -8px;
            border-radius: 50%;
            border: 1px solid rgba(234,88,12,.15);
            animation: pulseRing 2s 1s ease-out infinite;
        }
        @keyframes pulseRing {
            0%   { transform: scale(1); opacity: 1; }
            100% { transform: scale(1.35); opacity: 0; }
        }

        .success-wrap h1 {
            font-family: var(--font-d);
            font-size: 2.8rem;
            font-weight: 700;
            color: var(--ink);
            line-height: 1;
            margin-bottom: .75rem;
            animation: fadeUp .5s .5s both;
        }
        .success-wrap p {
            color: var(--ink-muted);
            font-size: .9rem;
            line-height: 1.6;
            margin-bottom: .5rem;
            animation: fadeUp .5s .55s both;
        }
        .success-wrap .note {
            font-size: .78rem;
            color: var(--ink-muted);
            opacity: .7;
            margin-bottom: 2.5rem;
            animation: fadeUp .5s .6s both;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: .55rem;
            padding: .9rem 2rem;
            background: var(--ink);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-family: var(--font-b);
            font-size: .88rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: transform .2s, box-shadow .25s, background .2s;
            animation: fadeUp .5s .65s both;
        }
        .btn-back:hover {
            background: #2d1a0a;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(26,14,5,.22);
            text-decoration: none;
        }
        .btn-back:active { transform: translateY(0); }

        /* ── MOBILE ──────────────────────────────────────── */
        @media (max-width: 820px) {
            html, body { overflow: auto; }
            .auth-wrap { flex-direction: column; height: auto; min-height: 100vh; }
            .panel-left {
                width: 100%;
                height: auto;
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
                padding: 1.25rem 1.5rem;
                animation: none;
            }
            .panel-left::after { display: none; }
            .panel-tagline, .security-list, .ghost-text { display: none; }
            .orb { display: none; }
            .brand { margin: 0; }
            .panel-right { flex: 1; animation: none; padding-bottom: 2rem; }
            .form-wrap { padding: 2rem 1.5rem; }
            .form-header h1, .success-wrap h1 { font-size: 2.5rem; }

            .back-link-mobile {
                display: flex;
                align-items: center;
                gap: .35rem;
                color: rgba(255,255,255,.45);
                font-size: .78rem;
                text-decoration: none;
            }
        }
        @media (min-width: 821px) {
            .back-link-mobile { display: none; }
        }
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
            <div class="brand-img">
                <img src="../assets/logo.jpeg" alt="ARS Shop">
            </div>
            <span class="brand-name">ARS<em>SHOP</em></span>
        </a>

        <a href="../index.php" class="back-link-mobile">
            <i class="bi bi-arrow-left"></i> Shop
        </a>

        <div class="panel-tagline">
            <h2>Your account<br>security is our<br><em>top priority.</em></h2>
            <p>Reset in under a minute</p>
        </div>

        <div class="security-list">
            <div class="sec-item">
                <div class="sec-icon"><i class="bi bi-envelope-lock"></i></div>
                <span>Reset link sent to your registered email</span>
            </div>
            <div class="sec-item">
                <div class="sec-icon"><i class="bi bi-clock-history"></i></div>
                <span>Link expires in 1 hour for your safety</span>
            </div>
            <div class="sec-item">
                <div class="sec-icon"><i class="bi bi-shield-lock"></i></div>
                <span>Account details never shared</span>
            </div>
        </div>
    </div>

    <!-- ═══ RIGHT PANEL ══════════════════════════════════════ -->
    <div class="panel-right">
        <div class="form-wrap">

            <?php if ($success): ?>
            <!-- ── SUCCESS STATE ──────────────────────────── -->
            <div class="success-wrap">
                <div class="success-icon-ring">
                    <i class="bi bi-envelope-check-fill env-icon"></i>
                </div>

                <h1>Check your<br>inbox.</h1>
                <p>If an account exists with that email or mobile,<br>we've sent a password reset link.</p>
                <p class="note">Don't see it? Check your spam folder.</p>

                <a href="login.php" class="btn-back">
                    <i class="bi bi-arrow-left"></i>
                    Back to Sign In
                </a>
            </div>

            <?php else: ?>
            <!-- ── FORM STATE ─────────────────────────────── -->
            <div class="form-header">
                <h1>Forgot<br>password?</h1>
                <p>Enter your email or mobile and we'll send<br>a secure reset link right away.</p>
            </div>

            <?php if ($error): ?>
                <div class="alert-error">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form action="forgot-password.php" method="POST" id="resetForm" autocomplete="on">
                <?= csrf_field() ?>

                <div class="field">
                    <input type="text" name="email_or_mobile" id="email_or_mobile"
                           placeholder=" "
                           required autocomplete="username" inputmode="text">
                    <label for="email_or_mobile">Email or mobile number</label>
                    <div class="field-line"></div>
                    <div class="field-hint">
                        <i class="bi bi-info-circle"></i>
                        Use the email or number linked to your account
                    </div>
                </div>

                <button type="submit" class="btn-submit" id="resetBtn">
                    <div class="btn-spinner"></div>
                    <span class="btn-text">Send Reset Link</span>
                    <i class="bi bi-send btn-icon"></i>
                </button>
            </form>

            <div class="form-footer">
                <p>Remembered it? <a href="login.php">Sign in</a></p>
                <a href="../index.php" class="back-link">
                    <i class="bi bi-arrow-left"></i>Back to shop
                </a>
            </div>

            <?php endif; ?>

        </div>
    </div>

</div>

<script>
    const form = document.getElementById('resetForm');
    const btn  = document.getElementById('resetBtn');

    if (form && btn) {
        form.addEventListener('submit', function () {
            btn.disabled = true;
            btn.classList.add('loading');
            btn.querySelector('.btn-text').textContent = 'Sending…';
        });
    }
</script>
</body>
</html>
