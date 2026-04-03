<?php
require_once __DIR__ . '/../includes/functions.php';

if (!is_admin()) {
    redirect('../auth/login.php', "Access denied.", "danger");
}

// Handle Delete
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    try {
        // Check if user has orders
        $count = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
        $count->execute([$del_id]);
        if ($count->fetchColumn() > 0) {
            redirect('customers.php', "Cannot delete customer: they have existing orders.", "danger");
        }
        $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'customer'")->execute([$del_id]);
        redirect('customers.php', "Customer deleted successfully.");
    } catch (PDOException $e) {
        redirect('customers.php', "Error: " . $e->getMessage(), "danger");
    }
}

try {
    // Fetch customers with their order counts
    $stmt = $pdo->query("
        SELECT u.*, COUNT(o.id) as order_count 
        FROM users u 
        LEFT JOIN orders o ON u.id = o.user_id 
        WHERE u.role = 'customer' 
        GROUP BY u.id 
        ORDER BY u.created_at DESC
    ");
    $customers = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

$page_title = "Customer Management";
include 'includes/header.php';
?>

<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Customers</h1>
        <p class="text-slate-500">View and manage registered customers and their shopping activity.</p>
    </div>
    <div class="flex items-center gap-3">
        <button class="flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
            <i data-lucide="download" class="w-4 h-4"></i> Export CSV
        </button>
    </div>
</div>

<div class="bg-white rounded-2xl soft-shadow border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50">
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Contact Info</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Orders</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Joined Date</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach($customers as $c): ?>
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center font-bold border border-brand-100 uppercase">
                                <?= substr($c['full_name'], 0, 1) ?>
                            </div>
                            <div class="text-sm font-bold text-slate-800"><?= htmlspecialchars($c['full_name']) ?></div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-slate-600"><?= htmlspecialchars($c['email'] ?: 'No Email') ?></div>
                        <div class="text-xs text-slate-400 font-medium"><?= htmlspecialchars($c['mobile']) ?></div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold <?= $c['order_count'] > 0 ? 'bg-brand-100 text-brand-700' : 'bg-slate-100 text-slate-600' ?>">
                            <?= $c['order_count'] ?> orders
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-slate-600"><?= date('M d, Y', strtotime($c['created_at'])) ?></div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            <a href="mailto:<?= htmlspecialchars($c['email']) ?>" class="p-2 text-slate-400 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition-all" title="Email Customer">
                                <i data-lucide="mail" class="w-4 h-4"></i>
                            </a>
                            <a href="customers.php?delete=<?= $c['id'] ?>" 
                               onclick="return confirm('Delete this customer account permanently?')"
                               class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all <?= $c['order_count'] > 0 ? 'opacity-30 cursor-not-allowed pointer-events-none' : '' ?>" title="Delete">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($customers)): ?>
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                        <i data-lucide="users" class="w-12 h-12 mx-auto mb-4 text-slate-200"></i>
                        <p class="text-lg font-medium">No customers found</p>
                        <p class="text-sm">When users register on your site, they will appear here.</p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
