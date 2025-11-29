<?php
session_start();

// --- Admin Authentication (Example) ---
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$message = '';
$message_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include('../db/db.php'); // Connect to DB for POST request

    $apiKey = trim($_POST['api_key']);
    $secretKey = trim($_POST['secret_key']);
    $contractCode = trim($_POST['contract_code']);

    // Update settings in the database
    try {
        $stmt = $pdo->prepare("UPDATE monnify_settings SET api_key = ?, secret_key = ?, contract_code = ? WHERE id = 1");
        $stmt->execute([$apiKey, $secretKey, $contractCode]);
        $message = 'Settings updated successfully!';
        $message_type = 'success';
    } catch (PDOException $e) {
        $message = 'Error: Could not prepare the statement.';
        $message_type = 'error';
    }
    $pdo = null; // Close connection after POST
}


// Fetch current settings
include('../db/db.php'); // Connect to DB to fetch settings for display
$settings = [];
try {
    $stmt = $pdo->query("SELECT api_key, secret_key, contract_code FROM monnify_settings WHERE id = 1");
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Optionally handle fetch error
}
$pdo = null; // Close connection after fetching
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Gateway Settings - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <?php include('header.php'); ?>

    <div class="max-w-2xl mx-auto px-4 py-10">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Monnify Payment Gateway Settings</h1>

        <?php if ($message): ?>
        <div class="p-4 mb-4 text-sm rounded-lg <?php echo $message_type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>

        <div class="bg-white rounded-lg shadow p-6">
            <form method="POST" class="space-y-6">
                <div>
                    <label for="api_key" class="block text-sm font-medium text-gray-700">API Key</label>
                    <input type="text" name="api_key" id="api_key" value="<?php echo htmlspecialchars($settings['api_key'] ?? ''); ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label for="secret_key" class="block text-sm font-medium text-gray-700">Secret Key</label>
                    <input type="password" name="secret_key" id="secret_key" value="<?php echo htmlspecialchars($settings['secret_key'] ?? ''); ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label for="contract_code" class="block text-sm font-medium text-gray-700">Contract Code</label>
                    <input type="text" name="contract_code" id="contract_code" value="<?php echo htmlspecialchars($settings['contract_code'] ?? ''); ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>