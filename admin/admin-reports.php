<?php
// filepath: c:\xampp\htdocs\acctverse\admin\admin-reports.php
session_start();
require_once '../db/db.php';
require_once 'function/admin_reports_function.php';

// Check if admin is logged in
// if (!isset($_SESSION['admin_id'])) { header('Location: admin-login.php'); exit; }

$pdo = get_pdo();

// Default date range (last 30 days)
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$report_type = isset($_GET['report_type']) ? $_GET['report_type'] : 'sales';

// Get all report data
$financial_summary = get_financial_summary($pdo, $start_date, $end_date);
$sales_report = get_sales_report($pdo, $start_date, $end_date);
$revenue_by_gateway = get_revenue_by_gateway_report($pdo, $start_date, $end_date);
$top_products = get_top_products_report($pdo, $start_date, $end_date, 10);
$customer_segmentation = get_customer_segmentation_report($pdo);
$transaction_report = get_transaction_report($pdo, $start_date, $end_date);
$order_status_report = get_order_status_report($pdo, $start_date, $end_date);
$user_growth = get_user_growth_report($pdo, $start_date, $end_date);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - AcctGlobe Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                    <a href="admin-reports.php" class="text-orange-500 font-medium">Reports</a>
                </div>
                <a href="admin-logout.php" class="bg-orange-500 text-white px-4 py-2 rounded font-medium hover:bg-orange-600">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-blue-900 mb-8">Reports & Analytics</h1>

        <!-- Date Range Filter -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Start Date</label>
                    <input type="date" name="start_date" value="<?php echo $start_date; ?>" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">End Date</label>
                    <input type="date" name="end_date" value="<?php echo $end_date; ?>" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Report Type</label>
                    <select name="report_type" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                        <option value="sales" <?php echo $report_type === 'sales' ? 'selected' : ''; ?>>Sales Report</option>
                        <option value="transactions" <?php echo $report_type === 'transactions' ? 'selected' : ''; ?>>Transaction Report</option>
                        <option value="users" <?php echo $report_type === 'users' ? 'selected' : ''; ?>>User Report</option>
                        <option value="products" <?php echo $report_type === 'products' ? 'selected' : ''; ?>>Product Report</option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="bg-orange-500 text-white px-6 py-2 rounded font-medium hover:bg-orange-600 w-full">Generate Report</button>
                </div>
            </form>
        </div>

        <!-- Financial Summary -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Revenue -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
                <p class="text-gray-600 text-sm mb-2">Total Revenue</p>
                <h3 class="text-2xl md:text-3xl font-bold text-green-600">₦<?php echo number_format($financial_summary['total_revenue'], 2); ?></h3>
                <p class="text-xs text-gray-500 mt-2">From <?php echo $start_date; ?> to <?php echo $end_date; ?></p>
            </div>

            <!-- Total Orders -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
                <p class="text-gray-600 text-sm mb-2">Total Orders</p>
                <h3 class="text-2xl md:text-3xl font-bold text-blue-600"><?php echo number_format($financial_summary['total_orders']); ?></h3>
                <p class="text-xs text-gray-500 mt-2">Avg: ₦<?php echo number_format($financial_summary['avg_order_value'], 2); ?></p>
            </div>

            <!-- Total Transactions -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-purple-500">
                <p class="text-gray-600 text-sm mb-2">Total Transactions</p>
                <h3 class="text-2xl md:text-3xl font-bold text-purple-600"><?php echo number_format($financial_summary['total_transactions']); ?></h3>
                <p class="text-xs text-gray-500 mt-2">Amount: ₦<?php echo number_format($financial_summary['transaction_amount'], 2); ?></p>
            </div>

            <!-- New Customers -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-orange-500">
                <p class="text-gray-600 text-sm mb-2">New Customers</p>
                <h3 class="text-2xl md:text-3xl font-bold text-orange-600"><?php echo number_format($financial_summary['new_customers']); ?></h3>
                <p class="text-xs text-gray-500 mt-2">Registered users</p>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Sales Trend Chart -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-bold text-blue-900 mb-4">Sales Trend</h2>
                <canvas id="salesChart"></canvas>
            </div>

            <!-- Revenue by Gateway -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-bold text-blue-900 mb-4">Revenue by Payment Gateway</h2>
                <canvas id="gatewayChart"></canvas>
            </div>
        </div>

        <!-- Additional Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Order Status Distribution -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-bold text-blue-900 mb-4">Order Status Distribution</h2>
                <canvas id="orderStatusChart"></canvas>
            </div>

            <!-- Customer Segmentation -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-bold text-blue-900 mb-4">Customer Segmentation</h2>
                <canvas id="segmentationChart"></canvas>
            </div>
        </div>

        <!-- Top Products Table -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <h2 class="text-xl font-bold text-blue-900 mb-6">Top 10 Products</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-blue-900 text-white">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Product</th>
                            <th class="px-4 py-3 text-left font-semibold">Quantity Sold</th>
                            <th class="px-4 py-3 text-left font-semibold">Revenue</th>
                            <th class="px-4 py-3 text-left font-semibold">Orders</th>
                            <th class="px-4 py-3 text-left font-semibold">Avg Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($top_products)): ?>
                            <?php foreach ($top_products as $product): ?>
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="px-4 py-3 font-semibold"><?php echo htmlspecialchars($product['product_name']); ?></td>
                                    <td class="px-4 py-3"><?php echo number_format($product['total_quantity']); ?></td>
                                    <td class="px-4 py-3 font-semibold">₦<?php echo number_format($product['total_revenue'], 2); ?></td>
                                    <td class="px-4 py-3"><?php echo number_format($product['orders_count']); ?></td>
                                    <td class="px-4 py-3">₦<?php echo number_format($product['avg_price'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">No product data available</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Revenue by Gateway Table -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-xl font-bold text-blue-900 mb-6">Revenue by Payment Gateway</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-blue-900 text-white">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Gateway</th>
                            <th class="px-4 py-3 text-left font-semibold">Transactions</th>
                            <th class="px-4 py-3 text-left font-semibold">Total Revenue</th>
                            <th class="px-4 py-3 text-left font-semibold">Completed</th>
                            <th class="px-4 py-3 text-left font-semibold">Failed</th>
                            <th class="px-4 py-3 text-left font-semibold">Avg Transaction</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($revenue_by_gateway)): ?>
                            <?php foreach ($revenue_by_gateway as $gateway): ?>
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="px-4 py-3 font-semibold"><?php echo htmlspecialchars($gateway['gateway']); ?></td>
                                    <td class="px-4 py-3"><?php echo number_format($gateway['transactions']); ?></td>
                                    <td class="px-4 py-3 font-semibold">₦<?php echo number_format($gateway['total_revenue'], 2); ?></td>
                                    <td class="px-4 py-3">₦<?php echo number_format($gateway['completed_revenue'], 2); ?></td>
                                    <td class="px-4 py-3 text-red-600">₦<?php echo number_format($gateway['failed_amount'], 2); ?></td>
                                    <td class="px-4 py-3">₦<?php echo number_format($gateway['avg_transaction'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">No gateway data available</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Chart.js Scripts -->
    <script>
        // Sales Trend Chart
        const salesData = <?php echo json_encode($sales_report ?? []); ?>;
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: salesData.map(d => d.date),
                datasets: [{
                    label: 'Revenue (₦)',
                    data: salesData.map(d => d.total_revenue || 0),
                    borderColor: '#f97316',
                    backgroundColor: 'rgba(249, 115, 22, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: true } },
                scales: { y: { beginAtZero: true } }
            }
        });

        // Revenue by Gateway Chart
        const gatewayData = <?php echo json_encode($revenue_by_gateway ?? []); ?>;
        const gatewayCtx = document.getElementById('gatewayChart').getContext('2d');
        new Chart(gatewayCtx, {
            type: 'doughnut',
            data: {
                labels: gatewayData.map(g => g.gateway),
                datasets: [{
                    data: gatewayData.map(g => g.total_revenue || 0),
                    backgroundColor: ['#3b82f6', '#f97316', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899']
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } }
            }
        });

        // Order Status Chart
        const orderStatusData = <?php echo json_encode($order_status_report ?? []); ?>;
        const orderStatusCtx = document.getElementById('orderStatusChart').getContext('2d');
        new Chart(orderStatusCtx, {
            type: 'bar',
            data: {
                labels: orderStatusData.map(o => o.status),
                datasets: [{
                    label: 'Order Count',
                    data: orderStatusData.map(o => o.order_count || 0),
                    backgroundColor: ['#fbbf24', '#60a5fa', '#34d399', '#ef4444', '#a78bfa']
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: true } },
                scales: { y: { beginAtZero: true } }
            }
        });

        // Customer Segmentation Chart
        const segmentationData = <?php echo json_encode($customer_segmentation ?? []); ?>;
        const segmentationCtx = document.getElementById('segmentationChart').getContext('2d');
        new Chart(segmentationCtx, {
            type: 'pie',
            data: {
                labels: segmentationData.map(s => s.segment),
                datasets: [{
                    data: segmentationData.map(s => s.customer_count || 0),
                    backgroundColor: ['#ef4444', '#f97316', '#eab308', '#84cc16', '#22c55e']
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    </script>
</body>
</html>
