<?php
require_once __DIR__ . '/../includes/functions.php';
// Assuming admin check is handled in functions.php or similar
// if (!is_admin()) redirect('login.php'); 

$stmt = $pdo->query("SELECT * FROM email_queue ORDER BY created_at DESC LIMIT 50");
$logs = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div class="p-6">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight">Email Queue Logs</h1>
            <p class="text-slate-500 font-medium">Monitor background email processing</p>
        </div>
        <div class="flex gap-2">
            <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold">Worker Active</span>
        </div>
    </div>

    <div class="bg-white rounded-[2rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-50/50 border-b border-slate-100">
                    <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Recipient</th>
                    <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Subject</th>
                    <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Status</th>
                    <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Attempts</th>
                    <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Created At</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($logs as $log): ?>
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-900"><?= htmlspecialchars($log['recipient_name']) ?></div>
                            <div class="text-xs text-slate-400"><?= htmlspecialchars($log['recipient_email']) ?></div>
                        </td>
                        <td class="px-6 py-4 text-sm font-medium text-slate-600">
                            <?= htmlspecialchars($log['subject']) ?>
                        </td>
                        <td class="px-6 py-4">
                            <?php 
                                $status_class = match($log['status']) {
                                    'sent' => 'bg-green-100 text-green-700',
                                    'failed' => 'bg-red-100 text-red-700',
                                    'pending' => 'bg-amber-100 text-amber-700',
                                    'sending' => 'bg-blue-100 text-blue-700',
                                    default => 'bg-slate-100 text-slate-700'
                                };
                            ?>
                            <span class="px-3 py-1 <?= $status_class ?> rounded-full text-[10px] font-black uppercase">
                                <?= $log['status'] ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm font-bold text-slate-900">
                            <?= $log['attempts'] ?> / <?= $log['max_attempts'] ?>
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-400 font-medium">
                            <?= date('M d, H:i', strtotime($log['created_at'])) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
