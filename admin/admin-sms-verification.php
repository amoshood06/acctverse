<?php
session_start();
require_once "../db/db.php";
require_once "../flash.php";

// Admin authentication check
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    set_flash("error", "Unauthorized access. Please login as admin.");
    header("Location: ../login.php");
    exit;
}

$flash = get_flash();

// Fetch all services
$stmt = $pdo->query("SELECT * FROM sms_services ORDER BY country, service_name");
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMS Verification Services - AcctGlobe Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
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
                    <a href="index.php" class="text-gray-300 hover:text-orange-500">Dashboard</a>
                    <a href="admin-users.php" class="text-gray-300 hover:text-orange-500">Users</a>
                    <a href="admin-sms-orders.php" class="text-gray-300 hover:text-orange-500">SMS Orders</a>
                    <a href="admin-sms-verification.php" class="text-orange-500 font-medium">SMS Services</a>
                    <a href="manage-products.php" class="text-gray-300 hover:text-orange-500">Products</a>
                </div>
                <a href="../logout.php" class="bg-orange-500 text-white px-4 py-2 rounded font-medium hover:bg-orange-600">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-blue-900">SMS Verification Services</h1>
                <p class="text-gray-600 mt-2">Manage SMS verification service offerings and pricing</p>
            </div>
            <a href="#add-service-form" class="bg-orange-500 text-white px-6 py-2 rounded-lg font-semibold hover:bg-orange-600">+ Add New Service</a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Add/Edit Service Form -->
            <div class="lg:col-span-2" id="add-service-form">
                <div class="bg-white rounded-lg shadow-sm p-8">
                    <h2 class="text-2xl font-bold text-blue-900 mb-6">Add SMS Verification Service</h2>
                    <form action="process-add-sms-service.php" method="POST">
                        <!-- Service Name & Country -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-blue-900 font-semibold mb-2">Service Name <span class="text-red-500">*</span></label>
                                <input type="text" name="service_name" placeholder="e.g., USA SMS Code" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500" required>
                            </div>
                            <div>
                                <label class="block text-blue-900 font-semibold mb-2">Country <span class="text-red-500">*</span></label>
                                <select name="country" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500" required>
                                    <option>Select Country</option>
                                    <option>USA</option>
                                    <option>UK</option>
                                    <option>Canada</option>
                                    <option>Australia</option>
                                    <option>Germany</option>
                                    <option>France</option>
                                    <option>India</option>
                                    <option>Nigeria</option>
                                </select>
                            </div>
                        </div>

                        <!-- Service Provider & Country Code -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-blue-900 font-semibold mb-2">Service Provider <span class="text-red-500">*</span></label>
                                <input type="text" name="service_provider" placeholder="e.g., Twilio, AWS SNS" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500" required>
                            </div>
                            <div>
                                <label class="block text-blue-900 font-semibold mb-2">Country Code <span class="text-red-500">*</span></label>
                                <input type="text" name="country_code" placeholder="e.g., +1" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500" required>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-6">
                            <label class="block text-blue-900 font-semibold mb-2">Description</label>
                            <textarea name="description" placeholder="Service description and features" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 h-20"></textarea>
                        </div>

                        <!-- Price & Limits -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            <div>
                                <label class="block text-blue-900 font-semibold mb-2">Price per SMS (₦) <span class="text-red-500">*</span></label>
                                <input type="number" name="price_per_sms" placeholder="0.00" step="0.01" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500" required>
                            </div>
                            <div>
                                <label class="block text-blue-900 font-semibold mb-2">Min SMS Per Order</label>
                                <input type="number" name="min_sms_per_order" placeholder="1" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                            </div>
                            <div>
                                <label class="block text-blue-900 font-semibold mb-2">Max SMS Per Order</label>
                                <input type="number" name="max_sms_per_order" placeholder="No limit" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                            </div>
                        </div>

                        <!-- Delivery Time & Availability -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-blue-900 font-semibold mb-2">Avg Delivery Time (minutes)</label>
                                <input type="number" name="avg_delivery_time" placeholder="5" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                            </div>
                            <div>
                                <label class="block text-blue-900 font-semibold mb-2">Availability</label>
                                <select name="availability" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                                    <option>24/7 Available</option>
                                    <option>Business Hours</option>
                                    <option>Limited Time</option>
                                </select>
                            </div>
                        </div>

                        <!-- Stock/Credits -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-blue-900 font-semibold mb-2">Available Credits <span class="text-red-500">*</span></label>
                                <input type="number" name="available_credits" placeholder="0" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500" required>
                            </div>
                            <div>
                                <label class="block text-blue-900 font-semibold mb-2">Restock Alert Level</label>
                                <input type="number" name="restock_alert_level" placeholder="100" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                            </div>
                        </div>

                        <!-- Status & Active Toggle -->
                        <div class="mb-6">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" class="w-4 h-4 border border-gray-300 rounded cursor-pointer" checked>
                                <span class="text-blue-900 font-semibold">Activate this service immediately</span>
                            </label>
                        </div>

                        <!-- Bulk Discount -->
                        <div class="border-t border-gray-200 pt-6 mb-6">
                            <h3 class="text-lg font-semibold text-blue-900 mb-4">Bulk Discount Configuration</h3>
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <p class="text-gray-500 col-span-3">Bulk discount configuration is not yet implemented.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-4">
                            <button type="submit" class="flex-1 bg-orange-500 text-white font-semibold py-3 rounded-lg hover:bg-orange-600 transition">Add Service</button>
                            <button type="reset" class="flex-1 border border-gray-300 text-blue-900 font-semibold py-3 rounded-lg hover:bg-gray-50 transition">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Active Services List -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-xl font-bold text-blue-900 mb-4">Active Services</h2>
                    <div class="space-y-4 max-h-[1200px] overflow-y-auto">
                        <?php if (empty($services)): ?>
                            <p class="text-gray-500">No services have been added yet.</p>
                        <?php else: ?>
                            <?php foreach ($services as $service): ?>
                                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                                    <div class="flex items-start justify-between mb-2">
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-blue-900"><?= htmlspecialchars($service['service_name']) ?></h3>
                                            <p class="text-sm text-gray-600"><?= htmlspecialchars($service['country_code']) ?></p>
                                        </div>
                                        <?php
                                            $statusClass = 'bg-green-100 text-green-800';
                                            $statusText = 'Active';
                                            if (!$service['is_active']) {
                                                $statusClass = 'bg-gray-200 text-gray-800';
                                                $statusText = 'Inactive';
                                            } elseif ($service['available_credits'] <= ($service['restock_alert_level'] ?? 10)) {
                                                $statusClass = 'bg-yellow-100 text-yellow-800';
                                                $statusText = 'Low Stock';
                                            }
                                        ?>
                                        <span class="<?= $statusClass ?> text-xs font-semibold px-2 py-1 rounded"><?= $statusText ?></span>
                                    </div>
                                    <div class="mb-3">
                                        <p class="text-sm text-gray-700 mb-1"><span class="font-semibold">Price:</span> ₦<?= number_format($service['price_per_sms'], 2) ?>/SMS</p>
                                        <p class="text-sm text-gray-700"><span class="font-semibold">Credits:</span> <?= number_format($service['available_credits']) ?></p>
                                    </div>
                                    <div class="flex gap-2">
                                        <a href="edit-sms-service.php?id=<?= $service['id'] ?>" class="flex-1 text-center bg-blue-50 text-blue-700 text-sm font-medium py-1 rounded hover:bg-blue-100">Edit</a>
                                        <a href="delete-sms-service.php?id=<?= $service['id'] ?>" onclick="return confirm('Are you sure you want to delete this service? This action cannot be undone.')" class="flex-1 text-center bg-red-50 text-red-700 text-sm font-medium py-1 rounded hover:bg-red-100">Delete</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
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
</body>
</html>
