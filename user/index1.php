<?php 
$pdo = require_once "../db/db.php";  // Ensure $pdo is initialized
require_once "../flash.php";

// Redirect if user is not logged in
if (!isset($_SESSION['user'])) {
    header("Location: ./login.php");
    exit;
}

// Get user data from session
$user = $_SESSION['user'];

// Get flash messages (if any)
$flash = get_flash();

// -----------------------------------------------------
// ✅ FETCH LATEST USER DATA
// -----------------------------------------------------
try {
    $stmt = $pdo->prepare("SELECT first_name, last_name, email, balance, address, state, zip_code, city 
                           FROM users WHERE id = ?");
    $stmt->execute([$user['id']]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($userData) {
        $_SESSION['user'] = array_merge($_SESSION['user'], $userData);
        $user = $_SESSION['user'];
    }
} catch (Exception $e) {
    set_flash("error", "Unable to fetch user data.");
}

// -----------------------------------------------------
// ✅ FETCH USER TICKETS COUNT
// -----------------------------------------------------
try {
    $ticketStmt = $pdo->prepare("SELECT COUNT(*) AS total_tickets FROM tickets WHERE user_id = ?");
    $ticketStmt->execute([$user['id']]);
    $totalTickets = $ticketStmt->fetch(PDO::FETCH_ASSOC)['total_tickets'] ?? 0;
} catch (Exception $e) {
    $totalTickets = 0;
}

// -----------------------------------------------------
// ✅ FETCH REFERRAL COUNT
// -----------------------------------------------------
try {
    $refStmt = $pdo->prepare("SELECT COUNT(*) AS total_referrals FROM referrals WHERE referred_by = ?");
    $refStmt->execute([$user['id']]);
    $referralCount = $refStmt->fetch(PDO::FETCH_ASSOC)['total_referrals'] ?? 0;
} catch (Exception $e) {
    $referralCount = 0;
}

?>




<?php
require_once "header.php";
?>


    <!-- Flash message -->
<?php if($flash): ?>
<script>
Toastify({
    text: <?= json_encode($flash['message']); ?>,
    duration: 4000,
    gravity: "top",
    position: "right",
    close: true,
    backgroundColor: <?= json_encode($flash['type']==='success'
        ? "linear-gradient(to right, #00b09b, #96c93d)"
        : "linear-gradient(to right, #ff5f6d, #ffc371)"); ?>
}).showToast();
</script>
<?php endif; ?>
    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
            <!-- My Information Card -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="bg-red-500 text-white p-4 font-bold">My Information</div>
                <div class="p-6">
                    <p class="text-gray-800 font-semibold">Moshood Ajose</p>
                    <p class="text-gray-600 text-sm mb-4">Nigeria</p>
                    <a href="profile.php">
                    <button class="w-full border border-gray-300 text-blue-900 py-2 rounded hover:bg-gray-50">
                        ✎ Update
                    </button>
                    </a>
                </div>
            </div>

            <!-- Fund Wallet Card -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden border-l-4 border-blue-400">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-gray-600 text-sm">Add Funds</p>
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">💳</div>
                    </div>
                    <h3 class="text-2xl font-bold text-blue-900"><a href="fund.php">Fund Wallet</a></h3>
                </div>
            </div>

            <!-- Total Spent Card -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden border-l-4 border-orange-400">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-gray-600 text-sm">Total Spent</p>
                        <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">💰</div>
                    </div>
                    <h3 class="text-2xl font-bold text-blue-900">₦0.00</h3>
                </div>
            </div>

            <!-- Orders Card -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden border-l-4 border-purple-400">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-gray-600 text-sm">Orders</p>
                        <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">🛒</div>
                    </div>
                    <h3 class="text-2xl font-bold text-blue-900">0</h3>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Balance Card -->
            <div class="bg-gradient-to-r from-cyan-400 to-cyan-500 rounded-lg shadow-sm p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm opacity-90">Balance</p>
                        <h3 class="text-3xl font-bold">₦<?= number_format($user['balance'], 2); ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-white bg-opacity-30 rounded-full flex items-center justify-center">💳</div>
                </div>
            </div>

            <!-- Tickets Card -->
            <div class="bg-gradient-to-r from-yellow-400 to-yellow-500 rounded-lg shadow-sm p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm opacity-90">Tickets</p>
                        <h3 class="text-3xl font-bold"><?= htmlspecialchars($totalTickets) ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-white bg-opacity-30 rounded-full flex items-center justify-center">🎫</div>
                </div>
            </div>

            <!-- Referrals Card -->
            <div class="bg-gradient-to-r from-gray-500 to-gray-600 rounded-lg shadow-sm p-6 text-white col-span-2">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm opacity-90">Referrals</p>
                        <h3 class="text-3xl font-bold"><?= $referralCount ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-white bg-opacity-30 rounded-full flex items-center justify-center">👥</div>
                </div>
            </div>
        </div>

        <!-- Latest Payments History -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-2xl font-bold text-blue-900 mb-6">Latest Monnify Transactions</h2>
            
            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-red-500 text-white">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Gateway | Trx</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Amount</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Status</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $monnifyTransactions = [];
                        try {
                            $transStmt = $pdo->prepare("SELECT payment_gateway, transaction_reference, amount, status, created_at FROM transactions WHERE user_id = ? AND payment_gateway = 'Monnify' ORDER BY created_at DESC LIMIT 10");
                            $transStmt->execute([$user['id']]);
                            $monnifyTransactions = $transStmt->fetchAll(PDO::FETCH_ASSOC);
                        } catch (Exception $e) {
                            // Log error, but don't show sensitive info to user
                            error_log("Error fetching Monnify transactions: " . $e->getMessage());
                        }

                        if (empty($monnifyTransactions)): ?>
                            <tr class="border-b border-gray-200">
                                <td colspan="4" class="px-4 py-8 text-center text-gray-500">No Monnify transactions found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($monnifyTransactions as $transaction): ?>
                                <tr class="border-b border-gray-200">
                                    <td class="px-4 py-3 text-sm text-gray-800"><?= htmlspecialchars($transaction['payment_gateway']); ?> | <?= htmlspecialchars($transaction['transaction_reference']); ?></td>
                                    <td class="px-4 py-3 text-sm text-gray-800">₦<?= number_format($transaction['amount'], 2); ?></td>
                                    <td class="px-4 py-3 text-sm text-gray-800">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php 
                                                if ($transaction['status'] == 'completed') echo 'bg-green-100 text-green-800';
                                                else if ($transaction['status'] == 'pending') echo 'bg-yellow-100 text-yellow-800';
                                                else if ($transaction['status'] == 'failed') echo 'bg-red-100 text-red-800';
                                                else echo 'bg-gray-100 text-gray-800';
                                            ?>">
                                            <?= ucfirst(htmlspecialchars($transaction['status'])); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-800"><?= date('M d, Y H:i', strtotime($transaction['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
</main>
</body>
</html>
