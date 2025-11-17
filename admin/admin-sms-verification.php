<?php
// filepath: c:\xampp\htdocs\acctverse\admin\admin-sms-verification.php
session_start();
require_once '../db/db.php';

// Check if admin is logged in
// if (!isset($_SESSION['admin_id'])) { header('Location: admin-login.php'); exit; }

$pdo = get_pdo();
$message = '';
$message_type = '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Create SMS services table if not exists
function create_sms_services_table($pdo) {
    try {
        $pdo->query("
            CREATE TABLE IF NOT EXISTS sms_services (
                id INT PRIMARY KEY AUTO_INCREMENT,
                service_name VARCHAR(255) NOT NULL,
                service_code VARCHAR(50) UNIQUE NOT NULL,
                description TEXT,
                category VARCHAR(100),
                price DECIMAL(10, 2) NOT NULL,
                currency VARCHAR(3) DEFAULT 'NGN',
                is_active BOOLEAN DEFAULT 1,
                image_url VARCHAR(255),
                api_endpoint VARCHAR(255),
                api_key VARCHAR(255),
                success_rate DECIMAL(5, 2) DEFAULT 0,
                total_orders INT DEFAULT 0,
                completed_orders INT DEFAULT 0,
                failed_orders INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_service_code (service_code),
                INDEX idx_is_active (is_active),
                INDEX idx_created_at (created_at)
            )
        ");

        $pdo->query("
            CREATE TABLE IF NOT EXISTS sms_orders (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                service_id INT NOT NULL,
                phone_number VARCHAR(20) NOT NULL,
                sms_code VARCHAR(10),
                status ENUM('pending', 'processing', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
                amount DECIMAL(10, 2) NOT NULL,
                currency VARCHAR(3) DEFAULT 'NGN',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (service_id) REFERENCES sms_services(id) ON DELETE CASCADE,
                INDEX idx_user_id (user_id),
                INDEX idx_service_id (service_id),
                INDEX idx_status (status),
                INDEX idx_created_at (created_at)
            )
        ");

        return ['success' => true];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// Get all SMS services
function get_all_sms_services($pdo, $page = 1, $limit = 10) {
    try {
        $offset = ($page - 1) * $limit;
        
        $stmt = $pdo->query("
            SELECT * FROM sms_services 
            ORDER BY created_at DESC 
            LIMIT $offset, $limit
        ");
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $countStmt = $pdo->query("SELECT COUNT(*) as total FROM sms_services");
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        return [
            'services' => $services,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    } catch (Exception $e) {
        return ['services' => [], 'total' => 0, 'page' => 1, 'limit' => $limit, 'pages' => 0];
    }
}

// Get SMS service by ID
function get_sms_service_by_id($pdo, $service_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM sms_services WHERE id = ?");
        $stmt->execute([$service_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return null;
    }
}

// Create SMS service
function create_sms_service($pdo, $data) {
    try {
        if (empty($data['service_name']) || empty($data['service_code']) || empty($data['price'])) {
            return ['success' => false, 'message' => 'Service name, code, and price are required'];
        }

        // Check if service code already exists
        $stmt = $pdo->prepare("SELECT id FROM sms_services WHERE service_code = ?");
        $stmt->execute([$data['service_code']]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Service code already exists'];
        }

        $stmt = $pdo->prepare("
            INSERT INTO sms_services (
                service_name, service_code, description, category, price, 
                currency, image_url, api_endpoint, api_key, is_active
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $result = $stmt->execute([
            $data['service_name'],
            $data['service_code'],
            $data['description'] ?? null,
            $data['category'] ?? null,
            (float)$data['price'],
            $data['currency'] ?? 'NGN',
            $data['image_url'] ?? null,
            $data['api_endpoint'] ?? null,
            $data['api_key'] ?? null,
            $data['is_active'] ?? 1
        ]);

        if ($result) {
            return [
                'success' => true,
                'message' => 'SMS service created successfully',
                'service_id' => $pdo->lastInsertId()
            ];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// Update SMS service
function update_sms_service($pdo, $service_id, $data) {
    try {
        $service = get_sms_service_by_id($pdo, $service_id);
        if (!$service) {
            return ['success' => false, 'message' => 'Service not found'];
        }

        $updateFields = [];
        $params = [];

        $allowed_fields = ['service_name', 'description', 'category', 'price', 'currency', 
                          'image_url', 'api_endpoint', 'api_key', 'is_active'];

        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                $updateFields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($updateFields)) {
            return ['success' => false, 'message' => 'No fields to update'];
        }

        $params[] = $service_id;
        $query = "UPDATE sms_services SET " . implode(', ', $updateFields) . ", updated_at = NOW() WHERE id = ?";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);

        return ['success' => true, 'message' => 'Service updated successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// Delete SMS service
function delete_sms_service($pdo, $service_id) {
    try {
        $service = get_sms_service_by_id($pdo, $service_id);
        if (!$service) {
            return ['success' => false, 'message' => 'Service not found'];
        }

        $stmt = $pdo->prepare("DELETE FROM sms_services WHERE id = ?");
        $stmt->execute([$service_id]);

        return ['success' => true, 'message' => 'Service deleted successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// Get SMS statistics
function get_sms_stats($pdo) {
    try {
        $stats = [];

        // Total services
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM sms_services");
        $stats['total_services'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        // Active services
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM sms_services WHERE is_active = 1");
        $stats['active_services'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        // Total orders
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM sms_orders");
        $stats['total_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        // Completed orders
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM sms_orders WHERE status = 'completed'");
        $stats['completed_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        // Total revenue
        $stmt = $pdo->query("SELECT SUM(amount) as total FROM sms_orders WHERE status = 'completed'");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_revenue'] = $result['total'] ?? 0;

        return $stats;
    } catch (Exception $e) {
        return [];
    }
}

// Handle form submissions
create_sms_services_table($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_service') {
        $image_url = '';
        if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
            $target_dir = "../uploads/sms-services/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            
            $target_file = $target_dir . time() . '_' . basename($_FILES['image']['name']);
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image_url = str_replace('../', '', $target_file);
            }
        }

        $result = create_sms_service($pdo, [
            'service_name' => $_POST['service_name'] ?? '',
            'service_code' => $_POST['service_code'] ?? '',
            'description' => $_POST['description'] ?? '',
            'category' => $_POST['category'] ?? '',
            'price' => $_POST['price'] ?? 0,
            'currency' => $_POST['currency'] ?? 'NGN',
            'image_url' => $image_url,
            'api_endpoint' => $_POST['api_endpoint'] ?? '',
            'api_key' => $_POST['api_key'] ?? '',
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ]);

        $message = $result['message'];
        $message_type = $result['success'] ? 'success' : 'error';
    }

    if ($action === 'update_service' && isset($_POST['service_id'])) {
        $service_id = (int)$_POST['service_id'];
        $service = get_sms_service_by_id($pdo, $service_id);

        $image_url = $service['image_url'];
        if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
            $target_dir = "../uploads/sms-services/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            
            $target_file = $target_dir . time() . '_' . basename($_FILES['image']['name']);
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image_url = str_replace('../', '', $target_file);
            }
        }

        $result = update_sms_service($pdo, $service_id, [
            'service_name' => $_POST['service_name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'category' => $_POST['category'] ?? '',
            'price' => $_POST['price'] ?? 0,
            'currency' => $_POST['currency'] ?? 'NGN',
            'image_url' => $image_url,
            'api_endpoint' => $_POST['api_endpoint'] ?? '',
            'api_key' => $_POST['api_key'] ?? '',
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ]);

        $message = $result['message'];
        $message_type = $result['success'] ? 'success' : 'error';
    }

    if ($action === 'delete_service' && isset($_POST['service_id'])) {
        $result = delete_sms_service($pdo, (int)$_POST['service_id']);
        $message = $result['message'];
        $message_type = $result['success'] ? 'success' : 'error';
    }
}

// Get data
$stats = get_sms_stats($pdo);
$services_data = get_all_sms_services($pdo, $page);
$view_service = isset($_GET['edit']) ? get_sms_service_by_id($pdo, (int)$_GET['edit']) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMS Verification Management - AcctGlobe Admin</title>
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
                    <a href="admin-sms-verification.php" class="text-orange-500 font-medium">SMS Services</a>
                    <a href="admin-users.php" class="text-gray-300 hover:text-orange-500">Users</a>
                </div>
                <a href="admin-logout.php" class="bg-orange-500 text-white px-4 py-2 rounded font-medium hover:bg-orange-600">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-blue-900 mb-8">SMS Verification Services</h1>

        <!-- Messages -->
        <?php if ($message): ?>
            <div class="mb-8 p-4 rounded-lg <?php echo $message_type === 'success' ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
                <p class="text-gray-600 text-sm mb-2">Total Services</p>
                <h3 class="text-3xl font-bold text-blue-900"><?php echo number_format($stats['total_services'] ?? 0); ?></h3>
                <p class="text-gray-500 text-xs mt-2">Active: <?php echo $stats['active_services'] ?? 0; ?></p>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
                <p class="text-gray-600 text-sm mb-2">Total Orders</p>
                <h3 class="text-3xl font-bold text-green-600"><?php echo number_format($stats['total_orders'] ?? 0); ?></h3>
                <p class="text-gray-500 text-xs mt-2">Completed: <?php echo $stats['completed_orders'] ?? 0; ?></p>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-purple-500">
                <p class="text-gray-600 text-sm mb-2">Total Revenue</p>
                <h3 class="text-3xl font-bold text-purple-600">₦<?php echo number_format($stats['total_revenue'] ?? 0, 2); ?></h3>
                <p class="text-gray-500 text-xs mt-2">NGN</p>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-orange-500">
                <p class="text-gray-600 text-sm mb-2">Success Rate</p>
                <h3 class="text-3xl font-bold text-orange-600"><?php echo $stats['total_orders'] > 0 ? round(($stats['completed_orders'] / $stats['total_orders']) * 100, 2) : 0; ?>%</h3>
                <p class="text-gray-500 text-xs mt-2">Completion rate</p>
            </div>
        </div>

        <!-- Edit Service View -->
        <?php if ($view_service): ?>
            <div class="mb-8">
                <a href="admin-sms-verification.php" class="text-blue-600 hover:text-blue-900 mb-4 inline-block">&larr; Back to Services</a>

                <div class="bg-white rounded-lg shadow-sm p-8">
                    <h2 class="text-2xl font-bold text-blue-900 mb-6">Edit Service: <?php echo htmlspecialchars($view_service['service_name']); ?></h2>

                    <form method="POST" enctype="multipart/form-data" class="space-y-6">
                        <input type="hidden" name="action" value="update_service">
                        <input type="hidden" name="service_id" value="<?php echo $view_service['id']; ?>">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">Service Name *</label>
                                <input type="text" name="service_name" value="<?php echo htmlspecialchars($view_service['service_name']); ?>" required class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                            </div>

                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">Service Code (Read-only)</label>
                                <input type="text" value="<?php echo htmlspecialchars($view_service['service_code']); ?>" disabled class="w-full border border-gray-300 rounded px-4 py-2 bg-gray-100">
                            </div>

                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">Category</label>
                                <input type="text" name="category" value="<?php echo htmlspecialchars($view_service['category'] ?? ''); ?>" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500" placeholder="e.g., Dating, Social Media">
                            </div>

                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">Price (₦) *</label>
                                <input type="number" name="price" value="<?php echo $view_service['price']; ?>" step="0.01" required class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Description</label>
                            <textarea name="description" rows="4" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500" placeholder="Describe this SMS service..."><?php echo htmlspecialchars($view_service['description'] ?? ''); ?></textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">API Endpoint</label>
                                <input type="url" name="api_endpoint" value="<?php echo htmlspecialchars($view_service['api_endpoint'] ?? ''); ?>" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500" placeholder="https://api.example.com/sms">
                            </div>

                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">API Key</label>
                                <input type="text" name="api_key" value="<?php echo htmlspecialchars($view_service['api_key'] ?? ''); ?>" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500" placeholder="Your API key">
                            </div>
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Service Image</label>
                            <?php if ($view_service['image_url']): ?>
                                <div class="mb-4">
                                    <img src="<?php echo htmlspecialchars($view_service['image_url']); ?>" alt="Service Image" class="h-32 w-32 object-cover rounded border border-gray-300">
                                </div>
                            <?php endif; ?>
                            <input type="file" name="image" accept="image/*" class="w-full border border-gray-300 rounded px-4 py-2">
                            <p class="text-gray-500 text-xs mt-2">Max 5MB, recommended 400x400px</p>
                        </div>

                        <div>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="is_active" <?php echo $view_service['is_active'] ? 'checked' : ''; ?> class="rounded">
                                <span class="text-gray-700 font-semibold">Service is Active</span>
                            </label>
                        </div>

                        <div class="flex gap-4">
                            <button type="submit" class="bg-orange-500 text-white px-6 py-2 rounded font-medium hover:bg-orange-600">Save Changes</button>
                            <a href="admin-sms-verification.php" class="bg-gray-500 text-white px-6 py-2 rounded font-medium hover:bg-gray-600">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <!-- Create New Service Button -->
            <div class="mb-8">
                <button onclick="toggleCreateForm()" class="bg-blue-600 text-white px-6 py-2 rounded font-medium hover:bg-blue-700">+ Add New Service</button>
            </div>

            <!-- Create Service Form -->
            <div id="createForm" class="hidden bg-white rounded-lg shadow-sm p-8 mb-8">
                <h2 class="text-2xl font-bold text-blue-900 mb-6">Add New SMS Service</h2>

                <form method="POST" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="action" value="create_service">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Service Name *</label>
                            <input type="text" name="service_name" required class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500" placeholder="e.g., Plenty of Fish, WhatsApp, etc.">
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Service Code *</label>
                            <input type="text" name="service_code" required class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500" placeholder="e.g., POF, WHATSAPP, TINDER">
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Category</label>
                            <input type="text" name="category" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500" placeholder="e.g., Dating, Social Media">
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Price (₦) *</label>
                            <input type="number" name="price" step="0.01" required class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500" placeholder="0.00">
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Description</label>
                        <textarea name="description" rows="4" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500" placeholder="Describe this SMS service..."></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">API Endpoint</label>
                            <input type="url" name="api_endpoint" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500" placeholder="https://api.example.com/sms">
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">API Key</label>
                            <input type="text" name="api_key" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500" placeholder="Your API key">
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Service Image</label>
                        <input type="file" name="image" accept="image/*" class="w-full border border-gray-300 rounded px-4 py-2">
                        <p class="text-gray-500 text-xs mt-2">Max 5MB, recommended 400x400px</p>
                    </div>

                    <div>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_active" checked class="rounded">
                            <span class="text-gray-700 font-semibold">Service is Active</span>
                        </label>
                    </div>

                    <div class="flex gap-4">
                        <button type="submit" class="bg-orange-500 text-white px-6 py-2 rounded font-medium hover:bg-orange-600">Create Service</button>
                        <button type="button" onclick="toggleCreateForm()" class="bg-gray-500 text-white px-6 py-2 rounded font-medium hover:bg-gray-600">Cancel</button>
                    </div>
                </form>
            </div>

            <!-- Services Table -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-blue-900 text-white">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold">Service Name</th>
                                <th class="px-4 py-3 text-left font-semibold">Code</th>
                                <th class="px-4 py-3 text-left font-semibold">Category</th>
                                <th class="px-4 py-3 text-left font-semibold">Price</th>
                                <th class="px-4 py-3 text-left font-semibold">Orders</th>
                                <th class="px-4 py-3 text-left font-semibold">Status</th>
                                <th class="px-4 py-3 text-left font-semibold">Created</th>
                                <th class="px-4 py-3 text-left font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($services_data['services'])): ?>
                                <?php foreach ($services_data['services'] as $service): ?>
                                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                                        <td class="px-4 py-3 font-semibold text-blue-600">
                                            <?php if ($service['image_url']): ?>
                                                <div class="flex items-center gap-2">
                                                    <img src="<?php echo htmlspecialchars($service['image_url']); ?>" alt="Service" class="w-8 h-8 rounded object-cover">
                                                    <span><?php echo htmlspecialchars($service['service_name']); ?></span>
                                                </div>
                                            <?php else: ?>
                                                <?php echo htmlspecialchars($service['service_name']); ?>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-3 text-sm"><?php echo htmlspecialchars($service['service_code']); ?></td>
                                        <td class="px-4 py-3 text-sm"><?php echo htmlspecialchars($service['category'] ?? '—'); ?></td>
                                        <td class="px-4 py-3 font-semibold">₦<?php echo number_format($service['price'], 2); ?></td>
                                        <td class="px-4 py-3 text-sm">
                                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">
                                                <?php echo $service['total_orders']; ?> orders
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="<?php echo $service['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?> text-xs px-2 py-1 rounded">
                                                <?php echo $service['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-gray-600 text-xs"><?php echo date('Y-m-d', strtotime($service['created_at'])); ?></td>
                                        <td class="px-4 py-3 text-sm">
                                            <a href="?edit=<?php echo $service['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                                            <form method="POST" class="inline">
                                                <input type="hidden" name="action" value="delete_service">
                                                <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                                                <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Delete this service?')">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="8" class="px-4 py-8 text-center text-gray-500">No SMS services available. Create one to get started.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($services_data['pages'] > 1): ?>
                    <div class="bg-gray-50 px-4 py-4 flex items-center justify-between border-t border-gray-200">
                        <span class="text-sm text-gray-600">Page <?php echo $services_data['page']; ?> of <?php echo $services_data['pages']; ?></span>
                        <div class="flex gap-2">
                            <?php if ($services_data['page'] > 1): ?>
                                <a href="?page=<?php echo $services_data['page'] - 1; ?>" class="px-3 py-2 border border-gray-300 rounded hover:bg-gray-100">Previous</a>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $services_data['pages']; $i++): ?>
                                <a href="?page=<?php echo $i; ?>" class="px-3 py-2 <?php echo $i === $services_data['page'] ? 'bg-orange-500 text-white' : 'border border-gray-300'; ?> rounded">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>

                            <?php if ($services_data['page'] < $services_data['pages']): ?>
                                <a href="?page=<?php echo $services_data['page'] + 1; ?>" class="px-3 py-2 border border-gray-300 rounded hover:bg-gray-100">Next</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
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