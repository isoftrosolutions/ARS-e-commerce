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

require_once __DIR__ . '/includes/header.php';
?>

<div class="container mx-auto px-4 md:px-6 py-12">
    <!-- Breadcrumbs -->
    <div class="flex items-center gap-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-8">
        <a href="index.php" class="hover:text-brand-600 transition-colors">Home</a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <a href="shop.php?category=<?= $product['category_id'] ?>" class="hover:text-brand-600 transition-colors"><?= htmlspecialchars($product['category_name']) ?></a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <span class="text-slate-900 truncate max-w-[200px]"><?= htmlspecialchars($product['name']) ?></span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20">
        <!-- Left: Image Gallery -->
        <div class="space-y-6">
            <div class="aspect-square bg-white rounded-[2rem] soft-shadow border border-slate-100 overflow-hidden group">
                <img id="mainImage" src="<?= $product['image'] ? UPLOAD_DIR . $product['image'] : 'https://via.placeholder.com/800x800' ?>" 
                     class="w-full h-full object-contain p-8 transition-transform duration-500 group-hover:scale-110" alt="<?= htmlspecialchars($product['name']) ?>">
            </div>
            
            <?php if(!empty($gallery)): ?>
            <div class="flex gap-4 overflow-x-auto pb-2 no-scrollbar">
                <!-- Original primary image as thumbnail -->
                <button onclick="changeImage('<?= UPLOAD_DIR . $product['image'] ?>')" class="w-20 h-20 bg-white rounded-xl border-2 border-brand-600 p-2 flex-shrink-0">
                    <img src="<?= UPLOAD_DIR . $product['image'] ?>" class="w-full h-full object-contain">
                </button>
                <?php foreach($gallery as $img): ?>
                <button onclick="changeImage('<?= UPLOAD_DIR . $img['image_path'] ?>')" class="w-20 h-20 bg-white rounded-xl border border-slate-200 p-2 flex-shrink-0 hover:border-brand-600 transition-all">
                    <img src="<?= UPLOAD_DIR . $img['image_path'] ?>" class="w-full h-full object-contain">
                </button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Right: Product Info -->
        <div class="flex flex-col">
            <div class="mb-2">
                <span class="text-[10px] font-black text-brand-600 uppercase tracking-[0.2em]"><?= htmlspecialchars($product['category_name']) ?></span>
            </div>
            <h1 class="text-3xl md:text-5xl font-black text-slate-900 tracking-tight leading-tight mb-4">
                <?= htmlspecialchars($product['name']) ?>
            </h1>

            <div class="flex items-center gap-4 mb-8">
                <div class="flex text-amber-400">
                    <?php for($i=1; $i<=5; $i++): ?>
                        <i data-lucide="star" class="w-4 h-4 <?= $i <= round($avg_rating) ? 'fill-current' : 'text-slate-200' ?>"></i>
                    <?php endfor; ?>
                </div>
                <span class="text-sm font-bold text-slate-400 underline decoration-slate-200"><?= count($reviews) ?> Customer Reviews</span>
            </div>

            <div class="flex items-baseline gap-4 mb-10">
                <span class="text-4xl font-black text-slate-900"><?= formatPrice($product['discount_price'] ?: $product['price']) ?></span>
                <?php if($product['discount_price']): ?>
                    <span class="text-lg text-slate-400 line-through"><?= formatPrice($product['price']) ?></span>
                    <span class="bg-red-50 text-red-600 text-xs font-black px-3 py-1 rounded-full uppercase">Save <?= round((($product['price'] - $product['discount_price']) / $product['price']) * 100) ?>%</span>
                <?php endif; ?>
            </div>

            <!-- Stock & Urgency -->
            <div class="p-6 bg-slate-50 rounded-3xl border border-slate-100 mb-10 space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-2.5 h-2.5 rounded-full <?= $product['stock'] > 0 ? 'bg-emerald-500 animate-pulse' : 'bg-red-500' ?>"></div>
                    <span class="text-sm font-bold text-slate-700">
                        <?= $product['stock'] > 0 ? 'Available in Stock' : 'Currently Out of Stock' ?>
                    </span>
                </div>
                <?php if($product['stock'] > 0 && $product['stock'] <= 5): ?>
                    <div class="flex items-center gap-2 text-amber-600 bg-amber-50 p-3 rounded-xl">
                        <i data-lucide="alert-circle" class="w-4 h-4"></i>
                        <span class="text-xs font-bold uppercase tracking-wide">Only <?= $product['stock'] ?> items left! Order soon.</span>
                    </div>
                <?php endif; ?>
                <div class="flex items-center gap-3 text-slate-500 text-xs font-medium">
                    <i data-lucide="truck" class="w-4 h-4"></i>
                    <span>Delivery within 2-4 business days across Nepal.</span>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row gap-4 mb-12">
                <button onclick="addToCart(<?= $product['id'] ?>)" class="flex-grow h-16 bg-slate-900 text-white rounded-2xl font-black text-lg hover:bg-brand-600 transition-all transform hover:-translate-y-1 shadow-xl shadow-slate-200 flex items-center justify-center gap-3">
                    <i data-lucide="shopping-cart" class="w-6 h-6"></i> Add to Shopping Bag
                </button>
                <button class="w-16 h-16 bg-white border border-slate-200 text-slate-400 rounded-2xl flex items-center justify-center hover:text-red-500 hover:border-red-100 transition-all">
                    <i data-lucide="heart" class="w-6 h-6"></i>
                </button>
            </div>

            <!-- Description -->
            <div class="border-t border-slate-100 pt-10">
                <h4 class="text-xs font-black text-slate-900 uppercase tracking-[0.2em] mb-6">Product Details</h4>
                <div class="prose prose-slate prose-sm max-w-none text-slate-600 leading-relaxed">
                    <?= nl2br(htmlspecialchars($product['description'])) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Review Section -->
    <section class="mt-24 pt-20 border-t border-slate-100">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-12">
            <div>
                <h3 class="text-3xl font-black text-slate-900 tracking-tight">Customer Feedback</h3>
                <p class="text-slate-500 mt-1">Real reviews from verified buyers.</p>
            </div>
            <button class="px-8 py-3 bg-white border border-slate-200 text-slate-900 rounded-xl font-bold hover:bg-slate-50 transition-all">Write a Review</button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
            <!-- Ratings Summary -->
            <div class="lg:col-span-1 bg-slate-50 rounded-[2rem] p-8 h-fit">
                <div class="text-center mb-8">
                    <p class="text-6xl font-black text-slate-900 mb-2"><?= number_format($avg_rating, 1) ?></p>
                    <div class="flex justify-center text-amber-400 mb-2">
                        <?php for($i=1; $i<=5; $i++): ?>
                            <i data-lucide="star" class="w-5 h-5 <?= $i <= round($avg_rating) ? 'fill-current' : 'text-slate-200' ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Based on <?= count($reviews) ?> reviews</p>
                </div>
                <!-- Simple bar chart could go here -->
            </div>

            <!-- Review List -->
            <div class="lg:col-span-2 space-y-8">
                <?php if(empty($reviews)): ?>
                    <div class="text-center py-12 text-slate-400 italic">No reviews yet. Be the first to share your thoughts!</div>
                <?php else: ?>
                    <?php foreach($reviews as $rev): ?>
                    <div class="bg-white p-8 rounded-3xl soft-shadow border border-slate-50">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <p class="font-black text-slate-900"><?= htmlspecialchars($rev['full_name']) ?></p>
                                <p class="text-[10px] font-bold text-slate-400 uppercase mt-0.5"><?= date('M d, Y', strtotime($rev['created_at'])) ?></p>
                            </div>
                            <div class="flex text-amber-400">
                                <?php for($i=1; $i<=5; $i++): ?>
                                    <i data-lucide="star" class="w-3 h-3 <?= $i <= $rev['rating'] ? 'fill-current' : 'text-slate-200' ?>"></i>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <p class="text-slate-600 text-sm leading-relaxed"><?= nl2br(htmlspecialchars($rev['comment'])) ?></p>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<script>
    function changeImage(src) {
        document.getElementById('mainImage').src = src;
    }
    function addToCart(productId) {
        window.location.href = 'cart-action.php?action=add&id=' + productId;
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
