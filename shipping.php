<?php
require_once __DIR__ . '/includes/functions.php';
$page_title = "Shipping & Return Policy";
require_once __DIR__ . '/includes/header-bootstrap.php';
?>

<section class="bg-light py-5">
    <div class="container py-4 text-center">
        <h1 class="display-5 fw-bold text-dark mb-3">Shipping & Returns</h1>
        <p class="text-muted">Last Updated: <?= date('F d, Y') ?></p>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 mb-5">
                    <h2 class="h4 fw-bold text-primary mb-4">Shipping Policy</h2>
                    <div class="prose">
                        <p>We aim to deliver your orders as quickly and safely as possible. Here are the details of our shipping policy:</p>
                        <ul>
                            <li><strong>Delivery Areas:</strong> We deliver all across Nepal.</li>
                            <li><strong>Shipping Costs:</strong> 
                                <ul>
                                    <li>Standard Shipping: Rs. <?= number_format(SHIPPING_FEE) ?></li>
                                    <li>Free Shipping: On orders over Rs. <?= number_format(FREE_SHIPPING_THRESHOLD) ?></li>
                                </ul>
                            </li>
                            <li><strong>Delivery Time:</strong> 
                                <ul>
                                    <li>Inside Kathmandu Valley: 1-2 business days.</li>
                                    <li>Outside Kathmandu Valley: 3-5 business days.</li>
                                </ul>
                            </li>
                            <li><strong>Tracking:</strong> You will receive a tracking ID once your order has been dispatched.</li>
                        </ul>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
                    <h2 class="h4 fw-bold text-primary mb-4">Return & Refund Policy</h2>
                    <div class="prose">
                        <p>If you are not entirely satisfied with your purchase, we're here to help.</p>
                        <h3 class="h6 fw-bold mt-4 mb-2">Returns</h3>
                        <ul>
                            <li>You have <strong>5 calendar days</strong> to return an item from the date you received it.</li>
                            <li>To be eligible for a return, your item must be unused and in the same condition that you received it.</li>
                            <li>Your item must be in the original packaging.</li>
                            <li>Your item needs to have the receipt or proof of purchase.</li>
                        </ul>

                        <h3 class="h6 fw-bold mt-4 mb-2">Refunds</h3>
                        <p>Once we receive your item, we will inspect it and notify you that we have received your returned item. We will immediately notify you on the status of your refund after inspecting the item.</p>
                        <p>If your return is approved, we will initiate a refund to your original method of payment (eSewa, FonePay, or Bank Transfer). You will receive the credit within a certain amount of days, depending on your bank's policies.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer-bootstrap.php'; ?>
