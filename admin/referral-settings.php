<?php
require_once "../db/db.php";
require_once "../flash.php";

// Admin authentication
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    set_flash("error", "You do not have permission to access this page.");
    header("Location: ../login.php");
    exit;
}

$flash = get_flash();
$edit_tier = null;

// Handle POST requests for Add/Edit/Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- Delete Tier ---
    if (isset($_POST['delete_tier'])) {
        $tier_id = filter_input(INPUT_POST, 'tier_id', FILTER_VALIDATE_INT);
        if ($tier_id) {
            $pdo->prepare("DELETE FROM referral_tiers WHERE id = ?")->execute([$tier_id]);
            set_flash("success", "Tier deleted successfully.");
        }
    }
    // --- Add/Update Tier ---
    else {
        $tier_id = filter_input(INPUT_POST, 'tier_id', FILTER_VALIDATE_INT);
        $tier_name = trim($_POST['tier_name']);
        $min_referrals = filter_input(INPUT_POST, 'min_referrals', FILTER_VALIDATE_INT);
        $commission_rate = filter_input(INPUT_POST, 'commission_rate', FILTER_VALIDATE_FLOAT);
        $description = trim($_POST['description']);

        if ($tier_name && $min_referrals >= 0 && $commission_rate > 0) {
            $rate_decimal = $commission_rate / 100.0; // Convert percentage to decimal

            if ($tier_id) { // Update existing tier
                $stmt = $pdo->prepare("UPDATE referral_tiers SET tier_name=?, min_referrals=?, commission_rate=?, description=? WHERE id=?");
                $stmt->execute([$tier_name, $min_referrals, $rate_decimal, $description, $tier_id]);
                set_flash("success", "Tier updated successfully.");
            } else { // Insert new tier
                $stmt = $pdo->prepare("INSERT INTO referral_tiers (tier_name, min_referrals, commission_rate, description) VALUES (?, ?, ?, ?)");
                $stmt->execute([$tier_name, $min_referrals, $rate_decimal, $description]);
                set_flash("success", "New tier added successfully.");
            }
        } else {
            set_flash("error", "Please fill all required fields correctly.");
        }
    }
    header("Location: referral-settings.php");
    exit;
}

// Handle GET request for editing a tier
if (isset($_GET['edit'])) {
    $edit_id = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
    if ($edit_id) {
        $stmt = $pdo->prepare("SELECT * FROM referral_tiers WHERE id = ?");
        $stmt->execute([$edit_id]);
        $edit_tier = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// Fetch all tiers to display
$tiers = $pdo->query("SELECT * FROM referral_tiers ORDER BY min_referrals ASC")->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Referral Commission Tiers - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
</head>
<body class="bg-gray-100">
    <!-- Admin Navigation (Simplified) -->
    <nav class="bg-blue-900 text-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <h1 class="text-xl font-bold">Admin Panel</h1>
            <a href="../user/index.php" class="hover:underline">← Back to Site</a>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto px-4 py-8">
        <h2 class="text-3xl font-bold text-gray-800 mb-6">Manage Referral Tiers</h2>

        <!-- Add/Edit Form -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h3 class="text-xl font-semibold mb-4"><?= $edit_tier ? 'Edit Tier' : 'Add New Tier' ?></h3>
            <form action="referral-settings.php" method="POST" class="space-y-4">
                <input type="hidden" name="tier_id" value="<?= $edit_tier['id'] ?? '' ?>">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tier Name</label>
                        <input type="text" name="tier_name" value="<?= htmlspecialchars($edit_tier['tier_name'] ?? '') ?>" required class="mt-1 w-full px-3 py-2 border rounded-md">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Min. Referrals</label>
                        <input type="number" name="min_referrals" value="<?= $edit_tier['min_referrals'] ?? 0 ?>" required class="mt-1 w-full px-3 py-2 border rounded-md">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Commission Rate (%)</label>
                        <input type="number" name="commission_rate" step="0.01" value="<?= ($edit_tier['commission_rate'] ?? 0) * 100 ?>" required class="mt-1 w-full px-3 py-2 border rounded-md">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <input type="text" name="description" value="<?= htmlspecialchars($edit_tier['description'] ?? '') ?>" class="mt-1 w-full px-3 py-2 border rounded-md">
                </div>
                <div class="flex justify-end gap-3">
                    <?php if ($edit_tier): ?>
                        <a href="referral-settings.php" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-400">Cancel</a>
                    <?php endif; ?>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700"><?= $edit_tier ? 'Update Tier' : 'Add Tier' ?></button>
                </div>
            </form>
        </div>

        <!-- Current Tiers Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <h3 class="text-xl font-semibold p-6">Current Commission Tiers</h3>
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tier Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Min. Referrals</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rate</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($tiers)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">No tiers defined.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($tiers as $tier): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($tier['tier_name']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $tier['min_referrals'] ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= rtrim(rtrim(number_format($tier['commission_rate'] * 100, 2), '0'), '.') ?>%</td>
                                <td class="px-6 py-4 text-sm text-gray-500"><?= htmlspecialchars($tier['description']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex gap-2">
                                        <a href="?edit=<?= $tier['id'] ?>" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                        <form action="referral-settings.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this tier?');">
                                            <input type="hidden" name="tier_id" value="<?= $tier['id'] ?>">
                                            <button type="submit" name="delete_tier" class="text-red-600 hover:text-red-900">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($flash): ?>
    <script>
    document.addEventListener('DOMContentLoaded', function(){
      Toastify({
        text: <?= json_encode($flash['message']) ?>,
        duration: 4000,
        gravity: 'top',
        position: 'right',
        close: true,
        backgroundColor: <?= json_encode($flash['type']==='success' ? 'linear-gradient(to right, #00b09b, #96c93d)' : 'linear-gradient(to right, #ff5f6d, #ffc371)') ?>
      }).showToast();
    });
    </script>
    <?php endif; ?>
</body>
</html>

```

### 3. Update Order Processing Logic

Finally, let's modify `c:\xampp\htdocs\acctv\user\process-order.php` to use the dynamic tiers from the database instead of the hardcoded `if/else` block.

```diff