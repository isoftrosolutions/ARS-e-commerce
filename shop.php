<?php
$page_title = "Shop All Products";
require_once __DIR__ . '/includes/header.php';

// Filtering logic
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

try {
    $sql = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";
    $params = [];
    
    if ($category_id > 0) {
        $sql .= " AND p.category_id = ?";
        $params[] = $category_id;
    }
    
    if ($query) {
        $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
        $params[] = "%$query%";
        $params[] = "%$query%";
    }

    // Sorting
    switch($sort) {
        case 'price_low': $sql .= " ORDER BY (CASE WHEN discount_price IS NOT NULL THEN discount_price ELSE price END) ASC"; break;
        case 'price_high': $sql .= " ORDER BY (CASE WHEN discount_price IS NOT NULL THEN discount_price ELSE price END) DESC"; break;
        default: $sql .= " ORDER BY p.created_at DESC"; break;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    $categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
} catch (PDOException $e) {
    die("Shop Error: " . $e->getMessage());
}
?>

<!-- Breadcrumbs & Title -->
<section class="bg-white border-b border-slate-100 py-10">
    <div class="container mx-auto px-4 md:px-6">
        <div class="flex items-center gap-2 text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">
            <a href="index.php" class="hover:text-brand-600 transition-colors">Home</a>
            <i data-lucide="chevron-right" class="w-3 h-3"></i>
            <span class="text-slate-900">Shop Catalog</span>
        </div>
        <h1 class="text-3xl md:text-4xl font-black text-slate-900 tracking-tight">
            <?php 
            if($category_id > 0) {
                foreach($categories as $cat) if($cat['id'] == $category_id) echo htmlspecialchars($cat['name']);
            } elseif($query) {
                echo "Search results for: '" . htmlspecialchars($query) . "'";
            } else {
                echo "All Collections";
            }
            ?>
        </h1>
    </div>
</section>

<div class="container mx-auto px-4 md:px-6 py-12">
    <div class="flex flex-col lg:flex-row gap-12">
        
        <!-- Sidebar Filters -->
        <aside class="w-full lg:w-72 flex-shrink-0 space-y-10">
            <!-- Categories -->
            <div>
                <h4 class="text-xs font-black text-slate-900 uppercase tracking-widest mb-6 pb-2 border-b-2 border-brand-600 w-fit">Categories</h4>
                <div class="flex flex-col gap-2">
                    <a href="shop.php" class="flex items-center justify-between group py-1.5 transition-all <?= $category_id === 0 ? 'text-brand-600 font-bold' : 'text-slate-500 hover:text-brand-600' ?>">
                        <span class="text-sm">All Products</span>
                        <span class="text-[10px] bg-slate-100 px-2 py-0.5 rounded-full text-slate-400 group-hover:bg-brand-50 group-hover:text-brand-600 transition-colors"><?= count($products) ?></span>
                    </a>
                    <?php foreach($categories as $cat): ?>
                        <a href="shop.php?category=<?= $cat['id'] ?>" class="flex items-center justify-between group py-1.5 transition-all <?= $category_id == $cat['id'] ? 'text-brand-600 font-bold' : 'text-slate-500 hover:text-brand-600' ?>">
                            <span class="text-sm"><?= htmlspecialchars($cat['name']) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Price Filter (UI Placeholder) -->
            <div>
                <h4 class="text-xs font-black text-slate-900 uppercase tracking-widest mb-6 pb-2 border-b-2 border-brand-600 w-fit">Filter by Price</h4>
                <div class="space-y-4">
                    <input type="range" class="w-full h-1.5 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-brand-600">
                    <div class="flex items-center justify-between text-xs font-bold text-slate-500">
                        <span>Rs. 0</span>
                        <span>Rs. 50,000+</span>
                    </div>
                </div>
            </div>

            <!-- Promo Banner -->
            <div class="bg-slate-900 rounded-3xl p-6 overflow-hidden relative group cursor-pointer">
                <div class="absolute top-0 right-0 w-32 h-32 bg-brand-600/20 rounded-full -mr-16 -mt-16 transition-transform group-hover:scale-110"></div>
                <h5 class="text-brand-500 font-black text-xl mb-2 italic">EXTRA 15% OFF</h5>
                <p class="text-white text-xs leading-relaxed mb-4">On your first mobile app order. Use code <span class="text-brand-500 font-bold tracking-widest">APP15</span></p>
                <button class="text-[10px] font-black uppercase tracking-widest text-white border-b border-brand-600 pb-1 hover:text-brand-500 transition-colors">Download Now</button>
            </div>
        </aside>

        <!-- Product Listing -->
        <div class="flex-grow">
            <!-- Toolbar -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-10 bg-white p-4 rounded-2xl soft-shadow border border-slate-100">
                <p class="text-sm font-bold text-slate-500">Showing <?= count($products) ?> results</p>
                <form action="shop.php" method="GET" class="flex items-center gap-3">
                    <?php if($category_id > 0): ?><input type="hidden" name="category" value="<?= $category_id ?>"><?php endif; ?>
                    <?php if($query): ?><input type="hidden" name="q" value="<?= htmlspecialchars($query) ?>"><?php endif; ?>
                    <span class="text-xs font-bold text-slate-400 uppercase whitespace-nowrap">Sort by:</span>
                    <select name="sort" onchange="this.form.submit()" class="bg-slate-50 border-none rounded-lg text-xs font-bold text-slate-700 focus:ring-2 focus:ring-brand-500/20 py-2 pl-3 pr-8 outline-none">
                        <option value="newest" <?= $sort == 'newest' ? 'selected' : '' ?>>Latest Arrivals</option>
                        <option value="price_low" <?= $sort == 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                        <option value="price_high" <?= $sort == 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                    </select>
                </form>
            </div>

            <!-- Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-8">
                <?php foreach ($products as $p): ?>
                    <div class="bg-white rounded-3xl soft-shadow border border-slate-100 overflow-hidden group hover:border-brand-500/30 transition-all transition-colors">
                        <div class="relative aspect-square overflow-hidden bg-slate-50">
                            <?php if($p['discount_price']): ?>
                                <div class="absolute top-4 left-4 z-10 bg-red-600 text-white text-[10px] font-black px-2.5 py-1 rounded-full uppercase tracking-tighter shadow-lg shadow-red-600/20">
                                    -<?= round((($p['price'] - $p['discount_price']) / $p['price']) * 100) ?>%
                                </div>
                            <?php endif; ?>
                            <button class="absolute top-4 right-4 z-10 w-9 h-9 bg-white/80 backdrop-blur-md text-slate-400 rounded-full flex items-center justify-center hover:text-red-500 transition-all scale-0 group-hover:scale-100 transform duration-300">
                                <i data-lucide="heart" class="w-5 h-5"></i>
                            </button>
                            <a href="product.php?slug=<?= $p['slug'] ?>">
                                <img src="<?= $p['image'] ? UPLOAD_DIR . $p['image'] : 'https://via.placeholder.com/400x400?text=' . urlencode($p['name']) ?>" 
                                     class="w-full h-full object-contain transition-transform duration-500 group-hover:scale-110 p-6" alt="<?= $p['name'] ?>">
                            </a>
                        </div>
                        <div class="p-6">
                            <span class="text-[10px] font-bold text-brand-600 uppercase tracking-widest"><?= htmlspecialchars($p['category_name'] ?? 'General') ?></span>
                            <a href="product.php?slug=<?= $p['slug'] ?>">
                                <h3 class="text-lg font-bold text-slate-800 mt-1 line-clamp-1 group-hover:text-brand-600 transition-colors"><?= htmlspecialchars($p['name']) ?></h3>
                            </a>
                            <div class="flex items-center gap-1 mt-2">
                                <div class="flex text-amber-400"><i data-lucide="star" class="w-3 h-3 fill-current"></i><i data-lucide="star" class="w-3 h-3 fill-current"></i><i data-lucide="star" class="w-3 h-3 fill-current"></i><i data-lucide="star" class="w-3 h-3 fill-current"></i><i data-lucide="star" class="w-3 h-3 fill-current"></i></div>
                                <span class="text-[10px] font-bold text-slate-400">(12 Reviews)</span>
                            </div>
                            <div class="flex items-center justify-between mt-6">
                                <div>
                                    <span class="text-xl font-black text-slate-900"><?= formatPrice($p['discount_price'] ?: $p['price']) ?></span>
                                    <?php if($p['discount_price']): ?>
                                        <span class="text-xs text-slate-400 line-through ml-2"><?= formatPrice($p['price']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <button onclick="addToCart(<?= $p['id'] ?>)" class="w-10 h-10 bg-slate-900 text-white rounded-xl flex items-center justify-center hover:bg-brand-600 transition-all transform group-hover:scale-110">
                                    <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if (empty($products)): ?>
                    <div class="col-span-full py-20 text-center bg-white rounded-[3rem] border-2 border-dashed border-slate-200">
                        <div class="w-20 h-20 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i data-lucide="search-x" class="w-10 h-10"></i>
                        </div>
                        <h3 class="text-2xl font-black text-slate-900 mb-2">No products found</h3>
                        <p class="text-slate-500 mb-8 max-w-sm mx-auto">We couldn't find anything matching your filters or search. Try widening your search range.</p>
                        <a href="shop.php" class="px-8 py-3 bg-slate-900 text-white rounded-2xl font-bold hover:bg-slate-800 transition-all shadow-xl">Reset All Filters</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    function addToCart(productId) {
        window.location.href = 'cart-action.php?action=add&id=' + productId;
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
