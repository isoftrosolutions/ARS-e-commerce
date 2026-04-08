<?php
// admin/customers.php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/layout-parts.php';

// Fetch Customers
$stmt = $pdo->query("
    SELECT u.*, COUNT(o.id) as order_count, COALESCE(SUM(o.total_amount), 0) as total_spent
    FROM users u
    LEFT JOIN orders o ON u.id = o.user_id
    WHERE u.role = 'customer'
    GROUP BY u.id
    ORDER BY u.created_at DESC
");
$customers = $stmt->fetchAll();

admin_header('Customers', 'customers');
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Customers</h1>
        <p class="page-subtitle">Registered customers — <?php echo count($customers); ?> total</p>
    </div>
</div>

<div class="table-wrapper">
    <div class="table-scroll">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Contact</th>
                    <th>Orders</th>
                    <th>Total Spent</th>
                    <th>Joined</th>
                    <th class="col-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customers as $customer): ?>
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:32px;height:32px;border-radius:var(--r-full);background:var(--accent-dim);border:1.5px solid var(--border-accent);display:flex;align-items:center;justify-content:center;font-family:var(--font-display);font-size:12px;font-weight:700;color:var(--accent);flex-shrink:0;">
                                <?php echo strtoupper(substr($customer['full_name'], 0, 1)); ?>
                            </div>
                            <div>
                                <div style="font-weight:600;"><?php echo htmlspecialchars($customer['full_name']); ?></div>
                                <div style="font-size:12px;color:var(--text-muted);">ID: #<?php echo $customer['id']; ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div><?php echo htmlspecialchars($customer['mobile']); ?></div>
                        <div style="font-size:12px;color:var(--text-secondary);"><?php echo htmlspecialchars($customer['email'] ?: '—'); ?></div>
                    </td>
                    <td>
                        <span class="badge badge-info"><?php echo $customer['order_count']; ?> Orders</span>
                    </td>
                    <td style="font-family:var(--font-mono);font-weight:600;">
                        <?php echo formatPrice($customer['total_spent']); ?>
                    </td>
                    <td style="font-size:13px;color:var(--text-secondary);">
                        <?php echo date('M d, Y', strtotime($customer['created_at'])); ?>
                    </td>
                    <td class="col-actions">
                        <div class="actions-cell">
                            <button class="btn btn-ghost btn-sm btn-icon" title="View Orders"
                                onclick="Toast.info('Customer detail view coming soon!')">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($customers)): ?>
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <div class="empty-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:26px;height:26px;"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div>
                            <div class="empty-title">No customers yet</div>
                            <div class="empty-text">Customers will appear here once they register.</div>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php admin_footer(); ?>
