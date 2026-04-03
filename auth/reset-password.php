<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/rate-limiter.php';

if (is_logged_in()) {
    redirect('../index.php');
}

$token = $_GET['token'] ?? '';
$error = null;
$success = false;
$userId = null;

if (empty($token)) {
    $error = "Invalid reset link.";
} elseif (strlen($token) !== 64 || !ctype_xdigit($token)) {
    $error = "Invalid reset link format.";
} else {
    try {
        $stmt = $pdo->prepare("SELECT id, full_name, email FROM users WHERE reset_expires > NOW() LIMIT 1");
        $stmt->execute();
        $users = $stmt->fetchAll();
        
        foreach ($users as $user) {
            if ($user['reset_token'] && password_verify($token, $user['reset_token'])) {
                $userId = $user['id'];
                break;
            }
        }
        
        if (!$userId) {
            $error = "This reset link has expired or is invalid. Please request a new one.";
        }
    } catch (PDOException $e) {
        error_log("Reset password error: " . $e->getMessage());
        $error = "Invalid reset link.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $userId) {
    require_csrf();
    
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $fieldErrors = [];
    
    if (empty($password)) {
        $fieldErrors['password'] = 'Password is required';
    } else {
        $password_errors = validate_password_strength($password);
        if (!empty($password_errors)) {
            $fieldErrors['password'] = implode(', ', $password_errors);
        }
    }
    
    if ($password !== $confirmPassword) {
        $fieldErrors['confirm_password'] = 'Passwords do not match';
    }
    
    if (empty($fieldErrors)) {
        try {
            $pdo->beginTransaction();
            
            // Check if token was already used (race condition prevention)
            $checkStmt = $pdo->prepare("SELECT reset_token FROM users WHERE id = ? AND reset_token IS NOT NULL");
            $checkStmt->execute([$userId]);
            $tokenExists = $checkStmt->fetch();
            
            if (!$tokenExists) {
                $pdo->rollBack();
                $error = "This reset link has already been used.";
            } else {
                $hashedPassword = hash_password($password);
                
                // Single-use: Update password and clear token in one atomic operation
                $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL, reset_token_used_at = NOW() WHERE id = ? AND reset_token IS NOT NULL");
                $stmt->execute([$hashedPassword, $userId]);
                
                if ($stmt->rowCount() === 0) {
                    $pdo->rollBack();
                    $error = "This reset link has already been used.";
                } else {
                    $pdo->commit();
                    $success = true;
                    
                    // Invalidate all other sessions for this user (security hardening)
                    invalidate_other_sessions($pdo, $userId);
                }
            }
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
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
            <?php if ($success): ?>
                <div class="text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i data-lucide="check-circle" class="w-8 h-8 text-green-600"></i>
                    </div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight mb-3">Password Reset!</h1>
                    <p class="text-slate-500 font-medium mb-8">
                        Your password has been successfully reset. You can now login with your new password.
                    </p>
                    <a href="login.php" class="inline-block w-full py-4 bg-brand-600 text-white rounded-2xl font-black text-center hover:bg-brand-500 transition-all">
                        Login Now
                    </a>
                </div>
            <?php elseif ($error): ?>
                <div class="text-center">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i data-lucide="x-circle" class="w-8 h-8 text-red-600"></i>
                    </div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight mb-3">Invalid Link</h1>
                    <p class="text-slate-500 font-medium mb-8">
                        <?= htmlspecialchars($error) ?>
                    </p>
                    <a href="forgot-password.php" class="inline-block w-full py-4 bg-slate-900 text-white rounded-2xl font-black text-center hover:bg-brand-600 transition-all">
                        Request New Link
                    </a>
                </div>
            <?php else: ?>
                <div class="text-center mb-10">
                    <div class="w-16 h-16 bg-brand-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i data-lucide="lock" class="w-8 h-8 text-brand-600"></i>
                    </div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight mb-2">Set New Password</h1>
                    <p class="text-slate-500 font-medium">Create a strong password for your account</p>
                </div>

                <?php if(isset($fieldErrors['general'])): ?>
                    <div class="bg-red-50 border border-red-100 text-red-600 px-4 py-3 rounded-2xl mb-8 flex items-center gap-3 text-sm font-bold">
                        <i data-lucide="alert-circle" class="w-5 h-5"></i>
                        <span><?= htmlspecialchars($fieldErrors['general']) ?></span>
                    </div>
                <?php endif; ?>

                <form action="reset-password.php?token=<?= htmlspecialchars($token) ?>" method="POST" class="space-y-6">
                    <?= csrf_field() ?>
                    
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">New Password</label>
                        <div class="relative">
                            <input type="password" name="password" id="password" required minlength="8" placeholder="Min 8 chars, 1 uppercase, 1 number" 
                                   class="w-full px-4 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold focus:ring-2 focus:ring-brand-500/20 outline-none transition-all pr-12 <?= isset($fieldErrors['password']) ? 'ring-2 ring-red-300' : '' ?>">
                            <button type="button" id="togglePassword" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-brand-600 transition-colors">
                                <i data-lucide="eye" class="w-5 h-5"></i>
                            </button>
                        </div>
                        <div id="passwordStrength" class="mt-2 hidden">
                            <div class="flex gap-1">
                                <div id="strength1" class="h-1 flex-1 rounded-full bg-slate-200"></div>
                                <div id="strength2" class="h-1 flex-1 rounded-full bg-slate-200"></div>
                                <div id="strength3" class="h-1 flex-1 rounded-full bg-slate-200"></div>
                                <div id="strength4" class="h-1 flex-1 rounded-full bg-slate-200"></div>
                            </div>
                            <p id="strengthText" class="text-xs mt-1 text-slate-400"></p>
                        </div>
                        <?php if (isset($fieldErrors['password'])): ?>
                            <p class="text-red-500 text-xs mt-1"><?= htmlspecialchars($fieldErrors['password']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Confirm Password</label>
                        <div class="relative">
                            <input type="password" name="confirm_password" id="confirm_password" required minlength="8" placeholder="Repeat new password" 
                                   class="w-full px-4 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold focus:ring-2 focus:ring-brand-500/20 outline-none transition-all pr-12 <?= isset($fieldErrors['confirm_password']) ? 'ring-2 ring-red-300' : '' ?>">
                            <button type="button" id="toggleConfirmPassword" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-brand-600 transition-colors">
                                <i data-lucide="eye" class="w-5 h-5"></i>
                            </button>
                        </div>
                        <?php if (isset($fieldErrors['confirm_password'])): ?>
                            <p class="text-red-500 text-xs mt-1"><?= htmlspecialchars($fieldErrors['confirm_password']) ?></p>
                        <?php endif; ?>
                    </div>

                    <button type="submit" id="resetBtn" class="w-full py-5 bg-slate-900 text-white rounded-2xl font-black text-lg hover:bg-brand-600 transition-all transform hover:-translate-y-1 shadow-xl shadow-slate-200">
                        Reset Password
                    </button>
                </form>
            <?php endif; ?>

            <div class="mt-10 pt-10 border-t border-slate-100 text-center">
                <p class="text-sm text-slate-500 font-medium">
                    <a href="login.php" class="text-brand-600 font-black hover:underline">Back to Login</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
        
        function setupPasswordToggle(toggleId, inputId) {
            const toggle = document.getElementById(toggleId);
            const input = document.getElementById(inputId);
            
            if (toggle && input) {
                toggle.addEventListener('click', function() {
                    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                    input.setAttribute('type', type);
                    const icon = this.querySelector('i');
                    if (icon) {
                        icon.setAttribute('data-lucide', type === 'password' ? 'eye' : 'eye-off');
                        lucide.createIcons();
                    }
                });
            }
        }
        
        setupPasswordToggle('togglePassword', 'password');
        setupPasswordToggle('toggleConfirmPassword', 'confirm_password');
        
        const passwordInput = document.getElementById('password');
        const strengthDiv = document.getElementById('passwordStrength');
        const strengthBars = [
            document.getElementById('strength1'),
            document.getElementById('strength2'),
            document.getElementById('strength3'),
            document.getElementById('strength4')
        ];
        const strengthText = document.getElementById('strengthText');
        
        function updateStrength(password) {
            if (!password) {
                strengthDiv.classList.add('hidden');
                return;
            }
            strengthDiv.classList.remove('hidden');
            
            let score = 0;
            if (password.length >= 8) score++;
            if (/[A-Z]/.test(password)) score++;
            if (/[a-z]/.test(password)) score++;
            if (/[0-9]/.test(password) || /[^A-Za-z0-9]/.test(password)) score++;
            
            const colors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-green-500'];
            const labels = ['Weak', 'Fair', 'Good', 'Strong'];
            
            strengthBars.forEach((bar, i) => {
                bar.className = 'h-1 flex-1 rounded-full ' + (i < score ? colors[score - 1] : 'bg-slate-200');
            });
            
            strengthText.textContent = password.length > 0 ? labels[score - 1] || 'Too short' : '';
        }
        
        passwordInput?.addEventListener('input', (e) => updateStrength(e.target.value));
        
        document.getElementById('resetBtn')?.addEventListener('click', function() {
            if (!this.disabled) {
                this.disabled = true;
                this.innerHTML = '<span class="flex items-center justify-center gap-2"><svg class="animate-spin h-5 w-5" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Resetting...</span>';
                this.closest('form')?.submit();
            }
        });
    </script>
</body>
</html>
