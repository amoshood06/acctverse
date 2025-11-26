<?php
session_start();
require_once "../db/db.php";
require_once "../flash.php";

// ==================================================
//  ADMIN AUTH CHECK
// ==================================================

// If no user is logged in OR user is not admin → kick out
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    set_flash("error", "Unauthorized access.");
    header("Location: ../login.php");
    exit;
}

// Get logged-in admin data
$admin = $_SESSION['user']; 
$flash = get_flash();

// ==================================================
//  DASHBOARD QUERIES (SAFE EXECUTION)
// ==================================================

try {

    // 1. Total Users
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $totalUsers = $stmt->fetchColumn();

    // 2. Total Transactions
    $stmt = $pdo->query("SELECT COUNT(*) FROM transactions");
    $totalTransactions = $stmt->fetchColumn();

    // 3. Total Revenue (completed transactions)
    $stmt = $pdo->query("SELECT SUM(amount) FROM transactions WHERE status = 'completed'");
    $totalRevenue = $stmt->fetchColumn() ?? 0;

    // 4. Pending Orders
    $stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
    $pendingOrders = $stmt->fetchColumn();

    // 5. Recent Transactions
    $stmt = $pdo->query("
        SELECT t.*, u.first_name, u.last_name 
        FROM transactions t
        JOIN users u ON u.id = t.user_id
        ORDER BY t.id DESC
        LIMIT 6
    ");
    $recentTransactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 6. Recent Users
    $stmt = $pdo->query("SELECT * FROM users ORDER BY id DESC LIMIT 6");
    $recentUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    set_flash("error", "Error loading dashboard data.");
    $totalUsers = $totalTransactions = $totalRevenue = $pendingOrders = 0;
    $recentTransactions = $recentUsers = [];
}

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

    <!-- Admin Navigation -->
    <nav class="bg-blue-900 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-gradient-to-br from-purple-600 to-orange-500 rounded-full"></div>
                <span class="text-white font-bold text-lg">AcctGlobe Admin</span>
            </div>

            <div class="hidden md:flex items-center gap-8">
            <!-- Desktop Nav -->
            <div class="hidden md:flex items-center gap-6">
                <a href="index.php" class="text-orange-500 font-medium">Dashboard</a>
                <a href="admin-users.php" class="text-gray-300 hover:text-orange-500">Users</a>
                <a href="admin-transactions.php" class="text-gray-300 hover:text-orange-500">Transactions</a>
                 <a href="manage-products.php" class="text-gray-300 hover:text-orange-500">Add product</a>
                <a href="admin-orders.php" class="text-gray-300 hover:text-orange-500">Orders</a>
                <a href="admin-reports.php" class="text-gray-300 hover:text-orange-500">Reports</a>
                <a href="admin-settings.php" class="text-gray-300 hover:text-orange-500">Settings</a>
            </div>

            <a href="logout.php" class="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600">Logout</a>
            <!-- Mobile Nav Toggle -->
            <div class="md:hidden">
                <button id="mobile-menu-button" class="text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
                </button>
            </div>
        </div>
        <!-- Mobile Nav -->
        <div id="mobile-menu" class="md:hidden hidden px-2 pt-2 pb-3 space-y-1 sm:px-3">
            <a href="index.php" class="text-orange-500 block px-3 py-2 rounded-md text-base font-medium">Dashboard</a>
            <a href="admin-users.php" class="text-gray-300 hover:text-white hover:bg-blue-800 block px-3 py-2 rounded-md text-base font-medium">Users</a>
            <a href="admin-orders.php" class="text-gray-300 hover:text-white hover:bg-blue-800 block px-3 py-2 rounded-md text-base font-medium">Orders</a>
            <a href="manage-products.php" class="text-gray-300 hover:text-white hover:bg-blue-800 block px-3 py-2 rounded-md text-base font-medium">Products</a>
            <a href="add-slider.php" class="text-gray-300 hover:text-white hover:bg-blue-800 block px-3 py-2 rounded-md text-base font-medium">Slider</a>
            <a href="add-about-us.php" class="text-gray-300 hover:text-white hover:bg-blue-800 block px-3 py-2 rounded-md text-base font-medium">About Us</a>
            <a href="add-faq.php" class="text-gray-300 hover:text-white hover:bg-blue-800 block px-3 py-2 rounded-md text-base font-medium">FAQs</a>
            <a href="add-privacy.php" class="text-gray-300 hover:text-white hover:bg-blue-800 block px-3 py-2 rounded-md text-base font-medium">Privacy Policy</a>
            <a href="add-terms.php" class="text-gray-300 hover:text-white hover:bg-blue-800 block px-3 py-2 rounded-md text-base font-medium">Terms</a>
            <a href="add-cookie-policy.php" class="text-gray-300 hover:text-white hover:bg-blue-800 block px-3 py-2 rounded-md text-base font-medium">Cookie Policy</a>
            <a href="site-settings.php" class="text-gray-300 hover:text-white hover:bg-blue-800 block px-3 py-2 rounded-md text-base font-medium">Settings</a>
            <a href="../logout.php" class="text-gray-300 hover:text-white hover:bg-blue-800 block px-3 py-2 rounded-md text-base font-medium">Logout</a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 py-8">

        <h1 class="text-3xl font-bold text-blue-900 mb-8">Admin Dashboard</h1>

        <!-- METRICS -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

            <!-- Total Users -->
            <div class="bg-white shadow-sm border-l-4 border-blue-500 rounded-lg p-6">
                <p class="text-gray-600">Total Users</p>
                <h3 class="text-3xl text-blue-900 font-bold"><?= number_format($totalUsers) ?></h3>
            </div>

            <!-- Total Transactions -->
            <div class="bg-white shadow-sm border-l-4 border-orange-500 rounded-lg p-6">
                <p class="text-gray-600">Total Transactions</p>
                <h3 class="text-3xl text-blue-900 font-bold"><?= number_format($totalTransactions) ?></h3>
            </div>

            <!-- Total Revenue -->
            <div class="bg-white shadow-sm border-l-4 border-green-500 rounded-lg p-6">
                <p class="text-gray-600">Total Revenue</p>
                <h3 class="text-3xl text-blue-900 font-bold">₦<?= number_format($totalRevenue, 2) ?></h3>
            </div>

            <!-- Pending Orders -->
            <div class="bg-white shadow-sm border-l-4 border-yellow-500 rounded-lg p-6">
                <p class="text-gray-600">Pending Orders</p>
                <h3 class="text-3xl text-blue-900 font-bold"><?= number_format($pendingOrders) ?></h3>
            </div>

        </div>

        <!-- Recent Users & System Status -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">

            <!-- Recent Users -->
            <div class="bg-white p-6 rounded-lg shadow-sm">
                <h2 class="text-xl font-bold text-blue-900 mb-4">Recent Users</h2>

                <?php foreach ($recentUsers as $u): ?>
                    <div class="flex justify-between py-2 border-b border-gray-200">
                        <div>
                            <p class="font-semibold"><?= htmlspecialchars($u['first_name'] . " " . $u['last_name']) ?></p>
                            <p class="text-xs text-gray-500">New user registered</p>
                        </div>
                        <span class="text-xs text-gray-500">ID: <?= $u['id'] ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- System Status -->
            <div class="bg-white p-6 rounded-lg shadow-sm">
                <h2 class="text-xl font-bold text-blue-900 mb-4">System Status</h2>

                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span>Server Status</span>
                        <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs">Operational</span>
                    </div>

                    <div class="flex justify-between">
                        <span>Database</span>
                        <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs">Healthy</span>
                    </div>

                    <div class="flex justify-between">
                        <span>API Gateway</span>
                        <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs">Active</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="bg-white p-6 rounded-lg shadow-sm">
            <h2 class="text-xl font-bold text-blue-900 mb-6">Recent Transactions</h2>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-blue-900 text-white">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm">User</th>
                            <th class="px-4 py-3 text-left text-sm">Amount</th>
                            <th class="px-4 py-3 text-left text-sm">Type</th>
                            <th class="px-4 py-3 text-left text-sm">Status</th>
                            <th class="px-4 py-3 text-left text-sm">Date</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (empty($recentTransactions)): ?>
                            <tr><td colspan="5" class="text-center py-6 text-gray-500">No transactions found</td></tr>
                        <?php else: ?>
                            <?php foreach ($recentTransactions as $t): ?>
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="px-4 py-3"><?= htmlspecialchars($t['first_name'] . ' ' . $t['last_name']) ?></td>
                                    <td class="px-4 py-3 font-semibold">₦<?= number_format($t['amount'], 2) ?></td>
                                    <td class="px-4 py-3"><?= htmlspecialchars($t['type']) ?></td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-1 rounded text-xs
                                            <?= $t['status'] === 'completed' ? 'bg-green-100 text-green-800' : 
                                               ($t['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                               'bg-red-100 text-red-800') ?>">
                                            <?= ucfirst($t['status']) ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3"><?= $t['created_at'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>

                </table>
            </div>
        </div>

    </div>

    <script>
        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function () {
            const btn = document.getElementById('mobile-menu-button');
            const menu = document.getElementById('mobile-menu');
            btn.addEventListener('click', () => {
                menu.classList.toggle('hidden');
            });
        });
    </script>
    <!-- AlpineJS for dropdown -->
    <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></script>

</body>
</html>
