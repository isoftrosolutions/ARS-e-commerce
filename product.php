<?php
require_once __DIR__ . '/includes/functions.php';

$slug = isset($_GET['slug']) ? $_GET['slug'] : '';
if (!$slug) redirect('shop.php');

try {
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.slug = ?");
    $stmt->execute([$slug]);
    $product = $stmt->fetch();
    
    if (!$product) redirect('shop.php');
    
    // Fetch Gallery
    $stmt_img = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ?");
    $stmt_img->execute([$product['id']]);
    $gallery = $stmt_img->fetchAll();

    // Fetch Reviews
    $stmt_rev = $pdo->prepare("SELECT r.*, u.full_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = ? AND r.status = 'approved' ORDER BY r.created_at DESC");
    $stmt_rev->execute([$product['id']]);
    $reviews = $stmt_rev->fetchAll();

    // Calculate Average Rating
    $avg_rating = 0;
    if (count($reviews) > 0) {
        $total_stars = array_sum(array_column($reviews, 'rating'));
        $avg_rating = $total_stars / count($reviews);
    }

    $page_title = $product['name'];
} catch (PDOException $e) {
    redirect('shop.php');
}

require_once __DIR__ . '/includes/header-bootstrap.php';
?>

<!-- Breadcrumb -->
<section class="bg-white border-bottom py-3">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item"><a href="shop.php?category=<?= $product['category_id'] ?>" class="text-decoration-none"><?= htmlspecialchars($product['category_name']) ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($product['name']) ?></li>
            </ol>
        </nav>
    </div>
</section>

<!-- Product Content -->
<section class="py-4 py-md-5">
    <div class="container">
        <div class="row g-4 g-lg-5">
            
            <!-- Product Images -->
            <div class="col-lg-6">
                <!-- Main Image -->
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden mb-3">
                    <div class="ratio ratio-1x1 bg-light">
                        <img id="mainImage" src="<?= $product['image'] ? UPLOAD_DIR . $product['image'] : 'https://via.placeholder.com/800x800' ?>" 
                             class="img-fluid p-4 p-lg-5" alt="<?= htmlspecialchars($product['name']) ?>" 
                             style="object-fit: contain;">
                    </div>
                </div>
                
                <!-- Thumbnails -->
                <?php if(!empty($gallery)): ?>
                    <div class="d-flex gap-2 overflow-auto pb-2">
                        <button onclick="changeImage('<?= UPLOAD_DIR . $product['image'] ?>')" class="btn btn-outline-primary p-2 rounded-2 flex-shrink-0 active">
                            <img src="<?= UPLOAD_DIR . $product['image'] ?>" alt="Main" style="width: 60px; height: 60px; object-fit: contain;">
                        </button>
                        <?php foreach($gallery as $img): ?>
                            <button onclick="changeImage('<?= UPLOAD_DIR . $img['image_path'] ?>')" class="btn btn-outline-secondary p-2 rounded-2 flex-shrink-0">
                                <img src="<?= UPLOAD_DIR . $img['image_path'] ?>" alt="Gallery" style="width: 60px; height: 60px; object-fit: contain;">
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Product Info -->
            <div class="col-lg-6">
                <div class="sticky-top" style="top: 100px;">
                    
                    <!-- Category & Title -->
                    <span class="badge bg-light text-primary mb-2"><?= htmlspecialchars($product['category_name']) ?></span>
                    <h1 class="h2 h1-lg fw-bold text-dark mb-3"><?= htmlspecialchars($product['name']) ?></h1>
                    
                    <!-- Rating -->
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="text-warning">
                            <?php for($i=1; $i<=5; $i++): ?>
                                <i class="bi bi-star<?= $i <= round($avg_rating) ? '-fill' : '' ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <a href="#reviews" class="text-decoration-none small">(<?= count($reviews) ?> reviews)</a>
                    </div>
                    
                    <!-- Price -->
                    <div class="mb-4">
                        <span class="display-6 fw-bold text-dark"><?= formatPrice($product['discount_price'] ?: $product['price']) ?></span>
                        <?php if($product['discount_price']): ?>
                            <span class="text-muted text-decoration-line-through fs-5 ms-2"><?= formatPrice($product['price']) ?></span>
                            <span class="badge bg-danger ms-2">Save <?= round((($product['price'] - $product['discount_price']) / $product['price']) * 100) ?>%</span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Stock Status -->
                    <div class="alert <?= $product['stock'] > 0 ? 'alert-success' : 'alert-danger' ?> d-flex align-items-center py-2 px-3 rounded-2 mb-4">
                        <i class="bi bi-<?= $product['stock'] > 0 ? 'check-circle-fill me-2' : 'x-circle-fill me-2' ?>"></i>
                        <span><?= $product['stock'] > 0 ? 'In Stock (' . $product['stock'] . ' available)' : 'Out of Stock' ?></span>
                    </div>
                    
                    <!-- Delivery Info -->
                    <div class="card bg-light border-0 rounded-3 mb-4">
                        <div class="card-body py-3">
                            <div class="d-flex align-items-center text-muted small">
                                <i class="bi bi-truck me-2 text-primary"></i>
                                <span>Delivery within 2-4 business days across Nepal</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="d-flex gap-3 mb-4">
                        <?php if($product['stock'] > 0): ?>
                            <button onclick="addToCart(<?= $product['id'] ?>)" class="btn btn-dark btn-lg flex-grow-1 rounded-2">
                                <i class="bi bi-cart-plus me-2"></i>Add to Cart
                            </button>
                        <?php else: ?>
                            <button class="btn btn-secondary btn-lg flex-grow-1 rounded-2" disabled>
                                Out of Stock
                            </button>
                        <?php endif; ?>
                        <button class="btn btn-outline-danger rounded-2">
                            <i class="bi bi-heart"></i>
                        </button>
                    </div>
                    
                    <!-- Description -->
                    <div class="border-top pt-4">
                        <h5 class="fw-bold mb-3">Description</h5>
                        <p class="text-muted lh-lg"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Reviews Section -->
        <section id="reviews" class="mt-5 pt-5 border-top">
            <div class="row">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <div class="card bg-light border-0 rounded-3 h-100">
                        <div class="card-body text-center p-4">
                            <h2 class="display-3 fw-bold text-dark mb-2"><?= number_format($avg_rating, 1) ?></h2>
                            <div class="text-warning mb-2">
                                <?php for($i=1; $i<=5; $i++): ?>
                                    <i class="bi bi-star<?= $i <= round($avg_rating) ? '-fill' : '' ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="text-muted mb-0">Based on <?= count($reviews) ?> reviews</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <h4 class="fw-bold mb-4">Customer Reviews</h4>

                    <?php if(is_logged_in()): ?>
                        <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                            <div class="card-body p-4">
                                <h6 class="fw-bold mb-3">Write a Review</h6>
                                <form id="reviewForm">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                    
                                    <div class="mb-3">
                                        <label class="form-label text-muted small mb-1">Your Rating</label>
                                        <div class="rating-input text-warning fs-5">
                                            <i class="bi bi-star rating-star" data-val="1"></i>
                                            <i class="bi bi-star rating-star" data-val="2"></i>
                                            <i class="bi bi-star rating-star" data-val="3"></i>
                                            <i class="bi bi-star rating-star" data-val="4"></i>
                                            <i class="bi bi-star rating-star" data-val="5"></i>
                                        </div>
                                        <input type="hidden" name="rating" id="ratingInput" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-muted small mb-1">Your Review</label>
                                        <textarea class="form-control bg-light" name="comment" rows="3" required minlength="5" placeholder="Share your experience with this product..."></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-dark rounded-2 px-4" id="submitReviewBtn">Submit Review</button>
                                </form>
                                <div id="reviewMessage" class="mt-3" style="display:none;"></div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-light border rounded-3 mb-4 d-flex align-items-center justify-content-between">
                            <span class="text-muted">Please login to write a review.</span>
                            <a href="auth/login.php" class="btn btn-sm btn-outline-dark">Login</a>
                        </div>
                    <?php endif; ?>

                    <?php if(empty($reviews)): ?>
                        <div class="alert alert-info rounded-3">No reviews yet. Be the first to share your thoughts!</div>
                    <?php else: ?>
                        <?php foreach($reviews as $rev): ?>
                            <div class="card border-0 shadow-sm rounded-3 mb-3">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="fw-bold mb-0"><?= htmlspecialchars($rev['full_name']) ?></h6>
                                            <small class="text-muted"><?= date('M d, Y', strtotime($rev['created_at'])) ?></small>
                                        </div>
                                        <div class="text-warning">
                                            <?php for($i=1; $i<=5; $i++): ?>
                                                <i class="bi bi-star<?= $i <= $rev['rating'] ? '-fill' : '' ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <p class="text-muted mb-0"><?= nl2br(htmlspecialchars($rev['comment'])) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>
</section>

<script>
    function changeImage(src) {
        document.getElementById('mainImage').src = src;
        // Update active thumbnail
        document.querySelectorAll('.rounded-2').forEach(btn => btn.classList.remove('btn-outline-primary', 'active'));
        event.target.closest('.rounded-2').classList.add('btn-outline-primary', 'active');
    }
    
    function addToCart(productId) {
        window.location.href = 'cart-action.php?action=add&id=' + productId;
    }

    // Review Form Logic
    const stars = document.querySelectorAll('.rating-star');
    const ratingInput = document.getElementById('ratingInput');
    
    stars.forEach(star => {
        star.addEventListener('click', function() {
            const val = this.getAttribute('data-val');
            ratingInput.value = val;
            
            // Update stars visually
            stars.forEach(s => {
                const sVal = s.getAttribute('data-val');
                if (sVal <= val) {
                    s.classList.remove('bi-star');
                    s.classList.add('bi-star-fill');
                } else {
                    s.classList.remove('bi-star-fill');
                    s.classList.add('bi-star');
                }
            });
        });
        
        star.style.cursor = 'pointer';
    });

    const reviewForm = document.getElementById('reviewForm');
    if (reviewForm) {
        reviewForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const submitBtn = document.getElementById('submitReviewBtn');
            const msgDiv = document.getElementById('reviewMessage');
            
            if (!ratingInput.value) {
                msgDiv.innerHTML = '<div class="alert alert-danger py-2 text-sm">Please select a rating!</div>';
                msgDiv.style.display = 'block';
                return;
            }
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Submitting...';
            
            try {
                const formData = new FormData(this);
                const response = await fetch('submit-review.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                msgDiv.style.display = 'block';
                if (result.success) {
                    msgDiv.innerHTML = `<div class="alert alert-success py-2 text-sm">${result.message}</div>`;
                    this.reset();
                    stars.forEach(s => {
                        s.classList.remove('bi-star-fill');
                        s.classList.add('bi-star');
                    });
                    ratingInput.value = '';
                } else {
                    msgDiv.innerHTML = `<div class="alert alert-danger py-2 text-sm">${result.message}</div>`;
                }
            } catch (err) {
                msgDiv.style.display = 'block';
                msgDiv.innerHTML = '<div class="alert alert-danger py-2 text-sm">An error occurred. Please try again.</div>';
            }
            
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Submit Review';
        });
    }
</script>

<?php require_once __DIR__ . '/includes/footer-bootstrap.php'; ?>
