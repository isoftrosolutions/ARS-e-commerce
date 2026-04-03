<?php
require_once __DIR__ . '/includes/functions.php';

if (!is_logged_in()) {
    redirect('auth/login.php', "Please login to view your wishlist.", "info");
}

try {
    $user_id = $_SESSION['user_id'];
    
    // Fetch wishlist items with product details
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name 
        FROM wishlist w
        JOIN products p ON w.product_id = p.id
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE w.user_id = ?
        ORDER BY w.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $wishlist_items = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Wishlist Error: " . $e->getMessage());
}

$page_title = "My Wishlist";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container mx-auto px-4 md:px-6 py-12">
    <div class="flex flex-col lg:flex-row gap-12">
        
        <!-- Account Sidebar (Shared) -->
        <aside class="w-full lg:w-64 flex-shrink-0">
            <div class="bg-white rounded-3xl soft-shadow border border-slate-100 p-8 sticky top-28">
                <nav class="flex flex-col gap-2">
                    <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-500 hover:bg-slate-50 font-bold transition-all">
                        <i data-lucide="layout-grid" class="w-4 h-4"></i> Dashboard
                    </a>
                    <a href="orders.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-500 hover:bg-slate-50 font-bold transition-all">
                        <i data-lucide="package" class="w-4 h-4"></i> My Orders
                    </a>
                    <a href="wishlist.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-brand-600 text-white font-bold transition-all shadow-lg shadow-brand-600/20">
                        <i data-lucide="heart" class="w-4 h-4"></i> Wishlist
                    </a>
                    <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-500 hover:bg-slate-50 font-bold transition-all">
                        <i data-lucide="settings" class="w-4 h-4"></i> Settings
                    </a>
                    <hr class="my-4 border-slate-100">
                    <a href="auth/logout.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-red-500 hover:bg-red-50 font-bold transition-all">
                        <i data-lucide="log-out" class="w-4 h-4"></i> Logout
                    </a>
                </nav>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-grow">
            <div class="mb-10">
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">My Wishlist</h1>
                <p class="text-slate-500 font-medium mt-1">Items you've saved for later.</p>
            </div>

            <?php if(empty($wishlist_items)): ?>
                <div class="text-center py-20 bg-white rounded-[3rem] border-2 border-dashed border-slate-200">
                    <div class="w-20 h-20 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i data-lucide="heart" class="w-10 h-10"></i>
                    </div>
                    <h3 class="text-2xl font-black text-slate-900 mb-2">Your wishlist is empty</h3>
                    <p class="text-slate-500 mb-8 max-w-sm mx-auto">Found something you like? Click the heart icon to save it here.</p>
                    <a href="shop.php" class="px-8 py-3 bg-slate-900 text-white rounded-2xl font-bold hover:bg-slate-800 transition-all shadow-xl">Start Browsing</a>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">
                    <?php foreach ($wishlist_items as $p): ?>
                        <div class="bg-white rounded-3xl soft-shadow border border-slate-100 overflow-hidden group hover:border-brand-500/30 transition-all relative">
                            <!-- Remove Button -->
                            <a href="wishlist-action.php?action=remove&id=<?= $p['id'] ?>" class="absolute top-4 right-4 z-10 w-8 h-8 bg-white/80 backdrop-blur-md text-red-500 rounded-full flex items-center justify-center hover:bg-red-500 hover:text-white transition-all shadow-sm">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </a>

                            <div class="relative aspect-square overflow-hidden bg-slate-50">
                                <a href="product.php?slug=<?= $p['slug'] ?>">
                                    <img src="<?= $p['image'] ? UPLOAD_DIR . $p['image'] : 'https://via.placeholder.com/400x400' ?>" 
                                         class="w-full h-full object-contain transition-transform duration-500 group-hover:scale-110 p-6" alt="<?= htmlspecialchars($p['name']) ?>">
                                </a>
                            </div>
                            <div class="p-6">
                                <span class="text-[10px] font-bold text-brand-600 uppercase tracking-widest"><?= htmlspecialchars($p['category_name'] ?? 'General') ?></span>
                                <a href="product.php?slug=<?= $p['slug'] ?>">
                                    <h3 class="text-lg font-bold text-slate-800 mt-1 line-clamp-1 group-hover:text-brand-600 transition-colors"><?= htmlspecialchars($p['name']) ?></h3>
                                </a>
                                
                                <div class="flex items-center justify-between mt-6">
                                    <div>
                                        <span class="text-xl font-black text-slate-900"><?= formatPrice($p['discount_price'] ?: $p['price']) ?></span>
                                    </div>
                                    <button onclick="addToCart(<?= $p['id'] ?>)" class="px-4 py-2 bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-brand-600 transition-all">
                                        Add to Bag
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function addToCart(productId) {
        window.location.href = 'cart-action.php?action=add&id=' + productId;
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
