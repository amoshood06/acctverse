<?php
$pdo = require_once "../db/db.php"; // Ensure $pdo is initialized
require_once "../flash.php";

// A list of Nigerian banks for the withdrawal form.
$banks = [
    "Access Bank", "Citibank", "Ecobank", "Fidelity Bank", "First Bank", "FCMB",
    "GTBank", "Heritage Bank", "Keystone Bank", "Kuda Bank", "Opay", "Palmpay",
    "Polaris Bank", "Stanbic IBTC Bank", "Standard Chartered Bank", "Sterling Bank",
    "Suntrust Bank", "Union Bank", "UBA", "Unity Bank", "Wema Bank", "Zenith Bank"
];
sort($banks);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['withdraw'])) {
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
    $bankName = filter_input(INPUT_POST, 'bank_name', FILTER_SANITIZE_STRING);
    $accountNumber = filter_input(INPUT_POST, 'account_number', FILTER_SANITIZE_STRING);
    $accountName = filter_input(INPUT_POST, 'account_name', FILTER_SANITIZE_STRING);
    $userId = $_SESSION['user']['id'];

    // Basic validation
    if ($amount && $bankName && $accountNumber && $accountName && $amount > 0) {
        try {
            $pdo->beginTransaction();

            // Check if user has enough referral earnings
            $stmt = $pdo->prepare("SELECT referral_earnings FROM users WHERE id = ? FOR UPDATE");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && $user['referral_earnings'] >= $amount) {
                // Move amount from referral_earnings to pending_earnings
                $newReferralEarnings = $user['referral_earnings'] - $amount;
                $updateStmt = $pdo->prepare("UPDATE users SET referral_earnings = ?, pending_earnings = pending_earnings + ? WHERE id = ?");
                $updateStmt->execute([$newReferralEarnings, $amount, $userId]);

                // Create withdrawal record
                $insertStmt = $pdo->prepare("INSERT INTO withdrawals (user_id, amount, bank_name, account_number, account_name) VALUES (?, ?, ?, ?, ?)");
                $insertStmt->execute([$userId, $amount, $bankName, $accountNumber, $accountName]);

                $pdo->commit();
                set_flash("success", "Withdrawal request submitted successfully!");
            } else {
                $pdo->rollBack();
                set_flash("error", "Insufficient referral balance.");
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            set_flash("error", "An error occurred: " . $e->getMessage());
        }
    } else {
        set_flash("error", "Please fill all fields correctly.");
    }
    header("Location: referral.php");
    exit;
}

// Redirect if user is not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Get user data from session
$user = $_SESSION['user'];

// Get flash messages (if any)
$flash = get_flash();

// Fetch latest user data from DB (ensures balance, profile, referral info are up-to-date)
try {
    $stmt = $pdo->prepare("
        SELECT id, username, referral_code, referral_earnings, pending_earnings, withdrawn_amount
        FROM users 
        WHERE id = ?
    ");
    $stmt->execute([$user['id']]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($userData) {
        $_SESSION['user'] = array_merge($_SESSION['user'], $userData);
        $user = $_SESSION['user'];
    }

    // Referral earnings
    $referralBalance = number_format($user['referral_earnings'] ?? 0, 2);
    $pendingAmount   = number_format($user['pending_earnings'] ?? 0, 2);
    $withdrawnAmount = number_format($user['withdrawn_amount'] ?? 0, 2);

    // Fetch latest referrals
    $stmtRef = $pdo->prepare("
        SELECT full_name, username, created_at 
        FROM users 
        WHERE referred_by = ? 
        ORDER BY created_at DESC
        LIMIT 10
    ");
    $stmtRef->execute([$user['id']]);
    $latestReferrals = $stmtRef->fetchAll(PDO::FETCH_ASSOC);

    // Fetch withdrawal history
    $stmtWithdrawals = $pdo->prepare("
        SELECT amount, status, created_at, admin_note 
        FROM withdrawals 
        WHERE user_id = ? 
        ORDER BY created_at DESC
        LIMIT 10
    ");
    $stmtWithdrawals->execute([$user['id']]);
    $withdrawalHistory = $stmtWithdrawals->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $referralBalance = $pendingAmount = $withdrawnAmount = "0.00";
    $latestReferrals = [];
    $withdrawalHistory = [];
}
?>
<?php
require_once "header.php";
?>


<!-- Main Content -->
<div class="max-w-4xl mx-auto px-4 py-8">

    <?php if ($flash): ?>
        <div class="mb-4 p-4 rounded-md <?php echo $flash['type'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
            <?php echo $flash['message']; ?>
        </div>
    <?php endif; ?>

    <!-- Earnings Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <!-- Referral Balance -->
        <div class="bg-cyan-400 rounded-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Referral Balance</p>
                    <h3 class="text-3xl font-bold">₦ <?php echo $referralBalance; ?></h3>
                </div>
                <div class="w-12 h-12 bg-white bg-opacity-30 rounded-full flex items-center justify-center">💳</div>
            </div>
        </div>

        <!-- Pending -->
        <div class="bg-orange-200 rounded-lg p-6 text-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-70">Pending</p>
                    <h3 class="text-3xl font-bold">₦ <?php echo $pendingAmount; ?></h3>
                </div>
                <div class="w-12 h-12 bg-orange-300 rounded-full flex items-center justify-center">💰</div>
            </div>
        </div>

        <!-- Withdrawn -->
        <div class="bg-purple-200 rounded-lg p-6 text-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-70">Withdrawal</p>
                    <h3 class="text-3xl font-bold">₦ <?php echo $withdrawnAmount; ?></h3>
                </div>
                <div class="w-12 h-12 bg-purple-300 rounded-full flex items-center justify-center">📤</div>
            </div>
        </div>
    </div>

    <!-- Referral Details -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
        <h3 class="text-lg font-bold text-blue-900 mb-6">Referral Code</h3>
        <div class="flex gap-2 mb-6">
            <input type="text" value="<?php echo htmlspecialchars($user['referral_code'] ?? ''); ?>" class="flex-1 px-4 py-2 border border-gray-300 rounded bg-gray-50" readonly>
            <button class="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600" onclick="navigator.clipboard.writeText('<?php echo htmlspecialchars($user['referral_code'] ?? ''); ?>')">📋</button>
        </div>

        <h3 class="text-lg font-bold text-blue-900 mb-6">Referral Link</h3>
        <div class="flex gap-2">
            <input type="text" value="https://acctverse.com/register?ref=<?php echo htmlspecialchars($user['referral_code'] ?? ''); ?>" class="flex-1 px-4 py-2 border border-gray-300 rounded bg-gray-50 text-sm" readonly>
            <button class="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600" onclick="navigator.clipboard.writeText('https://acctverse.com/register?ref=<?php echo htmlspecialchars($user['referral_code'] ?? ''); ?>')">📋</button>
        </div>
    </div>

    <!-- Withdrawal Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
        <!-- Withdrawal Form -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-bold text-blue-900 mb-6">Request Withdrawal</h3>
            <form action="referral.php" method="POST">
                <div class="mb-4">
                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">Amount (₦)</label>
                    <input type="number" name="amount" id="amount" step="0.01" min="1" class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-orange-500" required>
                </div>
                <div class="mb-4">
                    <label for="bank_name" class="block text-sm font-medium text-gray-700 mb-1">Bank</label>
                    <select name="bank_name" id="bank_name" class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-orange-500" required>
                        <option value="">-- Select Bank --</option>
                        <?php foreach ($banks as $bank): ?>
                            <option value="<?php echo htmlspecialchars($bank); ?>"><?php echo htmlspecialchars($bank); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="account_number" class="block text-sm font-medium text-gray-700 mb-1">Account Number</label>
                    <div class="flex gap-2">
                        <input type="text" name="account_number" id="account_number" class="flex-1 px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-orange-500" required>
                        <button type="button" onclick="verifyAccount()" class="bg-gray-200 text-sm px-3 rounded hover:bg-gray-300">Verify</button>
                    </div>
                </div>
                <div class="mb-6">
                    <label for="account_name" class="block text-sm font-medium text-gray-700 mb-1">Account Name</label>
                    <input type="text" name="account_name" id="account_name" class="w-full px-4 py-2 border border-gray-300 rounded bg-gray-100 focus:outline-none focus:border-orange-500" required readonly>
                </div>
                <button type="submit" name="withdraw" class="w-full bg-blue-900 text-white px-4 py-2 rounded hover:bg-blue-800 font-medium">Submit Request</button>
            </form>
        </div>

        <!-- Withdrawal History -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-bold text-blue-900 mb-6">Withdrawal History</h3>
            <div class="overflow-y-auto h-96">
                <?php if(count($withdrawalHistory) > 0): ?>
                    <?php foreach($withdrawalHistory as $withdrawal): ?>
                        <div class="border-b border-gray-200 pb-3 mb-3">
                            <div class="flex justify-between items-center">
                                <p class="font-bold text-gray-800">₦<?php echo number_format($withdrawal['amount'], 2); ?></p>
                                <span class="text-xs font-medium px-2 py-1 rounded-full status-<?php echo strtolower(htmlspecialchars($withdrawal['status'])); ?>">
                                    <?php echo htmlspecialchars(ucfirst($withdrawal['status'])); ?>
                                </span>
                            </div>
                            <p class="text-sm text-gray-500"><?php echo date("d M, Y, g:i a", strtotime($withdrawal['created_at'])); ?></p>
                            <?php if (!empty($withdrawal['admin_note'])): ?>
                                <p class="text-xs text-red-600 mt-1">Note: <?php echo htmlspecialchars($withdrawal['admin_note']); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-gray-500 pt-16">No withdrawal requests yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Latest Referrals -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-bold text-blue-900 mb-6">Latest Referrals</h3>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-blue-900 text-white">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-semibold">#</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold">Name</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold">Username</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold">Referred on</th>
                    </tr>
                </thead>
                <tbody>
                <?php if(count($latestReferrals) > 0): ?>
                    <?php foreach($latestReferrals as $index => $ref): ?>
                        <tr class="border-b border-gray-200">
                            <td class="px-4 py-3"><?php echo $index + 1; ?></td>
                            <td class="px-4 py-3"><?php echo htmlspecialchars($ref['full_name']); ?></td>
                            <td class="px-4 py-3"><?php echo htmlspecialchars($ref['username']); ?></td>
                            <td class="px-4 py-3"><?php echo date("d M, Y", strtotime($ref['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr class="border-b border-gray-200">
                        <td colspan="4" class="px-4 py-8 text-center text-gray-500">No referrals yet</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<script>
async function verifyAccount() {
    const bankSelect = document.getElementById('bank_name');
    const accountNumber = document.getElementById('account_number').value;
    const accountNameInput = document.getElementById('account_name');
    
    // Find the bank code from the selected option's data attribute (you would need to add this)
    // For now, we'll just use the bank name as a placeholder for the logic.
    const bankName = bankSelect.value;

    if (!bankName || !accountNumber) {
        alert("Please select a bank and enter an account number.");
        return;
    }

    // In a real application, you would make an AJAX call to your server.
    // Your server would then securely call the Paystack API.
    // This is a simulation.
    
    // Example of what the server-side call might look like:
    // const response = await fetch('verify-account.php', {
    //     method: 'POST',
    //     headers: { 'Content-Type': 'application/json' },
    //     body: JSON.stringify({ bank_code: '058', account_number: accountNumber })
    // });
    // const data = await response.json();

    // Simulating a successful API response for demonstration
    const simulatedData = {
        status: true,
        message: "Account number resolved",
        data: { account_name: "JOHN DOE" }
    };

    if (simulatedData.status) {
        accountNameInput.value = simulatedData.data.account_name;
    } else {
        alert("Could not verify account name. Please check the details and try again.");
    }
}
</script>
</main>
</body>
</html>
