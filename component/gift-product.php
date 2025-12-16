<?php
require_once "./db/db.php"; // PDO connection

// Fetch products from the gift_products table
$stmt = $pdo->prepare("SELECT * FROM gift_products WHERE stock > 0 ORDER BY created_at DESC");
$stmt->execute();
$gift_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="acctverse_product p-4 md:p-6 mb-6">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3 mb-6">
        <h1 class="text-lg sm:text-xl md:text-2xl font-bold text-white bg-red-700 px-4 py-2 rounded-lg">
            🎁 Available Gifts Product
        </h1>
    </div>

    <!-- Product Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">

        <?php if (!empty($gift_products)): ?>
            <?php foreach ($gift_products as $product): ?>

            <div class="bg-white rounded-lg shadow hover:shadow-lg transition p-4 flex flex-col">

                <!-- Product Image -->
                <?php if (!empty($product['image'])): ?>
                    <img 
                        src="./uploads/<?= htmlspecialchars($product['image']) ?>" 
                        class="w-full h-44 object-cover rounded mb-3"
                    >
                <?php else: ?>
                    <div class="w-full h-44 bg-gray-300 flex items-center justify-center rounded mb-3">
                        <span class="text-gray-700 font-bold">No Image</span>
                    </div>
                <?php endif; ?>

                <!-- Product Name -->
                <h2 class="font-bold text-lg text-gray-800 mb-1">
                    <?= htmlspecialchars($product['name']) ?>
                </h2>

                <!-- Price -->
                <p class="text-gray-700">₦<?= number_format($product['price'], 2) ?></p>

                <!-- Stock -->
                <p class="text-green-600 text-sm font-semibold mb-3">
                    Stock: <?= intval($product['stock']) ?>
                </p>

                <!-- Purchase Button -->
                <a href="gift-details.php?id=<?= $product['id'] ?>" class="mt-auto">
                    <button 
                        class="w-full bg-green-600 text-white py-2 rounded-lg hover:bg-green-700"
                    >
                        Purchase Gift
                    </button>
                </a>
            </div>

            <?php endforeach; ?>

        <?php else: ?>
            <p class="text-center text-gray-500 col-span-full p-10 bg-white rounded-lg shadow">
                No gifts available at the moment.
            </p>
        <?php endif; ?>

    </div>
</div>
