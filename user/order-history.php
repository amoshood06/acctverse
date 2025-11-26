<?php
require_once "../db/db.php";
require_once "../flash.php";

// Redirect if user not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

// Pagination settings
$items_per_page = 10; // Number of orders to display per page
$current_page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
if (!$current_page || $current_page < 1) {
    $current_page = 1;
}
$offset = ($current_page - 1) * $items_per_page;

// Get total number of orders for pagination
$total_orders_stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
$total_orders_stmt->execute([$user_id]);
$total_orders = $total_orders_stmt->fetchColumn();
$total_pages = ceil($total_orders / $items_per_page);


// Fetch order history for the logged-in user
try {
    $stmt = $pdo->prepare("
        SELECT 
            o.id, 
            o.created_at, 
            o.total_amount, 
            o.quantity, 
            p.product_name,
            o.status,
            o.admin_note
        FROM orders o
        JOIN products p ON o.product_id = p.id
        WHERE o.user_id = ?
        ORDER BY o.created_at DESC 
        LIMIT ? OFFSET ?
    ");
    $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
    $stmt->bindValue(2, $items_per_page, PDO::PARAM_INT);
    $stmt->bindValue(3, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $orders = [];
    set_flash("error", "Could not retrieve order history.");
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
            <img src="assets/image/acctverse.png" alt="Acctverse" class="w-[150px]">
            <a href="index.php" class="text-orange-500 font-medium">← Back to Dashboard</a>
        </div>
    </nav>
    <div class="min-h-screen p-4 md:p-8">
        <div class="max-w-6xl mx-auto bg-white rounded-lg shadow-sm p-6">
            <!-- Header with Search -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Order History</h1>
                <div class="flex gap-2">
                    <input 
                        type="text" 
                        placeholder="Search by Trx" 
                        class="flex-1 md:flex-none px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-orange-500"
                    >
                    <button class="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600 transition duration-200">
                        🔍
                    </button>
                </div>
            </div>

            <!-- Table Section -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <!-- Table Header -->
                    <thead>
                        <tr class="bg-blue-900 text-white">
                            <th class="px-4 py-4 text-left text-sm font-semibold">Transaction</th>
                            <th class="px-4 py-4 text-left text-sm font-semibold">Ordered At</th>
                            <th class="px-4 py-4 text-left text-sm font-semibold">Amount</th>
                            <th class="px-4 py-4 text-left text-sm font-semibold">Quantity</th>
                            <th class="px-4 py-4 text-left text-sm font-semibold">Status</th>
                            <th class="px-4 py-4 text-left text-sm font-semibold">Details</th>
                        </tr>
                    </thead>
                    <!-- Table Body -->
                    <tbody>
                        <?php if (empty($orders)): ?>
                            <tr class="border-b border-gray-200">
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">No orders found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="px-4 py-3 font-mono text-sm text-gray-700">#<?= htmlspecialchars($order['id']); ?></td>
                                    <td class="px-4 py-3 text-sm text-gray-600"><?= date("M d, Y, h:i A", strtotime($order['created_at'])); ?></td>
                                    <td class="px-4 py-3 font-semibold">₦<?= number_format($order['total_amount'], 2); ?></td>
                                    <td class="px-4 py-3 text-center"><?= htmlspecialchars($order['quantity']); ?></td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-1 text-xs rounded-full 
                                            <?= $order['status'] === 'completed' ? 'bg-green-100 text-green-800' : ($order['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') ?>">
                                            <?= ucfirst(htmlspecialchars($order['status'])); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-800">
                                        <?= htmlspecialchars($order['product_name']); ?>
                                        <?php if (!empty($order['admin_note'])): ?>
                                            <button onclick="alert('Admin Note: \n\n<?= htmlspecialchars(addslashes($order['admin_note'])) ?>')" class="ml-2 bg-blue-100 text-blue-700 px-2 py-1 text-xs rounded hover:bg-blue-200">View Note</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination Controls -->
        <?php if ($total_pages > 1): ?>
            <div class="flex justify-center items-center space-x-2 mt-8">
                <?php if ($current_page > 1): ?>
                    <a href="?page=<?= $current_page - 1 ?>" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Previous</a>
                <?php else: ?>
                    <span class="px-4 py-2 bg-gray-100 text-gray-400 rounded cursor-not-allowed">Previous</span>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?= $i ?>" class="px-4 py-2 rounded <?= ($i == $current_page) ? 'bg-blue-900 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <?php if ($current_page < $total_pages): ?>
                    <a href="?page=<?= $current_page + 1 ?>" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Next</a>
                <?php else: ?>
                    <span class="px-4 py-2 bg-gray-100 text-gray-400 rounded cursor-not-allowed">Next</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>
