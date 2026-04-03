<?php
require_once __DIR__ . '/includes/functions.php';

if (!is_logged_in()) {
    redirect('auth/login.php', "Please login to view your orders.", "info");
}

try {
    $user_id = $_SESSION['user_id'];
    
    // Fetch all orders for this user
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $all_orders = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Orders Error: " . $e->getMessage());
}

$page_title = "My Orders History";
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
                    <a href="orders.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-brand-600 text-white font-bold transition-all shadow-lg shadow-brand-600/20">
                        <i data-lucide="package" class="w-4 h-4"></i> My Orders
                    </a>
                    <a href="wishlist.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-500 hover:bg-slate-50 font-bold transition-all">
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
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">Order History</h1>
                <p class="text-slate-500 font-medium mt-1">Manage and track your recent purchases.</p>
            </div>

            <div class="space-y-6">
                <?php if(empty($all_orders)): ?>
                    <div class="text-center py-20 bg-white rounded-[3rem] border-2 border-dashed border-slate-200">
                        <div class="w-20 h-20 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i data-lucide="package-x" class="w-10 h-10"></i>
                        </div>
                        <h3 class="text-2xl font-black text-slate-900 mb-2">No orders found</h3>
                        <p class="text-slate-500 mb-8 max-w-sm mx-auto">You haven't placed any orders yet. Start exploring our amazing collection!</p>
                        <a href="shop.php" class="px-8 py-3 bg-slate-900 text-white rounded-2xl font-bold hover:bg-slate-800 transition-all shadow-xl">Start Shopping</a>
                    </div>
                <?php else: ?>
                    <?php foreach($all_orders as $order): ?>
                    <div class="bg-white rounded-[2rem] soft-shadow border border-slate-100 overflow-hidden group hover:border-brand-500/20 transition-all">
                        <div class="px-8 py-6 bg-slate-50/50 border-b border-slate-100 flex flex-wrap items-center justify-between gap-4">
                            <div class="flex gap-8">
                                <div>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Order Date</p>
                                    <p class="text-sm font-bold text-slate-700"><?= date('M d, Y', strtotime($order['created_at'])) ?></p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Order Total</p>
                                    <p class="text-sm font-black text-brand-600"><?= formatPrice($order['total_amount']) ?></p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Order ID</p>
                                    <p class="text-sm font-bold text-slate-700">#ARS-<?= $order['id'] ?></p>
                                </div>
                            </div>
                            <div>
                                <?php
                                $status_classes = [
                                    'Pending' => 'bg-amber-100 text-amber-700',
                                    'Confirmed' => 'bg-blue-100 text-blue-700',
                                    'Shipped' => 'bg-purple-100 text-purple-700',
                                    'Delivered' => 'bg-emerald-100 text-emerald-700',
                                    'Cancelled' => 'bg-red-100 text-red-700'
                                ];
                                $class = $status_classes[$order['delivery_status']] ?? 'bg-slate-100 text-slate-700';
                                ?>
                                <span class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest <?= $class ?>">
                                    <?= $order['delivery_status'] ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="p-8">
                            <!-- In a real system, you'd fetch order items here -->
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 bg-slate-50 rounded-xl border border-slate-100 flex items-center justify-center">
                                        <i data-lucide="package" class="w-6 h-6 text-slate-300"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-slate-900">Paid via <?= $order['payment_method'] ?></p>
                                        <p class="text-xs text-slate-500 font-medium">Shipped to: <?= htmlspecialchars(substr($order['address'], 0, 40)) ?>...</p>
                                    </div>
                                </div>
                                <div class="flex gap-3">
                                    <button class="px-6 py-2.5 bg-slate-900 text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-brand-600 transition-all shadow-lg shadow-slate-200">View Details</button>
                                    <?php if($order['delivery_status'] === 'Delivered'): ?>
                                        <button class="px-6 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-slate-50 transition-all">Write Review</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
