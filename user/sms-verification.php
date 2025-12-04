<?php
$pdo = require_once "../db/db.php";
require_once "../flash.php";

// User authentication check
if (!isset($_SESSION['user'])) {
    set_flash("error", "You must be logged in to access this page.");
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];
$flash = get_flash();

// Fetch active SMS services
$stmt = $pdo->query("SELECT * FROM sms_services WHERE is_active = 1 AND available_credits > 0 ORDER BY country, service_name");
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch latest user orders
$orderStmt = $pdo->prepare("
    SELECT o.*, s.service_name 
    FROM sms_orders o
    JOIN sms_services s ON o.service_id = s.id
    WHERE o.user_id = ? 
    ORDER BY o.created_at DESC 
    LIMIT 10
");
$orderStmt->execute([$user_id]);
$orders = $orderStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php
require_once "header.php";
?>

    <!-- Main Content -->
    <div class="max-w-4xl mx-auto px-4 py-8">
        <!-- Main Section -->
        <div class="bg-white rounded-lg shadow-sm p-6 md:p-8 mb-8">
            <h1 class="text-3xl font-bold text-blue-900 text-center mb-2">Choose Your Service</h1>
            <p class="text-center text-gray-500 mb-8">Select a service to get a verification code instantly.</p>

            <form action="process-sms-order.php" method="POST" class="space-y-8">
                <input type="hidden" name="user_id" value="<?= $user_id; ?>">
                <div>
                    <label class="block text-sm font-semibold text-blue-900 mb-2 flex items-center gap-2">
                        <span>Select service</span>
                        <span class="text-orange-500">📞</span>
                    </label>
                    <?php if (empty($services)): ?>
                        <p class="text-gray-600 p-4 bg-gray-100 rounded">No SMS services are available at the moment.</p>
                    <?php else: ?>
                        <select name="service_id" class="w-full px-4 py-3 border border-gray-300 rounded focus:outline-none focus:border-orange-500 bg-white" required>
                            <option value="">-- Select a Service --</option>
                            <?php foreach ($services as $service): ?>
                                <option value="<?= $service['id'] ?>">
                                    <?= htmlspecialchars($service['service_name']) ?> (<?= htmlspecialchars($service['country']) ?>) - ₦<?= number_format($service['price_per_sms'], 2) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="quantity" value="1">
                    <?php endif; ?>
                </div>

                <?php if (!empty($services)): ?>
                    <button type="submit" class="w-full bg-orange-500 text-white font-bold py-3 rounded hover:bg-orange-600 transition flex items-center justify-center gap-2">
                        🛒 Purchase
                    </button>
                <?php endif; ?>
            </form>

            <p class="text-center text-gray-600 text-sm mt-6">Please note that you will only be debited if you receive the SMS code.</p>
        </div>

        <!-- Latest Orders Table -->
        <div class="bg-white rounded-lg shadow-sm p-6" id="latest-orders">
            <h2 class="text-2xl font-bold text-blue-900 mb-6">Latest Orders</h2>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-blue-900 text-white">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Service</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Phone Number</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Code</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Amount</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Status</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Date</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                            <tr class="border-b border-gray-200">
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500">No Order Available</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                                <tr class="border-b border-gray-200">
                                    <td class="px-4 py-3"><?= htmlspecialchars($order['service_name']) ?></td>
                                    <td class="px-4 py-3 font-mono"><?= htmlspecialchars($order['phone_number_received'] ?? 'N/A') ?></td>
                                    <td class="px-4 py-3 font-mono font-bold"><?= htmlspecialchars($order['sms_code'] ?? 'N/A') ?></td>
                                    <td class="px-4 py-3">₦<?= number_format($order['total_cost'], 2) ?></td>
                                    <td class="px-4 py-3"><span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800"><?= htmlspecialchars($order['status']) ?></span></td>
                                    <td class="px-4 py-3 text-sm text-gray-500"><?= date("d M, Y H:i", strtotime($order['created_at'])) ?></td>
                                    <td class="px-4 py-3">
                                        <?php if(!empty($order['admin_note'])): ?>
                                            <button onclick="alert('Admin Note: \n\n<?= htmlspecialchars(addslashes($order['admin_note'])) ?>')" class="bg-blue-100 text-blue-700 px-2 py-1 text-xs rounded hover:bg-blue-200">View Note</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <p class="text-center text-gray-600 text-sm mt-6">No need to refresh the page to get the code. Click "<span class="bg-red-500 text-white px-2 py-1 rounded inline-block">X</span>" to cancel order.</p>
            <p class="text-center text-gray-600 text-sm">If your network is bad you may refresh.</p>
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
</main>
</body>
</html>
