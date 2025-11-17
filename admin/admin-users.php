<?php
// filepath: c:\xampp\htdocs\acctverse\admin\admin-users.php
session_start();
require_once '../db/db.php';
require_once 'function/admin_users_function.php';

// Check if admin is logged in
// if (!isset($_SESSION['admin_id'])) { header('Location: admin-login.php'); exit; }

$pdo = get_pdo();
$message = '';
$message_type = '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$user_type = isset($_GET['user_type']) ? $_GET['user_type'] : '';
$account_status = isset($_GET['account_status']) ? $_GET['account_status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$view_user_id = isset($_GET['view']) ? (int)$_GET['view'] : 0;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_user') {
        $result = create_user($pdo, [
            'username' => $_POST['username'] ?? '',
            'email' => $_POST['email'] ?? '',
            'password' => $_POST['password'] ?? '',
            'full_name' => $_POST['full_name'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'user_type' => $_POST['user_type'] ?? 'customer'
        ]);
        $message = $result['message'];
        $message_type = $result['success'] ? 'success' : 'error';
    }

    if ($action === 'update_user' && $view_user_id > 0) {
        $result = update_user($pdo, $view_user_id, [
            'full_name' => $_POST['full_name'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'address' => $_POST['address'] ?? '',
            'city' => $_POST['city'] ?? '',
            'state' => $_POST['state'] ?? '',
            'country' => $_POST['country'] ?? '',
            'postal_code' => $_POST['postal_code'] ?? ''
        ]);
        $message = $result['message'];
        $message_type = $result['success'] ? 'success' : 'error';
    }

    if ($action === 'update_status' && $view_user_id > 0) {
        $new_status = $_POST['account_status'] ?? '';
        $result = update_user_account_status($pdo, $view_user_id, $new_status);
        $message = $result['message'];
        $message_type = $result['success'] ? 'success' : 'error';
    }

    if ($action === 'change_password' && $view_user_id > 0) {
        $new_password = $_POST['new_password'] ?? '';
        $result = change_user_password($pdo, $view_user_id, $new_password);
        $message = $result['message'];
        $message_type = $result['success'] ? 'success' : 'error';
    }

    if ($action === 'verify_email' && $view_user_id > 0) {
        $result = verify_user_email($pdo, $view_user_id);
        $message = $result['message'];
        $message_type = $result['success'] ? 'success' : 'error';
    }

    if ($action === 'export') {
        export_users_to_csv($pdo, $user_type, $account_status);
    }
}

// Get data
$stats = get_user_stats($pdo);
$result = get_all_users($pdo, $page, 10, $user_type, $account_status, $search);
$view_user = $view_user_id > 0 ? get_user_by_id($pdo, $view_user_id) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Management - AcctGlobe Admin</title>
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
                    <a href="admin-users.php" class="text-orange-500 font-medium">Users</a>
                    <a href="admin-orders.php" class="text-gray-300 hover:text-orange-500">Orders</a>
                </div>
                <a href="admin-logout.php" class="bg-orange-500 text-white px-4 py-2 rounded font-medium hover:bg-orange-600">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-blue-900 mb-8">Users Management</h1>

        <!-- Messages -->
        <?php if ($message): ?>
            <div class="mb-8 p-4 rounded-lg <?php echo $message_type === 'success' ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- User Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
                <p class="text-gray-600 text-sm mb-2">Total Users</p>
                <h3 class="text-3xl font-bold text-blue-900"><?php echo number_format($stats['total_users'] ?? 0); ?></h3>
                <p class="text-gray-500 text-xs mt-2">Active: <?php echo $stats['active_users'] ?? 0; ?></p>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
                <p class="text-gray-600 text-sm mb-2">Customers</p>
                <h3 class="text-3xl font-bold text-green-600"><?php echo number_format($stats['total_customers'] ?? 0); ?></h3>
                <p class="text-gray-500 text-xs mt-2">Total customers</p>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-purple-500">
                <p class="text-gray-600 text-sm mb-2">Vendors</p>
                <h3 class="text-3xl font-bold text-purple-600"><?php echo number_format($stats['total_vendors'] ?? 0); ?></h3>
                <p class="text-gray-500 text-xs mt-2">Total vendors</p>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-orange-500">
                <p class="text-gray-600 text-sm mb-2">Email Verified</p>
                <h3 class="text-3xl font-bold text-orange-600"><?php echo number_format($stats['email_verified'] ?? 0); ?></h3>
                <p class="text-gray-500 text-xs mt-2">Verified users</p>
            </div>
        </div>

        <!-- View Single User -->
        <?php if ($view_user): ?>
            <div class="mb-8">
                <a href="admin-users.php" class="text-blue-600 hover:text-blue-900 mb-4 inline-block">&larr; Back to Users</a>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- User Info -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Basic Info -->
                        <div class="bg-white rounded-lg shadow-sm p-8">
                            <h2 class="text-2xl font-bold text-blue-900 mb-6">User Information</h2>
                            
                            <form method="POST" class="space-y-4">
                                <input type="hidden" name="action" value="update_user">

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-gray-700 font-semibold mb-2">Username</label>
                                        <input type="text" value="<?php echo htmlspecialchars($view_user['username']); ?>" disabled class="w-full border border-gray-300 rounded px-4 py-2 bg-gray-100">
                                    </div>

                                    <div>
                                        <label class="block text-gray-700 font-semibold mb-2">Email</label>
                                        <input type="email" value="<?php echo htmlspecialchars($view_user['email']); ?>" disabled class="w-full border border-gray-300 rounded px-4 py-2 bg-gray-100">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-gray-700 font-semibold mb-2">Full Name</label>
                                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($view_user['full_name'] ?? ''); ?>" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-gray-700 font-semibold mb-2">Phone</label>
                                        <input type="tel" name="phone" value="<?php echo htmlspecialchars($view_user['phone'] ?? ''); ?>" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                                    </div>

                                    <div>
                                        <label class="block text-gray-700 font-semibold mb-2">Gender</label>
                                        <select disabled class="w-full border border-gray-300 rounded px-4 py-2 bg-gray-100">
                                            <option><?php echo htmlspecialchars($view_user['gender'] ?? 'Not specified'); ?></option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-gray-700 font-semibold mb-2">Address</label>
                                    <input type="text" name="address" value="<?php echo htmlspecialchars($view_user['address'] ?? ''); ?>" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-gray-700 font-semibold mb-2">City</label>
                                        <input type="text" name="city" value="<?php echo htmlspecialchars($view_user['city'] ?? ''); ?>" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                                    </div>

                                    <div>
                                        <label class="block text-gray-700 font-semibold mb-2">State</label>
                                        <input type="text" name="state" value="<?php echo htmlspecialchars($view_user['state'] ?? ''); ?>" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                                    </div>

                                    <div>
                                        <label class="block text-gray-700 font-semibold mb-2">Postal Code</label>
                                        <input type="text" name="postal_code" value="<?php echo htmlspecialchars($view_user['postal_code'] ?? ''); ?>" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-gray-700 font-semibold mb-2">Country</label>
                                    <input type="text" name="country" value="<?php echo htmlspecialchars($view_user['country'] ?? ''); ?>" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                                </div>

                                <button type="submit" class="bg-orange-500 text-white px-6 py-2 rounded font-medium hover:bg-orange-600">Update Information</button>
                            </form>
                        </div>

                        <!-- Change Password -->
                        <div class="bg-white rounded-lg shadow-sm p-8">
                            <h3 class="text-xl font-bold text-blue-900 mb-6">Change Password</h3>
                            <form method="POST" class="space-y-4">
                                <input type="hidden" name="action" value="change_password">

                                <div>
                                    <label class="block text-gray-700 font-semibold mb-2">New Password</label>
                                    <input type="password" name="new_password" placeholder="Enter new password (min 8 characters)" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                                </div>

                                <button type="submit" class="bg-red-500 text-white px-6 py-2 rounded font-medium hover:bg-red-600">Change Password</button>
                            </form>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Account Status -->
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <h3 class="font-bold text-lg text-blue-900 mb-4">Account Status</h3>
                            
                            <form method="POST" class="space-y-4">
                                <input type="hidden" name="action" value="update_status">

                                <div>
                                    <p class="text-gray-600 text-sm mb-2">Current Status</p>
                                    <span class="<?php 
                                        $status_color = match($view_user['account_status']) {
                                            'active' => 'bg-green-100 text-green-800',
                                            'inactive' => 'bg-gray-100 text-gray-800',
                                            'suspended' => 'bg-yellow-100 text-yellow-800',
                                            'banned' => 'bg-red-100 text-red-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        };
                                        echo $status_color;
                                    ?> text-sm px-3 py-1 rounded-full font-semibold">
                                        <?php echo ucfirst($view_user['account_status']); ?>
                                    </span>
                                </div>

                                <div>
                                    <label class="block text-gray-700 font-semibold mb-2">Update Status</label>
                                    <select name="account_status" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                                        <option value="active" <?php echo $view_user['account_status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                        <option value="inactive" <?php echo $view_user['account_status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                        <option value="suspended" <?php echo $view_user['account_status'] === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                                        <option value="banned" <?php echo $view_user['account_status'] === 'banned' ? 'selected' : ''; ?>>Banned</option>
                                    </select>
                                </div>

                                <button type="submit" class="w-full bg-orange-500 text-white px-4 py-2 rounded font-medium hover:bg-orange-600">Update Status</button>
                            </form>
                        </div>

                        <!-- User Details -->
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <h3 class="font-bold text-lg text-blue-900 mb-4">Details</h3>
                            
                            <div class="space-y-3">
                                <div>
                                    <p class="text-gray-600 text-sm">User ID</p>
                                    <p class="font-semibold"><?php echo $view_user['id']; ?></p>
                                </div>

                                <div>
                                    <p class="text-gray-600 text-sm">User Type</p>
                                    <p class="font-semibold capitalize"><?php echo htmlspecialchars($view_user['user_type']); ?></p>
                                </div>

                                <div>
                                    <p class="text-gray-600 text-sm">Email Status</p>
                                    <span class="<?php echo $view_user['email_verified'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?> text-xs px-2 py-1 rounded">
                                        <?php echo $view_user['email_verified'] ? 'Verified' : 'Not Verified'; ?>
                                    </span>
                                </div>

                                <div>
                                    <p class="text-gray-600 text-sm">Phone Verified</p>
                                    <span class="<?php echo $view_user['phone_verified'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?> text-xs px-2 py-1 rounded">
                                        <?php echo $view_user['phone_verified'] ? 'Yes' : 'No'; ?>
                                    </span>
                                </div>

                                <div>
                                    <p class="text-gray-600 text-sm">2FA Enabled</p>
                                    <span class="<?php echo $view_user['two_factor_enabled'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?> text-xs px-2 py-1 rounded">
                                        <?php echo $view_user['two_factor_enabled'] ? 'Yes' : 'No'; ?>
                                    </span>
                                </div>

                                <div>
                                    <p class="text-gray-600 text-sm">Last Login</p>
                                    <p class="font-semibold"><?php echo $view_user['last_login'] ? date('Y-m-d H:i', strtotime($view_user['last_login'])) : 'Never'; ?></p>
                                </div>

                                <div>
                                    <p class="text-gray-600 text-sm">Joined</p>
                                    <p class="font-semibold"><?php echo date('Y-m-d H:i', strtotime($view_user['created_at'])); ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Email Verification -->
                        <?php if (!$view_user['email_verified']): ?>
                            <div class="bg-yellow-50 rounded-lg shadow-sm p-6 border border-yellow-200">
                                <h3 class="font-bold text-lg text-yellow-900 mb-4">Email Not Verified</h3>
                                <form method="POST">
                                    <input type="hidden" name="action" value="verify_email">
                                    <button type="submit" class="w-full bg-yellow-500 text-white px-4 py-2 rounded font-medium hover:bg-yellow-600">Verify Email</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Users List -->
            <!-- Create New User Button -->
            <div class="mb-8">
                <button onclick="toggleCreateForm()" class="bg-blue-600 text-white px-6 py-2 rounded font-medium hover:bg-blue-700">+ Create New User</button>
            </div>

            <!-- Create User Form -->
            <div id="createForm" class="hidden bg-white rounded-lg shadow-sm p-8 mb-8">
                <h2 class="text-2xl font-bold text-blue-900 mb-6">Create New User</h2>
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="create_user">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Username *</label>
                            <input type="text" name="username" required class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Email *</label>
                            <input type="email" name="email" required class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Password *</label>
                            <input type="password" name="password" required class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Full Name</label>
                            <input type="text" name="full_name" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Phone</label>
                            <input type="tel" name="phone" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">User Type</label>
                            <select name="user_type" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                                <option value="customer">Customer</option>
                                <option value="vendor">Vendor</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <button type="submit" class="bg-orange-500 text-white px-6 py-2 rounded font-medium hover:bg-orange-600">Create User</button>
                        <button type="button" onclick="toggleCreateForm()" class="bg-gray-500 text-white px-6 py-2 rounded font-medium hover:bg-gray-600">Cancel</button>
                    </div>
                </form>
            </div>

            <!-- Search and Filter -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <input type="text" name="search" placeholder="Search by username, email, name..." value="<?php echo htmlspecialchars($search); ?>" class="border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                    
                    <select name="user_type" class="border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                        <option value="">All User Types</option>
                        <option value="customer" <?php echo $user_type === 'customer' ? 'selected' : ''; ?>>Customer</option>
                        <option value="vendor" <?php echo $user_type === 'vendor' ? 'selected' : ''; ?>>Vendor</option>
                        <option value="admin" <?php echo $user_type === 'admin' ? 'selected' : ''; ?>>Admin</option>
                    </select>

                    <select name="account_status" class="border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                        <option value="">All Status</option>
                        <option value="active" <?php echo $account_status === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo $account_status === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        <option value="suspended" <?php echo $account_status === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                        <option value="banned" <?php echo $account_status === 'banned' ? 'selected' : ''; ?>>Banned</option>
                    </select>

                    <div class="flex gap-2">
                        <button type="submit" class="bg-orange-500 text-white px-4 py-2 rounded font-medium hover:bg-orange-600 flex-1">Filter</button>
                        <form method="POST" class="inline">
                            <input type="hidden" name="action" value="export">
                            <input type="hidden" name="user_type" value="<?php echo htmlspecialchars($user_type); ?>">
                            <input type="hidden" name="account_status" value="<?php echo htmlspecialchars($account_status); ?>">
                            <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded font-medium hover:bg-green-600">Export CSV</button>
                        </form>
                    </div>
                </form>
            </div>

            <!-- Users Table -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-blue-900 text-white">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold">Username</th>
                                <th class="px-4 py-3 text-left font-semibold">Email</th>
                                <th class="px-4 py-3 text-left font-semibold">Full Name</th>
                                <th class="px-4 py-3 text-left font-semibold">Type</th>
                                <th class="px-4 py-3 text-left font-semibold">Status</th>
                                <th class="px-4 py-3 text-left font-semibold">Verified</th>
                                <th class="px-4 py-3 text-left font-semibold">Joined</th>
                                <th class="px-4 py-3 text-left font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($result['users'])): ?>
                                <?php foreach ($result['users'] as $user): ?>
                                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                                        <td class="px-4 py-3 font-semibold text-blue-600"><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td class="px-4 py-3 text-sm"><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td class="px-4 py-3"><?php echo htmlspecialchars($user['full_name'] ?? '—'); ?></td>
                                        <td class="px-4 py-3">
                                            <span class="<?php 
                                                $type_color = match($user['user_type']) {
                                                    'customer' => 'bg-blue-100 text-blue-800',
                                                    'vendor' => 'bg-purple-100 text-purple-800',
                                                    'admin' => 'bg-red-100 text-red-800',
                                                    default => 'bg-gray-100 text-gray-800'
                                                };
                                                echo $type_color;
                                            ?> text-xs px-2 py-1 rounded">
                                                <?php echo ucfirst($user['user_type']); ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="<?php 
                                                $status_color = match($user['account_status']) {
                                                    'active' => 'bg-green-100 text-green-800',
                                                    'inactive' => 'bg-gray-100 text-gray-800',
                                                    'suspended' => 'bg-yellow-100 text-yellow-800',
                                                    'banned' => 'bg-red-100 text-red-800',
                                                    default => 'bg-gray-100 text-gray-800'
                                                };
                                                echo $status_color;
                                            ?> text-xs px-2 py-1 rounded">
                                                <?php echo ucfirst($user['account_status']); ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            <span class="<?php echo $user['email_verified'] ? 'text-green-600' : 'text-red-600'; ?>">
                                                <?php echo $user['email_verified'] ? '✓ Yes' : '✗ No'; ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-xs text-gray-600"><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                                        <td class="px-4 py-3 text-sm">
                                            <a href="?view=<?php echo $user['id']; ?>" class="text-blue-600 hover:text-blue-900 font-medium">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="8" class="px-4 py-8 text-center text-gray-500">No users found</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="bg-gray-50 px-4 py-4 flex items-center justify-between border-t border-gray-200">
                    <span class="text-sm text-gray-600">Showing <?php echo ($result['page'] - 1) * $result['limit'] + 1; ?> to <?php echo min($result['page'] * $result['limit'], $result['total']); ?> of <?php echo $result['total']; ?> users</span>
                    <div class="flex gap-2">
                        <?php if ($result['page'] > 1): ?>
                            <a href="?page=<?php echo $result['page'] - 1; ?>&user_type=<?php echo urlencode($user_type); ?>&account_status=<?php echo urlencode($account_status); ?>&search=<?php echo urlencode($search); ?>" class="px-3 py-2 border border-gray-300 rounded hover:bg-gray-100">Previous</a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $result['pages']; $i++): ?>
                            <a href="?page=<?php echo $i; ?>&user_type=<?php echo urlencode($user_type); ?>&account_status=<?php echo urlencode($account_status); ?>&search=<?php echo urlencode($search); ?>" class="px-3 py-2 <?php echo $i === $result['page'] ? 'bg-orange-500 text-white' : 'border border-gray-300'; ?> rounded">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($result['page'] < $result['pages']): ?>
                            <a href="?page=<?php echo $result['page'] + 1; ?>&user_type=<?php echo urlencode($user_type); ?>&account_status=<?php echo urlencode($account_status); ?>&search=<?php echo urlencode($search); ?>" class="px-3 py-2 border border-gray-300 rounded hover:bg-gray-100">Next</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function toggleCreateForm() {
            const form = document.getElementById('createForm');
            form.classList.toggle('hidden');
        }
    </script>
</body>
</html>
