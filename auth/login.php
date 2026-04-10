<?php
require_once __DIR__ . '/../includes/functions.php';

if (is_logged_in()) {
    redirect('../index.php');
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();

    $rawInput = trim($_POST['mobile'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = !empty($_POST['remember']);

    if (empty($rawInput) || empty($password)) {
        $error = "Please enter both credentials.";
    } else {
        // ── Rate Limiting: 5 failed attempts per IP per 15 minutes ──────────
        $ip = get_client_ip();
        $rlKey   = 'login_attempts_' . md5($ip);
        $rlCount = $_SESSION[$rlKey] ?? 0;
        $rlSince = $_SESSION[$rlKey . '_since'] ?? 0;

        // Reset window if 15 minutes have elapsed
        if (time() - $rlSince > 900) {
            $rlCount = 0;
            $rlSince = time();
        }

        if ($rlCount >= 5) {
            $wait = ceil((900 - (time() - $rlSince)) / 60);
            $error = "Too many failed login attempts. Please wait {$wait} minute(s) and try again.";
        } else {
            try {
                // Normalize input: strip country code, non-digits for mobile;
                // if it looks like an email keep as-is.
                $normalizedInput = (strpos($rawInput, '@') !== false)
                    ? strtolower($rawInput)
                    : normalize_mobile($rawInput);

                $stmt = $pdo->prepare(
                    "SELECT id, full_name, email, mobile, password, role FROM users
                     WHERE mobile = ? OR email = ?"
                );
                $stmt->execute([$normalizedInput, $normalizedInput]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password'])) {
                    // Reset rate-limit on successful login
                    unset($_SESSION[$rlKey], $_SESSION[$rlKey . '_since']);

                    session_regenerate_id(true);
                    csrf_rotate(); // Token rotation on state-change

                    $_SESSION['user_id']    = $user['id'];
                    $_SESSION['user_name']  = $user['full_name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['role']       = $user['role'];
                    $_SESSION['login_time'] = time();

                    // Remember-me: 30-day persistent cookie
                    if ($remember) {
                        $token = bin2hex(random_bytes(32));
                        $expiry = time() + (30 * 24 * 3600);
                        // Store hashed token in DB
                        $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?")
                            ->execute([hash('sha256', $token), $user['id']]);
                        $cookieParams = session_get_cookie_params();
                        setcookie(
                            'remember_me',
                            $user['id'] . ':' . $token,
                            $expiry,
                            $cookieParams['path'] ?: '/',
                            $cookieParams['domain'],
                            $cookieParams['secure'],
                            true // HttpOnly
                        );
                    }

                    if ($user['role'] === 'admin') {
                        redirect('../admin/dashboard.php', "Welcome back, Admin!");
                    } else {
                        redirect('../index.php', "Successfully logged in!");
                    }
                } else {
                    // Increment rate-limit counter on failure
                    $_SESSION[$rlKey] = $rlCount + 1;
                    $_SESSION[$rlKey . '_since'] = $rlSince ?: time();
                    $remaining = 4 - $rlCount;
                    $error = $remaining > 0
                        ? "Invalid credentials. {$remaining} attempt(s) remaining before lockout."
                        : "Invalid credentials. Too many failures — wait 15 minutes.";
                }
            } catch (PDOException $e) {
                error_log("Login error: " . $e->getMessage());
                $error = "Login failed. Please try again.";
            }
        }
    }
}

$page_title = "Sign In";
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
            --ember-glow: rgba(234, 88, 12, 0.18);
            --void:       #130c06;
            --void-2:     #1e1308;
            --cream:      #fdfaf7;
            --ink:        #1a0e05;
            --ink-light:  #6b5c4e;
            --ink-muted:  #a89688;
            --border:     #e4d9d0;
            --font-d:     'Cormorant Garamond', Georgia, serif;
            --font-b:     'DM Sans', system-ui, sans-serif;
        }

        html, body {
            height: 100%;
            font-family: var(--font-b);
            background: var(--void);
            overflow: hidden;
        }

        /* ── LAYOUT ─────────────────────────────────────── */
        .auth-wrap {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* ── LEFT PANEL ─────────────────────────────────── */
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

        /* grid overlay */
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

        /* decorative separator line */
        .panel-left::after {
            content: '';
            position: absolute;
            top: 0; right: 0;
            width: 1px;
            height: 100%;
            background: linear-gradient(to bottom, transparent, rgba(234,88,12,.25) 40%, rgba(234,88,12,.25) 60%, transparent);
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

        /* ghost brand text */
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

        /* brand logo */
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
            padding: 5px;
            flex-shrink: 0;
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

        /* tagline block */
        .panel-tagline {
            position: relative;
            z-index: 1;
            margin-top: auto;
            margin-bottom: 3.5rem;
            animation: fadeUp .7s .25s both;
        }
        .panel-tagline h2 {
            font-family: var(--font-d);
            font-size: clamp(2rem, 3.2vw, 3rem);
            font-weight: 600;
            color: #fff;
            line-height: 1.15;
            margin-bottom: .75rem;
        }
        .panel-tagline h2 em {
            font-style: italic;
            color: var(--ember);
        }
        .panel-tagline p {
            color: rgba(255,255,255,.38);
            font-size: .8rem;
            letter-spacing: .14em;
            text-transform: uppercase;
        }

        /* trust badges */
        .trust-badges {
            display: flex;
            flex-direction: column;
            gap: .875rem;
            position: relative;
            z-index: 1;
            animation: fadeUp .7s .35s both;
        }
        .badge-item {
            display: flex;
            align-items: center;
            gap: .75rem;
            color: rgba(255,255,255,.5);
            font-size: .82rem;
        }
        .badge-icon {
            width: 34px; height: 34px;
            background: rgba(234,88,12,.12);
            border: 1px solid rgba(234,88,12,.2);
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--ember);
            font-size: .95rem;
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

        /* form header */
        .form-header {
            margin-bottom: 2.5rem;
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

        /* ── FLOATING LABEL FIELDS ───────────────────────── */
        .field {
            position: relative;
            margin-bottom: 2rem;
        }
        .field:nth-child(1) { animation: fadeUp .5s .38s both; }
        .field:nth-child(2) { animation: fadeUp .5s .45s both; }
        .field:nth-child(3) { animation: fadeUp .5s .52s both; }

        .field input {
            width: 100%;
            padding: 1.4rem 2.2rem 0.45rem 0;
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
            top: 1.35rem;
            left: 0;
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

        /* animated bottom line */
        .field-line {
            position: absolute;
            bottom: 0; left: 0;
            width: 0; height: 2px;
            background: var(--ember);
            transition: width .35s cubic-bezier(.4,0,.2,1);
            pointer-events: none;
        }
        .field:focus-within .field-line { width: 100%; }

        /* password toggle */
        .toggle-pw {
            position: absolute;
            right: 0; top: 50%;
            transform: translateY(-25%);
            background: none; border: none;
            color: var(--ink-muted);
            cursor: pointer;
            padding: .25rem;
            font-size: .95rem;
            line-height: 1;
            transition: color .2s;
        }
        .toggle-pw:hover { color: var(--ember); }

        /* remember + forgot row */
        .form-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            animation: fadeUp .5s .52s both;
        }
        .remember {
            display: flex;
            align-items: center;
            gap: .45rem;
            cursor: pointer;
            font-size: .83rem;
            color: var(--ink-light);
            user-select: none;
        }
        .remember input[type="checkbox"] {
            width: 15px; height: 15px;
            accent-color: var(--ember);
            cursor: pointer;
        }
        .form-meta a {
            font-size: .83rem;
            color: var(--ember);
            text-decoration: none;
            font-weight: 500;
        }
        .form-meta a:hover { text-decoration: underline; }

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
            animation: fadeUp .5s .58s both;
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

        /* footer links */
        .form-footer {
            margin-top: 2rem;
            text-align: center;
            animation: fadeUp .5s .63s both;
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
        .back-link i { font-size: .75rem; }

        /* ── SECURITY NOTE ───────────────────────────────── */
        .security-note {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .4rem;
            color: var(--ink-muted);
            font-size: .72rem;
            margin-top: 1.5rem;
            opacity: .7;
            animation: fadeUp .5s .68s both;
        }

        /* ── MOBILE ──────────────────────────────────────── */
        @media (max-width: 820px) {
            html, body { overflow: auto; }
            .auth-wrap { flex-direction: column; height: auto; min-height: 100vh; }

            .panel-left {
                width: 100%;
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
                padding: 1.25rem 1.5rem;
                animation: none;
                min-height: 70px;
            }
            .panel-left::after { display: none; }
            .panel-tagline, .trust-badges, .ghost-text { display: none; }
            .orb { display: none; }
            .brand { margin: 0; }

            .panel-right {
                flex: 1;
                align-items: flex-start;
                animation: none;
                padding-bottom: 2rem;
            }
            .form-wrap { padding: 2rem 1.5rem 1.5rem; max-width: 100%; }
            .form-header h1 { font-size: 2.5rem; }

            .panel-left .back-link-mobile {
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
            <h2>Shop with<br><em>confidence.</em><br>Delivered<br>to your door.</h2>
            <p>Nepal's trusted marketplace</p>
        </div>

        <div class="trust-badges">
            <div class="badge-item">
                <div class="badge-icon"><i class="bi bi-shield-check"></i></div>
                <span>Secure Payments</span>
            </div>
            <div class="badge-item">
                <div class="badge-icon"><i class="bi bi-truck"></i></div>
                <span>Fast Delivery Across Nepal</span>
            </div>
            <div class="badge-item">
                <div class="badge-icon"><i class="bi bi-arrow-counterclockwise"></i></div>
                <span>5-Day Easy Returns</span>
            </div>
        </div>
    </div>

    <!-- ═══ RIGHT PANEL ══════════════════════════════════════ -->
    <div class="panel-right">
        <div class="form-wrap">

            <div class="form-header">
                <h1>Welcome<br>back.</h1>
                <p>Sign in to your ARS account</p>
            </div>

            <?php if ($error): ?>
                <div class="alert-error">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" autocomplete="on">
                <?= csrf_field() ?>

                <div class="field">
                    <input type="text" name="mobile" id="mobile"
                           placeholder=" "
                           value="<?= htmlspecialchars($_POST['mobile'] ?? '') ?>"
                           required autocomplete="username" inputmode="text">
                    <label for="mobile">Mobile number or email</label>
                    <div class="field-line"></div>
                </div>

                <div class="field">
                    <input type="password" name="password" id="password"
                           placeholder=" "
                           required autocomplete="current-password">
                    <label for="password">Password</label>
                    <div class="field-line"></div>
                    <button type="button" class="toggle-pw" id="togglePassword" tabindex="-1" aria-label="Toggle password">
                        <i class="bi bi-eye" id="toggleIcon"></i>
                    </button>
                </div>

                <div class="form-meta">
                    <label class="remember">
                        <input type="checkbox" name="remember">
                        <span>Remember me</span>
                    </label>
                    <a href="forgot-password.php">Forgot password?</a>
                </div>

                <button type="submit" class="btn-submit">
                    <span>Sign In</span>
                    <i class="bi bi-arrow-right"></i>
                </button>
            </form>

            <div class="form-footer">
                <p>Don't have an account? <a href="signup.php">Create one free</a></p>
                <a href="../index.php" class="back-link">
                    <i class="bi bi-arrow-left"></i>Back to shop
                </a>
            </div>

            <div class="security-note">
                <i class="bi bi-lock-fill"></i>
                Secured with end-to-end encryption
            </div>

        </div>
    </div>

</div>

<script>
    // Password toggle
    const toggleBtn  = document.getElementById('togglePassword');
    const pwInput    = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            const isPassword = pwInput.type === 'password';
            pwInput.type = isPassword ? 'text' : 'password';
            toggleIcon.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    }
</script>
</body>
</html>
