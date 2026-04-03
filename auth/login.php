<?php
require_once __DIR__ . '/../includes/functions.php';

if (is_logged_in()) {
    redirect('../index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mobile = $_POST['mobile'];
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE mobile = ? OR email = ?");
        $stmt->execute([$mobile, $mobile]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            
            if ($user['role'] === 'admin') {
                redirect('../admin/dashboard.php', "Welcome back, Admin!");
            } else {
                redirect('../index.php', "Successfully logged in!");
            }
        } else {
            $error = "Invalid mobile/email or password.";
        }
    } catch (PDOException $e) {
        $error = "Login failed: " . $e->getMessage();
    }
}

$page_title = "Login to your Account";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> | <?= SITE_NAME ?></title>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Tailwind CSS -->
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
        <!-- Logo -->
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

            <?php if(isset($error)): ?>
                <div class="bg-red-50 border border-red-100 text-red-600 px-4 py-3 rounded-2xl mb-8 flex items-center gap-3 text-sm font-bold animate-pulse">
                    <i data-lucide="alert-circle" class="w-5 h-5"></i>
                    <span><?= $error ?></span>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="space-y-6">
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
                        <a href="#" class="text-[10px] font-black text-brand-600 uppercase tracking-widest hover:underline">Forgot?</a>
                    </div>
                    <div class="relative group">
                        <input type="password" name="password" required placeholder="••••••••" 
                               class="w-full pl-12 pr-4 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold focus:ring-2 focus:ring-brand-500/20 outline-none transition-all">
                        <i data-lucide="lock" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 group-focus-within:text-brand-600 transition-colors"></i>
                    </div>
                </div>

                <button type="submit" class="w-full py-5 bg-slate-900 text-white rounded-2xl font-black text-lg hover:bg-brand-600 transition-all transform hover:-translate-y-1 shadow-xl shadow-slate-200">
                    Sign In
                </button>
            </form>

            <div class="mt-10 pt-10 border-t border-slate-100 text-center">
                <p class="text-sm text-slate-500 font-medium">
                    New to ARS Shop? <a href="signup.php" class="text-brand-600 font-black hover:underline">Create an account</a>
                </p>
            </div>
        </div>

        <!-- Footer -->
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
