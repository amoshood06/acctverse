<?php
// filepath: c:\xampp\htdocs\acctverse\admin\admin-settings.php
session_start();
require_once '../db/db.php';
require_once 'function/admin_settings_function.php';

// Check if admin is logged in
// if (!isset($_SESSION['admin_id'])) { header('Location: admin-login.php'); exit; }

$pdo = get_pdo();
$message = '';
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_general') {
        $result = update_general_settings($pdo, [
            'app_name' => $_POST['app_name'] ?? '',
            'company_email' => $_POST['company_email'] ?? '',
            'company_phone' => $_POST['company_phone'] ?? '',
            'company_address' => $_POST['company_address'] ?? '',
            'currency' => $_POST['currency'] ?? 'NGN',
            'timezone' => $_POST['timezone'] ?? 'UTC',
            'maintenance_mode' => isset($_POST['maintenance_mode']) ? 1 : 0,
            'allow_registration' => isset($_POST['allow_registration']) ? 1 : 0
        ]);
        $message = $result['success'] ? '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">' . $result['message'] . '</div>' : '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">' . $result['message'] . '</div>';
        $tab = 'general';
    }

    if ($action === 'update_payment') {
        $gateway = $_POST['gateway'] ?? '';
        $result = update_payment_gateway($pdo, $gateway, [
            'enabled' => isset($_POST[$gateway . '_enabled']) ? 1 : 0,
            'public_key' => $_POST[$gateway . '_public_key'] ?? '',
            'secret_key' => $_POST[$gateway . '_secret_key'] ?? ''
        ]);
        $message = $result['success'] ? '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">' . $result['message'] . '</div>' : '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">' . $result['message'] . '</div>';
        $tab = 'payment';
    }

    if ($action === 'update_email') {
        $result = update_email_settings($pdo, [
            'smtp_host' => $_POST['smtp_host'] ?? '',
            'smtp_port' => $_POST['smtp_port'] ?? '587',
            'smtp_username' => $_POST['smtp_username'] ?? '',
            'smtp_password' => $_POST['smtp_password'] ?? '',
            'from_email' => $_POST['from_email'] ?? '',
            'from_name' => $_POST['from_name'] ?? '',
            'smtp_secure' => $_POST['smtp_secure'] ?? 'tls'
        ]);
        $message = $result['success'] ? '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">' . $result['message'] . '</div>' : '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">' . $result['message'] . '</div>';
        $tab = 'email';
    }

    if ($action === 'update_security') {
        $result = update_security_settings($pdo, [
            'password_min_length' => $_POST['password_min_length'] ?? '8',
            'password_require_uppercase' => isset($_POST['password_require_uppercase']) ? 1 : 0,
            'password_require_numbers' => isset($_POST['password_require_numbers']) ? 1 : 0,
            'password_require_special' => isset($_POST['password_require_special']) ? 1 : 0,
            'session_timeout' => $_POST['session_timeout'] ?? '30',
            'enable_two_factor' => isset($_POST['enable_two_factor']) ? 1 : 0,
            'max_login_attempts' => $_POST['max_login_attempts'] ?? '5'
        ]);
        $message = $result['success'] ? '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">' . $result['message'] . '</div>' : '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">' . $result['message'] . '</div>';
        $tab = 'security';
    }
}

// Get all settings
$general_settings = get_general_settings($pdo);
$payment_gateways = get_payment_gateways($pdo);
$email_settings = get_email_settings($pdo);
$sms_settings = get_sms_settings($pdo);
$security_settings = get_security_settings($pdo);
$system_health = get_system_health($pdo);
$activity_logs = get_activity_logs($pdo, 1, 10);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - AcctGlobe Admin</title>
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
                    <a href="admin-reports.php" class="text-gray-300 hover:text-orange-500">Reports</a>
                    <a href="admin-settings.php" class="text-orange-500 font-medium">Settings</a>
                </div>
                <a href="admin-logout.php" class="bg-orange-500 text-white px-4 py-2 rounded font-medium hover:bg-orange-600">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-blue-900 mb-8">System Settings</h1>

        <!-- Messages -->
        <?php if ($message): echo $message; endif; ?>

        <!-- Settings Tabs -->
        <div class="flex gap-2 mb-8 overflow-x-auto">
            <a href="?tab=general" class="px-4 py-2 rounded font-medium <?php echo $tab === 'general' ? 'bg-orange-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'; ?>">General</a>
            <a href="?tab=payment" class="px-4 py-2 rounded font-medium <?php echo $tab === 'payment' ? 'bg-orange-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'; ?>">Payment</a>
            <a href="?tab=email" class="px-4 py-2 rounded font-medium <?php echo $tab === 'email' ? 'bg-orange-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'; ?>">Email</a>
            <a href="?tab=sms" class="px-4 py-2 rounded font-medium <?php echo $tab === 'sms' ? 'bg-orange-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'; ?>">SMS</a>
            <a href="?tab=security" class="px-4 py-2 rounded font-medium <?php echo $tab === 'security' ? 'bg-orange-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'; ?>">Security</a>
            <a href="?tab=system" class="px-4 py-2 rounded font-medium <?php echo $tab === 'system' ? 'bg-orange-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'; ?>">System</a>
        </div>

        <!-- General Settings -->
        <?php if ($tab === 'general'): ?>
        <div class="bg-white rounded-lg shadow-sm p-8">
            <h2 class="text-2xl font-bold text-blue-900 mb-6">General Settings</h2>
            <form method="POST" class="space-y-6">
                <input type="hidden" name="action" value="update_general">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- App Name -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Application Name</label>
                        <input type="text" name="app_name" value="<?php echo htmlspecialchars($general_settings['app_name'] ?? ''); ?>" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                    </div>

                    <!-- Currency -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Currency</label>
                        <select name="currency" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                            <option value="NGN" <?php echo ($general_settings['currency'] ?? '') === 'NGN' ? 'selected' : ''; ?>>NGN (Nigerian Naira)</option>
                            <option value="USD" <?php echo ($general_settings['currency'] ?? '') === 'USD' ? 'selected' : ''; ?>>USD (US Dollar)</option>
                            <option value="GBP" <?php echo ($general_settings['currency'] ?? '') === 'GBP' ? 'selected' : ''; ?>>GBP (British Pound)</option>
                            <option value="EUR" <?php echo ($general_settings['currency'] ?? '') === 'EUR' ? 'selected' : ''; ?>>EUR (Euro)</option>
                        </select>
                    </div>

                    <!-- Company Email -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Company Email</label>
                        <input type="email" name="company_email" value="<?php echo htmlspecialchars($general_settings['company_email'] ?? ''); ?>" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                    </div>

                    <!-- Company Phone -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Company Phone</label>
                        <input type="tel" name="company_phone" value="<?php echo htmlspecialchars($general_settings['company_phone'] ?? ''); ?>" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                    </div>

                    <!-- Timezone -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Timezone</label>
                        <select name="timezone" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                            <option value="UTC" <?php echo ($general_settings['timezone'] ?? '') === 'UTC' ? 'selected' : ''; ?>>UTC</option>
                            <option value="Africa/Lagos" <?php echo ($general_settings['timezone'] ?? '') === 'Africa/Lagos' ? 'selected' : ''; ?>>Africa/Lagos (WAT)</option>
                            <option value="Europe/London" <?php echo ($general_settings['timezone'] ?? '') === 'Europe/London' ? 'selected' : ''; ?>>Europe/London (GMT)</option>
                            <option value="America/New_York" <?php echo ($general_settings['timezone'] ?? '') === 'America/New_York' ? 'selected' : ''; ?>>America/New_York (EST)</option>
                        </select>
                    </div>

                    <!-- Allow Registration -->
                    <div class="flex items-center pt-6">
                        <input type="checkbox" name="allow_registration" id="allow_registration" <?php echo ($general_settings['allow_registration'] ?? false) ? 'checked' : ''; ?> class="w-4 h-4 cursor-pointer">
                        <label for="allow_registration" class="ml-2 text-gray-700 font-semibold cursor-pointer">Allow User Registration</label>
                    </div>
                </div>

                <!-- Company Address -->
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Company Address</label>
                    <textarea name="company_address" rows="3" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500"><?php echo htmlspecialchars($general_settings['company_address'] ?? ''); ?></textarea>
                </div>

                <!-- Maintenance Mode -->
                <div class="flex items-center gap-4">
                    <input type="checkbox" name="maintenance_mode" id="maintenance_mode" <?php echo ($general_settings['maintenance_mode'] ?? false) ? 'checked' : ''; ?> class="w-4 h-4 cursor-pointer">
                    <label for="maintenance_mode" class="text-gray-700 font-semibold cursor-pointer">Enable Maintenance Mode</label>
                    <p class="text-gray-500 text-sm">Users will see a maintenance message</p>
                </div>

                <!-- Submit Button -->
                <div class="flex gap-4 pt-4">
                    <button type="submit" class="bg-orange-500 text-white px-6 py-2 rounded font-medium hover:bg-orange-600">Save Changes</button>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <!-- Payment Gateway Settings -->
        <?php if ($tab === 'payment'): ?>
        <div class="bg-white rounded-lg shadow-sm p-8">
            <h2 class="text-2xl font-bold text-blue-900 mb-6">Payment Gateway Settings</h2>
            
            <!-- Paystack -->
            <div class="mb-8 pb-8 border-b border-gray-200">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Paystack</h3>
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="update_payment">
                    <input type="hidden" name="gateway" value="paystack">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Public Key</label>
                            <input type="password" name="paystack_public_key" value="<?php echo htmlspecialchars($payment_gateways['paystack']['public_key'] ?? ''); ?>" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Secret Key</label>
                            <input type="password" name="paystack_secret_key" value="<?php echo htmlspecialchars($payment_gateways['paystack']['secret_key'] ?? ''); ?>" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="paystack_enabled" id="paystack_enabled" <?php echo ($payment_gateways['paystack']['enabled'] ?? false) ? 'checked' : ''; ?> class="w-4 h-4 cursor-pointer">
                        <label for="paystack_enabled" class="text-gray-700 font-semibold cursor-pointer">Enable Paystack</label>
                    </div>

                    <button type="submit" class="bg-orange-500 text-white px-6 py-2 rounded font-medium hover:bg-orange-600">Save Paystack</button>
                </form>
            </div>

            <!-- Flutterwave -->
            <div>
                <h3 class="text-xl font-bold text-gray-800 mb-4">Flutterwave</h3>
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="update_payment">
                    <input type="hidden" name="gateway" value="flutterwave">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Public Key</label>
                            <input type="password" name="flutterwave_public_key" value="<?php echo htmlspecialchars($payment_gateways['flutterwave']['public_key'] ?? ''); ?>" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Secret Key</label>
                            <input type="password" name="flutterwave_secret_key" value="<?php echo htmlspecialchars($payment_gateways['flutterwave']['secret_key'] ?? ''); ?>" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="flutterwave_enabled" id="flutterwave_enabled" <?php echo ($payment_gateways['flutterwave']['enabled'] ?? false) ? 'checked' : ''; ?> class="w-4 h-4 cursor-pointer">
                        <label for="flutterwave_enabled" class="text-gray-700 font-semibold cursor-pointer">Enable Flutterwave</label>
                    </div>

                    <button type="submit" class="bg-orange-500 text-white px-6 py-2 rounded font-medium hover:bg-orange-600">Save Flutterwave</button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- Email Settings -->
        <?php if ($tab === 'email'): ?>
        <div class="bg-white rounded-lg shadow-sm p-8">
            <h2 class="text-2xl font-bold text-blue-900 mb-6">Email Settings</h2>
            <form method="POST" class="space-y-6">
                <input type="hidden" name="action" value="update_email">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">SMTP Host</label>
                        <input type="text" name="smtp_host" value="<?php echo htmlspecialchars($email_settings['smtp_host'] ?? ''); ?>" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500" placeholder="smtp.gmail.com">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">SMTP Port</label>
                        <input type="number" name="smtp_port" value="<?php echo htmlspecialchars($email_settings['smtp_port'] ?? '587'); ?>" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">SMTP Username</label>
                        <input type="email" name="smtp_username" value="<?php echo htmlspecialchars($email_settings['smtp_username'] ?? ''); ?>" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">SMTP Password</label>
                        <input type="password" name="smtp_password" value="<?php echo htmlspecialchars($email_settings['smtp_password'] ?? ''); ?>" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">From Email</label>
                        <input type="email" name="from_email" value="<?php echo htmlspecialchars($email_settings['from_email'] ?? ''); ?>" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">From Name</label>
                        <input type="text" name="from_name" value="<?php echo htmlspecialchars($email_settings['from_name'] ?? ''); ?>" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Security</label>
                        <select name="smtp_secure" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                            <option value="tls" <?php echo ($email_settings['smtp_secure'] ?? '') === 'tls' ? 'selected' : ''; ?>>TLS</option>
                            <option value="ssl" <?php echo ($email_settings['smtp_secure'] ?? '') === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                            <option value="none" <?php echo ($email_settings['smtp_secure'] ?? '') === 'none' ? 'selected' : ''; ?>>None</option>
                        </select>
                    </div>
                </div>

                <div class="flex gap-4 pt-4">
                    <button type="submit" class="bg-orange-500 text-white px-6 py-2 rounded font-medium hover:bg-orange-600">Save Email Settings</button>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <!-- SMS Settings -->
        <?php if ($tab === 'sms'): ?>
        <div class="bg-white rounded-lg shadow-sm p-8">
            <h2 class="text-2xl font-bold text-blue-900 mb-6">SMS Settings</h2>
            <form method="POST" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">SMS Provider</label>
                        <select name="sms_provider" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                            <option value="twilio" <?php echo ($sms_settings['sms_provider'] ?? '') === 'twilio' ? 'selected' : ''; ?>>Twilio</option>
                            <option value="aws_sns" <?php echo ($sms_settings['sms_provider'] ?? '') === 'aws_sns' ? 'selected' : ''; ?>>AWS SNS</option>
                            <option value="nexmo" <?php echo ($sms_settings['sms_provider'] ?? '') === 'nexmo' ? 'selected' : ''; ?>>Vonage (Nexmo)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">API Key</label>
                        <input type="password" name="sms_api_key" value="<?php echo htmlspecialchars($sms_settings['sms_api_key'] ?? ''); ?>" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">API Secret</label>
                        <input type="password" name="sms_api_secret" value="<?php echo htmlspecialchars($sms_settings['sms_api_secret'] ?? ''); ?>" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">From Number</label>
                        <input type="text" name="sms_from" value="<?php echo htmlspecialchars($sms_settings['sms_from'] ?? ''); ?>" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="sms_enabled" id="sms_enabled" <?php echo ($sms_settings['sms_enabled'] ?? false) ? 'checked' : ''; ?> class="w-4 h-4 cursor-pointer">
                    <label for="sms_enabled" class="text-gray-700 font-semibold cursor-pointer">Enable SMS Service</label>
                </div>

                <div class="flex gap-4 pt-4">
                    <button type="submit" class="bg-orange-500 text-white px-6 py-2 rounded font-medium hover:bg-orange-600">Save SMS Settings</button>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <!-- Security Settings -->
        <?php if ($tab === 'security'): ?>
        <div class="bg-white rounded-lg shadow-sm p-8">
            <h2 class="text-2xl font-bold text-blue-900 mb-6">Security Settings</h2>
            <form method="POST" class="space-y-6">
                <input type="hidden" name="action" value="update_security">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Minimum Password Length</label>
                        <input type="number" name="password_min_length" value="<?php echo htmlspecialchars($security_settings['password_min_length'] ?? '8'); ?>" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500" min="4">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Session Timeout (minutes)</label>
                        <input type="number" name="session_timeout" value="<?php echo htmlspecialchars($security_settings['session_timeout'] ?? '30'); ?>" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Max Login Attempts</label>
                        <input type="number" name="max_login_attempts" value="<?php echo htmlspecialchars($security_settings['max_login_attempts'] ?? '5'); ?>" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                    </div>
                </div>

                <div class="space-y-3">
                    <h3 class="font-semibold text-gray-800">Password Requirements</h3>
                    
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="password_require_uppercase" id="password_require_uppercase" <?php echo ($security_settings['password_require_uppercase'] ?? false) ? 'checked' : ''; ?> class="w-4 h-4 cursor-pointer">
                        <label for="password_require_uppercase" class="text-gray-700 cursor-pointer">Require uppercase letters</label>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="password_require_numbers" id="password_require_numbers" <?php echo ($security_settings['password_require_numbers'] ?? false) ? 'checked' : ''; ?> class="w-4 h-4 cursor-pointer">
                        <label for="password_require_numbers" class="text-gray-700 cursor-pointer">Require numbers</label>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="password_require_special" id="password_require_special" <?php echo ($security_settings['password_require_special'] ?? false) ? 'checked' : ''; ?> class="w-4 h-4 cursor-pointer">
                        <label for="password_require_special" class="text-gray-700 cursor-pointer">Require special characters</label>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="enable_two_factor" id="enable_two_factor" <?php echo ($security_settings['enable_two_factor'] ?? false) ? 'checked' : ''; ?> class="w-4 h-4 cursor-pointer">
                        <label for="enable_two_factor" class="text-gray-700 cursor-pointer">Enable Two-Factor Authentication</label>
                    </div>
                </div>

                <div class="flex gap-4 pt-4">
                    <button type="submit" class="bg-orange-500 text-white px-6 py-2 rounded font-medium hover:bg-orange-600">Save Security Settings</button>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <!-- System Status -->
        <?php if ($tab === 'system'): ?>
        <div class="bg-white rounded-lg shadow-sm p-8">
            <h2 class="text-2xl font-bold text-blue-900 mb-6">System Status & Health</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <!-- System Status -->
                <div class="border border-gray-200 rounded-lg p-6">
                    <h3 class="font-bold text-lg text-gray-800 mb-4">System Health</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-700">Status</span>
                            <span class="<?php echo ($system_health['status'] ?? 'unknown') === 'healthy' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?> text-xs px-2 py-1 rounded">
                                <?php echo ucfirst($system_health['status'] ?? 'unknown'); ?>
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-700">Database</span>
                            <span class="<?php echo ($system_health['checks']['database'] ?? '') === 'healthy' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?> text-xs px-2 py-1 rounded">
                                <?php echo ucfirst($system_health['checks']['database'] ?? 'unknown'); ?>
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-700">Disk Space</span>
                            <span class="<?php echo ($system_health['checks']['disk_space'] ?? '') === 'healthy' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?> text-xs px-2 py-1 rounded">
                                <?php echo $system_health['disk_usage'] ?? 'N/A'; ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Server Info -->
                <div class="border border-gray-200 rounded-lg p-6">
                    <h3 class="font-bold text-lg text-gray-800 mb-4">Server Information</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-700">PHP Version</span>
                            <span class="font-semibold"><?php echo $system_health['php_version'] ?? 'N/A'; ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-700">Memory Limit</span>
                            <span class="font-semibold"><?php echo $system_health['memory_limit'] ?? 'N/A'; ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-700">Server Software</span>
                            <span class="font-semibold"><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'N/A'; ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activity Logs -->
            <h3 class="font-bold text-lg text-gray-800 mb-4">Recent Activity Logs</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-blue-900 text-white">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">User</th>
                            <th class="px-4 py-3 text-left font-semibold">Action</th>
                            <th class="px-4 py-3 text-left font-semibold">Description</th>
                            <th class="px-4 py-3 text-left font-semibold">IP Address</th>
                            <th class="px-4 py-3 text-left font-semibold">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($activity_logs['logs'])): ?>
                            <?php foreach ($activity_logs['logs'] as $log): ?>
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="px-4 py-3"><?php echo htmlspecialchars($log['user_id'] ?? 'System'); ?></td>
                                    <td class="px-4 py-3 font-semibold"><?php echo htmlspecialchars($log['action'] ?? ''); ?></td>
                                    <td class="px-4 py-3"><?php echo htmlspecialchars($log['description'] ?? ''); ?></td>
                                    <td class="px-4 py-3 text-sm text-gray-600"><?php echo htmlspecialchars($log['ip_address'] ?? 'N/A'); ?></td>
                                    <td class="px-4 py-3"><?php echo date('Y-m-d H:i', strtotime($log['created_at'] ?? 'now')); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">No activity logs</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
