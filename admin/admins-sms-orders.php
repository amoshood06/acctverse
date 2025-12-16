<?php
require_once "../db/db.php";
require_once "../flash.php";
include 'header.php';


$flash = get_flash();

// ==================================================
//  Fetch Statistics
// ==================================================
try {
    $totalOrders = $pdo->query("SELECT COUNT(*) FROM sms_orders")->fetchColumn();
    $pendingOrders = $pdo->query("SELECT COUNT(*) FROM sms_orders WHERE status = 'pending'")->fetchColumn();
    $completedOrders = $pdo->query("SELECT COUNT(*) FROM sms_orders WHERE status = 'completed'")->fetchColumn();
    $cancelledOrders = $pdo->query("SELECT COUNT(*) FROM sms_orders WHERE status = 'cancelled'")->fetchColumn();
} catch (Exception $e) {
    $totalOrders = $pendingOrders = $completedOrders = $cancelledOrders = 0;
    set_flash("error", "Could not load order statistics.");
}

// ==================================================
//  Fetch and Filter Orders
// ==================================================
$sql = "
    SELECT 
        o.id, o.quantity, o.total_cost, o.status, o.created_at,
        s.name as service_name,
        u.first_name, u.last_name
    FROM sms_orders o
    JOIN users u ON o.user_id = u.id
    LEFT JOIN sms_services s ON o.service_id = s.id
";

$params = [];
$whereClauses = [];

// Filter by Order ID
$order_id = trim($_GET['order_id'] ?? '');
if ($order_id) {
    $whereClauses[] = "o.id = ?";
    $params[] = $order_id;
}

// Filter by Status
$status_filter = trim($_GET['status'] ?? '');
if ($status_filter) {
    $whereClauses[] = "o.status = ?";
    $params[] = $status_filter;
}

// Filter by Date
$date_filter = trim($_GET['date'] ?? '');
if ($date_filter) {
    $whereClauses[] = "DATE(o.created_at) = ?";
    $params[] = $date_filter;
}

if (!empty($whereClauses)) {
    $sql .= " WHERE " . implode(" AND ", $whereClauses);
}

$sql .= " ORDER BY o.created_at DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $orders = [];
    set_flash("error", "Could not retrieve SMS orders.");
}

?>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto">
        <h1 class="text-3xl font-bold text-blue-900 mb-8">SMS Order Management</h1>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-blue-500">
                <p class="text-gray-600 text-sm">Total Orders</p>
                <h3 class="text-2xl font-bold text-blue-900"><?= number_format($totalOrders) ?></h3>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-orange-500">
                <p class="text-gray-600 text-sm">Pending Orders</p>
                <h3 class="text-2xl font-bold text-blue-900"><?= number_format($pendingOrders) ?></h3>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-green-500">
                <p class="text-gray-600 text-sm">Completed</p>
                <h3 class="text-2xl font-bold text-blue-900"><?= number_format($completedOrders) ?></h3>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-red-500">
                <p class="text-gray-600 text-sm">Cancelled</p>
                <h3 class="text-2xl font-bold text-blue-900"><?= number_format($cancelledOrders) ?></h3>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <form method="GET" action="admin-sms-orders.php">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <input type="text" name="order_id" placeholder="Order ID..." value="<?= htmlspecialchars($order_id) ?>" class="border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                    <select name="status" class="border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                        <option value="">All Status</option>
                        <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="completed" <?= $status_filter === 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                    <input type="date" name="date" value="<?= htmlspecialchars($date_filter) ?>" class="border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                    <button type="submit" class="bg-orange-500 text-white px-4 py-2 rounded font-medium hover:bg-orange-600">Search</button>
                </div>
            </form>
        </div>

        <!-- Orders Table -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-blue-900 text-white">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Order ID</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">User</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Service</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Quantity</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Amount</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Date</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Status</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-10 text-gray-500">No SMS orders found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <form action="update-sms-order-status.php" method="POST">
                                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                        <td class="px-4 py-3 font-mono">#<?= htmlspecialchars($order['id']) ?></td>
                                        <td class="px-4 py-3"><?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?></td>
                                        <td class="px-4 py-3"><?= htmlspecialchars($order['service_name']) ?></td>
                                        <td class="px-4 py-3"><?= htmlspecialchars($order['quantity']) ?></td>
                                        <td class="px-4 py-3 font-semibold">₦<?= number_format($order['total_cost'], 2) ?></td>
                                        <td class="px-4 py-3 text-gray-500"><?= date("M d, Y", strtotime($order['created_at'])) ?></td>
                                        <td class="px-4 py-3">
                                            <select name="status" class="text-xs border border-gray-300 rounded p-1">
                                                <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                                <option value="completed" <?= $order['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                                <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                            </select>
                                        </td>
                                        <td class="px-4 py-3">
                                            <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded text-xs hover:bg-blue-700">Update</button>
                                        </td>
                                    </form>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php if ($flash): ?>
    <script>
    Toastify({
        text: <?= json_encode($flash['message']); ?>,
        duration: 4000,
        gravity: "top",
        position: "right",
        close: true,
        backgroundColor: <?= json_encode($flash['type']==='success' ? "linear-gradient(to right, #00b09b, #96c93d)" : "linear-gradient(to right, #ff5f6d, #ffc371)") ?>
    }).showToast();
    </script>
    <?php endif; ?>