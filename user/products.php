<?php
session_start();
require_once "../db/db.php";
require_once "../flash.php";

// Redirect if user not logged in
if (!isset($_SESSION['user'])) {
    set_flash("error", "You must be logged in to view products.");
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$flash = get_flash();

// Fetch categories for the filter dropdown
try {
    $cat_stmt = $pdo->query("SELECT DISTINCT category FROM products WHERE stock > 0 ORDER BY category ASC");
    $categories = $cat_stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $categories = [];
}

// Base query for fetching products
$sql = "SELECT * FROM products WHERE stock > 0";
$params = [];

// Handle search term
$search = trim($_GET['search'] ?? '');
if ($search) {
    $sql .= " AND product_name LIKE ?";
    $params[] = "%$search%";
}

// Handle category filter
$category_filter = trim($_GET['category'] ?? '');
if ($category_filter) {
    $sql .= " AND category = ?";
    $params[] = $category_filter;
}

$sql .= " ORDER BY created_at DESC";

// Fetch products from the database
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $products = [];
    set_flash("error", "Could not load products.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Acctverse</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
</head>
<body class="bg-gray-50">
    <!-- Navigation Header -->
    <nav class="bg-white shadow-sm sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
            <img src="assets/image/acctverse.png" alt="Acctverse" class="w-[150px]">
            <div class="hidden md:flex items-center gap-6">
                <a href="index.php" class="text-gray-600 hover:text-orange-500">Dashboard</a>
                <a href="order-history.php" class="text-gray-600 hover:text-orange-500">Orders</a>
                <a href="products.php" class="text-blue-900 font-medium">Products</a>
            </div>
            <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded font-medium hover:bg-orange-600">Logout</a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Category and Search -->
        <form method="GET" action="products.php">
            <div class="flex flex-col md:flex-row gap-4 mb-8">
                <select name="category" onchange="this.form.submit()" class="bg-white border border-gray-300 px-4 py-2 rounded focus:outline-none focus:border-orange-500">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat) ?>" <?= ($category_filter === $cat) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="flex-1 flex gap-2">
                    <input type="text" name="search" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>" class="flex-1 px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-orange-500">
                    <button type="submit" class="bg-orange-500 text-white px-6 py-2 rounded hover:bg-orange-600">🔍</button>
                </div>
            </div>
        </form>

        <form action="process-order.php" method="POST">
            <!-- Selected Products Section -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-8 sticky top-20 z-10">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Selected Products</h3>
                <div class="flex justify-between items-center mb-4 pb-4 border-b border-gray-200">
                    <p class="text-gray-600">Total Products: <span id="total-products" class="font-bold">0</span></p>
                    <p class="text-gray-600">Total Price: <span id="total-price" class="font-bold">₦0.00</span></p>
                </div>
                <button type="submit" class="bg-orange-500 text-white px-6 py-3 rounded font-bold hover:bg-orange-600 float-right">
                    🛒 Purchase
                </button>
            </div>

            <!-- Products List -->
            <div class="space-y-4">
                <?php if (empty($products)): ?>
                    <div class="text-center py-16 text-gray-500">
                        <p class="text-2xl">😕</p>
                        <p>No products found matching your criteria.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <div class="bg-white rounded-lg shadow-sm p-4 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <img src="../uploads/<?= htmlspecialchars($product['image'] ?? 'placeholder.png') ?>" alt="<?= htmlspecialchars($product['product_name']) ?>" class="w-16 h-16 rounded-lg object-cover border">
                                <div>
                                    <h4 class="font-bold text-gray-800"><?= htmlspecialchars($product['product_name']) ?></h4>
                                    <p class="text-gray-600 text-sm">Price: <span class="font-semibold">₦<?= number_format($product['price'], 2) ?></span></p>
                                    <p class="text-xs text-gray-500">In Stock: <?= htmlspecialchars($product['stock']) ?></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <label class="text-sm">Qty:</label>
                                <input 
                                    type="number" 
                                    name="products[<?= $product['id'] ?>]"
                                    class="w-20 px-2 py-1 border border-gray-300 rounded focus:outline-none focus:border-orange-500"
                                    min="0"
                                    max="<?= htmlspecialchars($product['stock']) ?>"
                                    value="0"
                                    data-price="<?= $product['price'] ?>"
                                    onchange="updateTotals()"
                                >
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </form>
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

    <script>
    function updateTotals() {
        let totalProducts = 0;
        let totalPrice = 0;
        const inputs = document.querySelectorAll('input[type="number"][name^="products"]');

        inputs.forEach(input => {
            const quantity = parseInt(input.value, 10) || 0;
            const price = parseFloat(input.dataset.price) || 0;

            if (quantity > 0) {
                totalProducts += quantity;
                totalPrice += quantity * price;
            }
        });

        document.getElementById('total-products').textContent = totalProducts;
        document.getElementById('total-price').textContent = '₦' + totalPrice.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    </script>
</body>
</html>
