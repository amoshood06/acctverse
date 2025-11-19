<?php
require_once "./db/db.php";

if (!isset($_GET['category'])) {
    die("Category not found.");
}

$category = $_GET['category'];

// Search and Sort Logic
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? '';

// Build dynamic SQL
$sql = "SELECT * FROM products WHERE category = ?";
$params = [$category];

if (!empty($search)) {
    $sql .= " AND product_name LIKE ?";
    $params[] = "%$search%";
}

if ($sort === "low-high") {
    $sql .= " ORDER BY price ASC";
} elseif ($sort === "high-low") {
    $sql .= " ORDER BY price DESC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($category) ?> Products</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

<!-- Header -->
<div class="bg-white shadow p-4 flex justify-between items-center">
    <h1 class="text-xl font-bold text-gray-800">
        <?= htmlspecialchars($category) ?> Products
    </h1>
    <a href="index.php" class="text-blue-600 hover:underline">← Back</a>
</div>

<div class="p-4">

    <!-- Search & Sorting -->
    <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">

        <!-- Search -->
        <input 
            type="text" 
            name="search" 
            placeholder="Search product name..."
            value="<?= htmlspecialchars($search) ?>"
            class="w-full p-3 border rounded-lg focus:ring focus:ring-blue-300"
        >

        <!-- Sorting -->
        <select name="sort" class="w-full p-3 border rounded-lg">
            <option value="">Sort by</option>
            <option value="low-high" <?= $sort === 'low-high' ? 'selected' : '' ?>>Price: Low to High</option>
            <option value="high-low" <?= $sort === 'high-low' ? 'selected' : '' ?>>Price: High to Low</option>
        </select>

        <!-- Submit -->
        <button class="bg-green-600 text-white px-4 py-3 rounded-lg hover:bg-green-700">
            Apply Filters
        </button>
    </form>

    <!-- Product Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">

        <?php if (!empty($products)): ?>
            <?php foreach ($products as $product): ?>

            <div class="bg-white rounded-lg shadow hover:shadow-lg transition p-4 flex flex-col">

                <!-- Product Image -->
                <?php if (!empty($product['image'])): ?>
                    <img 
                        src="uploads/<?= htmlspecialchars($product['image']) ?>" 
                        class="w-full h-44 object-cover rounded mb-3"
                    >
                <?php else: ?>
                    <div class="w-full h-44 bg-gray-300 flex items-center justify-center rounded mb-3">
                        <span class="text-gray-700 font-bold">No Image</span>
                    </div>
                <?php endif; ?>

                <!-- Product Name -->
                <h2 class="font-bold text-lg text-gray-800 mb-1">
                    <?= htmlspecialchars($product['product_name']) ?>
                </h2>

                <!-- Price -->
                <p class="text-gray-700">₦<?= number_format($product['price'], 2) ?></p>

                <!-- Stock -->
                <p class="text-green-600 text-sm font-semibold mb-3">
                    Stock: <?= intval($product['stock']) ?>
                </p>

                <!-- Buy Button -->
                <form action="buy-product.php" method="POST" class="mt-auto">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <button 
                        class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700"
                    >
                        Buy Now
                    </button>
                </form>
            </div>

            <?php endforeach; ?>

        <?php else: ?>
            <p class="text-center text-gray-500 col-span-full p-10 bg-white rounded-lg shadow">
                No products found in this category.
            </p>
        <?php endif; ?>

    </div>
</div>

</body>
</html>
