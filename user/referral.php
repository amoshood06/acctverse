<?php
session_start();
require_once "../db/db.php";  // Ensure $pdo is initialized
require_once "../flash.php";

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

} catch (Exception $e) {
    $referralBalance = $pendingAmount = $withdrawnAmount = "0.00";
    $latestReferrals = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Referral & Earnings - Acctverse</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
<!-- Navigation -->
<nav class="bg-white shadow-sm sticky top-0 z-10">
    <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
        <img src="assets/image/acctverse.png" alt="" class="w-[150px]">
        <a href="dashboard.php" class="text-orange-500 font-medium">← Back to Dashboard</a>
    </div>
</nav>

<!-- Main Content -->
<div class="max-w-4xl mx-auto px-4 py-8">

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
            <input type="text" value="<?php echo $user['referral_code']; ?>" class="flex-1 px-4 py-2 border border-gray-300 rounded bg-gray-50" readonly>
            <button class="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600" onclick="navigator.clipboard.writeText('<?php echo $user['referral_code']; ?>')">📋</button>
        </div>

        <h3 class="text-lg font-bold text-blue-900 mb-6">Referral Link</h3>
        <div class="flex gap-2">
            <input type="text" value="https://acctglobe.com/register?ref=<?php echo $user['referral_code']; ?>" class="flex-1 px-4 py-2 border border-gray-300 rounded bg-gray-50 text-sm" readonly>
            <button class="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600" onclick="navigator.clipboard.writeText('https://acctglobe.com/register?ref=<?php echo $user['referral_code']; ?>')">📋</button>
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

</div>
</body>
</html>
