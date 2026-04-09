<?php
require_once __DIR__ . '/includes/functions.php';
$page_title     = 'FAQ — Orders, Shipping & Payments | Easy Shopping A.R.S Nepal';
$page_meta_desc = 'Find answers to common questions about placing orders, shipping times, payment methods (eSewa, FonePay, COD), returns and more at Easy Shopping A.R.S.';

$page_schema = '<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "FAQPage",
    "mainEntity": [
        {
            "@type": "Question",
            "name": "How do I place an order?",
            "acceptedAnswer": {"@type": "Answer", "text": "Simply browse our shop, add items to your cart, and proceed to checkout. You can choose to pay via eSewa, FonePay, or Cash on Delivery."}
        },
        {
            "@type": "Question",
            "name": "What is your return policy?",
            "acceptedAnswer": {"@type": "Answer", "text": "We offer a 7-day return policy for unused and undamaged items in their original packaging."}
        },
        {
            "@type": "Question",
            "name": "How long does delivery take?",
            "acceptedAnswer": {"@type": "Answer", "text": "Delivery within Kathmandu Valley usually takes 1-2 business days. For outside valley orders, it typically takes 3-5 business days."}
        },
        {
            "@type": "Question",
            "name": "Do you offer free shipping?",
            "acceptedAnswer": {"@type": "Answer", "text": "Yes! We offer free delivery on all orders over Rs. 1,000."}
        },
        {
            "@type": "Question",
            "name": "Which payment methods do you accept?",
            "acceptedAnswer": {"@type": "Answer", "text": "We accept eSewa, FonePay, Bank Transfer, and Cash on Delivery (COD)."}
        }
    ]
}
</script>';
require_once __DIR__ . '/includes/header-bootstrap.php';
?>

<section class="bg-light py-5">
    <div class="container py-5 text-center">
        <h1 class="display-5 fw-bold text-dark mb-3">Frequently Asked Questions</h1>
        <p class="text-muted lead mx-auto" style="max-width: 600px;">
            Find quick answers to common questions about orders, shipping, and payments at Easy Shopping A.R.S.
        </p>
    </div>
</section>

<section class="py-5">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <!-- Category: Orders -->
                <h3 class="fw-bold mb-4 d-flex align-items-center gap-2">
                    <i class="bi bi-box-seam text-primary"></i> Orders & Returns
                </h3>
                <div class="accordion accordion-flush shadow-sm rounded-4 overflow-hidden mb-5" id="accordionOrders">
                    <div class="accordion-item border-bottom">
                        <h2 class="accordion-header">
                            <button class="accordion-button fw-bold py-3" type="button" data-bs-toggle="collapse" data-bs-target="#order1">
                                How do I place an order?
                            </button>
                        </h2>
                        <div id="order1" class="accordion-collapse collapse show" data-bs-parent="#accordionOrders">
                            <div class="accordion-body text-muted">
                                Simply browse our shop, add items to your cart, and proceed to checkout. You can choose to pay via eSewa, FonePay, or Cash on Delivery.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item border-bottom">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold py-3" type="button" data-bs-toggle="collapse" data-bs-target="#order2">
                                What is your return policy?
                            </button>
                        </h2>
                        <div id="order2" class="accordion-collapse collapse" data-bs-parent="#accordionOrders">
                            <div class="accordion-body text-muted">
                                We offer a 7-day return policy for unused and undamaged items in their original packaging. Please visit our <a href="shipping.php">Shipping & Returns</a> page for full details.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Category: Shipping -->
                <h3 class="fw-bold mb-4 d-flex align-items-center gap-2">
                    <i class="bi bi-truck text-primary"></i> Shipping & Delivery
                </h3>
                <div class="accordion accordion-flush shadow-sm rounded-4 overflow-hidden mb-5" id="accordionShipping">
                    <div class="accordion-item border-bottom">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold py-3" type="button" data-bs-toggle="collapse" data-bs-target="#ship1">
                                How long does delivery take?
                            </button>
                        </h2>
                        <div id="ship1" class="accordion-collapse collapse" data-bs-parent="#accordionShipping">
                            <div class="accordion-body text-muted">
                                Delivery within Kathmandu Valley usually takes 1-2 business days. For outside valley orders, it typically takes 3-5 business days.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item border-bottom">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold py-3" type="button" data-bs-toggle="collapse" data-bs-target="#ship2">
                                Do you offer free shipping?
                            </button>
                        </h2>
                        <div id="ship2" class="accordion-collapse collapse" data-bs-parent="#accordionShipping">
                            <div class="accordion-body text-muted">
                                Yes! We offer free delivery on all orders over Rs. <?= number_format(FREE_SHIPPING_THRESHOLD) ?>.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Category: Payments -->
                <h3 class="fw-bold mb-4 d-flex align-items-center gap-2">
                    <i class="bi bi-credit-card text-primary"></i> Payments
                </h3>
                <div class="accordion accordion-flush shadow-sm rounded-4 overflow-hidden mb-5" id="accordionPayments">
                    <div class="accordion-item border-bottom">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold py-3" type="button" data-bs-toggle="collapse" data-bs-target="#pay1">
                                Which payment methods do you accept?
                            </button>
                        </h2>
                        <div id="pay1" class="accordion-collapse collapse" data-bs-parent="#accordionPayments">
                            <div class="accordion-body text-muted">
                                We accept eSewa, FonePay, Bank Transfer, and Cash on Delivery (COD).
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-5">
                    <p class="text-muted">Still have questions?</p>
                    <a href="contact.php" class="btn btn-dark px-5 py-3 rounded-pill fw-bold">Contact Support</a>
                </div>

            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer-bootstrap.php'; ?>
