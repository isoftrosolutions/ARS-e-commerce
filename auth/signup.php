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
$field_errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    
    $lockout = $limiter->getLockoutRemaining($ip, 'register');
    if ($lockout > 0) {
        $error = "Too many registration attempts. Please try again later.";
    } else {
        $full_name = trim($_POST['full_name'] ?? '');
        $mobile = trim($_POST['mobile'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $password = $_POST['password'] ?? '';
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
        } else {
            $password_errors = validate_password_strength($password);
            if (!empty($password_errors)) {
                $field_errors['password'] = implode(', ', $password_errors);
            }
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
                    
                    $limiter->reset($ip, 'register');
                    
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
        } else {
            $limiter->record($ip, 'register');
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
    <div class="min-h-screen flex flex-col items-center justify-center p-6 py-12">
        <a href="../index.php" class="flex items-center gap-2 mb-10 group">
            <div class="w-12 h-12 bg-brand-600 text-white rounded-2xl flex items-center justify-center transition-transform group-hover:scale-110 shadow-xl shadow-brand-600/20">
                <i data-lucide="shopping-bag" class="w-7 h-7"></i>
            </div>
            <span class="text-2xl font-black tracking-tighter text-slate-900">ARS<span class="text-brand-600">SHOP</span></span>
        </a>

        <div class="w-full max-w-2xl bg-white rounded-[2.5rem] soft-shadow border border-slate-100 p-8 md:p-12">
            <div class="text-center mb-10">
                <h1 class="text-3xl font-black text-slate-900 tracking-tight mb-2">Create Account</h1>
                <p class="text-slate-500 font-medium">Join 10,000+ shoppers across Nepal</p>
            </div>

            <?php if(isset($field_errors['general'])): ?>
                <div class="bg-red-50 border border-red-100 text-red-600 px-4 py-3 rounded-2xl mb-8 flex items-center gap-3 text-sm font-bold">
                    <i data-lucide="alert-circle" class="w-5 h-5"></i>
                    <span><?= htmlspecialchars($field_errors['general']) ?></span>
                </div>
            <?php endif; ?>

            <form action="signup.php" method="POST" class="space-y-6">
                <?= csrf_field() ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Full Name</label>
                        <input type="text" name="full_name" required minlength="2" placeholder="e.g. Hari Prasad" 
                               value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>"
                               class="w-full px-4 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold focus:ring-2 focus:ring-brand-500/20 outline-none transition-all <?= isset($field_errors['full_name']) ? 'ring-2 ring-red-300' : '' ?>">
                        <?php if (isset($field_errors['full_name'])): ?>
                            <p class="text-red-500 text-xs mt-1"><?= htmlspecialchars($field_errors['full_name']) ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Mobile Number</label>
                        <input type="tel" name="mobile" required pattern="[0-9]{9,12}" placeholder="98XXXXXXXX" 
                               value="<?= htmlspecialchars($_POST['mobile'] ?? '') ?>"
                               class="w-full px-4 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold focus:ring-2 focus:ring-brand-500/20 outline-none transition-all <?= isset($field_errors['mobile']) ? 'ring-2 ring-red-300' : '' ?>">
                        <?php if (isset($field_errors['mobile'])): ?>
                            <p class="text-red-500 text-xs mt-1"><?= htmlspecialchars($field_errors['mobile']) ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Email Address</label>
                        <input type="email" name="email" required placeholder="hari@example.com" 
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                               class="w-full px-4 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold focus:ring-2 focus:ring-brand-500/20 outline-none transition-all <?= isset($field_errors['email']) ? 'ring-2 ring-red-300' : '' ?>">
                        <?php if (isset($field_errors['email'])): ?>
                            <p class="text-red-500 text-xs mt-1"><?= htmlspecialchars($field_errors['email']) ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Password</label>
                        <input type="password" name="password" required minlength="8" placeholder="Min 8 chars, 1 uppercase, 1 number" 
                               class="w-full px-4 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold focus:ring-2 focus:ring-brand-500/20 outline-none transition-all <?= isset($field_errors['password']) ? 'ring-2 ring-red-300' : '' ?>">
                        <?php if (isset($field_errors['password'])): ?>
                            <p class="text-red-500 text-xs mt-1"><?= htmlspecialchars($field_errors['password']) ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Confirm Password</label>
                        <input type="password" name="confirm_password" required minlength="8" placeholder="Repeat password" 
                               class="w-full px-4 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold focus:ring-2 focus:ring-brand-500/20 outline-none transition-all <?= isset($field_errors['confirm_password']) ? 'ring-2 ring-red-300' : '' ?>">
                        <?php if (isset($field_errors['confirm_password'])): ?>
                            <p class="text-red-500 text-xs mt-1"><?= htmlspecialchars($field_errors['confirm_password']) ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Delivery Address</label>
                        <textarea name="address" required rows="3" placeholder="Street, City, Ward No." 
                                  class="w-full px-4 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold focus:ring-2 focus:ring-brand-500/20 outline-none transition-all <?= isset($field_errors['address']) ? 'ring-2 ring-red-300' : '' ?>"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                        <?php if (isset($field_errors['address'])): ?>
                            <p class="text-red-500 text-xs mt-1"><?= htmlspecialchars($field_errors['address']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="flex items-center gap-3 py-2">
                    <input type="checkbox" required class="w-5 h-5 rounded-lg border-slate-200 text-brand-600 focus:ring-brand-500/20">
                    <p class="text-xs text-slate-500 font-medium">I agree to the <a href="#" class="text-brand-600 font-bold hover:underline">Terms of Service</a> and <a href="#" class="text-brand-600 font-bold hover:underline">Privacy Policy</a>.</p>
                </div>

                <button type="submit" class="w-full py-5 bg-slate-900 text-white rounded-2xl font-black text-lg hover:bg-brand-600 transition-all transform hover:-translate-y-1 shadow-xl shadow-slate-200">
                    Create My Account
                </button>
            </form>

            <div class="mt-10 pt-10 border-t border-slate-100 text-center">
                <p class="text-sm text-slate-500 font-medium">
                    Already have an account? <a href="login.php" class="text-brand-600 font-black hover:underline">Sign in instead</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
