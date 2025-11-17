<?php
// filepath: c:\xampp\htdocs\acctverse\admin\index.php
session_start();
require_once '../db/db.php';
require_once 'function/admin_dashboard_functions.php';

// Check if admin is logged in
// if (!isset($_SESSION['admin_id'])) { header('Location: login.php'); exit; }

$pdo = get_pdo();

// Get all dashboard data
$summary = get_dashboard_summary($pdo) ?? [];
$gateway_revenue = get_revenue_by_gateway($pdo) ?? [];
$daily_revenue = get_daily_revenue_chart($pdo, 7) ?? [];
$user_registrations = get_user_registration_chart($pdo, 7) ?? [];
$order_status = get_order_status_summary($pdo) ?? [];
$top_products = get_top_products($pdo, 5) ?? [];

// Extract data with fallbacks
$total_users = $summary['total_users'] ?? 0;
$users_growth = $summary['users_growth'] ?? ['growth' => 0, 'positive' => false];
$total_transactions = $summary['total_transactions'] ?? 0;
$transactions_growth = $summary['transactions_growth'] ?? ['growth' => 0, 'positive' => false];
$total_revenue = $summary['total_revenue'] ?? 0;
$revenue_growth = $summary['revenue_growth'] ?? ['growth' => 0, 'positive' => false];
$pending_orders = $summary['pending_orders'] ?? 0;
$recent_activity = $summary['recent_activity'] ?? [];
$system_status = $summary['system_status'] ?? [];
$recent_transactions = $summary['recent_transactions'] ?? [];

/**
 * Helper function to format time elapsed
 */
function time_elapsed($datetime) {
    if (empty($datetime)) {
        return 'N/A';
    }
    
    try {
        $time_ago = strtotime($datetime);
        if (!$time_ago) {
            return 'N/A';
        }
        
        $current_time = time();
        $time_difference = $current_time - $time_ago;
        $seconds = $time_difference;
        $minutes = round($seconds / 60);
        $hours = round($seconds / 3600);
        $days = round($seconds / 86400);

        if ($seconds < 60) {
            return "just now";
        } else if ($minutes < 60) {
            return "$minutes mins ago";
        } else if ($hours < 24) {
            return "$hours hours ago";
        } else {
            return "$days days ago";
        }
    } catch (Exception $e) {
        return 'N/A';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - AcctGlobe</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-50">
    <!-- Admin Navigation Header -->
    <nav class="bg-blue-900 shadow-lg sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-gradient-to-br from-purple-600 to-orange-500 rounded-full"></div>
                    <span class="font-bold text-lg text-white">AcctGlobe Admin</span>
                </div>
                <div class="hidden md:flex items-center gap-8">
                    <a href="index.php" class="text-orange-500 font-medium">Dashboard</a>
                    <a href="admin-users.php" class="text-gray-300 hover:text-orange-500 transition">Users</a>
                    <a href="admin-transactions.php" class="text-gray-300 hover:text-orange-500 transition">Transactions</a>
                    <a href="admin-orders.php" class="text-gray-300 hover:text-orange-500 transition">Orders</a>
                    <a href="admin-reports.php" class="text-gray-300 hover:text-orange-500 transition">Reports</a>
                    <a href="admin-settings.php" class="text-gray-300 hover:text-orange-500 transition">Settings</a>
                </div>
                <button onclick="logout()" class="bg-orange-500 text-white px-4 py-2 rounded font-medium hover:bg-orange-600 transition">Logout</button>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <h1 class="text-3xl font-bold text-blue-900">Admin Dashboard</h1>
            <p class="text-gray-600 text-sm md:text-base">Last updated: <?php echo date('M d, Y H:i:s'); ?></p>
        </div>

        <!-- Key Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Users -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500 hover:shadow-lg transition">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-gray-600 text-sm mb-2">Total Users</p>
                        <h3 class="text-3xl font-bold text-blue-900"><?php echo number_format($total_users); ?></h3>
                        <p class="text-<?php echo ($users_growth['positive'] ?? false) ? 'green' : 'red'; ?>-500 text-xs mt-2">
                            <?php echo ($users_growth['positive'] ?? false) ? '↑' : '↓'; ?> <?php echo abs($users_growth['growth'] ?? 0); ?>% from last month
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center text-xl flex-shrink-0">👥</div>
                </div>
            </div>

            <!-- Total Transactions -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-orange-500 hover:shadow-lg transition">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-gray-600 text-sm mb-2">Total Transactions</p>
                        <h3 class="text-3xl font-bold text-blue-900"><?php echo number_format($total_transactions); ?></h3>
                        <p class="text-<?php echo ($transactions_growth['positive'] ?? false) ? 'green' : 'red'; ?>-500 text-xs mt-2">
                            <?php echo ($transactions_growth['positive'] ?? false) ? '↑' : '↓'; ?> <?php echo abs($transactions_growth['growth'] ?? 0); ?>% from last month
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center text-xl flex-shrink-0">💳</div>
                </div>
            </div>

            <!-- Total Revenue -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500 hover:shadow-lg transition">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-gray-600 text-sm mb-2">Total Revenue</p>
                        <h3 class="text-3xl font-bold text-blue-900">₦<?php echo number_format($total_revenue, 2); ?></h3>
                        <p class="text-<?php echo ($revenue_growth['positive'] ?? false) ? 'green' : 'red'; ?>-500 text-xs mt-2">
                            <?php echo ($revenue_growth['positive'] ?? false) ? '↑' : '↓'; ?> <?php echo abs($revenue_growth['growth'] ?? 0); ?>% from last month
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center text-xl flex-shrink-0">💰</div>
                </div>
            </div>

            <!-- Pending Orders -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-yellow-500 hover:shadow-lg transition">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-gray-600 text-sm mb-2">Pending Orders</p>
                        <h3 class="text-3xl font-bold text-blue-900"><?php echo number_format($pending_orders); ?></h3>
                        <p class="text-yellow-500 text-xs mt-2">⚠️ Needs attention</p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center text-xl flex-shrink-0">⏳</div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Revenue Chart -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-bold text-blue-900 mb-4">Revenue (Last 7 Days)</h2>
                <div class="relative h-80">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <!-- User Registration Chart -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-bold text-blue-900 mb-4">New Users (Last 7 Days)</h2>
                <div class="relative h-80">
                    <canvas id="usersChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Quick Stats Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Recent Activity -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-bold text-blue-900 mb-4">Recent User Activity</h2>
                <div class="space-y-3">
                    <?php if (!empty($recent_activity) && is_array($recent_activity)): ?>
                        <?php foreach ($recent_activity as $activity): ?>
                            <div class="flex items-center justify-between py-2 border-b border-gray-200 last:border-b-0">
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-gray-800 truncate"><?php echo htmlspecialchars($activity['name'] ?? 'Unknown'); ?></p>
                                    <p class="text-xs text-gray-500 truncate"><?php echo htmlspecialchars($activity['activity'] ?? 'No activity'); ?></p>
                                </div>
                                <p class="text-xs text-gray-500 ml-2 flex-shrink-0"><?php echo time_elapsed($activity['last_activity'] ?? date('Y-m-d H:i:s')); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-gray-500 text-sm text-center py-8">No recent activity</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- System Status -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-bold text-blue-900 mb-4">System Status</h2>
                <div class="space-y-4">
                    <?php
                    $status_mapping = [
                        'server' => 'Server Status',
                        'database' => 'Database',
                        'api' => 'API Gateway',
                        'payment_gateway' => 'Payment Gateway',
                        'sms_service' => 'SMS Service'
                    ];
                    
                    foreach ($status_mapping as $key => $label):
                        $value = $system_status[$key] ?? 'Unknown';
                        $badge_color = match($value) {
                            'Operational', 'Healthy', 'Active', 'Connected' => 'bg-green-100 text-green-800',
                            'Limited' => 'bg-yellow-100 text-yellow-800',
                            default => 'bg-red-100 text-red-800'
                        };
                    ?>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-700 text-sm"><?php echo htmlspecialchars($label); ?></span>
                            <span class="<?php echo $badge_color; ?> text-xs px-3 py-1 rounded-full font-semibold"><?php echo htmlspecialchars($value); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Revenue by Gateway -->
        <?php if (!empty($gateway_revenue) && is_array($gateway_revenue)): ?>
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <h2 class="text-xl font-bold text-blue-900 mb-4">Revenue by Payment Gateway</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <?php foreach ($gateway_revenue as $gateway): ?>
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 hover:border-orange-300 transition">
                        <p class="font-semibold text-gray-800 mb-2 text-sm"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $gateway['gateway'] ?? 'Unknown'))); ?></p>
                        <p class="text-lg font-bold text-blue-600 mb-1">₦<?php echo number_format($gateway['total_amount'] ?? 0, 2); ?></p>
                        <p class="text-xs text-gray-600"><?php echo $gateway['transaction_count'] ?? 0; ?> transactions</p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Recent Transactions -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-xl font-bold text-blue-900 mb-6">Recent Transactions</h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-blue-900 text-white">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold">User</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Amount</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Gateway</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Status</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recent_transactions) && is_array($recent_transactions)): ?>
                            <?php foreach ($recent_transactions as $txn): ?>
                                <tr class="border-b border-gray-200 hover:bg-gray-50 transition">
                                    <td class="px-4 py-3 text-sm">
                                        <div>
                                            <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($txn['user_name'] ?? 'Unknown'); ?></p>
                                            <p class="text-xs text-gray-500"><?php echo htmlspecialchars($txn['email'] ?? 'N/A'); ?></p>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm font-semibold">₦<?php echo number_format($txn['amount'] ?? 0, 2); ?></td>
                                    <td class="px-4 py-3 text-sm"><?php echo htmlspecialchars(ucfirst($txn['type'] ?? 'N/A')); ?></td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="<?php 
                                            $status = $txn['status'] ?? 'pending';
                                            echo match($status) {
                                                'success' => 'bg-green-100 text-green-800',
                                                'pending' => 'bg-yellow-100 text-yellow-800',
                                                'failed' => 'bg-red-100 text-red-800',
                                                default => 'bg-gray-100 text-gray-800'
                                            };
                                        ?> text-xs px-2 py-1 rounded font-semibold">
                                            <?php echo htmlspecialchars(ucfirst($status)); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">
                                        <?php 
                                            $date = $txn['created_at'] ?? null;
                                            echo $date ? date('M d, Y H:i', strtotime($date)) : 'N/A';
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-500">No recent transactions</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Chart.js Scripts -->
    <script>
        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart');
        if (revenueCtx) {
            const revenueChart = new Chart(revenueCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: [
                        <?php 
                        if (!empty($daily_revenue) && is_array($daily_revenue)) {
                            foreach ($daily_revenue as $day) {
                                echo "'" . htmlspecialchars(date('M d', strtotime($day['date'] ?? date('Y-m-d')))) . "',";
                            }
                        } else {
                            echo "'No data'";
                        }
                        ?>
                    ],
                    datasets: [{
                        label: 'Revenue (₦)',
                        data: [
                            <?php 
                            if (!empty($daily_revenue) && is_array($daily_revenue)) {
                                foreach ($daily_revenue as $day) {
                                    echo (float)($day['revenue'] ?? 0) . ",";
                                }
                            } else {
                                echo "0";
                            }
                            ?>
                        ],
                        borderColor: '#059669',
                        backgroundColor: 'rgba(5, 150, 105, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointRadius: 5,
                        pointBackgroundColor: '#059669'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: true },
                        filler: { propagate: true }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₦' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }

        // User Registration Chart
        const usersCtx = document.getElementById('usersChart');
        if (usersCtx) {
            const usersChart = new Chart(usersCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: [
                        <?php 
                        if (!empty($user_registrations) && is_array($user_registrations)) {
                            foreach ($user_registrations as $day) {
                                echo "'" . htmlspecialchars(date('M d', strtotime($day['date'] ?? date('Y-m-d')))) . "',";
                            }
                        } else {
                            echo "'No data'";
                        }
                        ?>
                    ],
                    datasets: [{
                        label: 'New Users',
                        data: [
                            <?php 
                            if (!empty($user_registrations) && is_array($user_registrations)) {
                                foreach ($user_registrations as $day) {
                                    echo (int)($day['new_users'] ?? 0) . ",";
                                }
                            } else {
                                echo "0";
                            }
                            ?>
                        ],
                        backgroundColor: '#3b82f6',
                        borderColor: '#1e40af',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: true }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }

        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'logout.php';
            }
        }
    </script>
</body>
</html>
