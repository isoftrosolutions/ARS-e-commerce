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
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    
    $lockout = $limiter->getLockoutRemaining($ip, 'reset');
    if ($lockout > 0) {
        $error = "Too many reset attempts. Please try again later.";
    } else {
        $emailOrMobile = trim($_POST['email_or_mobile'] ?? '');
        
        if (empty($emailOrMobile)) {
            $error = "Please enter your email or mobile number.";
        } else {
            try {
                $stmt = $pdo->prepare("SELECT id, full_name, email, mobile FROM users WHERE mobile = ? OR email = ?");
                $stmt->execute([$emailOrMobile, $emailOrMobile]);
                $user = $stmt->fetch();
                
                $resetToken = bin2hex(random_bytes(32));
                $resetExpires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                $resetTokenHash = password_hash($resetToken, PASSWORD_DEFAULT);
                
                if ($user) {
                    $updateStmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
                    $updateStmt->execute([$resetTokenHash, $resetExpires, $user['id']]);
                    
                    $resetUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/reset-password.php?token=' . $resetToken;
                    
                    require_once __DIR__ . '/../includes/classes/EmailManager.php';
                    $emailMgr = new EmailManager($pdo);
                    $emailSent = $emailMgr->send($user['email'], $user['full_name'], 'password_reset', [
                        'name' => $user['full_name'],
                        'reset_url' => $resetUrl
                    ]);
                    
                    error_log("Password reset for {$user['email']}: email_sent=" . ($emailSent ? 'yes' : 'no'));
                }
                
                $success = true;
                
            } catch (PDOException $e) {
                error_log("Password reset error: " . $e->getMessage());
                $error = "Request failed. Please try again.";
            }
        }
        
        if ($error) {
            $limiter->record($ip, 'reset');
        } else {
            $limiter->reset($ip, 'reset');
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
    <style>
        @keyframes pulse-ring {
            0% { transform: scale(0.8); opacity: 1; }
            100% { transform: scale(2); opacity: 0; }
        }
        .animate-pulse-ring { animation: pulse-ring 1.5s ease-out infinite; }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        .animate-float { animation: float 2s ease-in-out infinite; }
    </style>
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
            <?php if ($success): ?>
                <div class="text-center" id="successContent">
                    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6 animate-float">
                        <i data-lucide="mail-check" class="w-10 h-10 text-green-600"></i>
                    </div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight mb-3">Check Your Email</h1>
                    <p class="text-slate-500 font-medium mb-4">
                        If an account exists with that email or mobile, we've sent password reset instructions.
                    </p>
                    <p class="text-slate-400 text-sm mb-8">
                        Check your inbox and spam folder.
                    </p>
                    <a href="login.php" class="inline-block w-full py-4 bg-slate-900 text-white rounded-2xl font-black text-center hover:bg-brand-600 transition-all">
                        Back to Login
                    </a>
                </div>
            <?php elseif (isset($_SESSION['sending_email'])): ?>
                <div class="text-center" id="sendingContent">
                    <div class="relative w-24 h-24 mx-auto mb-8">
                        <div class="absolute inset-0 bg-brand-500 rounded-full animate-pulse-ring opacity-20"></div>
                        <div class="absolute inset-0 bg-brand-500 rounded-full animate-pulse-ring opacity-40" style="animation-delay: 0.5s;"></div>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="w-16 h-16 bg-brand-600 rounded-full flex items-center justify-center">
                                <svg class="animate-spin h-8 w-8 text-white" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight mb-3">Sending Email...</h1>
                    <p class="text-slate-500 font-medium">
                        Please wait while we send the reset link to your email.
                    </p>
                </div>
                <meta http-equiv="refresh" content="2">
                <?php unset($_SESSION['sending_email']); ?>
            <?php else: ?>
                <div class="text-center mb-10">
                    <div class="w-16 h-16 bg-brand-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i data-lucide="key" class="w-8 h-8 text-brand-600"></i>
                    </div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight mb-2">Forgot Password?</h1>
                    <p class="text-slate-500 font-medium">Enter your email or mobile to reset password</p>
                </div>

                <?php if($error): ?>
                    <div class="bg-red-50 border border-red-100 text-red-600 px-4 py-3 rounded-2xl mb-8 flex items-center gap-3 text-sm font-bold">
                        <i data-lucide="alert-circle" class="w-5 h-5"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                <?php endif; ?>

                <form action="forgot-password.php" method="POST" id="resetForm" class="space-y-6">
                    <?= csrf_field() ?>
                    
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Email or Mobile</label>
                        <div class="relative group">
                            <input type="text" name="email_or_mobile" required placeholder="e.g. 98XXXXXXXX or email@example.com" 
                                   class="w-full pl-12 pr-4 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold focus:ring-2 focus:ring-brand-500/20 outline-none transition-all">
                            <i data-lucide="mail" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 group-focus-within:text-brand-600 transition-colors"></i>
                        </div>
                    </div>

                    <button type="submit" id="resetBtn" class="w-full py-5 bg-slate-900 text-white rounded-2xl font-black text-lg hover:bg-brand-600 transition-all transform hover:-translate-y-1 shadow-xl shadow-slate-200">
                        <span id="btnText">Send Reset Link</span>
                        <span id="btnLoading" class="hidden">
                            <svg class="animate-spin h-5 w-5 inline mr-2" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Sending...
                        </span>
                    </button>
                </form>

                <div class="mt-10 pt-10 border-t border-slate-100 text-center">
                    <p class="text-sm text-slate-500 font-medium">
                        Remember your password? <a href="login.php" class="text-brand-600 font-black hover:underline">Sign in</a>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        lucide.createIcons();
        
        const form = document.getElementById('resetForm');
        const btn = document.getElementById('resetBtn');
        const btnText = document.getElementById('btnText');
        const btnLoading = document.getElementById('btnLoading');
        
        if (form && btn) {
            form.addEventListener('submit', function(e) {
                btn.disabled = true;
                btn.classList.add('opacity-70', 'cursor-not-allowed');
                btnText.classList.add('hidden');
                btnLoading.classList.remove('hidden');
            });
        }
    </script>
</body>
</html>
