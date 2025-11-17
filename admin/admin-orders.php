<?php
// filepath: c:\xampp\htdocs\acctverse\admin\admin-orders.php
session_start();
require_once '../db/db.php';
require_once 'function/admin_orders_function.php';

// Check if admin is logged in
// if (!isset($_SESSION['admin_id'])) { header('Location: admin-login.php'); exit; }

$pdo = get_pdo();
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$status = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Handle status update
if (isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $order_id = $_POST['order_id'] ?? 0;
    $new_status = $_POST['status'] ?? '';
    if ($order_id && $new_status) {
        update_order_status($pdo, $order_id, $new_status);
        header('Location: admin-orders.php?page=' . $page . '&status=' . urlencode($status) . '&search=' . urlencode($search));
        exit;
    }
}

$result = get_all_orders($pdo, $page, 10, $status, '', $search);
$stats = get_orders_stats($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Management - AcctGlobe Admin</title>
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
                    <a href="admin-products.php" class="text-gray-300 hover:text-orange-500">Products</a>
                    <a href="admin-orders.php" class="text-orange-500 font-medium">Orders</a>
                    <a href="admin-transactions.php" class="text-gray-300 hover:text-orange-500">Transactions</a>
                </div>
                <a href="admin-logout.php" class="bg-orange-500 text-white px-4 py-2 rounded font-medium hover:bg-orange-600">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-bold text-blue-900">Orders Management</h1>
            <a href="admin-add-order.php" class="bg-orange-500 text-white px-6 py-2 rounded font-medium hover:bg-orange-600">+ Create Order</a>
        </div>

        <!-- Orders Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Orders -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm mb-2">Total Orders</p>
                        <h3 class="text-3xl font-bold text-blue-900"><?php echo number_format($stats['total_orders']); ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center text-xl">📦</div>
                </div>
            </div>

            <!-- Pending Orders -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm mb-2">Pending Orders</p>
                        <h3 class="text-3xl font-bold text-yellow-600"><?php echo number_format($stats['pending_orders']); ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center text-xl">⏳</div>
                </div>
            </div>

            <!-- Processing Orders -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm mb-2">Processing</p>
                        <h3 class="text-3xl font-bold text-purple-600"><?php echo number_format($stats['processing_orders']); ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center text-xl">⚙️</div>
                </div>
            </div>

            <!-- Total Revenue -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm mb-2">Total Revenue</p>
                        <h3 class="text-3xl font-bold text-green-600">₦<?php echo number_format($stats['total_revenue'], 2); ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center text-xl">💰</div>
                </div>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <input type="text" name="search" placeholder="Search by order number or customer..." value="<?php echo htmlspecialchars($search); ?>" class="border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                <select name="status" class="border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                    <option value="">All Status</option>
                    <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="processing" <?php echo $status === 'processing' ? 'selected' : ''; ?>>Processing</option>
                    <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    <option value="refunded" <?php echo $status === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                </select>
                <button type="submit" class="bg-orange-500 text-white px-4 py-2 rounded font-medium hover:bg-orange-600">Search</button>
            </form>
        </div>

        <!-- Orders Table -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-blue-900 text-white">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Order #</th>
                            <th class="px-4 py-3 text-left font-semibold">Customer</th>
                            <th class="px-4 py-3 text-left font-semibold">Items</th>
                            <th class="px-4 py-3 text-left font-semibold">Amount</th>
                            <th class="px-4 py-3 text-left font-semibold">Status</th>
                            <th class="px-4 py-3 text-left font-semibold">Date</th>
                            <th class="px-4 py-3 text-left font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($result['orders'])): ?>
                            <?php foreach ($result['orders'] as $order): ?>
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="px-4 py-3 font-semibold text-blue-600"><?php echo htmlspecialchars($order['order_number']); ?></td>
                                    <td class="px-4 py-3">
                                        <p class="font-semibold"><?php echo htmlspecialchars($order['username']); ?></p>
                                        <p class="text-gray-500 text-xs"><?php echo htmlspecialchars($order['email']); ?></p>
                                    </td>
                                    <td class="px-4 py-3 text-center"><?php echo $order['item_count']; ?></td>
                                    <td class="px-4 py-3 font-semibold">₦<?php echo number_format($order['total_amount'], 2); ?></td>
                                    <td class="px-4 py-3">
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            <select name="status" onchange="this.form.submit()" class="<?php 
                                                $status_class = match($order['status']) {
                                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                                    'processing' => 'bg-blue-100 text-blue-800',
                                                    'completed' => 'bg-green-100 text-green-800',
                                                    'cancelled' => 'bg-red-100 text-red-800',
                                                    'refunded' => 'bg-gray-100 text-gray-800',
                                                    default => 'bg-gray-100 text-gray-800'
                                                };
                                                echo $status_class;
                                            ?> text-xs px-2 py-1 rounded border-0 cursor-pointer">
                                                <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                                <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                <option value="refunded" <?php echo $order['status'] === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600"><?php echo date('Y-m-d H:i', strtotime($order['created_at'])); ?></td>
                                    <td class="px-4 py-3 text-sm">
                                        <a href="admin-view-order.php?id=<?php echo $order['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                                        <a href="#" onclick="deleteOrder(<?php echo $order['id']; ?>)" class="text-red-600 hover:text-red-900">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">No orders found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="bg-gray-50 px-4 py-4 flex items-center justify-between border-t border-gray-200">
                <span class="text-sm text-gray-600">Showing <?php echo ($result['page'] - 1) * $result['limit'] + 1; ?> to <?php echo min($result['page'] * $result['limit'], $result['total']); ?> of <?php echo $result['total']; ?> orders</span>
                <div class="flex gap-2">
                    <?php if ($result['page'] > 1): ?>
                        <a href="?page=<?php echo $result['page'] - 1; ?>&status=<?php echo urlencode($status); ?>&search=<?php echo urlencode($search); ?>" class="px-3 py-2 border border-gray-300 rounded hover:bg-gray-100">Previous</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $result['pages']; $i++): ?>
                        <a href="?page=<?php echo $i; ?>&status=<?php echo urlencode($status); ?>&search=<?php echo urlencode($search); ?>" class="px-3 py-2 <?php echo $i === $result['page'] ? 'bg-orange-500 text-white' : 'border border-gray-300'; ?> rounded">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($result['page'] < $result['pages']): ?>
                        <a href="?page=<?php echo $result['page'] + 1; ?>&status=<?php echo urlencode($status); ?>&search=<?php echo urlencode($search); ?>" class="px-3 py-2 border border-gray-300 rounded hover:bg-gray-100">Next</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function deleteOrder(orderId) {
            if (confirm('Are you sure you want to delete this order?')) {
                window.location.href = 'admin-action.php?action=delete_order&order_id=' + orderId;
            }
        }
    </script>
</body>
</html>
