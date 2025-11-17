<?php
// filepath: c:\xampp\htdocs\acctverse\admin\admin-update-order-status.php
session_start();
require_once '../db/db.php';
require_once 'function/admin_update_order_status_function.php';

// Check if admin is logged in
// if (!isset($_SESSION['admin_id'])) { header('Location: admin-login.php'); exit; }

$pdo = get_pdo();
$message = '';
$message_type = '';
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_status' && $order_id > 0) {
        $new_status = $_POST['new_status'] ?? '';
        $notes = $_POST['notes'] ?? '';
        $admin_id = $_SESSION['user_id'] ?? null;

        $result = update_order_status($pdo, $order_id, $new_status, $admin_id, $notes);

        if ($result['success']) {
            send_order_status_notification($pdo, $order_id, $new_status);
            $message = $result['message'];
            $message_type = 'success';
        } else {
            $message = $result['message'];
            $message_type = 'error';
        }
    }

    if ($action === 'bulk_update') {
        $selected_orders = $_POST['selected_orders'] ?? [];
        $new_status = $_POST['bulk_status'] ?? '';
        $bulk_notes = $_POST['bulk_notes'] ?? '';
        $admin_id = $_SESSION['user_id'] ?? null;

        if (empty($selected_orders)) {
            $message = 'Please select at least one order';
            $message_type = 'error';
        } else {
            $result = bulk_update_order_status($pdo, $selected_orders, $new_status, $admin_id, $bulk_notes);
            $message = 'Updated: ' . $result['success'] . ' orders. Failed: ' . $result['failed'] . ' orders.';
            $message_type = $result['failed'] > 0 ? 'warning' : 'success';
        }
    }

    if ($action === 'export') {
        export_orders_to_csv($pdo, $status_filter);
    }
}

// Get order details if viewing single order
$order = null;
$order_items = [];
$order_history = [];
if ($order_id > 0) {
    $order = get_order_by_id($pdo, $order_id);
    if ($order) {
        $order_items = get_order_items($pdo, $order_id);
        $order_history = get_order_status_history($pdo, $order_id);
    }
}

// Get data
$stats = get_order_status_stats($pdo);
$pending_orders = get_pending_orders($pdo, 1, 5);

if (!empty($status_filter)) {
    $orders_data = get_orders_by_status($pdo, $status_filter, $page, 10, $search);
} else {
    $orders_data = get_orders_by_status($pdo, 'pending', $page, 10, $search);
    $status_filter = 'pending';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Order Status - AcctGlobe Admin</title>
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
                    <a href="admin-orders.php" class="text-gray-300 hover:text-orange-500">Orders</a>
                    <a href="admin-update-order-status.php" class="text-orange-500 font-medium">Update Status</a>
                </div>
                <a href="admin-logout.php" class="bg-orange-500 text-white px-4 py-2 rounded font-medium hover:bg-orange-600">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-blue-900 mb-8">Order Status Management</h1>

        <!-- Success/Error Messages -->
        <?php if ($message): ?>
            <div class="mb-8 p-4 rounded-lg <?php echo $message_type === 'success' ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- View Single Order -->
        <?php if ($order): ?>
            <div class="mb-8">
                <a href="admin-update-order-status.php" class="text-blue-600 hover:text-blue-900 mb-4 inline-block">&larr; Back to Orders</a>

                <div class="bg-white rounded-lg shadow-sm p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                        <!-- Order Info -->
                        <div>
                            <h2 class="text-2xl font-bold text-blue-900 mb-4">Order #<?php echo htmlspecialchars($order['order_number']); ?></h2>
                            <div class="space-y-3">
                                <div>
                                    <p class="text-gray-600 text-sm">Order ID</p>
                                    <p class="font-semibold"><?php echo $order['id']; ?></p>
                                </div>
                                <div>
                                    <p class="text-gray-600 text-sm">Current Status</p>
                                    <span class="<?php 
                                        $status_color = match($order['status']) {
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'confirmed' => 'bg-blue-100 text-blue-800',
                                            'processing' => 'bg-purple-100 text-purple-800',
                                            'shipped' => 'bg-cyan-100 text-cyan-800',
                                            'delivered' => 'bg-green-100 text-green-800',
                                            'cancelled' => 'bg-red-100 text-red-800',
                                            'refunded' => 'bg-gray-100 text-gray-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        };
                                        echo $status_color;
                                    ?> text-sm px-3 py-1 rounded-full font-semibold">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </div>
                                <div>
                                    <p class="text-gray-600 text-sm">Created Date</p>
                                    <p class="font-semibold"><?php echo date('Y-m-d H:i', strtotime($order['created_at'])); ?></p>
                                </div>
                                <div>
                                    <p class="text-gray-600 text-sm">Total Amount</p>
                                    <p class="font-bold text-lg text-orange-600">₦<?php echo number_format($order['total_amount'], 2); ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Customer Info -->
                        <div>
                            <h3 class="font-bold text-lg text-blue-900 mb-4">Customer Information</h3>
                            <div class="space-y-3">
                                <div>
                                    <p class="text-gray-600 text-sm">Name</p>
                                    <p class="font-semibold"><?php echo htmlspecialchars($order['full_name'] ?? $order['username']); ?></p>
                                </div>
                                <div>
                                    <p class="text-gray-600 text-sm">Email</p>
                                    <p class="font-semibold"><?php echo htmlspecialchars($order['email']); ?></p>
                                </div>
                                <div>
                                    <p class="text-gray-600 text-sm">Phone</p>
                                    <p class="font-semibold"><?php echo htmlspecialchars($order['phone'] ?? 'N/A'); ?></p>
                                </div>
                                <div>
                                    <p class="text-gray-600 text-sm">Address</p>
                                    <p class="font-semibold"><?php echo htmlspecialchars($order['address'] ?? 'N/A'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div class="mb-8">
                        <h3 class="font-bold text-lg text-blue-900 mb-4">Order Items</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="px-4 py-2 text-left">Product</th>
                                        <th class="px-4 py-2 text-left">Quantity</th>
                                        <th class="px-4 py-2 text-left">Price</th>
                                        <th class="px-4 py-2 text-left">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($order_items)): ?>
                                        <?php foreach ($order_items as $item): ?>
                                            <tr class="border-b border-gray-200">
                                                <td class="px-4 py-3"><?php echo htmlspecialchars($item['product_name']); ?></td>
                                                <td class="px-4 py-3"><?php echo $item['quantity']; ?></td>
                                                <td class="px-4 py-3">₦<?php echo number_format($item['price'], 2); ?></td>
                                                <td class="px-4 py-3 font-semibold">₦<?php echo number_format($item['subtotal'], 2); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">No items</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Update Status Form -->
                    <div class="mb-8 bg-blue-50 p-6 rounded-lg border border-blue-200">
                        <h3 class="font-bold text-lg text-blue-900 mb-4">Update Order Status</h3>
                        <form method="POST" class="space-y-4">
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">

                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">New Status</label>
                                <select name="new_status" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                                    <option value="">-- Select Status --</option>
                                    <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="confirmed" <?php echo $order['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                    <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                    <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                    <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                    <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    <option value="refunded" <?php echo $order['status'] === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">Notes</label>
                                <textarea name="notes" rows="3" placeholder="Add notes about this status update..." class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500"></textarea>
                            </div>

                            <button type="submit" class="bg-orange-500 text-white px-6 py-2 rounded font-medium hover:bg-orange-600">Update Status</button>
                        </form>
                    </div>

                    <!-- Status History -->
                    <div>
                        <h3 class="font-bold text-lg text-blue-900 mb-4">Status Change History</h3>
                        <div class="space-y-3">
                            <?php if (!empty($order_history)): ?>
                                <?php foreach ($order_history as $history): ?>
                                    <div class="border-l-4 border-blue-500 bg-blue-50 p-4 rounded">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <p class="font-semibold text-gray-800">
                                                    <?php echo ucfirst($history['old_status']); ?> 
                                                    <span class="text-gray-600">→</span> 
                                                    <?php echo ucfirst($history['new_status']); ?>
                                                </p>
                                                <?php if ($history['notes']): ?>
                                                    <p class="text-gray-600 text-sm mt-2"><?php echo htmlspecialchars($history['notes']); ?></p>
                                                <?php endif; ?>
                                            </div>
                                            <span class="text-gray-500 text-sm whitespace-nowrap"><?php echo date('Y-m-d H:i', strtotime($history['created_at'])); ?></span>
                                        </div>
                                        <p class="text-gray-600 text-sm mt-2">Changed by: <?php echo htmlspecialchars($history['full_name'] ?? $history['username'] ?? 'System'); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-gray-500">No status history available</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Order List -->
            <!-- Status Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4 mb-8">
                <?php foreach ($stats as $stat_status => $stat_data): ?>
                    <a href="?status=<?php echo urlencode($stat_status); ?>" class="bg-white rounded-lg shadow-sm p-4 text-center hover:shadow-md hover:border-orange-500 border-2 border-transparent <?php echo $status_filter === $stat_status ? 'border-orange-500 shadow-md' : ''; ?>">
                        <p class="text-gray-600 text-xs font-semibold uppercase"><?php echo $stat_status; ?></p>
                        <p class="text-2xl font-bold text-blue-900"><?php echo $stat_data['count']; ?></p>
                        <p class="text-gray-500 text-xs">₦<?php echo number_format($stat_data['total_amount'], 0); ?></p>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Search and Filter -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>">
                    <input type="text" name="search" placeholder="Search by order number, customer..." value="<?php echo htmlspecialchars($search); ?>" class="border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                    <button type="submit" class="bg-orange-500 text-white px-4 py-2 rounded font-medium hover:bg-orange-600">Search</button>
                    <form method="POST" class="inline">
                        <input type="hidden" name="action" value="export">
                        <input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>">
                        <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded font-medium hover:bg-green-600 w-full">Export CSV</button>
                    </form>
                </form>
            </div>

            <!-- Bulk Actions Form -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <form method="POST" id="bulkForm" class="space-y-4">
                    <input type="hidden" name="action" value="bulk_update">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Update selected to:</label>
                            <select name="bulk_status" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                                <option value="">-- Select Status --</option>
                                <option value="pending">Pending</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="processing">Processing</option>
                                <option value="shipped">Shipped</option>
                                <option value="delivered">Delivered</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="refunded">Refunded</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Notes (optional)</label>
                            <input type="text" name="bulk_notes" placeholder="Add notes to bulk update..." class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                        </div>
                    </div>

                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded font-medium hover:bg-blue-700">Update Selected Orders</button>
                </form>
            </div>

            <!-- Orders Table -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-blue-900 text-white">
                            <tr>
                                <th class="px-4 py-3 text-left"><input type="checkbox" id="selectAll" class="cursor-pointer"></th>
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
                            <?php if (!empty($orders_data['orders'])): ?>
                                <?php foreach ($orders_data['orders'] as $o): ?>
                                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                                        <td class="px-4 py-3"><input type="checkbox" name="selected_orders[]" value="<?php echo $o['id']; ?>" class="order-checkbox cursor-pointer"></td>
                                        <td class="px-4 py-3 font-semibold text-blue-600"><?php echo htmlspecialchars($o['order_number']); ?></td>
                                        <td class="px-4 py-3">
                                            <p class="font-semibold"><?php echo htmlspecialchars($o['username']); ?></p>
                                            <p class="text-gray-500 text-xs"><?php echo htmlspecialchars($o['email']); ?></p>
                                        </td>
                                        <td class="px-4 py-3"><?php echo $o['items_count']; ?></td>
                                        <td class="px-4 py-3 font-semibold">₦<?php echo number_format($o['total_amount'], 2); ?></td>
                                        <td class="px-4 py-3">
                                            <span class="<?php 
                                                $status_color = match($o['status']) {
                                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                                    'confirmed' => 'bg-blue-100 text-blue-800',
                                                    'processing' => 'bg-purple-100 text-purple-800',
                                                    'shipped' => 'bg-cyan-100 text-cyan-800',
                                                    'delivered' => 'bg-green-100 text-green-800',
                                                    'cancelled' => 'bg-red-100 text-red-800',
                                                    'refunded' => 'bg-gray-100 text-gray-800',
                                                    default => 'bg-gray-100 text-gray-800'
                                                };
                                                echo $status_color;
                                            ?> text-xs px-2 py-1 rounded-full font-semibold">
                                                <?php echo ucfirst($o['status']); ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-gray-600 text-xs"><?php echo date('Y-m-d', strtotime($o['created_at'])); ?></td>
                                        <td class="px-4 py-3 text-sm">
                                            <a href="?order_id=<?php echo $o['id']; ?>" class="text-blue-600 hover:text-blue-900 font-medium">Update</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="8" class="px-4 py-8 text-center text-gray-500">No orders found</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="bg-gray-50 px-4 py-4 flex items-center justify-between border-t border-gray-200">
                    <span class="text-sm text-gray-600">Showing page <?php echo $orders_data['page']; ?> of <?php echo $orders_data['pages']; ?> (<?php echo $orders_data['total']; ?> total)</span>
                    <div class="flex gap-2">
                        <?php if ($orders_data['page'] > 1): ?>
                            <a href="?page=<?php echo $orders_data['page'] - 1; ?>&status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search); ?>" class="px-3 py-2 border border-gray-300 rounded hover:bg-gray-100">Previous</a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $orders_data['pages']; $i++): ?>
                            <a href="?page=<?php echo $i; ?>&status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search); ?>" class="px-3 py-2 <?php echo $i === $orders_data['page'] ? 'bg-orange-500 text-white' : 'border border-gray-300'; ?> rounded">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($orders_data['page'] < $orders_data['pages']): ?>
                            <a href="?page=<?php echo $orders_data['page'] + 1; ?>&status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search); ?>" class="px-3 py-2 border border-gray-300 rounded hover:bg-gray-100">Next</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Select all checkboxes
        document.getElementById('selectAll')?.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.order-checkbox');
            checkboxes.forEach(checkbox => checkbox.checked = this.checked);
        });

        // Update form with selected orders
        document.getElementById('bulkForm')?.addEventListener('submit', function(e) {
            const selected = document.querySelectorAll('.order-checkbox:checked');
            if (selected.length === 0) {
                e.preventDefault();
                alert('Please select at least one order');
                return false;
            }

            selected.forEach(checkbox => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'selected_orders[]';
                input.value = checkbox.value;
                this.appendChild(input);
            });
        });
    </script>
</body>
</html>
