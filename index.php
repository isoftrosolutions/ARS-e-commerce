<?php
require_once __DIR__ . '/includes/header.php';

// Fetch featured products
try {
    $stmt = $pdo->query("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC LIMIT 8");
    $latest_products = $stmt->fetchAll();
    
    $categories = $pdo->query("SELECT * FROM categories LIMIT 6")->fetchAll();
} catch (PDOException $e) {
    $latest_products = [];
    $categories = [];
}
?>

<!-- Hero Section -->
<section class="relative bg-slate-900 overflow-hidden">
    <div class="absolute inset-0 opacity-40">
        <img src="https://images.unsplash.com/photo-1441986300917-64674bd600d8?q=80&w=2070&auto=format&fit=crop" class="w-full h-full object-cover" alt="Hero Background">
    </div>
    <div class="relative container mx-auto px-4 md:px-6 py-20 md:py-32 flex flex-col items-center text-center">
        <span class="inline-block px-4 py-1.5 bg-brand-600 text-white text-xs font-bold tracking-widest uppercase rounded-full mb-6 animate-bounce">
            New Season Arrival
        </span>
        <h1 class="text-4xl md:text-7xl font-black text-white mb-6 tracking-tighter leading-tight max-w-4xl">
            UPGRADE YOUR <span class="text-brand-500">LIFESTYLE</span> WITH ARS
        </h1>
        <p class="text-lg md:text-xl text-slate-300 mb-10 max-w-2xl leading-relaxed">
            Discover the latest trends in electronics, fashion, and home essentials. Premium quality, delivered fast to your doorstep.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 w-full sm:w-auto">
            <a href="shop.php" class="px-8 py-4 bg-brand-600 text-white rounded-2xl font-bold hover:bg-brand-700 transition-all shadow-xl shadow-brand-600/20 transform hover:-translate-y-1">
                Shop Collection
            </a>
            <a href="#featured" class="px-8 py-4 bg-white/10 backdrop-blur-md text-white border border-white/20 rounded-2xl font-bold hover:bg-white/20 transition-all transform hover:-translate-y-1">
                View Deals
            </a>
        </div>
    </div>
</section>

<!-- Features Bar -->
<section class="bg-white border-b border-slate-100 py-8 relative z-10 -mt-8 mx-4 md:mx-auto container rounded-3xl soft-shadow grid grid-cols-2 lg:grid-cols-4 gap-8 px-8">
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center flex-shrink-0">
            <i data-lucide="truck" class="w-6 h-6"></i>
        </div>
        <div>
            <h4 class="font-bold text-sm">Free Shipping</h4>
            <p class="text-xs text-slate-500">On orders over Rs. 1,000</p>
        </div>
    </div>
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center flex-shrink-0">
            <i data-lucide="shield-check" class="w-6 h-6"></i>
        </div>
        <div>
            <h4 class="font-bold text-sm">Secure Payment</h4>
            <p class="text-xs text-slate-500">100% Protected transfers</p>
        </div>
    </div>
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center flex-shrink-0">
            <i data-lucide="refresh-cw" class="w-6 h-6"></i>
        </div>
        <div>
            <h4 class="font-bold text-sm">Easy Returns</h4>
            <p class="text-xs text-slate-500">7-Day easy replacement</p>
        </div>
    </div>
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-2xl flex items-center justify-center flex-shrink-0">
            <i data-lucide="headphones" class="w-6 h-6"></i>
        </div>
        <div>
            <h4 class="font-bold text-sm">24/7 Support</h4>
            <p class="text-xs text-slate-500">Expert help anytime</p>
        </div>
    </div>
</section>

<!-- Category Grid -->
<section class="container mx-auto px-4 md:px-6 py-20">
    <div class="flex items-center justify-between mb-10">
        <div>
            <h2 class="text-3xl font-black text-slate-900 tracking-tight">Shop by Category</h2>
            <p class="text-slate-500 mt-1">Handpicked categories for your needs.</p>
        </div>
        <a href="shop.php" class="text-brand-600 font-bold hover:underline flex items-center gap-1">
            All Categories <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </a>
    </div>
    
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
        <?php foreach($categories as $cat): ?>
            <a href="shop.php?category=<?= $cat['id'] ?>" class="group">
                <div class="aspect-square bg-white rounded-3xl soft-shadow border border-slate-100 flex flex-col items-center justify-center p-6 transition-all group-hover:bg-brand-600 group-hover:-translate-y-2">
                    <div class="w-16 h-16 bg-brand-50 text-brand-600 rounded-2xl flex items-center justify-center mb-4 transition-all group-hover:bg-white/20 group-hover:text-white">
                        <i data-lucide="layers" class="w-8 h-8"></i>
                    </div>
                    <h4 class="font-bold text-slate-800 transition-all group-hover:text-white"><?= htmlspecialchars($cat['name']) ?></h4>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- Featured Products -->
<section id="featured" class="bg-slate-100/50 py-20">
    <div class="container mx-auto px-4 md:px-6">
        <div class="flex items-center justify-between mb-12">
            <div>
                <h2 class="text-3xl font-black text-slate-900 tracking-tight">Featured Products</h2>
                <p class="text-slate-500 mt-1">Trending items that people love right now.</p>
            </div>
            <div class="flex gap-2">
                <button class="w-10 h-10 bg-white border border-slate-200 rounded-full flex items-center justify-center hover:bg-brand-600 hover:text-white transition-all transition-colors"><i data-lucide="chevron-left" class="w-5 h-5"></i></button>
                <button class="w-10 h-10 bg-white border border-slate-200 rounded-full flex items-center justify-center hover:bg-brand-600 hover:text-white transition-all transition-colors"><i data-lucide="chevron-right" class="w-5 h-5"></i></button>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            <?php foreach($latest_products as $p): ?>
                <div class="bg-white rounded-3xl soft-shadow border border-slate-100 overflow-hidden group hover:border-brand-500/30 transition-all transition-colors">
                    <div class="relative aspect-square overflow-hidden bg-slate-50">
                        <?php if($p['discount_price']): ?>
                            <div class="absolute top-4 left-4 z-10 bg-red-600 text-white text-[10px] font-black px-2.5 py-1 rounded-full uppercase tracking-tighter">
                                Save <?= round((($p['price'] - $p['discount_price']) / $p['price']) * 100) ?>%
                            </div>
                        <?php endif; ?>
                        <button class="absolute top-4 right-4 z-10 w-9 h-9 bg-white/80 backdrop-blur-md text-slate-400 rounded-full flex items-center justify-center hover:text-red-500 transition-all scale-0 group-hover:scale-100 transform duration-300">
                            <i data-lucide="heart" class="w-5 h-5"></i>
                        </button>
                        <a href="product.php?slug=<?= $p['slug'] ?>">
                            <img src="<?= $p['image'] ? UPLOAD_DIR . $p['image'] : 'https://via.placeholder.com/400x400?text=' . urlencode($p['name']) ?>" 
                                 class="w-full h-full object-contain transition-transform duration-500 group-hover:scale-110 p-4" alt="<?= $p['name'] ?>">
                        </a>
                    </div>
                    <div class="p-6">
                        <span class="text-[10px] font-bold text-brand-600 uppercase tracking-widest"><?= htmlspecialchars($p['cat_name'] ?? 'General') ?></span>
                        <a href="product.php?slug=<?= $p['slug'] ?>">
                            <h3 class="text-lg font-bold text-slate-800 mt-1 line-clamp-1 group-hover:text-brand-600 transition-colors"><?= htmlspecialchars($p['name']) ?></h3>
                        </a>
                        <div class="flex items-center gap-1 mt-2">
                            <div class="flex text-amber-400"><i data-lucide="star" class="w-3 h-3 fill-current"></i><i data-lucide="star" class="w-3 h-3 fill-current"></i><i data-lucide="star" class="w-3 h-3 fill-current"></i><i data-lucide="star" class="w-3 h-3 fill-current"></i><i data-lucide="star" class="w-3 h-3 fill-current"></i></div>
                            <span class="text-[10px] font-bold text-slate-400">(24 Reviews)</span>
                        </div>
                        <div class="flex items-center justify-between mt-6">
                            <div>
                                <span class="text-xl font-black text-slate-900"><?= formatPrice($p['discount_price'] ?: $p['price']) ?></span>
                                <?php if($p['discount_price']): ?>
                                    <span class="text-xs text-slate-400 line-through ml-2"><?= formatPrice($p['price']) ?></span>
                                <?php endif; ?>
                            </div>
                            <button onclick="addToCart(<?= $p['id'] ?>)" class="w-10 h-10 bg-slate-900 text-white rounded-xl flex items-center justify-center hover:bg-brand-600 transition-all transform group-hover:scale-110">
                                <i data-lucide="plus" class="w-5 h-5"></i>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="container mx-auto px-4 md:px-6 py-20">
    <div class="bg-brand-600 rounded-[3rem] p-8 md:p-16 flex flex-col md:flex-row items-center justify-between gap-10 overflow-hidden relative">
        <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-32 -mt-32"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-black/10 rounded-full -ml-24 -mb-24"></div>
        
        <div class="relative z-10 max-w-xl text-center md:text-left">
            <h2 class="text-3xl md:text-5xl font-black text-white leading-tight mb-6">READY TO EXPERIENCE PREMIUM SHOPPING?</h2>
            <p class="text-brand-100 text-lg">Join 10,000+ satisfied customers across Nepal. Get exclusive deals delivered to your inbox.</p>
        </div>
        <div class="relative z-10 flex flex-col gap-4 w-full md:w-auto">
            <a href="auth/signup.php" class="px-10 py-5 bg-white text-brand-600 rounded-2xl font-black hover:bg-slate-100 transition-all text-center shadow-xl">Create Account</a>
            <p class="text-center text-brand-200 text-xs font-bold uppercase tracking-widest">No Credit Card Required</p>
        </div>
    </div>
</section>

<script>
    function addToCart(productId) {
        window.location.href = 'cart-action.php?action=add&id=' + productId;
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
