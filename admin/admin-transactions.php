<?php
// filepath: c:\xampp\htdocs\acctverse\admin\admin-transactions.php
session_start();
require_once '../db/db.php';
require_once 'function/admin_transactions_function.php';

// Check if admin is logged in
// if (!isset($_SESSION['admin_id'])) { header('Location: admin-login.php'); exit; }

$pdo = get_pdo();
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$status = isset($_GET['status']) ? $_GET['status'] : '';
$gateway = isset($_GET['gateway']) ? $_GET['gateway'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'transactions';

// Handle status update
if (isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $payment_id = $_POST['payment_id'] ?? 0;
    $new_status = $_POST['status'] ?? '';
    if ($payment_id && $new_status) {
        update_transaction_status($pdo, $payment_id, $new_status);
        header('Location: admin-transactions.php?page=' . $page . '&status=' . urlencode($status) . '&gateway=' . urlencode($gateway) . '&search=' . urlencode($search) . '&tab=' . $tab);
        exit;
    }
}

// Handle refund actions
if (isset($_POST['action']) && $_POST['action'] === 'approve_refund') {
    $refund_id = $_POST['refund_id'] ?? 0;
    $notes = $_POST['notes'] ?? '';
    if ($refund_id) {
        approve_refund($pdo, $refund_id, $_SESSION['user_id'] ?? 0, $notes);
        header('Location: admin-transactions.php?tab=refunds&page=' . $page);
        exit;
    }
}

if (isset($_POST['action']) && $_POST['action'] === 'reject_refund') {
    $refund_id = $_POST['refund_id'] ?? 0;
    $notes = $_POST['notes'] ?? '';
    if ($refund_id) {
        reject_refund($pdo, $refund_id, $_SESSION['user_id'] ?? 0, $notes);
        header('Location: admin-transactions.php?tab=refunds&page=' . $page);
        exit;
    }
}

// Get data
$result = get_all_transactions($pdo, $page, 10, $status, $gateway, $search);
$stats = get_transaction_stats($pdo);
$gateway_stats = get_transactions_by_gateway($pdo);
$refunds = get_refunds($pdo, 1, 10, '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions - AcctGlobe Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <!-- Admin Navigation Header -->
    <nav class="bg-blue-900 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-gradient-to-br from-purple-600 to-orange-500 rounded-full"></div>
                    <span class="font-bold text-lg text-white">AcctGlobe Admin</span>
                </div>
                <div class="hidden md:flex items-center gap-8">
                    <a href="admin-dashboard.php" class="text-gray-300 hover:text-orange-500">Dashboard</a>
                    <a href="admin-users.php" class="text-gray-300 hover:text-orange-500">Users</a>
                    <a href="admin-orders.php" class="text-gray-300 hover:text-orange-500">Orders</a>
                    <a href="admin-transactions.php" class="text-orange-500 font-medium">Transactions</a>
                </div>
                <a href="admin-logout.php" class="bg-orange-500 text-white px-4 py-2 rounded font-medium hover:bg-orange-600">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-blue-900 mb-8">Transactions Management</h1>

        <!-- Transaction Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Transactions -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
                <p class="text-gray-600 text-sm mb-2">Total Transactions</p>
                <h3 class="text-3xl font-bold text-blue-900"><?php echo number_format($stats['total_transactions']); ?></h3>
                <p class="text-gray-500 text-xs mt-2">₦<?php echo number_format($stats['total_amount'], 2); ?></p>
            </div>

            <!-- Completed Transactions -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
                <p class="text-gray-600 text-sm mb-2">Completed</p>
                <h3 class="text-3xl font-bold text-green-600"><?php echo number_format($stats['completed_transactions']); ?></h3>
                <p class="text-gray-500 text-xs mt-2">₦<?php echo number_format($stats['completed_amount'], 2); ?></p>
            </div>

            <!-- Failed Transactions -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-red-500">
                <p class="text-gray-600 text-sm mb-2">Failed</p>
                <h3 class="text-3xl font-bold text-red-600"><?php echo number_format($stats['failed_transactions']); ?></h3>
                <p class="text-gray-500 text-xs mt-2">₦<?php echo number_format($stats['failed_amount'], 2); ?></p>
            </div>

            <!-- Success Rate -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-orange-500">
                <p class="text-gray-600 text-sm mb-2">Success Rate</p>
                <h3 class="text-3xl font-bold text-orange-600"><?php echo $stats['success_rate']; ?>%</h3>
                <p class="text-gray-500 text-xs mt-2">Avg: ₦<?php echo number_format($stats['avg_transaction'], 2); ?></p>
            </div>
        </div>

        <!-- Tabs -->
        <div class="flex gap-2 mb-8 overflow-x-auto">
            <a href="?tab=transactions" class="px-4 py-2 rounded font-medium <?php echo $tab === 'transactions' ? 'bg-orange-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'; ?>">All Transactions</a>
            <a href="?tab=by-gateway" class="px-4 py-2 rounded font-medium <?php echo $tab === 'by-gateway' ? 'bg-orange-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'; ?>">By Gateway</a>
            <a href="?tab=refunds" class="px-4 py-2 rounded font-medium <?php echo $tab === 'refunds' ? 'bg-orange-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'; ?>">Refunds</a>
        </div>

        <!-- Transactions Tab -->
        <?php if ($tab === 'transactions'): ?>
            <!-- Search and Filter -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <input type="hidden" name="tab" value="transactions">
                    <input type="text" name="search" placeholder="Search by transaction ID, reference..." value="<?php echo htmlspecialchars($search); ?>" class="border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                    <select name="status" class="border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="processing" <?php echo $status === 'processing' ? 'selected' : ''; ?>>Processing</option>
                        <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="failed" <?php echo $status === 'failed' ? 'selected' : ''; ?>>Failed</option>
                        <option value="refunded" <?php echo $status === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                    </select>
                    <select name="gateway" class="border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                        <option value="">All Gateways</option>
                        <option value="paystack" <?php echo $gateway === 'paystack' ? 'selected' : ''; ?>>Paystack</option>
                        <option value="flutterwave" <?php echo $gateway === 'flutterwave' ? 'selected' : ''; ?>>Flutterwave</option>
                        <option value="bank_transfer" <?php echo $gateway === 'bank_transfer' ? 'selected' : ''; ?>>Bank Transfer</option>
                    </select>
                    <button type="submit" class="bg-orange-500 text-white px-4 py-2 rounded font-medium hover:bg-orange-600">Filter</button>
                </form>
            </div>

            <!-- Transactions Table -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-blue-900 text-white">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold">Transaction ID</th>
                                <th class="px-4 py-3 text-left font-semibold">Customer</th>
                                <th class="px-4 py-3 text-left font-semibold">Amount</th>
                                <th class="px-4 py-3 text-left font-semibold">Gateway</th>
                                <th class="px-4 py-3 text-left font-semibold">Status</th>
                                <th class="px-4 py-3 text-left font-semibold">Date</th>
                                <th class="px-4 py-3 text-left font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($result['transactions'])): ?>
                                <?php foreach ($result['transactions'] as $transaction): ?>
                                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                                        <td class="px-4 py-3 font-semibold text-blue-600"><?php echo htmlspecialchars($transaction['transaction_id'] ?? 'N/A'); ?></td>
                                        <td class="px-4 py-3">
                                            <p class="font-semibold"><?php echo htmlspecialchars($transaction['username']); ?></p>
                                            <p class="text-gray-500 text-xs"><?php echo htmlspecialchars($transaction['email']); ?></p>
                                        </td>
                                        <td class="px-4 py-3 font-semibold">₦<?php echo number_format($transaction['amount'], 2); ?></td>
                                        <td class="px-4 py-3 text-sm"><?php echo htmlspecialchars($transaction['gateway'] ?? 'N/A'); ?></td>
                                        <td class="px-4 py-3">
                                            <form method="POST" class="inline">
                                                <input type="hidden" name="action" value="update_status">
                                                <input type="hidden" name="payment_id" value="<?php echo $transaction['id']; ?>">
                                                <select name="status" onchange="this.form.submit()" class="<?php 
                                                    $status_class = match($transaction['status']) {
                                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                                        'processing' => 'bg-blue-100 text-blue-800',
                                                        'completed' => 'bg-green-100 text-green-800',
                                                        'failed' => 'bg-red-100 text-red-800',
                                                        'refunded' => 'bg-gray-100 text-gray-800',
                                                        default => 'bg-gray-100 text-gray-800'
                                                    };
                                                    echo $status_class;
                                                ?> text-xs px-2 py-1 rounded border-0 cursor-pointer">
                                                    <option value="pending" <?php echo $transaction['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="processing" <?php echo $transaction['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                                    <option value="completed" <?php echo $transaction['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                    <option value="failed" <?php echo $transaction['status'] === 'failed' ? 'selected' : ''; ?>>Failed</option>
                                                    <option value="refunded" <?php echo $transaction['status'] === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                                                </select>
                                            </form>
                                        </td>
                                        <td class="px-4 py-3 text-gray-600"><?php echo date('Y-m-d H:i', strtotime($transaction['created_at'])); ?></td>
                                        <td class="px-4 py-3 text-sm">
                                            <a href="#" onclick="viewTransaction(<?php echo $transaction['id']; ?>)" class="text-blue-600 hover:text-blue-900">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">No transactions found</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="bg-gray-50 px-4 py-4 flex items-center justify-between border-t border-gray-200">
                    <span class="text-sm text-gray-600">Showing <?php echo ($result['page'] - 1) * $result['limit'] + 1; ?> to <?php echo min($result['page'] * $result['limit'], $result['total']); ?> of <?php echo $result['total']; ?> transactions</span>
                    <div class="flex gap-2">
                        <?php if ($result['page'] > 1): ?>
                            <a href="?page=<?php echo $result['page'] - 1; ?>&status=<?php echo urlencode($status); ?>&gateway=<?php echo urlencode($gateway); ?>&search=<?php echo urlencode($search); ?>&tab=transactions" class="px-3 py-2 border border-gray-300 rounded hover:bg-gray-100">Previous</a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $result['pages']; $i++): ?>
                            <a href="?page=<?php echo $i; ?>&status=<?php echo urlencode($status); ?>&gateway=<?php echo urlencode($gateway); ?>&search=<?php echo urlencode($search); ?>&tab=transactions" class="px-3 py-2 <?php echo $i === $result['page'] ? 'bg-orange-500 text-white' : 'border border-gray-300'; ?> rounded">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($result['page'] < $result['pages']): ?>
                            <a href="?page=<?php echo $result['page'] + 1; ?>&status=<?php echo urlencode($status); ?>&gateway=<?php echo urlencode($gateway); ?>&search=<?php echo urlencode($search); ?>&tab=transactions" class="px-3 py-2 border border-gray-300 rounded hover:bg-gray-100">Next</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- By Gateway Tab -->
        <?php if ($tab === 'by-gateway'): ?>
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-blue-900 text-white">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold">Gateway</th>
                                <th class="px-4 py-3 text-left font-semibold">Transactions</th>
                                <th class="px-4 py-3 text-left font-semibold">Total Amount</th>
                                <th class="px-4 py-3 text-left font-semibold">Completed</th>
                                <th class="px-4 py-3 text-left font-semibold">Failed</th>
                                <th class="px-4 py-3 text-left font-semibold">Avg Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($gateway_stats)): ?>
                                <?php foreach ($gateway_stats as $gw): ?>
                                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                                        <td class="px-4 py-3 font-semibold text-blue-600"><?php echo htmlspecialchars($gw['gateway'] ?? 'Unknown'); ?></td>
                                        <td class="px-4 py-3"><?php echo number_format($gw['transactions']); ?></td>
                                        <td class="px-4 py-3 font-semibold">₦<?php echo number_format($gw['total_amount'], 2); ?></td>
                                        <td class="px-4 py-3 text-green-600"><?php echo number_format($gw['completed']); ?></td>
                                        <td class="px-4 py-3 text-red-600"><?php echo number_format($gw['failed']); ?></td>
                                        <td class="px-4 py-3">₦<?php echo number_format($gw['avg_amount'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">No gateway data available</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- Refunds Tab -->
        <?php if ($tab === 'refunds'): ?>
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-blue-900 text-white">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold">Refund ID</th>
                                <th class="px-4 py-3 text-left font-semibold">Transaction</th>
                                <th class="px-4 py-3 text-left font-semibold">Amount</th>
                                <th class="px-4 py-3 text-left font-semibold">Reason</th>
                                <th class="px-4 py-3 text-left font-semibold">Status</th>
                                <th class="px-4 py-3 text-left font-semibold">Requested By</th>
                                <th class="px-4 py-3 text-left font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($refunds['refunds'])): ?>
                                <?php foreach ($refunds['refunds'] as $refund): ?>
                                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                                        <td class="px-4 py-3 font-semibold">#<?php echo $refund['id']; ?></td>
                                        <td class="px-4 py-3 text-blue-600"><?php echo htmlspecialchars($refund['transaction_id']); ?></td>
                                        <td class="px-4 py-3 font-semibold">₦<?php echo number_format($refund['amount'], 2); ?></td>
                                        <td class="px-4 py-3 text-sm"><?php echo htmlspecialchars($refund['reason']); ?></td>
                                        <td class="px-4 py-3">
                                            <span class="<?php 
                                                $status_class = match($refund['status']) {
                                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                                    'approved' => 'bg-green-100 text-green-800',
                                                    'rejected' => 'bg-red-100 text-red-800',
                                                    'processed' => 'bg-blue-100 text-blue-800',
                                                    default => 'bg-gray-100 text-gray-800'
                                                };
                                                echo $status_class;
                                            ?> text-xs px-2 py-1 rounded">
                                                <?php echo ucfirst($refund['status']); ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3"><?php echo htmlspecialchars($refund['username'] ?? 'N/A'); ?></td>
                                        <td class="px-4 py-3 text-sm">
                                            <?php if ($refund['status'] === 'pending'): ?>
                                                <form method="POST" class="inline">
                                                    <input type="hidden" name="action" value="approve_refund">
                                                    <input type="hidden" name="refund_id" value="<?php echo $refund['id']; ?>">
                                                    <textarea name="notes" placeholder="Approval notes" class="hidden"></textarea>
                                                    <button type="submit" class="text-green-600 hover:text-green-900 mr-2">Approve</button>
                                                </form>
                                                <form method="POST" class="inline">
                                                    <input type="hidden" name="action" value="reject_refund">
                                                    <input type="hidden" name="refund_id" value="<?php echo $refund['id']; ?>">
                                                    <textarea name="notes" placeholder="Rejection notes" class="hidden"></textarea>
                                                    <button type="submit" class="text-red-600 hover:text-red-900">Reject</button>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-gray-500">—</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">No refunds found</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal for Transaction Details -->
    <div id="transactionModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg max-w-2xl w-full mx-4 p-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-blue-900">Transaction Details</h2>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
            </div>
            <div id="modalContent" class="space-y-4">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>

    <script>
        function viewTransaction(id) {
            // Fetch and display transaction details
            alert('Transaction ID: ' + id + '\nDetailed view coming soon');
        }

        function closeModal() {
            document.getElementById('transactionModal').classList.add('hidden');
        }
    </script>
</body>
</html>
