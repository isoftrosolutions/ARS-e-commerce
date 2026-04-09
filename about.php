<?php
require_once __DIR__ . '/includes/functions.php';
$page_title     = 'About Us | Easy Shopping A.R.S — Online Store in Birgunj, Nepal';
$page_meta_desc = 'Learn about Easy Shopping A.R.S, Nepal\'s trusted online shopping destination based in Birgunj-13 Radhemai, Parsa. Founded in 2026 with a mission to deliver quality products across Nepal.';
require_once __DIR__ . '/includes/header-bootstrap.php';
?>

<!-- Hero Section -->
<section class="bg-dark py-5">
    <div class="container py-5 text-center">
        <h1 class="display-3 fw-bold text-white mb-3">Our Story</h1>
        <p class="lead text-muted mx-auto mb-0" style="max-width: 700px;">
            Redefining the online shopping experience in Nepal with quality, trust, and speed.
        </p>
    </div>
</section>

<!-- Mission & Vision -->
<section class="py-5">
    <div class="container py-5">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <img src="https://images.unsplash.com/photo-1556742044-3c52d6e88c62?q=80&w=1000" alt="About Easy Shopping A.R.S" class="img-fluid rounded-4 shadow-lg">
            </div>
            <div class="col-lg-6">
                <h2 class="display-5 fw-bold mb-4">Welcome to <span class="text-primary">Easy Shopping A.R.S</span></h2>
                <p class="text-muted lead mb-4">
                    Founded in 2026, Easy Shopping A.R.S started with a simple vision: to make premium quality products accessible to every corner of Nepal.
                </p>
                <p class="text-muted mb-4">
                    We understand that online shopping is more than just a transaction—it's about trust. That's why we meticulously curate our collection, ensuring every item meets our high standards of quality and durability. From the latest electronics to trendy fashion, we bring the world to your doorstep.
                </p>
                <div class="row g-4 mt-2">
                    <div class="col-6">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3">
                                <i class="bi bi-shield-check fs-4"></i>
                            </div>
                            <span class="fw-bold">100% Authentic</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3">
                                <i class="bi bi-truck fs-4"></i>
                            </div>
                            <span class="fw-bold">Fast Delivery</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats/Features -->
<section class="bg-light py-5">
    <div class="container py-5">
        <div class="row g-4 text-center">
            <div class="col-md-4">
                <div class="p-4 bg-white rounded-4 shadow-sm h-100">
                    <i class="bi bi-people-fill text-primary display-4 mb-3 d-block"></i>
                    <h3 class="fw-bold h4">10,000+</h3>
                    <p class="text-muted mb-0">Happy Customers</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4 bg-white rounded-4 shadow-sm h-100">
                    <i class="bi bi-box-seam-fill text-primary display-4 mb-3 d-block"></i>
                    <h3 class="fw-bold h4">5,000+</h3>
                    <p class="text-muted mb-0">Products Delivered</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4 bg-white rounded-4 shadow-sm h-100">
                    <i class="bi bi-geo-alt-fill text-primary display-4 mb-3 d-block"></i>
                    <h3 class="fw-bold h4">77 Districts</h3>
                    <p class="text-muted mb-0">Nationwide Coverage</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-5 my-5">
    <div class="container">
        <div class="bg-primary rounded-5 p-5 text-center text-white shadow-lg">
            <h2 class="display-5 fw-bold mb-4">Ready to Start Shopping?</h2>
            <p class="lead mb-4 opacity-75 mx-auto" style="max-width: 600px;">
                Join thousands of satisfied customers and experience the best of online shopping in Nepal.
            </p>
            <div class="d-flex justify-content-center gap-3">
                <a href="shop.php" class="btn btn-light btn-lg px-5 py-3 rounded-pill fw-bold">Shop Now</a>
                <a href="contact.php" class="btn btn-outline-light btn-lg px-5 py-3 rounded-pill fw-bold">Contact Us</a>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer-bootstrap.php'; ?>
