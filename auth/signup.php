<?php
require_once __DIR__ . '/../includes/functions.php';

if (is_logged_in()) {
    redirect('../index.php');
}

$ip = get_client_ip();
$error = null;
$field_errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    
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
        .form-control, .form-select {
            border-radius: 0.75rem;
            padding: 0.875rem 1rem;
            border: 2px solid #e2e8f0;
            font-weight: 500;
        }
        .form-control:focus, .form-select:focus {
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
        .is-invalid {
            border-color: #dc2626 !important;
        }
    </style>
</head>
<body>
    <div class="container py-4 py-md-5">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-10 col-xl-8">
                
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
                            <h3 class="fw-bold text-dark mb-1">Create Account</h3>
                            <p class="text-muted">Join 10,000+ shoppers across Nepal</p>
                        </div>
                        
                        <?php if(isset($field_errors['general'])): ?>
                            <div class="alert alert-danger d-flex align-items-center rounded-3" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <div><?= htmlspecialchars($field_errors['general']) ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <form action="signup.php" method="POST" class="needs-validation" novalidate>
                            <?= csrf_field() ?>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Full Name</label>
                                    <input type="text" name="full_name" required minlength="2" placeholder="e.g. Hari Prasad" 
                                           value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>"
                                           class="form-control <?= isset($field_errors['full_name']) ? 'is-invalid' : '' ?>">
                                    <?php if (isset($field_errors['full_name'])): ?>
                                        <div class="invalid-feedback"><?= htmlspecialchars($field_errors['full_name']) ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Mobile Number</label>
                                    <input type="tel" name="mobile" required placeholder="98XXXXXXXX" 
                                           value="<?= htmlspecialchars($_POST['mobile'] ?? '') ?>"
                                           class="form-control <?= isset($field_errors['mobile']) ? 'is-invalid' : '' ?>" inputmode="numeric">
                                    <?php if (isset($field_errors['mobile'])): ?>
                                        <div class="invalid-feedback"><?= htmlspecialchars($field_errors['mobile']) ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Email Address</label>
                                    <input type="email" name="email" required placeholder="hari@example.com" 
                                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                           class="form-control <?= isset($field_errors['email']) ? 'is-invalid' : '' ?>">
                                    <?php if (isset($field_errors['email'])): ?>
                                        <div class="invalid-feedback"><?= htmlspecialchars($field_errors['email']) ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Password</label>
                                    <div class="input-group">
                                        <input type="password" name="password" id="password" required minlength="8" placeholder="Min 8 chars"
                                               class="form-control <?= isset($field_errors['password']) ? 'is-invalid' : '' ?>">
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <?php if (isset($field_errors['password'])): ?>
                                            <div class="invalid-feedback"><?= htmlspecialchars($field_errors['password']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Confirm Password</label>
                                    <div class="input-group">
                                        <input type="password" name="confirm_password" id="confirm_password" required minlength="8" placeholder="Repeat password"
                                               class="form-control <?= isset($field_errors['confirm_password']) ? 'is-invalid' : '' ?>">
                                        <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <?php if (isset($field_errors['confirm_password'])): ?>
                                            <div class="invalid-feedback"><?= htmlspecialchars($field_errors['confirm_password']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>                                
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Delivery Address</label>
                                    <textarea name="address" required rows="3" placeholder="Street, City, Ward No." 
                                              class="form-control <?= isset($field_errors['address']) ? 'is-invalid' : '' ?>"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                                    <?php if (isset($field_errors['address'])): ?>
                                        <div class="invalid-feedback"><?= htmlspecialchars($field_errors['address']) ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" required id="terms">
                                        <label class="form-check-label text-muted" for="terms">
                                            I agree to the <a href="#" class="text-primary">Terms of Service</a> and <a href="#" class="text-primary">Privacy Policy</a>
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-person-plus me-2"></i>Create My Account
                                    </button>
                                </div>
                            </div>
                        </form>
                        
                        <div class="text-center mt-4">
                            <p class="text-muted mb-0">
                                Already have an account? 
                                <a href="login.php" class="fw-semibold text-decoration-none">Sign in instead</a>
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
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
