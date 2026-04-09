<?php
require_once __DIR__ . '/includes/functions.php';
$page_title     = 'Contact Us | Easy Shopping A.R.S — Birgunj, Nepal';
$page_meta_desc = 'Get in touch with Easy Shopping A.R.S. Visit us at Birgunj-13 Radhemai, Parsa, Nepal or call +977 982-0210361. We\'re here to help with orders, returns and enquiries.';
$error = null;
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    
    if (empty($name) || empty($email) || empty($message)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please provide a valid email address.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO contact_submissions (name, email, subject, message) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $subject, $message]);
            
            // Queue an email notification (optional logic)
            require_once __DIR__ . '/includes/classes/EmailManager.php';
            $emailMgr = new EmailManager($pdo);
            $emailMgr->queue(env('SMTP_FROM_EMAIL'), 'Admin', 'new_contact_message', [
                'name' => $name,
                'email' => $email,
                'subject' => $subject,
                'message' => $message
            ]);
            
            $success = true;
        } catch (PDOException $e) {
            error_log("Contact form error: " . $e->getMessage());
            $error = "Something went wrong. Please try again later.";
        }
    }
}

require_once __DIR__ . '/includes/header-bootstrap.php';
?>

<!-- Hero Section -->
<section class="bg-dark py-5">
    <div class="container text-center py-4">
        <h1 class="display-4 fw-bold text-white mb-3">Get In Touch</h1>
        <p class="lead text-muted mb-0 mx-auto" style="max-width: 600px;">
            Have questions? We're here to help. Send us a message and our team will get back to you within 24 hours.
        </p>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-5">
            <!-- Contact Info Sidebar -->
            <div class="col-lg-4">
                <div class="p-4 rounded-4 bg-light h-100 shadow-sm">
                    <h3 class="h4 fw-bold mb-4">Official Contact</h3>
                    
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="flex-shrink-0 bg-primary bg-opacity-10 text-primary rounded-3 p-3">
                            <i class="bi bi-geo-alt-fill fs-4"></i>
                        </div>
                        <div>
                            <span class="text-muted small d-block">Shop Address</span>
                            <span class="fw-semibold">Birgunj-13 Radhemai, Parsa, Nepal</span>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="flex-shrink-0 bg-primary bg-opacity-10 text-primary rounded-3 p-3">
                            <i class="bi bi-envelope-fill fs-4"></i>
                        </div>
                        <div>
                            <span class="text-muted small d-block">Email Support</span>
                            <a href="mailto:easyshoppinga.r.s1@gmail.com" class="text-decoration-none text-dark fw-semibold">easyshoppinga.r.s1@gmail.com</a>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="flex-shrink-0 bg-primary bg-opacity-10 text-primary rounded-3 p-3">
                            <i class="bi bi-headset fs-4"></i>
                        </div>
                        <div>
                            <span class="text-muted small d-block">Support Contact</span>
                            <a href="tel:+9779820210361" class="text-decoration-none text-dark fw-semibold">+977 982-0210361</a>
                        </div>
                    </div>

                    <div class="mt-5 pt-4 border-top">
                        <h4 class="h6 fw-bold mb-3">Follow Us</h4>
                        <div class="d-flex gap-2">
                            <a href="https://www.facebook.com/easyshoppinga.r.s1" class="btn btn-outline-dark btn-sm rounded-circle shadow-sm" style="width: 35px; height: 35px; padding: 4px;"><i class="bi bi-facebook"></i></a>
                            <a href="https://www.tiktok.com/@easyshopinga.r.s1" class="btn btn-outline-dark btn-sm rounded-circle shadow-sm" style="width: 35px; height: 35px; padding: 4px;"><i class="bi bi-tiktok"></i></a>
                            <a href="https://www.instagram.com/easyshoppinga.r.s1" class="btn btn-outline-dark btn-sm rounded-circle shadow-sm" style="width: 35px; height: 35px; padding: 4px;"><i class="bi bi-instagram"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="col-lg-8">
                <?php if ($success): ?>
                    <div class="card border-0 bg-green-50 rounded-4 shadow-sm h-100 p-5 text-center">
                        <div class="mb-4">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                        </div>
                        <h2 class="fw-bold mb-3">Message Sent!</h2>
                        <p class="text-muted mb-4">
                            Thank you for reaching out to Easy Shopping A.R.S. We have received your message and will respond as soon as possible.
                        </p>
                        <a href="index.php" class="btn btn-dark px-5 py-3 rounded-pill fw-bold">Return Home</a>
                    </div>
                <?php else: ?>
                    <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
                        <h2 class="fw-bold mb-4">Send us a Message</h2>
                        
                        <?php if($error): ?>
                            <div class="alert alert-danger d-flex align-items-center gap-2 mb-4" role="alert">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                <span><?= $error ?></span>
                            </div>
                        <?php endif; ?>

                        <form action="contact.php" method="POST">
                            <?= csrf_field() ?>
                            
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label fw-semibold small">Your Name *</label>
                                    <input type="text" name="name" id="name" class="form-control" required placeholder="Enter full name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label fw-semibold small">Email Address *</label>
                                    <input type="email" name="email" id="email" class="form-control" required placeholder="example@gmail.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="subject" class="form-label fw-semibold small">Subject</label>
                                <input type="text" name="subject" id="subject" class="form-control" placeholder="What is this regarding?" value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>">
                            </div>

                            <div class="mb-4">
                                <label for="message" class="form-label fw-semibold small">Your Message *</label>
                                <textarea name="message" id="message" rows="6" class="form-control" required placeholder="Describe your inquiry in detail..."><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                            </div>

                            <button type="submit" class="btn btn-dark btn-lg w-100 py-3 rounded-pill fw-bold">
                                <i class="bi bi-send me-2"></i>Send Message
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer-bootstrap.php'; ?>
