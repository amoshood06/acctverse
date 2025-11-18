<?php
session_start();
require_once "../db/db.php"; // PDO connection

// --- ADMIN AUTH CHECK ---
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

// ================================
// FETCH DASHBOARD STATISTICS
// ================================

// TOTAL USERS
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

// TOTAL TRANSACTIONS
$totalTransactions = $pdo->query("SELECT COUNT(*) FROM transactions")->fetchColumn();

// TOTAL REVENUE
$totalRevenue = $pdo->query("SELECT SUM(amount) FROM transactions WHERE status='completed'")->fetchColumn();
$totalRevenue = $totalRevenue ? number_format($totalRevenue, 2) : "0.00";

// PENDING ORDERS
$pendingOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetchColumn();

// RECENT USER ACTIVITY (last 5 users)
$recentUsers = $pdo->query("
    SELECT full_name, activity, created_at 
    FROM user_activity 
    ORDER BY id DESC LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// RECENT TRANSACTIONS (last 5)
$recentTransactions = $pdo->query("
    SELECT users.full_name, transactions.amount, transactions.type, transactions.status, transactions.created_at 
    FROM transactions 
    JOIN users ON users.id = transactions.user_id 
    ORDER BY transactions.id DESC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - AcctGlobe</title>
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
                    <a href="admin-dashboard.php" class="text-orange-500 font-medium">Dashboard</a>
                    <a href="admin-users.php" class="text-gray-300 hover:text-orange-500">Users</a>
                    <a href="admin-transactions.php" class="text-gray-300 hover:text-orange-500">Transactions</a>
                    <a href="admin-orders.php" class="text-gray-300 hover:text-orange-500">Orders</a>
                    <a href="admin-reports.php" class="text-gray-300 hover:text-orange-500">Reports</a>
                    <a href="admin-settings.php" class="text-gray-300 hover:text-orange-500">Settings</a>
                </div>

                <a href="logout.php" class="bg-orange-500 text-white px-4 py-2 rounded font-medium hover:bg-orange-600">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-blue-900 mb-8">Admin Dashboard</h1>

        <!-- Key Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

            <!-- Total Users -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm mb-2">Total Users</p>
                        <h3 class="text-3xl font-bold text-blue-900"><?= $totalUsers ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center text-xl">👥</div>
                </div>
            </div>

            <!-- Total Transactions -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-orange-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm mb-2">Total Transactions</p>
                        <h3 class="text-3xl font-bold text-blue-900"><?= $totalTransactions ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center text-xl">💳</div>
                </div>
            </div>

            <!-- Total Revenue -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm mb-2">Total Revenue</p>
                        <h3 class="text-3xl font-bold text-blue-900">₦<?= $totalRevenue ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center text-xl">💰</div>
                </div>
            </div>

            <!-- Pending Orders -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm mb-2">Pending Orders</p>
                        <h3 class="text-3xl font-bold text-blue-900"><?= $pendingOrders ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center text-xl">⏳</div>
                </div>
            </div>

        </div>

        <!-- Recent Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">

            <!-- User Activity -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-bold text-blue-900 mb-4">Recent User Activity</h2>

                <?php if (count($recentUsers) > 0): ?>
                    <?php foreach ($recentUsers as $log): ?>
                        <div class="flex items-center justify-between py-2 border-b border-gray-200">
                            <div>
                                <p class="font-semibold text-gray-800"><?= htmlspecialchars($log['full_name']) ?></p>
                                <p class="text-xs text-gray-500"><?= htmlspecialchars($log['activity']) ?></p>
                            </div>
                            <p class="text-xs text-gray-500"><?= $log['created_at'] ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-gray-500 text-sm">No recent activity</p>
                <?php endif; ?>
            </div>

            <!-- System Status -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-bold text-blue-900 mb-4">System Status</h2>

                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-700">Server Status</span>
                        <span class="bg-green-100 text-green-800 text-xs px-3 py-1 rounded-full">Operational</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-gray-700">Database</span>
                        <span class="bg-green-100 text-green-800 text-xs px-3 py-1 rounded-full">Healthy</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-gray-700">API Gateway</span>
                        <span class="bg-green-100 text-green-800 text-xs px-3 py-1 rounded-full">Active</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-gray-700">Payment Gateway</span>
                        <span class="bg-green-100 text-green-800 text-xs px-3 py-1 rounded-full">Connected</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Recent Transactions -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-xl font-bold text-blue-900 mb-6">Recent Transactions</h2>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-blue-900 text-white">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold">User</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Amount</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Type</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Status</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Date</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (count($recentTransactions) > 0): ?>
                            <?php foreach ($recentTransactions as $t): ?>
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm"><?= htmlspecialchars($t['full_name']) ?></td>
                                    <td class="px-4 py-3 text-sm font-semibold">₦<?= number_format($t['amount'], 2) ?></td>
                                    <td class="px-4 py-3 text-sm"><?= $t['type'] ?></td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded">
                                            <?= ucfirst($t['status']) ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm"><?= $t['created_at'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">No transactions found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</body>
</html>
