<?php
require_once __DIR__ . '/../includes/functions.php';

if (is_logged_in()) {
    redirect('../index.php');
}

$ip = get_client_ip();
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    
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
                $error = "Invalid credentials.";
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            $error = "Login failed. Please try again.";
        }
    }
}

$page_title = "Login";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> | <?= SITE_NAME ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            --ars-primary: #ea580c;
            --ars-primary-hover: #c2410c;
        }
        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
        }
        .auth-card {
            border: none;
            border-radius: 1.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
        }
        .brand-logo {
            width: 56px;
            height: 56px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            padding: 6px;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
        }
        .brand-text {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.02em;
        }
        .form-control {
            border-radius: 0.75rem;
            padding: 0.875rem 1rem;
            border: 2px solid #e2e8f0;
            font-weight: 500;
        }
        .form-control:focus {
            border-color: var(--ars-primary);
            box-shadow: 0 0 0 3px rgba(234, 88, 12, 0.1);
        }
        .input-group-text {
            border-radius: 0.75rem 0 0 0.75rem;
            border: 2px solid #e2e8f0;
            border-right: none;
            background: #f8fafc;
        }
        .btn-primary {
            background: var(--ars-primary);
            border-color: var(--ars-primary);
            border-radius: 0.75rem;
            padding: 0.875rem 1.5rem;
            font-weight: 600;
        }
        .btn-primary:hover {
            background: var(--ars-primary-hover);
            border-color: var(--ars-primary-hover);
        }
        .social-login .btn {
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100 py-5">
            <div class="col-12 col-sm-10 col-md-8 col-lg-5">
                
                <!-- Logo -->
                <div class="text-center mb-4">
                    <a href="../index.php" class="text-decoration-none">
                        <div class="brand-logo d-inline-flex align-items-center justify-content-center mb-3">
                            <img src="../assets/logo.jpeg" alt="ARS Shop Logo" style="width:100%;height:100%;object-fit:contain;">
                        </div>
                        <span class="brand-text d-block text-dark">ARS<span class="text-primary">SHOP</span></span>
                    </a>
                </div>
                
                <!-- Card -->
                <div class="card auth-card">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <h3 class="fw-bold text-dark mb-1">Welcome Back</h3>
                            <p class="text-muted">Sign in to your account</p>
                        </div>
                        
                        <?php if($error): ?>
                            <div class="alert alert-danger d-flex align-items-center rounded-3 py-2" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <div><?= htmlspecialchars($error) ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <form action="login.php" method="POST" class="needs-validation" novalidate>
                            <?= csrf_field() ?>
                            
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Email or Mobile</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" name="mobile" required placeholder="e.g. 98XXXXXXXX" 
                                           class="form-control" inputmode="numeric">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <label class="form-label fw-semibold mb-0">Password</label>
                                    <a href="forgot-password.php" class="small text-decoration-none">Forgot?</a>
                                </div>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" name="password" required placeholder="Enter password" 
                                           class="form-control" minlength="6">
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="remember">
                                    <label class="form-check-label text-muted" for="remember">
                                        Remember me
                                    </label>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100" <?= $lockout_remaining > 0 ? 'disabled' : '' ?>>
                                <?= $lockout_remaining > 0 ? 'Please wait...' : '<i class="bi bi-box-arrow-in-right me-2"></i>Sign In' ?>
                            </button>
                        </form>
                        
                        <div class="text-center mt-4">
                            <p class="text-muted mb-0">
                                Don't have an account? 
                                <a href="signup.php" class="fw-semibold text-decoration-none">Create Account</a>
                            </p>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <a href="../index.php" class="text-decoration-none text-muted small">
                                <i class="bi bi-house me-1"></i>Back to Shop
                            </a>
                        </div>
                    </div>
                </div>
                
                <p class="text-center text-muted small mt-4">
                    <i class="bi bi-shield-check me-1"></i>Your data is secure with AES-256 encryption
                </p>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');

        togglePassword.addEventListener('click', function (e) {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.querySelector('i').classList.toggle('bi-eye');
            this.querySelector('i').classList.toggle('bi-eye-slash');
        });
    </script>
</body>
</html>
