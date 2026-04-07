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
                        <div class="success-icon mb-4">
                            <i class="bi bi-check-circle-fill text-success fs-1"></i>
                        </div>
                        <h2 class="h4 fw-bold mb-3">
                            <?= isset($alreadyVerified) ? 'Already Verified!' : 'Email Verified!' ?>
                        </h2>
                        <p class="text-muted mb-4">
                            <?= isset($alreadyVerified) 
                                ? 'Your email was already verified. You can login to your account.' 
                                : 'Your email has been successfully verified. You can now login to your account.' ?>
                        </p>
                        
                        <?php if (isset($_SESSION['dev_verify_token']) && isset($_SESSION['dev_verify_email'])): ?>
                            <div class="alert alert-warning text-start" role="alert">
                                <strong class="d-block mb-2">DEV MODE - Email not configured:</strong>
                                <small class="d-block mb-1">Email: <?= htmlspecialchars($_SESSION['dev_verify_email']) ?></small>
                                <small class="d-block text-break mb-2">Token: <?= htmlspecialchars($_SESSION['dev_verify_token']) ?></small>
                                <a href="verify-email.php?token=<?= htmlspecialchars($_SESSION['dev_verify_token']) ?>" class="fw-bold">Click to verify</a>
                            </div>
                        <?php endif; ?>
                        
                        <a href="login.php" class="btn btn-primary w-100 py-2 fw-bold">
                            Login Now
                        </a>
                    </div>
                <?php else: ?>
                    <div class="card-body text-center p-5">
                        <div class="mb-4">
                            <i class="bi bi-x-circle-fill text-danger fs-1"></i>
                        </div>
                        <h2 class="h4 fw-bold mb-3">Verification Failed</h2>
                        <p class="text-muted mb-4">
                            <?= htmlspecialchars($error) ?>
                        </p>
                        <?php if (strpos($error, 'expired') !== false || strpos($error, 'invalid') !== false): ?>
                            <a href="signup.php" class="btn btn-dark w-100 py-2 fw-bold">
                                Register Again
                            </a>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-dark w-100 py-2 fw-bold">
                                Back to Login
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer-bootstrap.php'; ?>
