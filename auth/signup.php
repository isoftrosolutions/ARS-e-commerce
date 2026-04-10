<?php
require_once __DIR__ . '/../includes/functions.php';

if (is_logged_in()) {
    redirect('../index.php');
}

$error = null;
$field_errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();

    $full_name        = trim($_POST['full_name'] ?? '');
    $mobile           = trim($_POST['mobile'] ?? '');
    $email            = trim($_POST['email'] ?? '');
    $address          = trim($_POST['address'] ?? '');
    $password         = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($full_name)) {
        $field_errors['full_name'] = 'Full name is required';
    } elseif (strlen($full_name) < 2) {
        $field_errors['full_name'] = 'Name must be at least 2 characters';
    }

    if (empty($mobile)) {
        $field_errors['mobile'] = 'Mobile number is required';
    } elseif (!validate_mobile($mobile)) {
        $field_errors['mobile'] = 'Invalid mobile number format';
    }

    if (empty($email)) {
        $field_errors['email'] = 'Email is required';
    } elseif (!validate_email($email)) {
        $field_errors['email'] = 'Invalid email address';
    }

    if (empty($password)) {
        $field_errors['password'] = 'Password is required';
    } elseif (strlen($password) < 8) {
        $field_errors['password'] = 'Password must be at least 8 characters long';
    }

    if ($password !== $confirm_password) {
        $field_errors['confirm_password'] = 'Passwords do not match';
    }

    if (empty($address)) {
        $field_errors['address'] = 'Address is required';
    }

    if (empty($field_errors)) {
        try {
            $checkStmt = $pdo->prepare("SELECT id FROM users WHERE mobile = ? OR email = ?");
            $checkStmt->execute([$mobile, $email]);
            if ($checkStmt->fetch()) {
                $field_errors['general'] = 'This mobile number or email is already registered.';
            } else {
                $hashedPassword = hash_password($password);

                $stmt = $pdo->prepare("INSERT INTO users (full_name, mobile, email, address, password, role) VALUES (?, ?, ?, ?, ?, 'customer')");
                $stmt->execute([$full_name, $mobile, $email, $address, $hashedPassword]);

                try {
                    require_once __DIR__ . '/../includes/classes/EmailManager.php';
                    $emailMgr = new EmailManager($pdo);
                    $emailMgr->queue($email, $full_name, 'welcome_email', ['name' => $full_name]);
                } catch (Exception $e) {
                    error_log("Welcome email error: " . $e->getMessage());
                }

                redirect('login.php', "Account created! Please login with your credentials.", "success");
            }
        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            $field_errors['general'] = "Registration failed. Please try again.";
        }
    }
}

$page_title = "Create Account";
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
            --void:       #130c06;
            --cream:      #fdfaf7;
            --ink:        #1a0e05;
            --ink-light:  #6b5c4e;
            --ink-muted:  #a89688;
            --border:     #e4d9d0;
            --red:        #ef4444;
            --green:      #22c55e;
            --font-d:     'Cormorant Garamond', Georgia, serif;
            --font-b:     'DM Sans', system-ui, sans-serif;
        }

        html, body {
            min-height: 100%;
            font-family: var(--font-b);
            background: var(--void);
        }

        /* ── LAYOUT ─────────────────────────────────────── */
        .auth-wrap {
            display: flex;
            min-height: 100vh;
        }

        /* ── LEFT PANEL ─────────────────────────────────── */
        .panel-left {
            width: 38%;
            background: var(--void);
            position: sticky;
            top: 0;
            height: 100vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            padding: 3.5rem;
            flex-shrink: 0;
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
            background: linear-gradient(to bottom, transparent, rgba(234,88,12,.22) 40%, rgba(234,88,12,.22) 60%, transparent);
        }

        .orb {
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
            filter: blur(70px);
        }
        .orb-1 {
            width: 320px; height: 320px;
            background: radial-gradient(circle, rgba(234,88,12,.22) 0%, transparent 70%);
            top: -80px; right: -80px;
            animation: orbFloat 7s ease-in-out infinite;
        }
        .orb-2 {
            width: 220px; height: 220px;
            background: radial-gradient(circle, rgba(217,119,6,.14) 0%, transparent 70%);
            bottom: 60px; left: -60px;
            animation: orbFloat 9s ease-in-out infinite reverse;
        }
        @keyframes orbFloat {
            0%,100% { transform: translateY(0) scale(1); }
            50%      { transform: translateY(-20px) scale(1.04); }
        }

        .ghost-text {
            position: absolute;
            bottom: -20px; right: -15px;
            font-family: var(--font-d);
            font-size: clamp(100px, 14vw, 170px);
            font-weight: 700;
            color: transparent;
            -webkit-text-stroke: 1px rgba(234,88,12,.07);
            letter-spacing: -.05em;
            line-height: 1;
            user-select: none;
            pointer-events: none;
        }

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

        .panel-tagline {
            position: relative;
            z-index: 1;
            margin-top: auto;
            margin-bottom: 3rem;
            animation: fadeUp .7s .25s both;
        }
        .panel-tagline h2 {
            font-family: var(--font-d);
            font-size: clamp(1.8rem, 2.8vw, 2.6rem);
            font-weight: 600;
            color: #fff;
            line-height: 1.18;
            margin-bottom: .7rem;
        }
        .panel-tagline h2 em { font-style: italic; color: var(--ember); }
        .panel-tagline p {
            color: rgba(255,255,255,.38);
            font-size: .78rem;
            letter-spacing: .14em;
            text-transform: uppercase;
        }

        /* step indicators */
        .steps {
            display: flex;
            flex-direction: column;
            gap: .7rem;
            position: relative;
            z-index: 1;
            animation: fadeUp .7s .35s both;
        }
        .step-item {
            display: flex;
            align-items: center;
            gap: .75rem;
            color: rgba(255,255,255,.45);
            font-size: .82rem;
        }
        .step-num {
            width: 26px; height: 26px;
            border-radius: 50%;
            border: 1px solid rgba(234,88,12,.3);
            background: rgba(234,88,12,.1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .7rem;
            font-weight: 600;
            color: var(--ember);
            flex-shrink: 0;
        }

        /* ── RIGHT PANEL ─────────────────────────────────── */
        .panel-right {
            flex: 1;
            background: var(--cream);
            display: flex;
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
            max-width: 560px;
            padding: 3rem 2.5rem;
        }

        .form-header {
            margin-bottom: 2.25rem;
            animation: fadeUp .55s .3s both;
        }
        .form-header h1 {
            font-family: var(--font-d);
            font-size: 3rem;
            font-weight: 700;
            color: var(--ink);
            line-height: .95;
            margin-bottom: .5rem;
        }
        .form-header p {
            color: var(--ink-muted);
            font-size: .875rem;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(18px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* error alert */
        .alert-error {
            display: flex;
            align-items: center;
            gap: .6rem;
            background: #fff5f5;
            border: 1px solid #fca5a5;
            border-left: 3px solid var(--red);
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

        /* ── FORM GRID ───────────────────────────────────── */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0 2rem;
        }
        .form-grid .field-full { grid-column: 1 / -1; }

        /* ── FLOATING LABEL FIELDS ───────────────────────── */
        .field {
            position: relative;
            margin-bottom: 1.875rem;
        }

        .field input,
        .field textarea {
            width: 100%;
            padding: 1.35rem 2.2rem 0.4rem 0;
            background: transparent;
            border: none;
            border-bottom: 1.5px solid var(--border);
            outline: none;
            font-family: var(--font-b);
            font-size: .93rem;
            color: var(--ink);
            transition: border-color .3s;
            -webkit-appearance: none;
        }
        .field textarea {
            padding-right: 0;
            resize: none;
            line-height: 1.55;
        }

        .field label {
            position: absolute;
            top: 1.3rem; left: 0;
            font-size: .88rem;
            color: var(--ink-muted);
            transition: all .22s cubic-bezier(.4,0,.2,1);
            pointer-events: none;
        }

        .field input:focus ~ label,
        .field input:not(:placeholder-shown) ~ label,
        .field textarea:focus ~ label,
        .field textarea:not(:placeholder-shown) ~ label {
            top: .1rem;
            font-size: .63rem;
            color: var(--ember);
            letter-spacing: .1em;
            text-transform: uppercase;
            font-weight: 600;
        }

        .field.is-invalid input,
        .field.is-invalid textarea {
            border-color: var(--red);
        }
        .field.is-invalid label { color: var(--red); }
        .field.is-invalid .field-line { background: var(--red); width: 100%; }
        .field.is-invalid input:focus ~ label,
        .field.is-invalid input:not(:placeholder-shown) ~ label,
        .field.is-invalid textarea:focus ~ label,
        .field.is-invalid textarea:not(:placeholder-shown) ~ label {
            color: var(--red);
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

        .field-error {
            display: flex;
            align-items: center;
            gap: .3rem;
            font-size: .72rem;
            color: var(--red);
            margin-top: .3rem;
        }
        .field-error i { font-size: .65rem; }

        /* password toggle */
        .toggle-pw {
            position: absolute;
            right: 0; top: 50%;
            transform: translateY(-25%);
            background: none; border: none;
            color: var(--ink-muted);
            cursor: pointer;
            padding: .25rem;
            font-size: .9rem;
            line-height: 1;
            transition: color .2s;
        }
        .toggle-pw:hover { color: var(--ember); }

        /* password strength bar */
        .pw-strength {
            margin-top: .4rem;
            height: 3px;
            border-radius: 2px;
            background: var(--border);
            overflow: hidden;
            display: none;
        }
        .pw-strength.visible { display: block; }
        .pw-strength-bar {
            height: 100%;
            width: 0;
            border-radius: 2px;
            transition: width .35s ease, background .35s ease;
        }
        .pw-strength-label {
            font-size: .65rem;
            color: var(--ink-muted);
            margin-top: .25rem;
            letter-spacing: .05em;
            text-transform: uppercase;
            display: none;
        }
        .pw-strength-label.visible { display: block; }

        /* terms checkbox */
        .terms-row {
            margin-bottom: 1.75rem;
            animation: fadeUp .5s .6s both;
        }
        .terms-check {
            display: flex;
            align-items: flex-start;
            gap: .6rem;
            cursor: pointer;
        }
        .terms-check input[type="checkbox"] {
            width: 16px; height: 16px;
            accent-color: var(--ember);
            margin-top: 2px;
            flex-shrink: 0;
            cursor: pointer;
        }
        .terms-check span {
            font-size: .82rem;
            color: var(--ink-muted);
            line-height: 1.4;
        }
        .terms-check a {
            color: var(--ember);
            text-decoration: none;
            font-weight: 500;
        }
        .terms-check a:hover { text-decoration: underline; }

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
            transition: transform .2s, box-shadow .25s;
            animation: fadeUp .5s .65s both;
        }
        .btn-submit::after {
            content: '';
            position: absolute;
            inset: 0;
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
        .btn-submit i { transition: transform .3s; }
        .btn-submit:hover i { transform: translateX(5px); }

        /* form footer */
        .form-footer {
            margin-top: 1.75rem;
            text-align: center;
            animation: fadeUp .5s .7s both;
        }
        .form-footer p {
            color: var(--ink-muted);
            font-size: .85rem;
            margin-bottom: .75rem;
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

        /* ── MOBILE ──────────────────────────────────────── */
        @media (max-width: 820px) {
            .auth-wrap { flex-direction: column; }
            .panel-left {
                width: 100%;
                position: static;
                height: auto;
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
                padding: 1.25rem 1.5rem;
                animation: none;
            }
            .panel-left::after { display: none; }
            .panel-tagline, .steps, .ghost-text { display: none; }
            .orb { display: none; }
            .brand { margin: 0; }
            .panel-right { animation: none; }
            .form-wrap { padding: 2rem 1.25rem; }
            .form-header h1 { font-size: 2.4rem; }
            .form-grid { grid-template-columns: 1fr; gap: 0; }
            .form-grid .field-full { grid-column: 1; }

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
            <h2>Join thousands of<br><em>happy shoppers</em><br>across Nepal.</h2>
            <p>Free to join — always</p>
        </div>

        <div class="steps">
            <div class="step-item">
                <div class="step-num">1</div>
                <span>Create your free account</span>
            </div>
            <div class="step-item">
                <div class="step-num">2</div>
                <span>Browse 500+ products</span>
            </div>
            <div class="step-item">
                <div class="step-num">3</div>
                <span>Fast delivery to your door</span>
            </div>
        </div>
    </div>

    <!-- ═══ RIGHT PANEL ══════════════════════════════════════ -->
    <div class="panel-right">
        <div class="form-wrap">

            <div class="form-header">
                <h1>Create<br>account.</h1>
                <p>Join 10,000+ shoppers — it only takes a minute</p>
            </div>

            <?php if (isset($field_errors['general'])): ?>
                <div class="alert-error">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <?= htmlspecialchars($field_errors['general']) ?>
                </div>
            <?php endif; ?>

            <form action="signup.php" method="POST" autocomplete="on" novalidate>
                <?= csrf_field() ?>

                <div class="form-grid">

                    <!-- Full Name -->
                    <div class="field <?= isset($field_errors['full_name']) ? 'is-invalid' : '' ?>" style="animation: fadeUp .5s .35s both;">
                        <input type="text" name="full_name" id="full_name"
                               placeholder=" "
                               value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>"
                               required minlength="2" autocomplete="name">
                        <label for="full_name">Full name</label>
                        <div class="field-line"></div>
                        <?php if (isset($field_errors['full_name'])): ?>
                            <div class="field-error"><i class="bi bi-exclamation-circle"></i><?= htmlspecialchars($field_errors['full_name']) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Mobile -->
                    <div class="field <?= isset($field_errors['mobile']) ? 'is-invalid' : '' ?>" style="animation: fadeUp .5s .4s both;">
                        <input type="tel" name="mobile" id="mobile"
                               placeholder=" "
                               value="<?= htmlspecialchars($_POST['mobile'] ?? '') ?>"
                               required inputmode="numeric" autocomplete="tel">
                        <label for="mobile">Mobile number</label>
                        <div class="field-line"></div>
                        <?php if (isset($field_errors['mobile'])): ?>
                            <div class="field-error"><i class="bi bi-exclamation-circle"></i><?= htmlspecialchars($field_errors['mobile']) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Email -->
                    <div class="field field-full <?= isset($field_errors['email']) ? 'is-invalid' : '' ?>" style="animation: fadeUp .5s .45s both;">
                        <input type="email" name="email" id="email"
                               placeholder=" "
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                               required autocomplete="email">
                        <label for="email">Email address</label>
                        <div class="field-line"></div>
                        <?php if (isset($field_errors['email'])): ?>
                            <div class="field-error"><i class="bi bi-exclamation-circle"></i><?= htmlspecialchars($field_errors['email']) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Password -->
                    <div class="field <?= isset($field_errors['password']) ? 'is-invalid' : '' ?>" style="animation: fadeUp .5s .5s both;">
                        <input type="password" name="password" id="password"
                               placeholder=" "
                               required minlength="8" autocomplete="new-password">
                        <label for="password">Password</label>
                        <div class="field-line"></div>
                        <button type="button" class="toggle-pw" id="togglePassword" tabindex="-1">
                            <i class="bi bi-eye"></i>
                        </button>
                        <?php if (isset($field_errors['password'])): ?>
                            <div class="field-error"><i class="bi bi-exclamation-circle"></i><?= htmlspecialchars($field_errors['password']) ?></div>
                        <?php endif; ?>
                        <div class="pw-strength" id="pwStrength"><div class="pw-strength-bar" id="pwStrengthBar"></div></div>
                        <div class="pw-strength-label" id="pwStrengthLabel"></div>
                    </div>

                    <!-- Confirm Password -->
                    <div class="field <?= isset($field_errors['confirm_password']) ? 'is-invalid' : '' ?>" style="animation: fadeUp .5s .55s both;">
                        <input type="password" name="confirm_password" id="confirm_password"
                               placeholder=" "
                               required minlength="8" autocomplete="new-password">
                        <label for="confirm_password">Confirm password</label>
                        <div class="field-line"></div>
                        <button type="button" class="toggle-pw" id="toggleConfirmPassword" tabindex="-1">
                            <i class="bi bi-eye"></i>
                        </button>
                        <?php if (isset($field_errors['confirm_password'])): ?>
                            <div class="field-error"><i class="bi bi-exclamation-circle"></i><?= htmlspecialchars($field_errors['confirm_password']) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Address -->
                    <div class="field field-full <?= isset($field_errors['address']) ? 'is-invalid' : '' ?>" style="animation: fadeUp .5s .57s both;">
                        <textarea name="address" id="address"
                                  placeholder=" "
                                  rows="2"
                                  required><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                        <label for="address">Delivery address</label>
                        <div class="field-line"></div>
                        <?php if (isset($field_errors['address'])): ?>
                            <div class="field-error"><i class="bi bi-exclamation-circle"></i><?= htmlspecialchars($field_errors['address']) ?></div>
                        <?php endif; ?>
                    </div>

                </div><!-- /form-grid -->

                <div class="terms-row">
                    <label class="terms-check">
                        <input type="checkbox" required id="terms">
                        <span>I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></span>
                    </label>
                </div>

                <button type="submit" class="btn-submit">
                    <span>Create My Account</span>
                    <i class="bi bi-arrow-right"></i>
                </button>
            </form>

            <div class="form-footer">
                <p>Already have an account? <a href="login.php">Sign in</a></p>
                <a href="../index.php" class="back-link">
                    <i class="bi bi-arrow-left"></i>Back to shop
                </a>
            </div>

        </div>
    </div>

</div>

<script>
    // Password toggles
    function setupToggle(btnId, inputId) {
        const btn = document.getElementById(btnId);
        const inp = document.getElementById(inputId);
        if (!btn || !inp) return;
        btn.addEventListener('click', () => {
            const hide = inp.type === 'password';
            inp.type = hide ? 'text' : 'password';
            btn.querySelector('i').className = hide ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    }
    setupToggle('togglePassword', 'password');
    setupToggle('toggleConfirmPassword', 'confirm_password');

    // Password strength meter
    const pwInput    = document.getElementById('password');
    const strengthEl = document.getElementById('pwStrength');
    const barEl      = document.getElementById('pwStrengthBar');
    const labelEl    = document.getElementById('pwStrengthLabel');

    function getStrength(pw) {
        let score = 0;
        if (pw.length >= 8)  score++;
        if (pw.length >= 12) score++;
        if (/[A-Z]/.test(pw)) score++;
        if (/[0-9]/.test(pw)) score++;
        if (/[^A-Za-z0-9]/.test(pw)) score++;
        return score;
    }

    pwInput && pwInput.addEventListener('input', () => {
        const pw = pwInput.value;
        if (!pw) {
            strengthEl.classList.remove('visible');
            labelEl.classList.remove('visible');
            return;
        }
        strengthEl.classList.add('visible');
        labelEl.classList.add('visible');
        const score = getStrength(pw);
        const levels = [
            { pct: '20%', color: '#ef4444', label: 'Very weak' },
            { pct: '40%', color: '#f97316', label: 'Weak' },
            { pct: '60%', color: '#eab308', label: 'Fair' },
            { pct: '80%', color: '#22c55e', label: 'Strong' },
            { pct: '100%',color: '#16a34a', label: 'Very strong' },
        ];
        const lvl = levels[Math.min(score - 1, 4)] || levels[0];
        barEl.style.width = lvl.pct;
        barEl.style.background = lvl.color;
        labelEl.textContent = lvl.label;
        labelEl.style.color = lvl.color;
    });
</script>
</body>
</html>
