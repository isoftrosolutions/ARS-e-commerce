<?php
require_once __DIR__ . '/../includes/functions.php';

if (is_logged_in()) {
    redirect('../index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = htmlspecialchars(trim($_POST['full_name']));
    $mobile = htmlspecialchars(trim($_POST['mobile']));
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $address = htmlspecialchars(trim($_POST['address']));
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (full_name, mobile, email, address, password, role) VALUES (?, ?, ?, ?, ?, 'customer')");
        $stmt->execute([$full_name, $mobile, $email, $address, $password]);
        
        // --- NEW: Email Integration ---
        require_once __DIR__ . '/../includes/classes/EmailManager.php';
        $emailMgr = new EmailManager($pdo);
        $emailMgr->queue($email, $full_name, 'welcome_email', ['name' => $full_name]);
        // ------------------------------

        redirect('login.php', "Signup successful! Please login with your mobile number.", "success");
    } catch (PDOException $e) {
        $error = "Registration failed. This mobile or email is already registered.";
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
    <div class="min-h-screen flex flex-col items-center justify-center p-6 py-12">
        <!-- Logo -->
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

            <?php if(isset($error)): ?>
                <div class="bg-red-50 border border-red-100 text-red-600 px-4 py-3 rounded-2xl mb-8 flex items-center gap-3 text-sm font-bold">
                    <i data-lucide="alert-circle" class="w-5 h-5"></i>
                    <span><?= $error ?></span>
                </div>
            <?php endif; ?>

            <form action="signup.php" method="POST" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Full Name</label>
                        <input type="text" name="full_name" required placeholder="e.g. Hari Prasad" 
                               class="w-full px-4 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold focus:ring-2 focus:ring-brand-500/20 outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Mobile Number</label>
                        <input type="tel" name="mobile" required placeholder="98XXXXXXXX" 
                               class="w-full px-4 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold focus:ring-2 focus:ring-brand-500/20 outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Email Address</label>
                        <input type="email" name="email" required placeholder="hari@example.com" 
                               class="w-full px-4 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold focus:ring-2 focus:ring-brand-500/20 outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Password</label>
                        <input type="password" name="password" required placeholder="••••••••" 
                               class="w-full px-4 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold focus:ring-2 focus:ring-brand-500/20 outline-none transition-all">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Delivery Address</label>
                        <textarea name="address" required rows="3" placeholder="Street, City, Ward No." 
                                  class="w-full px-4 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold focus:ring-2 focus:ring-brand-500/20 outline-none transition-all"></textarea>
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
