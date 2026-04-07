<?php
http_response_code(404);
$page_title = "Page Not Found";
require_once __DIR__ . '/includes/header-bootstrap.php';
?>

<div class="container py-5 my-5 text-center">
    <div class="row justify-content-center">
        <div class="col-md-6 py-5">
            <div class="display-1 fw-bold text-primary mb-4">404</div>
            <h1 class="h2 mb-4">Page Not Found</h1>
            <p class="text-muted mb-5">
                The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.
            </p>
            <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                <a href="index.php" class="btn btn-dark btn-lg px-5 rounded-pill shadow-lg">
                    <i class="bi bi-house-door me-2"></i>Go to Homepage
                </a>
                <a href="shop.php" class="btn btn-outline-dark btn-lg px-5 rounded-pill">
                    <i class="bi bi-bag me-2"></i>Browse Shop
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer-bootstrap.php'; ?>
