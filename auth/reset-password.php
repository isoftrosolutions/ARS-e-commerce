<?php
require_once __DIR__ . '/../includes/functions.php';

if (is_logged_in()) {
    redirect('../index.php');
}

$token = $_GET['token'] ?? '';
$success = false;
$userId = null;
$error = null;

if (empty($token)) {
    $error = "Invalid reset link.";
} elseif (strlen($token) !== 64 || !ctype_xdigit($token)) {
    $error = "Invalid reset link format.";
} else {
    try {
        $stmt = $pdo->prepare("SELECT id, full_name, email, reset_token FROM users WHERE reset_expires > NOW() AND reset_token IS NOT NULL");
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
                            <i class="bi bi-check-circle-fill text-success fs-1"></i>
                        </div>
                        <h2 class="h4 fw-bold mb-3">Password Reset!</h2>
                        <p class="text-muted mb-4">
                            Your password has been successfully reset. You can now login with your new password.
                        </p>
                        <a href="login.php" class="btn btn-primary w-100 py-2 fw-bold">
                            Login Now
                        </a>
                    </div>
                <?php elseif ($error): ?>
                    <div class="card-body text-center p-5">
                        <div class="mb-4">
                            <i class="bi bi-x-circle-fill text-danger fs-1"></i>
                        </div>
                        <h2 class="h4 fw-bold mb-3">Invalid Link</h2>
                        <p class="text-muted mb-4">
                            <?= htmlspecialchars($error) ?>
                        </p>
                        <a href="forgot-password.php" class="btn btn-dark w-100 py-2 fw-bold">
                            Request New Link
                        </a>
                    </div>
                <?php else: ?>
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:64px;height:64px;">
                                <i class="bi bi-lock-fill text-primary fs-4"></i>
                            </div>
                            <h2 class="h5 fw-bold mb-2">Set New Password</h2>
                            <p class="text-muted small">Create a strong password for your account</p>
                        </div>

                        <?php if(isset($fieldErrors['general'])): ?>
                            <div class="alert alert-danger d-flex align-items-center gap-2" role="alert">
                                <i class="bi bi-exclamation-circle"></i>
                                <span><?= htmlspecialchars($fieldErrors['general']) ?></span>
                            </div>
                        <?php endif; ?>

                        <form action="reset-password.php?token=<?= htmlspecialchars($token) ?>" method="POST">
                            <?= csrf_field() ?>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label small fw-semibold text-muted">New Password</label>
                                <div class="input-group">
                                    <input type="password" name="password" id="password" required minlength="8" 
                                           placeholder="Min 8 chars, 1 uppercase, 1 number" 
                                           class="form-control <?= isset($fieldErrors['password']) ? 'is-invalid' : '' ?>">
                                    <button type="button" id="togglePassword" class="btn btn-outline-secondary">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <div id="passwordStrength" class="mt-2 d-none">
                                    <div class="progress" style="height:4px;">
                                        <div id="strengthBar" class="progress-bar" role="progressbar" style="width:0%"></div>
                                    </div>
                                    <small id="strengthText" class="text-muted"></small>
                                </div>
                                <?php if (isset($fieldErrors['password'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($fieldErrors['password']) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-4">
                                <label for="confirm_password" class="form-label small fw-semibold text-muted">Confirm Password</label>
                                <div class="input-group">
                                    <input type="password" name="confirm_password" id="confirm_password" required minlength="8" 
                                           placeholder="Repeat new password" 
                                           class="form-control <?= isset($fieldErrors['confirm_password']) ? 'is-invalid' : '' ?>">
                                    <button type="button" id="toggleConfirmPassword" class="btn btn-outline-secondary">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <?php if (isset($fieldErrors['confirm_password'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($fieldErrors['confirm_password']) ?></div>
                                <?php endif; ?>
                            </div>

                            <button type="submit" id="resetBtn" class="btn btn-dark w-100 py-2 fw-bold">
                                Reset Password
                            </button>
                        </form>

                        <div class="text-center mt-4 pt-3 border-top">
                            <a href="login.php" class="text-primary fw-semibold text-decoration-none small">
                                Back to Login
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function setupPasswordToggle(toggleId, inputId) {
    const toggle = document.getElementById(toggleId);
    const input = document.getElementById(inputId);
    
    if (toggle && input) {
        toggle.addEventListener('click', function() {
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            const icon = this.querySelector('i');
            if (icon) {
                icon.className = type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
            }
        });
    }
}

setupPasswordToggle('togglePassword', 'password');
setupPasswordToggle('toggleConfirmPassword', 'confirm_password');

const passwordInput = document.getElementById('password');
const strengthDiv = document.getElementById('passwordStrength');
const strengthBar = document.getElementById('strengthBar');
const strengthText = document.getElementById('strengthText');

function updateStrength(password) {
    if (!password) {
        strengthDiv.classList.add('d-none');
        return;
    }
    strengthDiv.classList.remove('d-none');
    
    let score = 0;
    if (password.length >= 8) score++;
    if (/[A-Z]/.test(password)) score++;
    if (/[a-z]/.test(password)) score++;
    if (/[0-9]/.test(password) || /[^A-Za-z0-9]/.test(password)) score++;
    
    const widths = ['25%', '50%', '75%', '100%'];
    const colors = ['bg-danger', 'bg-warning', 'bg-info', 'bg-success'];
    const labels = ['Weak', 'Fair', 'Good', 'Strong'];
    
    strengthBar.style.width = widths[score - 1] || '0%';
    strengthBar.className = 'progress-bar ' + (colors[score - 1] || 'bg-secondary');
    strengthText.textContent = labels[score - 1] || 'Too short';
}

passwordInput?.addEventListener('input', (e) => updateStrength(e.target.value));

document.getElementById('resetBtn')?.addEventListener('click', function(e) {
    if (!this.disabled) {
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Resetting...';
        this.closest('form')?.submit();
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer-bootstrap.php'; ?>
