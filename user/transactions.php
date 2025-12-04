<?php
session_start();
$pdo = require_once "../db/db.php";

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

$user = $_SESSION['user'];

// Fetch transactions for the user
try {
    $stmt = $pdo->prepare("
        SELECT type, amount, description, status, created_at 
        FROM transactions 
        WHERE user_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$user['id']]);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch summary
    $summaryStmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_transactions,
            SUM(CASE WHEN type IN ('deposit', 'referral_earning') AND status = 'completed' THEN amount ELSE 0 END) as total_received,
            SUM(CASE WHEN type IN ('purchase', 'withdrawal') AND status = 'completed' THEN amount ELSE 0 END) as total_spent,
            COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_transactions
        FROM transactions 
        WHERE user_id = ?
    ");
    $summaryStmt->execute([$user['id']]);
    $summary = $summaryStmt->fetch(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $transactions = [];
    $summary = ['total_transactions' => 0, 'total_received' => 0, 'total_spent' => 0, 'pending_transactions' => 0];
    // You might want to log the error: error_log($e->getMessage());
}
?>
<?php
require_once "header.php";
?>

    <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-blue-900 mb-2">All Transactions</h1>
            <p class="text-gray-600">View and manage all your account transactions</p>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
                <p class="text-gray-600 text-sm mb-2">Total Transactions</p>
                <p class="text-2xl font-bold text-blue-900"><?php echo $summary['total_transactions']; ?></p>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
                <p class="text-gray-600 text-sm mb-2">Total Received</p>
                <p class="text-2xl font-bold text-green-600">₦<?php echo number_format($summary['total_received'], 2); ?></p>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-red-500">
                <p class="text-gray-600 text-sm mb-2">Total Spent</p>
                <p class="text-2xl font-bold text-red-600">₦<?php echo number_format($summary['total_spent'], 2); ?></p>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-orange-500">
                <p class="text-gray-600 text-sm mb-2">Pending</p>
                <p class="text-2xl font-bold text-orange-600"><?php echo $summary['pending_transactions']; ?></p>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-blue-900 text-white">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-semibold">Date</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold">Type</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold">Description</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold">Amount</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($transactions) > 0): ?>
                            <?php foreach ($transactions as $tx): ?>
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm text-gray-600"><?php echo date("M d, Y, g:i a", strtotime($tx['created_at'])); ?></td>
                                    <td class="px-6 py-4 text-sm">
                                        <?php
                                            $typeClass = 'bg-gray-100 text-gray-800';
                                            if (in_array($tx['type'], ['deposit', 'referral_earning'])) $typeClass = 'bg-blue-100 text-blue-800';
                                            if (in_array($tx['type'], ['purchase', 'withdrawal'])) $typeClass = 'bg-purple-100 text-purple-800';
                                        ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $typeClass; ?>">
                                            <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $tx['type']))); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600"><?php echo htmlspecialchars($tx['description']); ?></td>
                                    <td class="px-6 py-4 text-sm font-semibold <?php echo (in_array($tx['type'], ['deposit', 'referral_earning'])) ? 'text-green-600' : 'text-red-600'; ?>">
                                        <?php echo (in_array($tx['type'], ['deposit', 'referral_earning'])) ? '+' : '-'; ?>₦<?php echo number_format($tx['amount'], 2); ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <?php
                                            $statusClass = 'bg-gray-100 text-gray-800';
                                            if ($tx['status'] === 'completed') $statusClass = 'bg-green-100 text-green-800';
                                            if ($tx['status'] === 'pending') $statusClass = 'bg-yellow-100 text-yellow-800';
                                            if ($tx['status'] === 'failed' || $tx['status'] === 'cancelled') $statusClass = 'bg-red-100 text-red-800';
                                        ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $statusClass; ?>">
                                            <?php echo htmlspecialchars(ucfirst($tx['status'])); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr class="border-b border-gray-200">
                                <td colspan="5" class="px-4 py-8 text-center text-gray-500">No transactions yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <!-- Footer -->
    <footer class="bg-blue-900 text-white mt-16 py-8">
        <div class="max-w-7xl mx-auto px-4 text-center text-sm">
            <p>&copy; <?php echo date("Y"); ?> Acctverse. All rights reserved.</p>
        </div>
    </footer>
</main>
</body>
</html>
