<?php
require_once "../db/db.php";
require_once "../flash.php";
include 'header.php';

$flash = get_flash();

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_withdrawal'])) {
    $withdrawalId = filter_input(INPUT_POST, 'withdrawal_id', FILTER_VALIDATE_INT);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
    $adminNote = filter_input(INPUT_POST, 'admin_note', FILTER_SANITIZE_STRING);
    $userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);

    if ($withdrawalId && $status && $userId && $amount) {
        try {
            $pdo->beginTransaction();

            // Get original status
            $stmt = $pdo->prepare("SELECT status FROM withdrawals WHERE id = ?");
            $stmt->execute([$withdrawalId]);
            $originalStatus = $stmt->fetchColumn();

            // Update withdrawal status
            $updateStmt = $pdo->prepare("UPDATE withdrawals SET status = ?, admin_note = ? WHERE id = ?");
            $updateStmt->execute([$status, $adminNote, $withdrawalId]);

            // Adjust user earnings based on status change
            if ($originalStatus === 'pending') {
                if ($status === 'completed') {
                    // Move from pending to withdrawn
                    $userUpdateStmt = $pdo->prepare("UPDATE users SET pending_earnings = pending_earnings - ?, withdrawn_amount = withdrawn_amount + ? WHERE id = ?");
                    $userUpdateStmt->execute([$amount, $amount, $userId]);
                } elseif ($status === 'rejected') {
                    // Return amount from pending to referral earnings
                    $userUpdateStmt = $pdo->prepare("UPDATE users SET pending_earnings = pending_earnings - ?, earnings = earnings + ? WHERE id = ?");
                    $userUpdateStmt->execute([$amount, $amount, $userId]);
                }
            }

            $pdo->commit();
            set_flash('success', 'Withdrawal status updated.');
        } catch (Exception $e) {
            $pdo->rollBack();
            set_flash('error', 'Database error: ' . $e->getMessage());
        }
    } else {
        set_flash('error', 'Invalid data provided.');
    }
    header("Location: admin-transactions.php");
    exit;
}

// Fetch pending withdrawals
try {
    $stmt = $pdo->prepare("
        SELECT w.id, w.amount, w.bank_name, w.account_number, w.account_name, w.status, w.created_at, u.username, u.id as user_id
        FROM withdrawals w
        JOIN users u ON w.user_id = u.id
        WHERE w.status = 'pending'
        ORDER BY w.created_at ASC
    ");
    $stmt->execute();
    $pendingWithdrawals = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $pendingWithdrawals = [];
    set_flash('error', 'Could not fetch withdrawals.');
}
?>    
    <div class="max-w-7xl mx-auto">
        <h1 class="text-3xl font-bold text-blue-900 mb-8">Withdrawal Requests</h1>

        <?php if ($flash): ?>
            <div class="mb-4 p-4 rounded-md <?php echo $flash['type'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                <?php echo $flash['message']; ?>
            </div>
        <?php endif; ?>

        <!-- Transactions Table -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-blue-900 text-white">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold">User</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Amount</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Bank Details</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Date</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($pendingWithdrawals) > 0): ?>
                            <?php foreach ($pendingWithdrawals as $w): ?>
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm"><?php echo htmlspecialchars($w['username']); ?></td>
                                    <td class="px-4 py-3 text-sm font-semibold">₦<?php echo number_format($w['amount'], 2); ?></td>
                                    <td class="px-4 py-3 text-sm">
                                        <?php echo htmlspecialchars($w['bank_name']); ?><br>
                                        <strong><?php echo htmlspecialchars($w['account_number']); ?></strong><br>
                                        <?php echo htmlspecialchars($w['account_name']); ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm"><?php echo date("d M, Y", strtotime($w['created_at'])); ?></td>
                                    <td class="px-4 py-3 text-sm">
                                        <form action="admin-transactions.php" method="POST" class="flex items-center gap-2">
                                            <input type="hidden" name="withdrawal_id" value="<?php echo $w['id']; ?>">
                                            <input type="hidden" name="user_id" value="<?php echo $w['user_id']; ?>">
                                            <input type="hidden" name="amount" value="<?php echo $w['amount']; ?>">
                                            <select name="status" class="border border-gray-300 rounded px-2 py-1 text-xs">
                                                <option value="completed">Complete</option>
                                                <option value="rejected">Reject</option>
                                            </select>
                                            <input type="text" name="admin_note" placeholder="Note (optional)" class="border border-gray-300 rounded px-2 py-1 text-xs w-24">
                                            <button type="submit" name="update_withdrawal" class="bg-blue-600 text-white px-3 py-1 rounded text-xs hover:bg-blue-700">Update</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-12 text-gray-500">No pending withdrawal requests.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
