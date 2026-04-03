<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/rate-limiter.php';

if (is_logged_in()) {
    redirect('../index.php');
}

$limiter = new RateLimiter($pdo);
$limiter->createTable();

$ip = get_client_ip();
$error = null;
$lockout_remaining = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    
    $lockout_remaining = $limiter->getLockoutRemaining($ip, 'login');
    if ($lockout_remaining > 0) {
        $error = "Too many failed attempts. Please try again in " . ceil($lockout_remaining / 60) . " minutes.";
    } else {
        $mobile = filter_input(INPUT_POST, 'mobile', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $password = $_POST['password'] ?? '';

        if (empty($mobile) || empty($password)) {
            $error = "Please enter both credentials.";
        } else {
            try {
                $stmt = $pdo->prepare("SELECT id, full_name, email, mobile, password, role FROM users WHERE mobile = ? OR email = ?");
                $stmt->execute([$mobile, $mobile]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password'])) {
                    $limiter->reset($ip, 'login');
                    session_regenerate_id(true);
                    
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['full_name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['login_time'] = time();

                    if ($user['role'] === 'admin') {
                        redirect('../admin/dashboard.php', "Welcome back, Admin!");
                    } else {
                        redirect('../index.php', "Successfully logged in!");
                    }
                } else {
                    $limiter->record($ip, 'login');
                    $remaining = $limiter->getRemainingAttempts($ip, 'login');
                    $error = "Invalid credentials. " . ($remaining > 0 ? "($remaining attempts remaining)" : "");
                }
            } catch (PDOException $e) {
                error_log("Login error: " . $e->getMessage());
                $error = "Login failed. Please try again.";
            }
        }
    }
}

$lockout_remaining = $limiter->getLockoutRemaining($ip, 'login');
$page_title = "Login to your Account";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> | <?= SITE_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        brand: {
                            50: '#fff7ed',
                            100: '#ffedd5',
                            500: '#f97316',
                            600: '#ea580c',
                            900: '#7c2d12',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-50 text-slate-900 font-sans antialiased">
    <div class="min-h-screen flex flex-col items-center justify-center p-6">
        <a href="../index.php" class="flex items-center gap-2 mb-10 group">
            <div class="w-12 h-12 bg-brand-600 text-white rounded-2xl flex items-center justify-center transition-transform group-hover:scale-110 shadow-xl shadow-brand-600/20">
                <i data-lucide="shopping-bag" class="w-7 h-7"></i>
            </div>
            <span class="text-2xl font-black tracking-tighter text-slate-900">ARS<span class="text-brand-600">SHOP</span></span>
        </a>

        <div class="w-full max-w-md bg-white rounded-[2.5rem] soft-shadow border border-slate-100 p-8 md:p-12">
            <div class="text-center mb-10">
                <h1 class="text-3xl font-black text-slate-900 tracking-tight mb-2">Welcome Back</h1>
                <p class="text-slate-500 font-medium">Please enter your details to sign in</p>
            </div>

            <?php if($error): ?>
                <div class="bg-red-50 border border-red-100 text-red-600 px-4 py-3 rounded-2xl mb-8 flex items-center gap-3 text-sm font-bold">
                    <i data-lucide="alert-circle" class="w-5 h-5"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="space-y-6">
                <?= csrf_field() ?>
                
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Email or Mobile</label>
                    <div class="relative group">
                        <input type="text" name="mobile" required placeholder="e.g. 98XXXXXXXX" 
                               class="w-full pl-12 pr-4 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold focus:ring-2 focus:ring-brand-500/20 outline-none transition-all">
                        <i data-lucide="user" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 group-focus-within:text-brand-600 transition-colors"></i>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between items-center mb-2 ml-1">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Password</label>
                        <a href="forgot-password.php" class="text-[10px] font-black text-brand-600 uppercase tracking-widest hover:underline">Forgot?</a>
                    </div>
                    <div class="relative group">
                        <input type="password" name="password" required placeholder="••••••••" minlength="6"
                               class="w-full pl-12 pr-4 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold focus:ring-2 focus:ring-brand-500/20 outline-none transition-all">
                        <i data-lucide="lock" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 group-focus-within:text-brand-600 transition-colors"></i>
                    </div>
                </div>

                <button type="submit" class="w-full py-5 bg-slate-900 text-white rounded-2xl font-black text-lg hover:bg-brand-600 transition-all transform hover:-translate-y-1 shadow-xl shadow-slate-200" <?= $lockout_remaining > 0 ? 'disabled' : '' ?>>
                    <?= $lockout_remaining > 0 ? 'Please wait...' : 'Sign In' ?>
                </button>
            </form>

            <div class="mt-10 pt-10 border-t border-slate-100 text-center">
                <p class="text-sm text-slate-500 font-medium">
                    New to ARS Shop? <a href="signup.php" class="text-brand-600 font-black hover:underline">Create an account</a>
                </p>
            </div>
        </div>

        <p class="mt-10 text-xs font-bold text-slate-400 uppercase tracking-widest flex items-center gap-2">
            <i data-lucide="shield-check" class="w-4 h-4"></i>
            Secure AES-256 Encryption
        </p>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
