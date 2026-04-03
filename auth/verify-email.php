<?php
require_once __DIR__ . '/../includes/functions.php';

if (is_logged_in()) {
    redirect('../index.php');
}

$token = $_GET['token'] ?? '';
$error = null;
$success = false;

if (empty($token)) {
    $error = "Invalid verification link.";
} elseif (strlen($token) !== 64 || !ctype_xdigit($token)) {
    // Validate token format (should be 64 hex characters from bin2hex)
    $error = "Invalid verification link format.";
} else {
    try {
        // Hash the token from URL to compare with stored hash
        $tokenHash = hash('sha256', $token);
        
        $stmt = $pdo->prepare("SELECT id, full_name, email, is_verified FROM users WHERE verification_token = ? AND verification_expires > NOW() AND is_verified = 0 LIMIT 1");
        $stmt->execute([$tokenHash]);
        $user = $stmt->fetch();
        
        if (!$user) {
            // Check if token exists but user already verified
            $stmt2 = $pdo->prepare("SELECT id, is_verified FROM users WHERE verification_token = ?");
            $stmt2->execute([$tokenHash]);
            $existingUser = $stmt2->fetch();
            
            if ($existingUser && $existingUser['is_verified']) {
                $success = true;
                $alreadyVerified = true;
            } else {
                $error = "This verification link has expired or is invalid. Please register again.";
            }
        } else {
            $updateStmt = $pdo->prepare("UPDATE users SET is_verified = 1, verification_token = NULL, verification_expires = NULL WHERE id = ?");
            $updateStmt->execute([$user['id']]);
            $success = true;
            
            // Clear any dev tokens
            if (isset($_SESSION['dev_verify_token'])) {
                unset($_SESSION['dev_verify_token']);
                unset($_SESSION['dev_verify_email']);
            }
        }
    } catch (PDOException $e) {
        error_log("Email verification error: " . $e->getMessage());
        $error = "Verification failed. Please try again.";
    }
}

$page_title = "Verify Email";
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
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        .animate-float { animation: float 3s ease-in-out infinite; }
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
                <div class="text-center">
                    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6 animate-float">
                        <i data-lucide="check-circle" class="w-10 h-10 text-green-600"></i>
                    </div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight mb-3">
                        <?= isset($alreadyVerified) ? 'Already Verified!' : 'Email Verified!' ?>
                    </h1>
                    <p class="text-slate-500 font-medium mb-4">
                        <?= isset($alreadyVerified) 
                            ? 'Your email was already verified. You can login to your account.' 
                            : 'Your email has been successfully verified. You can now login to your account.' ?>
                    </p>
                    
                    <?php if (isset($_SESSION['dev_verify_token']) && isset($_SESSION['dev_verify_email'])): ?>
                        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-6 text-left">
                            <p class="text-yellow-700 text-xs font-bold mb-2">⚠️ DEV MODE - Email not configured:</p>
                            <p class="text-yellow-600 text-xs mb-1">Email: <?= htmlspecialchars($_SESSION['dev_verify_email']) ?></p>
                            <p class="text-yellow-600 text-xs break-all">Token: <?= htmlspecialchars($_SESSION['dev_verify_token']) ?></p>
                            <a href="verify-email.php?token=<?= htmlspecialchars($_SESSION['dev_verify_token']) ?>" class="text-yellow-700 text-xs font-bold hover:underline mt-2 inline-block">Click to verify</a>
                        </div>
                    <?php endif; ?>
                    
                    <a href="login.php" class="inline-block w-full py-4 bg-brand-600 text-white rounded-2xl font-black text-center hover:bg-brand-500 transition-all transform hover:-translate-y-1 shadow-xl shadow-brand-500/20">
                        Login Now
                    </a>
                </div>
            <?php else: ?>
                <div class="text-center">
                    <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i data-lucide="x-circle" class="w-10 h-10 text-red-600"></i>
                    </div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight mb-3">Verification Failed</h1>
                    <p class="text-slate-500 font-medium mb-8">
                        <?= htmlspecialchars($error) ?>
                    </p>
                    <?php if (strpos($error, 'expired') !== false || strpos($error, 'invalid') !== false): ?>
                        <a href="signup.php" class="inline-block w-full py-4 bg-slate-900 text-white rounded-2xl font-black text-center hover:bg-brand-600 transition-all">
                            Register Again
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="inline-block w-full py-4 bg-slate-900 text-white rounded-2xl font-black text-center hover:bg-brand-600 transition-all">
                            Back to Login
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
