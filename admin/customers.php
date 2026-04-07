<?php
// admin/customers.php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/layout-parts.php';

// Fetch Customers
$stmt = $pdo->query("
    SELECT u.*, COUNT(o.id) as order_count, SUM(o.total_amount) as total_spent
    FROM users u
    LEFT JOIN orders o ON u.id = o.user_id
    WHERE u.role = 'customer'
    GROUP BY u.id
    ORDER BY u.created_at DESC
");
$customers = $stmt->fetchAll();

admin_header('Customers', 'customers');
?>

<div class="flex-between mb-6" style="margin-bottom: 24px;">
    <h2 style="margin-bottom: 0;">Registered Customers</h2>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Contact</th>
                    <th>Orders</th>
                    <th>Total Spent</th>
                    <th>Joined</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customers as $customer): ?>
                <tr>
                    <td>
                        <div style="font-weight: 600;"><?php echo htmlspecialchars($customer['full_name']); ?></div>
                        <div style="font-size: 12px; color: var(--text-secondary);">ID: #<?php echo $customer['id']; ?></div>
                    </td>
                    <td>
                        <div><?php echo htmlspecialchars($customer['mobile']); ?></div>
                        <div style="font-size: 12px; color: var(--text-secondary);"><?php echo htmlspecialchars($customer['email'] ?: 'No email'); ?></div>
                    </td>
                    <td>
                        <span class="badge badge-info"><?php echo $customer['order_count']; ?> Orders</span>
                    </td>
                    <td style="font-weight: 600;">
                        <?php echo formatPrice($customer['total_spent'] ?: 0); ?>
                    </td>
                    <td>
                        <div style="font-size: 14px;"><?php echo date('M d, Y', strtotime($customer['created_at'])); ?></div>
                    </td>
                    <td style="text-align: right;">
                        <div style="display: flex; gap: 8px; justify-content: flex-end;">
                            <button class="btn btn-ghost btn-sm" title="View History" onclick="viewCustomer(<?php echo $customer['id']; ?>)">
                                <i data-lucide="history" style="width: 16px;"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($customers)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 60px; color: var(--text-secondary);">
                        <i data-lucide="users" style="width: 48px; height: 48px; margin-bottom: 16px; opacity: 0.2;"></i>
                        <p>No customers registered yet.</p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function viewCustomer(id) {
    // This could open a modal with customer order history
    Toast.info('Customer detail view coming soon!');
}
</script>

<?php admin_footer(); ?>
