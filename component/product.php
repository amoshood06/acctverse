<?php
require_once "./db/db.php"; // PDO connection

// Categories you want to display
$categories = ['Facebook', 'Instagram', 'TikTok', 'Twitter'];

$allProducts = [];

// Fetch products for each category
foreach ($categories as $cat) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE category = ?");
    $stmt->execute([$cat]);
    $allProducts[$cat] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<script src="//unpkg.com/alpinejs" defer></script>

<?php foreach ($categories as $category): ?>
<div class="acctverse_product p-4 md:p-6 mb-6">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3 mb-6">
        <h1 class="text-lg sm:text-xl md:text-2xl font-bold text-white bg-red-700 px-4 py-2 rounded-lg">
            🎁 <?= htmlspecialchars($category) ?>
        </h1>

        <a href="" class="w-full md:w-auto px-4 py-2 border-2 border-green-500 text-green-500 rounded-lg hover:bg-green-50 text-sm font-semibold text-center transition">
            View All
        </a>
    </div>

    <div class="space-y-4">
        <?php if (!empty($allProducts[$category])): ?>
            <?php foreach ($allProducts[$category] as $product): ?>
            <div x-data="{ open: false }" class="bg-white rounded-lg border border-gray-200 p-4 md:p-6">

                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

                    <div class="flex gap-4">
                        <!-- Product Image -->
                        <div class="w-12 h-12 rounded-full overflow-hidden flex items-center justify-center">

                            <?php 
                            $imagePath = "uploads/" . $product['image']; 
                            ?>

                            <?php if (!empty($product['image']) && file_exists($imagePath)): ?>
                                <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($product['product_name']) ?>" class="w-full h-full object-cover">

                            <?php else: ?>
                                <!-- Fallback Category Icons -->
                                <?php
                                $color = "bg-gray-500";
                                $text = "?";

                                if ($category === "Facebook") { $color = "bg-blue-600"; $text = "F"; }
                                if ($category === "Instagram") { $color = "bg-pink-500"; $text = "IG"; }
                                if ($category === "TikTok") { $color = "bg-black"; $text = "T"; }
                                if ($category === "Twitter") { $color = "bg-sky-500"; $text = "Tw"; }
                                ?>

                                <div class="w-full h-full <?= $color ?> flex items-center justify-center text-white font-bold">
                                    <?= $text ?>
                                </div>
                            <?php endif; ?>

                        </div>

                        <div>
                            <h3 class="font-bold text-gray-900 text-sm md:text-base"><?= htmlspecialchars($product['product_name']) ?></h3>
                            <p class="text-green-600 text-sm font-semibold mt-1">In Stock: <?= intval($product['stock']) ?> qty.</p>
                            <p class="text-gray-700 text-sm mt-1">Per Quantity: 
                                <span class="font-bold text-gray-900">₦<?= number_format($product['price'], 2) ?></span>
                            </p>
                        </div>
                    </div>

                    <button @click="open = true" 
                        class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 text-sm font-semibold">
                        ✓ View Account
                    </button>
                </div>

                <!-- Modal -->
                <div x-show="open" x-transition class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">

                    <div @click.away="open = false" class="bg-white rounded-lg shadow-lg max-w-lg w-full p-6 relative">

                        <?php if (!empty($product['image']) && file_exists($imagePath)): ?>
                            <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($product['product_name']) ?>" 
                                 class="w-full h-48 object-cover rounded mb-4">
                        <?php endif; ?>

                        <h2 class="text-xl font-bold mb-2"><?= htmlspecialchars($product['product_name']) ?></h2>
                        <p class="text-gray-700 mb-2"><strong>Category:</strong> <?= htmlspecialchars($product['category']) ?></p>
                        <p class="text-gray-700 mb-2"><strong>Description:</strong> <?= htmlspecialchars($product['description']) ?></p>
                        <p class="text-gray-700 mb-2"><strong>Price:</strong> ₦<?= number_format($product['price'], 2) ?></p>
                        <p class="text-green-600 mb-4"><strong>Stock:</strong> <?= intval($product['stock']) ?></p>

                        <form action="buy-product.php" method="POST">
                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                            <button type="submit" 
                                class="w-full px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-semibold">
                                Buy Now
                            </button>
                        </form>

                        <button @click="open = false" 
                            class="absolute top-2 right-2 text-gray-500 hover:text-gray-800 text-lg">&times;</button>

                    </div>
                </div>

            </div>
            <?php endforeach; ?>

        <?php else: ?>
            <p class="text-gray-500 text-sm text-center">No products available in <?= htmlspecialchars($category) ?>.</p>
        <?php endif; ?>
    </div>
</div>
<?php endforeach; ?>
