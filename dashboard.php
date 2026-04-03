<?php
require_once __DIR__ . '/includes/functions.php';

if (!is_logged_in()) {
    redirect('auth/login.php', "Please login to access your dashboard.", "info");
}

try {
    $user_id = $_SESSION['user_id'];
    
    // Fetch User Info
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    // Fetch stats
    $stmt_stats = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
    $stmt_stats->execute([$user_id]);
    $total_orders = $stmt_stats->fetchColumn();

    // Fetch recent orders
    $stmt_recent = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 3");
    $stmt_recent->execute([$user_id]);
    $recent_orders = $stmt_recent->fetchAll();

} catch (PDOException $e) {
    die("Dashboard Error: " . $e->getMessage());
}

$page_title = "My Account Dashboard";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container mx-auto px-4 md:px-6 py-12">
    <div class="flex flex-col lg:flex-row gap-12">
        
        <!-- Account Sidebar -->
        <aside class="w-full lg:w-64 flex-shrink-0">
            <div class="bg-white rounded-3xl soft-shadow border border-slate-100 p-8">
                <div class="flex flex-col items-center text-center mb-8">
                    <div class="w-20 h-20 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center text-2xl font-black border-4 border-white soft-shadow mb-4 uppercase">
                        <?= substr($user['full_name'], 0, 1) ?>
                    </div>
                    <h2 class="text-lg font-black text-slate-900"><?= htmlspecialchars($user['full_name']) ?></h2>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Customer Since <?= date('Y', strtotime($user['created_at'])) ?></p>
                </div>

                <nav class="flex flex-col gap-2">
                    <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-brand-600 text-white font-bold transition-all shadow-lg shadow-brand-600/20">
                        <i data-lucide="layout-grid" class="w-4 h-4"></i> Dashboard
                    </a>
                    <a href="orders.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-500 hover:bg-slate-50 font-bold transition-all">
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
        <div class="flex-grow space-y-10">
            <div>
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">Account Overview</h1>
                <p class="text-slate-500 font-medium mt-1">Welcome back, <?= explode(' ', $user['full_name'])[0] ?>!</p>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white p-8 rounded-3xl soft-shadow border border-slate-100">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Total Orders</p>
                    <p class="text-3xl font-black text-slate-900"><?= $total_orders ?></p>
                </div>
                <div class="bg-white p-8 rounded-3xl soft-shadow border border-slate-100">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Active Cart</p>
                    <p class="text-3xl font-black text-slate-900"><?= get_cart_count() ?> <span class="text-sm font-bold text-slate-400">items</span></p>
                </div>
                <div class="bg-white p-8 rounded-3xl soft-shadow border border-slate-100">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Saved Items</p>
                    <p class="text-3xl font-black text-slate-900">0</p>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="bg-white rounded-[2.5rem] soft-shadow border border-slate-100 overflow-hidden">
                <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-lg font-black text-slate-900">Recent Orders</h3>
                    <a href="orders.php" class="text-xs font-black text-brand-600 uppercase tracking-widest hover:underline">View All</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50">
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Order ID</th>
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Date</th>
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Status</th>
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <?php foreach($recent_orders as $order): ?>
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-8 py-5 font-bold text-slate-900">#ARS-<?= $order['id'] ?></td>
                                <td class="px-8 py-5 text-slate-500 font-medium"><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                                <td class="px-8 py-5 text-center">
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
                                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter <?= $class ?>">
                                        <?= $order['delivery_status'] ?>
                                    </span>
                                </td>
                                <td class="px-8 py-5 text-right font-black text-slate-900"><?= formatPrice($order['total_amount']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($recent_orders)): ?>
                            <tr>
                                <td colspan="4" class="px-8 py-12 text-center text-slate-400 font-medium">No orders placed yet.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Profile Info -->
            <div class="bg-white p-8 md:p-10 rounded-[2.5rem] soft-shadow border border-slate-100">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-lg font-black text-slate-900">Profile Information</h3>
                    <button class="px-4 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-slate-200 transition-all">Edit</button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Email Address</p>
                        <p class="text-sm font-bold text-slate-700"><?= htmlspecialchars($user['email'] ?: 'Not provided') ?></p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Mobile Number</p>
                        <p class="text-sm font-bold text-slate-700"><?= htmlspecialchars($user['mobile']) ?></p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Default Address</p>
                        <p class="text-sm font-bold text-slate-700 leading-relaxed"><?= nl2br(htmlspecialchars($user['address'])) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
