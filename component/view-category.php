<?php
require_once "../db/db.php";

// Get category from URL
$category = $_GET['category'] ?? '';

if (empty($category)) {
    die("Category not specified.");
}

// Fetch all products for this category
$stmt = $pdo->prepare("SELECT * FROM products WHERE category = ?");
$stmt->execute([$category]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($category) ?> Products</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-6">

<h1 class="text-2xl font-bold mb-6 text-blue-900"><?= htmlspecialchars($category) ?> Products</h1>

<?php if (!empty($products)): ?>
<div class="space-y-4">
    <?php foreach ($products as $product): ?>
    <div class="bg-white rounded-lg border border-gray-200 p-4 md:p-6 flex justify-between items-center">
        <div>
            <h3 class="font-bold text-gray-900"><?= htmlspecialchars($product['product_name']) ?></h3>
            <p class="text-green-600 text-sm font-semibold mt-1">In Stock: <?= intval($product['stock']) ?></p>
            <p class="text-gray-700 text-sm mt-1">Price: ₦<?= number_format($product['price'], 2) ?></p>
            <p class="text-gray-700 text-sm mt-1">Description: <?= htmlspecialchars($product['description']) ?></p>
        </div>
        <button class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 text-sm">✓ View account</button>
    </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
<p class="text-gray-500 text-center">No products found in this category.</p>
<?php endif; ?>

<a href="index.php" class="mt-6 inline-block px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">← Back</a>

</body>
</html>
