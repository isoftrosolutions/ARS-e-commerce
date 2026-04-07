<?php
require_once __DIR__ . '/includes/functions.php';
$page_title = "Terms & Conditions";
require_once __DIR__ . '/includes/header-bootstrap.php';
?>

<section class="bg-light py-5">
    <div class="container py-4 text-center">
        <h1 class="display-5 fw-bold text-dark mb-3">Terms & Conditions</h1>
        <p class="text-muted">Last Updated: <?= date('F d, Y') ?></p>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
                    <div class="prose">
                        <h2 class="h4 fw-bold text-primary mb-4">1. Agreement to Terms</h2>
                        <p>By accessing or using the Easy Shopping A.R.S website, you agree to be bound by these Terms and Conditions and our Privacy Policy. If you do not agree to all of these terms, do not use this website.</p>

                        <h2 class="h4 fw-bold text-primary mt-5 mb-4">2. Use of the Site</h2>
                        <p>You may use the site only for lawful purposes and in accordance with these Terms. You are responsible for maintaining the confidentiality of your account and password.</p>

                        <h2 class="h4 fw-bold text-primary mt-5 mb-4">3. Intellectual Property</h2>
                        <p>The content on this website, including but not limited to text, graphics, logos, images, and software, is the property of Easy Shopping A.R.S and is protected by copyright and other intellectual property laws.</p>

                        <h2 class="h4 fw-bold text-primary mt-5 mb-4">4. Products and Pricing</h2>
                        <p>All products are subject to availability. We reserve the right to limit the quantities of any products or services that we offer. Prices for our products are subject to change without notice.</p>

                        <h2 class="h4 fw-bold text-primary mt-5 mb-4">5. Limitation of Liability</h2>
                        <p>In no event shall Easy Shopping A.R.S, nor its directors, employees, partners, agents, suppliers, or affiliates, be liable for any indirect, incidental, special, consequential or punitive damages, including without limitation, loss of profits, data, use, goodwill, or other intangible losses.</p>

                        <h2 class="h4 fw-bold text-primary mt-5 mb-4">6. Governing Law</h2>
                        <p>These Terms shall be governed and construed in accordance with the laws of Nepal, without regard to its conflict of law provisions.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer-bootstrap.php'; ?>
