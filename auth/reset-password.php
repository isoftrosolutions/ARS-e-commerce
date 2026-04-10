<?php
require_once __DIR__ . '/../includes/functions.php';

if (is_logged_in()) {
    redirect('../index.php');
}

// Guard: must have passed OTP verification
if (
    empty($_SESSION['otp_reset_verified']) ||
    empty($_SESSION['otp_reset_user_id']) ||
    time() > ($_SESSION['otp_reset_expires'] ?? 0)
) {
    redirect('forgot-password.php', 'Please verify your OTP first.', 'danger');
}

$userId      = (int)$_SESSION['otp_reset_user_id'];
$success     = false;
$error       = null;
$fieldErrors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();

    $password        = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validate password first
    if (empty($password)) {
        $fieldErrors['password'] = 'Password is required.';
    } else {
        // Enforce strength in backend
        $strengthErrors = validate_password_strength($password);
        if (!empty($strengthErrors)) {
            $fieldErrors['password'] = $strengthErrors[0]; // Take first error
        }
    }

    // Only check confirm if password itself passed
    if (empty($fieldErrors['password']) && $password !== $confirmPassword) {
        $fieldErrors['confirm_password'] = 'Passwords do not match.';
    }

    if (empty($fieldErrors)) {
        try {
            $pdo->beginTransaction();

            $hashedPassword = hash_password($password);

            $stmt = $pdo->prepare(
                "UPDATE users
                 SET password = ?, remember_token = NULL, reset_token = NULL, reset_expires = NULL,
                     reset_token_used_at = NOW(), otp_attempts = 0, otp_issued_at = NULL
                 WHERE id = ?"
            );
            $stmt->execute([$hashedPassword, $userId]);

            $pdo->commit();

            csrf_rotate(); // Invalidate token after successful password change

            // Send security alert email
            $userEmail = $_SESSION['otp_reset_user_email'] ?? '';
            $userName  = $_SESSION['otp_reset_user_name'] ?? 'User';
            if ($userEmail) {
                send_password_reset_notification($userEmail, $userName);
            }

            // Invalidate any existing sessions from other tabs/devices
            session_regenerate_id(true);

            // Clear all OTP reset session state
            unset(
                $_SESSION['otp_reset_verified'],
                $_SESSION['otp_reset_user_id'],
                $_SESSION['otp_reset_expires'],
                $_SESSION['otp_reset_user_email'],
                $_SESSION['otp_reset_user_name']
            );

            $success = true;

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log("Reset password update error: " . $e->getMessage());
            $error = "Failed to reset password. Please try again.";
        }
    }
}

$page_title = "Set New Password";
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
            --green:      #16a34a;
            --red:        #ef4444;
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
        .orb-1 { width: 380px; height: 380px; background: radial-gradient(circle, rgba(234,88,12,.22) 0%, transparent 70%); top: -120px; right: -100px; animation: orbFloat 7s ease-in-out infinite; }
        .orb-2 { width: 260px; height: 260px; background: radial-gradient(circle, rgba(217,119,6,.14) 0%, transparent 70%); bottom: 40px; left: -70px; animation: orbFloat 9s ease-in-out infinite reverse; }
        @keyframes orbFloat {
            0%, 100% { transform: translateY(0) scale(1); }
            50%       { transform: translateY(-22px) scale(1.04); }
        }
        .ghost-text {
            position: absolute; bottom: -30px; right: -20px;
            font-family: var(--font-d); font-size: clamp(110px, 16vw, 190px);
            font-weight: 700; color: transparent;
            -webkit-text-stroke: 1px rgba(234,88,12,.07);
            letter-spacing: -.05em; line-height: 1;
            user-select: none; pointer-events: none;
        }
        .brand { display: flex; align-items: center; gap: .875rem; text-decoration: none; position: relative; z-index: 1; animation: fadeUp .6s .15s both; }
        .brand-img { width: 46px; height: 46px; border-radius: 12px; overflow: hidden; background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.08); padding: 5px; flex-shrink: 0; }
        .brand-img img { width: 100%; height: 100%; object-fit: contain; }
        .brand-name { font-family: var(--font-d); font-size: 1.7rem; font-weight: 700; color: #fff; letter-spacing: .02em; line-height: 1; }
        .brand-name em { font-style: normal; color: var(--ember); }
        .panel-tagline { position: relative; z-index: 1; margin-top: auto; margin-bottom: 3.5rem; animation: fadeUp .7s .25s both; }
        .panel-tagline h2 { font-family: var(--font-d); font-size: clamp(2rem, 3.2vw, 3rem); font-weight: 600; color: #fff; line-height: 1.15; margin-bottom: .75rem; }
        .panel-tagline h2 em { font-style: italic; color: var(--ember); }
        .panel-tagline p { color: rgba(255,255,255,.38); font-size: .8rem; letter-spacing: .14em; text-transform: uppercase; }
        .security-list { display: flex; flex-direction: column; gap: .875rem; position: relative; z-index: 1; animation: fadeUp .7s .35s both; }
        .sec-item { display: flex; align-items: center; gap: .75rem; color: rgba(255,255,255,.5); font-size: .82rem; }
        .sec-icon { width: 34px; height: 34px; background: rgba(234,88,12,.12); border: 1px solid rgba(234,88,12,.2); border-radius: 9px; display: flex; align-items: center; justify-content: center; color: var(--ember); font-size: .95rem; flex-shrink: 0; }

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
        .form-wrap { width: 100%; max-width: 420px; padding: 2.5rem 2rem; }

        /* ── FORM HEADER ── */
        .form-header { margin-bottom: 2.25rem; animation: fadeUp .55s .3s both; }
        .form-header h1 { font-family: var(--font-d); font-size: 3.2rem; font-weight: 700; color: var(--ink); line-height: .95; margin-bottom: .5rem; }
        .form-header p { color: var(--ink-muted); font-size: .875rem; line-height: 1.5; }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(18px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── ALERT ── */
        .alert-error {
            display: flex; align-items: center; gap: .6rem;
            background: #fff5f5; border: 1px solid #fca5a5; border-left: 3px solid var(--red);
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

        /* ── SUCCESS STATE ── */
        .success-wrap { text-align: center; animation: fadeUp .5s .2s both; }
        .success-icon {
            width: 80px; height: 80px; margin: 0 auto 1.75rem;
            background: rgba(22,163,74,.1); border: 2px solid rgba(22,163,74,.25);
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-size: 2rem; color: var(--green);
            animation: popIn .5s .1s cubic-bezier(.34,1.56,.64,1) both;
        }
        @keyframes popIn {
            from { transform: scale(0); opacity: 0; }
            to   { transform: scale(1); opacity: 1; }
        }
        .success-wrap h2 { font-family: var(--font-d); font-size: 2.4rem; font-weight: 700; color: var(--ink); margin-bottom: .6rem; line-height: 1.1; }
        .success-wrap p { color: var(--ink-muted); font-size: .9rem; line-height: 1.6; margin-bottom: 2rem; }
        .redirect-bar {
            height: 3px; background: var(--border); border-radius: 2px;
            margin-bottom: 1.5rem; overflow: hidden;
        }
        .redirect-bar-fill {
            height: 100%; width: 100%; background: var(--ember);
            border-radius: 2px;
            animation: drainBar 5s linear forwards;
        }
        @keyframes drainBar {
            from { width: 100%; }
            to   { width: 0%; }
        }
        .redirect-hint { font-size: .75rem; color: var(--ink-muted); margin-bottom: 1.5rem; }

        /* ── FLOATING LABEL FIELDS ── */
        .field { position: relative; margin-bottom: 1.75rem; }
        .field:nth-child(1) { animation: fadeUp .5s .35s both; }
        .field:nth-child(2) { animation: fadeUp .5s .42s both; }

        .field input {
            width: 100%; padding: 1.4rem 2.8rem 0.45rem 0;
            background: transparent; border: none;
            border-bottom: 1.5px solid var(--border);
            outline: none; font-family: var(--font-b);
            font-size: .95rem; color: var(--ink);
            transition: border-color .3s; -webkit-appearance: none;
        }
        .field.is-invalid input { border-color: var(--red); }

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
        .field.is-invalid label,
        .field.is-invalid input:focus ~ label,
        .field.is-invalid input:not(:placeholder-shown) ~ label { color: var(--red); }

        .field-line {
            position: absolute; bottom: 0; left: 0;
            width: 0; height: 2px; background: var(--ember);
            transition: width .35s cubic-bezier(.4,0,.2,1); pointer-events: none;
        }
        .field:focus-within .field-line { width: 100%; }
        .field.is-invalid .field-line { background: var(--red); width: 100%; }

        .field-error { font-size: .72rem; color: var(--red); margin-top: .35rem; display: flex; align-items: center; gap: .3rem; }
        .field-error i { font-size: .65rem; }

        /* password toggle */
        .toggle-pw {
            position: absolute; right: 0; top: 50%;
            transform: translateY(-25%);
            background: none; border: none; color: var(--ink-muted);
            cursor: pointer; padding: .25rem; font-size: .95rem; line-height: 1;
            transition: color .2s;
        }
        .toggle-pw:hover { color: var(--ember); }

        /* strength bar */
        .pw-strength-wrap { margin-top: .5rem; animation: fadeUp .3s ease; }
        .pw-strength-track { height: 3px; background: var(--border); border-radius: 2px; overflow: hidden; }
        .pw-strength-bar { height: 100%; width: 0; border-radius: 2px; transition: width .35s ease, background .35s ease; }
        .pw-strength-label { font-size: .65rem; color: var(--ink-muted); margin-top: .25rem; letter-spacing: .05em; text-transform: uppercase; }

        /* ── REQUIREMENTS CHECKLIST ── */
        .pw-rules { margin-top: .6rem; display: flex; flex-direction: column; gap: .25rem; animation: fadeUp .3s ease; }
        .pw-rule { display: flex; align-items: center; gap: .4rem; font-size: .72rem; color: var(--ink-muted); transition: color .2s; }
        .pw-rule i { font-size: .65rem; transition: color .2s; }
        .pw-rule.ok { color: var(--green); }
        .pw-rule.ok i::before { content: '\f26b'; } /* bi-check-circle-fill */

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

        /* ── LOGIN LINK ── */
        .btn-login {
            width: 100%; padding: 1rem 1.5rem;
            background: var(--ink); color: #fff;
            border: none; border-radius: 10px;
            font-family: var(--font-b); font-size: .93rem; font-weight: 600;
            cursor: pointer; text-decoration: none;
            display: flex; align-items: center; justify-content: center; gap: .55rem;
            transition: background .2s, transform .2s;
        }
        .btn-login:hover { background: #2d1a0c; transform: translateY(-2px); color: #fff; }

        /* ── FORM FOOTER ── */
        .form-footer { margin-top: 1.75rem; text-align: center; animation: fadeUp .5s .55s both; }
        .form-footer a { color: var(--ember); font-weight: 600; text-decoration: none; font-size: .85rem; }
        .form-footer a:hover { text-decoration: underline; }
        .back-link { display: inline-flex; align-items: center; gap: .35rem; color: var(--ink-muted) !important; font-size: .78rem; font-weight: 400 !important; transition: color .2s; }
        .back-link:hover { color: var(--ink) !important; text-decoration: none !important; }

        /* ── MOBILE ≤ 820px ── */
        @media (max-width: 820px) {
            html, body { overflow: auto; }
            .auth-wrap { flex-direction: column; height: auto; min-height: 100vh; }
            .panel-left {
                width: 100%; flex-direction: row; align-items: center;
                justify-content: space-between; padding: 1.25rem 1.5rem;
                animation: none; min-height: 70px; flex-shrink: 0;
            }
            .panel-left::after { display: none; }
            .panel-tagline, .security-list, .ghost-text, .orb { display: none; }
            .brand { margin: 0; }
            .panel-right { flex: 1; animation: none; padding-bottom: 2rem; align-items: flex-start; overflow-y: auto; -webkit-overflow-scrolling: touch; }
            .form-wrap { padding: 2rem 1.5rem 2rem; max-width: 100%; width: 100%; }
            .form-header h1 { font-size: 2.5rem; }
            .panel-left .back-link-mobile { display: flex; align-items: center; gap: .35rem; color: rgba(255,255,255,.45); font-size: .78rem; text-decoration: none; }
        }
        @media (max-width: 400px) {
            .form-wrap { padding: 1.5rem 1rem 2rem; }
            .form-header h1 { font-size: 2rem; }
            .btn-submit, .btn-login { padding: .875rem 1rem; font-size: .88rem; }
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
            <?php if ($success): ?>
                <h2>You're all<br><em>set!</em><br>Login with<br>your new password.</h2>
            <?php else: ?>
                <h2>Almost<br><em>done.</em><br>Set a strong<br>password.</h2>
            <?php endif; ?>
            <p>Secure account recovery</p>
        </div>

        <div class="security-list">
            <div class="sec-item">
                <div class="sec-icon"><i class="bi bi-shield-lock"></i></div>
                <span>Passwords are bcrypt-hashed</span>
            </div>
            <div class="sec-item">
                <div class="sec-icon"><i class="bi bi-key"></i></div>
                <span>Min 8 chars · uppercase · number</span>
            </div>
            <div class="sec-item">
                <div class="sec-icon"><i class="bi bi-arrow-repeat"></i></div>
                <span>Old sessions invalidated on reset</span>
            </div>
        </div>
    </div>

    <!-- ═══ RIGHT PANEL ══════════════════════════════════════ -->
    <div class="panel-right">
        <div class="form-wrap">

            <?php if ($success): ?>
            <!-- ── Success State ── -->
            <div class="success-wrap">
                <div class="success-icon"><i class="bi bi-check-lg"></i></div>
                <h2>Password<br>updated!</h2>
                <p>Your password has been successfully reset. You can now sign in with your new credentials.</p>
                <div class="redirect-bar"><div class="redirect-bar-fill"></div></div>
                <p class="redirect-hint">Redirecting to login in <span id="countdown">5</span>s…</p>
                <a href="login.php" class="btn-login">
                    <i class="bi bi-box-arrow-in-right"></i>
                    <span>Sign In Now</span>
                </a>
            </div>

            <?php else: ?>
            <!-- ── Set Password Form ── -->
            <div class="form-header">
                <h1>New<br>password.</h1>
                <p>Choose something strong and unique.</p>
            </div>

            <?php if ($error): ?>
            <div class="alert-error">
                <i class="bi bi-exclamation-circle-fill"></i>
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <form action="reset-password.php" method="POST" id="resetForm" novalidate>
                <?= csrf_field() ?>

                <!-- New Password -->
                <div class="field <?= isset($fieldErrors['password']) ? 'is-invalid' : '' ?>">
                    <input type="password" name="password" id="password"
                           placeholder=" " required
                           autocomplete="new-password">
                    <label for="password">New password</label>
                    <div class="field-line"></div>
                    <button type="button" class="toggle-pw" id="togglePw" tabindex="-1" aria-label="Show password">
                        <i class="bi bi-eye" id="togglePwIcon"></i>
                    </button>
                    <?php if (isset($fieldErrors['password'])): ?>
                    <div class="field-error"><i class="bi bi-exclamation-circle"></i><?= htmlspecialchars($fieldErrors['password']) ?></div>
                    <?php endif; ?>
                    <!-- Strength bar -->
                    <div class="pw-strength-wrap">
                        <div class="pw-strength-track"><div class="pw-strength-bar"></div></div>
                        <div class="pw-strength-label"></div>
                    </div>
                    <!-- Rules checklist -->
                    <div class="pw-rules">
                        <div class="pw-rule" id="rule-len"><i class="bi bi-circle"></i>At least 8 characters</div>
                        <div class="pw-rule" id="rule-upper"><i class="bi bi-circle"></i>One uppercase letter</div>
                        <div class="pw-rule" id="rule-lower"><i class="bi bi-circle"></i>One lowercase letter</div>
                        <div class="pw-rule" id="rule-num"><i class="bi bi-circle"></i>One number</div>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div class="field <?= isset($fieldErrors['confirm_password']) ? 'is-invalid' : '' ?>">
                    <input type="password" name="confirm_password" id="confirm_password"
                           placeholder=" " required
                           autocomplete="new-password">
                    <label for="confirm_password">Confirm password</label>
                    <div class="field-line"></div>
                    <button type="button" class="toggle-pw" id="toggleConfirm" tabindex="-1" aria-label="Show confirm password">
                        <i class="bi bi-eye" id="toggleConfirmIcon"></i>
                    </button>
                    <?php if (isset($fieldErrors['confirm_password'])): ?>
                    <div class="field-error"><i class="bi bi-exclamation-circle"></i><?= htmlspecialchars($fieldErrors['confirm_password']) ?></div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn-submit" id="resetBtn">
                    <span>Reset Password</span>
                    <i class="bi bi-arrow-right"></i>
                </button>
            </form>

            <div class="form-footer">
                <a href="login.php" class="back-link">
                    <i class="bi bi-arrow-left"></i>Back to login
                </a>
            </div>
            <?php endif; ?>

        </div>
    </div>

</div>

<script>
// ── Password toggles ──────────────────────────────────────────
function setupToggle(btnId, inputId, iconId) {
    const btn  = document.getElementById(btnId);
    const inp  = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    if (!btn || !inp) return;
    btn.addEventListener('click', () => {
        const show = inp.type === 'password';
        inp.type = show ? 'text' : 'password';
        if (icon) icon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
    });
}
setupToggle('togglePw', 'password', 'togglePwIcon');
setupToggle('toggleConfirm', 'confirm_password', 'toggleConfirmIcon');

// ── Live password strength meter + checklist ──────────────────
const pwInput       = document.getElementById('password');
const strengthTrack = document.querySelector('.pw-strength-track');
const strengthBar   = document.querySelector('.pw-strength-bar');
const strengthLabel = document.querySelector('.pw-strength-label');
const rules = {
    len:   { el: document.getElementById('rule-len'),   test: v => v.length >= 8 },
    upper: { el: document.getElementById('rule-upper'), test: v => /[A-Z]/.test(v) },
    lower: { el: document.getElementById('rule-lower'), test: v => /[a-z]/.test(v) },
    num:   { el: document.getElementById('rule-num'),   test: v => /[0-9]/.test(v) },
};

const levels = [
    { pct: 25,  bg: '#ef4444', label: 'Weak' },
    { pct: 50,  bg: '#f59e0b', label: 'Fair' },
    { pct: 75,  bg: '#3b82f6', label: 'Good' },
    { pct: 100, bg: '#22c55e', label: 'Strong' },
];

if (pwInput && strengthBar) {
    pwInput.addEventListener('input', () => {
        const val   = pwInput.value;
        let passing = 0;
        Object.values(rules).forEach(r => {
            const ok = r.test(val);
            if (r.el) r.el.classList.toggle('ok', ok);
            if (ok) passing++;
        });

        const lvl = levels[passing - 1] || { pct: 0, bg: 'transparent', label: '' };
        strengthBar.style.width      = lvl.pct + '%';
        strengthBar.style.background = lvl.bg;
        if (strengthLabel) strengthLabel.textContent = lvl.label;
    });
}

// ── Submit loading state ──────────────────────────────────────
const resetForm = document.getElementById('resetForm');
const resetBtn  = document.getElementById('resetBtn');
if (resetForm && resetBtn) {
    resetForm.addEventListener('submit', () => {
        resetBtn.disabled = true;
        resetBtn.innerHTML = '<span>Resetting…</span>';
    });
}

<?php if ($success): ?>
// ── Auto-redirect countdown ───────────────────────────────────
const countdownEl = document.getElementById('countdown');
let timeLeft = 5;
const tick = setInterval(() => {
    timeLeft--;
    if (countdownEl) countdownEl.textContent = timeLeft;
    if (timeLeft <= 0) {
        clearInterval(tick);
        window.location.href = 'login.php';
    }
}, 1000);
<?php endif; ?>
</script>
</body>
</html>
