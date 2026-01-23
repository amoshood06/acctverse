<?php
require_once "./db/db.php"; // PDO connection

// Categories you want to display
$categories = ['Facebook', 'Instagram', 'TikTok', 'Twitter'];

$allProducts = [];

// Fetch products for each category
foreach ($categories as $cat) {
    // Group by sub_category to show one entry per sub-category
    // We get the total stock, the lowest price, and an example product ID and name.
    $stmt = $pdo->prepare("
        SELECT 
            sub_category,
            COUNT(id) as total_stock,
            MIN(price) as min_price,
            MIN(product_name) as product_name,
            MIN(id) as product_id
        FROM products 
        WHERE category = ? AND sub_category IS NOT NULL AND sub_category != ''
        GROUP BY sub_category
        ORDER BY sub_category
    ");
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

        <a href="products.php?category=<?= urlencode($category) ?>" class="w-full md:w-auto px-4 py-2 border-2 border-green-500 text-green-500 rounded-lg hover:bg-green-50 text-sm font-semibold text-center transition">
            View All
        </a>
    </div>

    <div class="space-y-4">
        <?php if (!empty($allProducts[$category])): ?>
            <?php foreach ($allProducts[$category] as $sub_category_group): ?>
            <!-- Each product gets a unique x-data scope to manage its own modal state -->
            <div x-data="{ isModalOpen: false, qty: 1, unitPrice: <?= $sub_category_group['min_price'] ?>, totalStock: <?= intval($sub_category_group['total_stock']) ?>,
                         get totalPrice() { return (this.qty * this.unitPrice).toLocaleString('en-NG', {minimumFractionDigits: 2, maximumFractionDigits: 2}); } }" class="bg-white rounded-lg border border-gray-200 p-4 md:p-6">

                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

                    <div class="flex gap-4">
                        <!-- Product Image -->
                        <div class="w-12 h-12 rounded-full overflow-hidden flex items-center justify-center">

                            
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
                            

                        </div>

                        <div>
                            <h3 class="font-bold text-gray-900 text-sm md:text-base"><?= htmlspecialchars($sub_category_group['sub_category'])?></h3>
                            <p class="text-green-600 text-sm font-semibold mt-1">In Stock: <?= intval($sub_category_group['total_stock']) ?> qty.</p>
                            <p class="text-gray-700 text-sm mt-1">Per Quantity: 
                                <span class="font-bold text-gray-900">₦<?= number_format($sub_category_group['min_price'], 2) ?></span>
                            </p>
                        </div>
                    </div>

                    <!-- This button now only toggles the modal for this specific product -->
                    <button @click="isModalOpen = true" 
                        class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 text-sm font-semibold">
                        ✓ View Account
                    </button>
                </div>

                <!-- Modal -->
                <!-- The modal's visibility is tied to the unique 'isModalOpen' state -->
                <div x-show="isModalOpen" x-transition class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="display: none;">

                    <div @click.away="isModalOpen = false" 
                         class="bg-white rounded-lg shadow-lg max-w-lg w-full p-6 relative">

                        <h2 class="text-xl font-bold mb-2"><?= htmlspecialchars($sub_category_group['sub_category']) ?></h2>

                        <p class="text-gray-700 mb-2"><strong>Category:</strong> <?= htmlspecialchars($category) ?></p>
                        <p class="text-gray-700 mb-2"><strong>Description:</strong> Accounts from the <?= htmlspecialchars($sub_category_group['sub_category']) ?> pool.</p>

                        <!-- Price -->
                        <p class="text-gray-700 mb-1">
                            <strong>Price per unit:</strong> ₦<?= number_format($sub_category_group['min_price'], 2) ?>
                        </p>

                        <div class="mt-4">
                            <label for="quantity-<?= $sub_category_group['product_id'] ?>" class="text-gray-700 font-semibold">Quantity:</label>
                            <input type="number" 
                                   id="quantity-<?= $sub_category_group['product_id'] ?>"
                                   x-model.number="qty" 
                                   @change="if(qty < 1) qty = 1; if(qty > totalStock) qty = totalStock"
                                   min="1" 
                                   x-bind:max="totalStock" 
                                   class="w-24 px-2 py-1 border rounded-md text-center">
                            <span class="text-sm text-gray-500"> (Max: <span x-text="totalStock"></span>)</span>
                        </div>

                        <!-- Total Price -->
                        <p class="text-green-600 font-semibold mt-3">
                            Total: ₦<span x-text="totalPrice"></span>
                        </p>

                        <!-- Buy Form -->
                        <!-- Form now uses POST method and properly binds qty value with x-model.number -->
                                                    <form x-ref="buyForm" action="buy-product.php" method="POST" class="mt-5">
                                                        <input type="hidden" name="product_id" value="<?= $sub_category_group['product_id'] ?>">
                                                        <input type="hidden" name="quantity" x-model.number="qty">
                        
                                                        <button type="button"
                                                            @click="
                                                                if (qty > 1) {
                                                                    window.location.href = 'confirm-multi-purchase.php?sub_category=<?= urlencode($sub_category_group['sub_category']) ?>&quantity=' + qty;
                                                                } else {
                                                                    $refs.buyForm.submit();
                                                                }
                                                            "
                                                            class="w-full px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-semibold text-sm">
                                                            Buy Now
                                                        </button>
                                                    </form>
                        <!-- Close Modal -->
                        <button @click="isModalOpen = false" 
                                class="absolute top-2 right-2 text-gray-500 hover:text-gray-800 text-xl">
                            &times;
                        </button>

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
