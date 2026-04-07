<?php
require_once __DIR__ . '/../includes/functions.php';

if (is_logged_in()) {
    redirect('../index.php');
}

$ip = get_client_ip();
$error = null;
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    
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
}

$page_title = "Reset Password";
require_once __DIR__ . '/../includes/header-bootstrap.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="text-center mb-4">
                <a href="../index.php" class="text-decoration-none d-inline-block mb-4">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-white border rounded-3 d-flex align-items-center justify-content-center overflow-hidden" style="width:48px;height:48px;">
                            <img src="../assets/logo.jpeg" alt="ARS Shop Logo" style="width:100%;height:100%;object-fit:contain;padding:6px;">
                        </div>
                        <span class="fs-4 fw-bold text-dark">ARS<span class="text-primary">SHOP</span></span>
                    </div>
                </a>
            </div>

            <div class="card shadow">
                <?php if ($success): ?>
                    <div class="card-body text-center p-5">
                        <div class="mb-4">
                            <i class="bi bi-envelope-check-fill text-success fs-1"></i>
                        </div>
                        <h2 class="h4 fw-bold mb-3">Check Your Email</h2>
                        <p class="text-muted mb-3">
                            If an account exists with that email or mobile, we've sent password reset instructions.
                        </p>
                        <p class="text-muted small mb-4">
                            Check your inbox and spam folder.
                        </p>
                        <a href="login.php" class="btn btn-dark w-100 py-2 fw-bold">
                            Back to Login
                        </a>
                    </div>
                <?php elseif (isset($_SESSION['sending_email'])): ?>
                    <div class="card-body text-center p-5">
                        <div class="mb-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                        <h2 class="h4 fw-bold mb-3">Sending Email...</h2>
                        <p class="text-muted">
                            Please wait while we send the reset link to your email.
                        </p>
                    </div>
                    <meta http-equiv="refresh" content="2">
                    <?php unset($_SESSION['sending_email']); ?>
                <?php else: ?>
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:64px;height:64px;">
                                <i class="bi bi-key-fill text-primary fs-4"></i>
                            </div>
                            <h2 class="h5 fw-bold mb-2">Forgot Password?</h2>
                            <p class="text-muted small">Enter your email or mobile to reset password</p>
                        </div>

                        <?php if($error): ?>
                            <div class="alert alert-danger d-flex align-items-center gap-2" role="alert">
                                <i class="bi bi-exclamation-circle"></i>
                                <span><?= htmlspecialchars($error) ?></span>
                            </div>
                        <?php endif; ?>

                        <form action="forgot-password.php" method="POST" id="resetForm">
                            <?= csrf_field() ?>
                            
                            <div class="mb-3">
                                <label for="email_or_mobile" class="form-label small fw-semibold text-muted">Email or Mobile</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                                    <input type="text" name="email_or_mobile" id="email_or_mobile" required 
                                           placeholder="e.g. 98XXXXXXXX or email@example.com" 
                                           class="form-control">
                                </div>
                            </div>

                            <button type="submit" id="resetBtn" class="btn btn-dark w-100 py-2 fw-bold">
                                <span id="btnText">Send Reset Link</span>
                                <span id="btnLoading" class="d-none">
                                    <span class="spinner-border spinner-border-sm me-2"></span>Sending...
                                </span>
                            </button>
                        </form>

                        <div class="text-center mt-4 pt-3 border-top">
                            <a href="login.php" class="text-primary fw-semibold text-decoration-none small">
                                Remember your password? Sign in
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
const form = document.getElementById('resetForm');
const btn = document.getElementById('resetBtn');
const btnText = document.getElementById('btnText');
const btnLoading = document.getElementById('btnLoading');

if (form && btn) {
    form.addEventListener('submit', function() {
        btn.disabled = true;
        btnText.classList.add('d-none');
        btnLoading.classList.remove('d-none');
    });
}
</script>

<?php require_once __DIR__ . '/../includes/footer-bootstrap.php'; ?>
