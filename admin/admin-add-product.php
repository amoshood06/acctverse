<?php
// filepath: c:\xampp\htdocs\acctverse\admin\admin-add-product.php
session_start();
require_once '../db/db.php';
require_once 'function/admin_add_product_function.php';

// Check if admin is logged in
// if (!isset($_SESSION['admin_id'])) { header('Location: admin-login.php'); exit; }

$pdo = get_pdo();
$message = '';
$product_id = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => $_POST['name'] ?? '',
        'description' => $_POST['description'] ?? '',
        'price' => $_POST['price'] ?? 0,
        'stock' => $_POST['stock'] ?? 0,
        'category' => $_POST['category'] ?? '',
        'sku' => $_POST['sku'] ?? '',
        'status' => $_POST['status'] ?? 'active',
        'created_by' => $_SESSION['user_id'] ?? null
    ];

    // Handle image upload
    if (isset($_FILES['image'])) {
        $upload_result = upload_product_image($_FILES['image']);
        if ($upload_result['success']) {
            $data['image_url'] = $upload_result['filename'];
        } else {
            $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">' . $upload_result['message'] . '</div>';
        }
    }

    // Add product
    if (empty($message)) {
        $result = add_product($pdo, $data);
        if ($result['success']) {
            $product_id = $result['product_id'];
            $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">' . $result['message'] . ' (ID: ' . $product_id . ')</div>';
            // Clear form
            $_POST = [];
        } else {
            $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">' . $result['message'] . '</div>';
        }
    }
}

$categories = get_product_categories($pdo);
$stats = get_product_stats($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - AcctGlobe Admin</title>
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
                    <a href="admin-products.php" class="text-orange-500 font-medium">Products</a>
                    <a href="admin-transactions.php" class="text-gray-300 hover:text-orange-500">Transactions</a>
                </div>
                <a href="admin-logout.php" class="bg-orange-500 text-white px-4 py-2 rounded font-medium hover:bg-orange-600">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-bold text-blue-900">Add New Product</h1>
            <a href="admin-products.php" class="text-blue-600 hover:text-blue-900">← Back to Products</a>
        </div>

        <!-- Product Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-lg shadow-sm p-4">
                <p class="text-gray-600 text-sm">Total Products</p>
                <h3 class="text-2xl font-bold text-blue-900"><?php echo $stats['total_products']; ?></h3>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4">
                <p class="text-gray-600 text-sm">Active Products</p>
                <h3 class="text-2xl font-bold text-green-600"><?php echo $stats['active_products']; ?></h3>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4">
                <p class="text-gray-600 text-sm">Out of Stock</p>
                <h3 class="text-2xl font-bold text-red-600"><?php echo $stats['out_of_stock']; ?></h3>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4">
                <p class="text-gray-600 text-sm">Inventory Value</p>
                <h3 class="text-2xl font-bold text-orange-600">₦<?php echo number_format($stats['inventory_value'], 2); ?></h3>
            </div>
        </div>

        <!-- Messages -->
        <?php if ($message): echo $message; endif; ?>

        <!-- Add Product Form -->
        <div class="bg-white rounded-lg shadow-sm p-8">
            <form method="POST" enctype="multipart/form-data">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Product Name -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Product Name *</label>
                        <input type="text" name="name" required class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500" placeholder="Enter product name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                    </div>

                    <!-- SKU -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">SKU</label>
                        <input type="text" name="sku" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500" placeholder="Enter product SKU" value="<?php echo htmlspecialchars($_POST['sku'] ?? ''); ?>">
                    </div>

                    <!-- Price -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Price (₦) *</label>
                        <input type="number" name="price" step="0.01" required class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500" placeholder="0.00" value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>">
                    </div>

                    <!-- Stock -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Stock Quantity</label>
                        <input type="number" name="stock" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500" placeholder="0" value="<?php echo htmlspecialchars($_POST['stock'] ?? ''); ?>">
                    </div>

                    <!-- Category -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Category</label>
                        <select name="category" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                            <option value="">Select or type a new category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo ($_POST['category'] ?? '') === $cat ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="text" name="category_new" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500 mt-2" placeholder="Or enter new category" value="<?php echo htmlspecialchars($_POST['category_new'] ?? ''); ?>">
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Status</label>
                        <select name="status" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500">
                            <option value="active" <?php echo ($_POST['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo ($_POST['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            <option value="discontinued" <?php echo ($_POST['status'] ?? '') === 'discontinued' ? 'selected' : ''; ?>>Discontinued</option>
                        </select>
                    </div>
                </div>

                <!-- Description -->
                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">Description</label>
                    <textarea name="description" rows="5" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-orange-500" placeholder="Enter product description"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                </div>

                <!-- Product Image -->
                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">Product Image</label>
                    <div class="border-2 border-dashed border-gray-300 rounded px-6 py-8 text-center hover:border-orange-500 cursor-pointer" onclick="document.getElementById('image_input').click()">
                        <input type="file" id="image_input" name="image" class="hidden" accept="image/*" onchange="previewImage(event)">
                        <div id="image_preview" class="hidden mb-4">
                            <img id="preview_img" class="max-h-48 mx-auto rounded">
                        </div>
                        <div id="image_placeholder">
                            <p class="text-gray-600 text-lg mb-2">📸</p>
                            <p class="text-gray-700">Click to upload image or drag and drop</p>
                            <p class="text-gray-500 text-sm">JPEG, PNG, GIF, WebP up to 5MB</p>
                        </div>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex gap-4 justify-end">
                    <a href="admin-products.php" class="bg-gray-500 text-white px-6 py-2 rounded font-medium hover:bg-gray-600">Cancel</a>
                    <button type="submit" class="bg-orange-500 text-white px-6 py-2 rounded font-medium hover:bg-orange-600">Add Product</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('image_placeholder').classList.add('hidden');
                    document.getElementById('image_preview').classList.remove('hidden');
                    document.getElementById('preview_img').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        }

        // Handle category selection
        document.querySelector('select[name="category"]').addEventListener('change', function() {
            if (this.value) {
                document.querySelector('input[name="category_new"]').value = '';
            }
        });

        document.querySelector('input[name="category_new"]').addEventListener('input', function() {
            if (this.value) {
                document.querySelector('select[name="category"]').value = '';
            }
        });
    </script>
</body>
</html>
