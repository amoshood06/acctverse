<?php
// filepath: c:\xampp\htdocs\acctverse\component\product-detail-ajax.php
require_once '../db/db.php';
require_once '../user/function/product_functions.php';

$pdo = get_pdo();
$product_id = (int)($_GET['id'] ?? 0);
$user_id = $_SESSION['user_id'] ?? null;

if ($product_id <= 0) {
    echo '<p class="text-red-500">Invalid product ID</p>';
    exit;
}

$product = get_product_details($pdo, $product_id);

if (!$product) {
    echo '<p class="text-red-500">Product not found</p>';
    exit;
}

$discount = get_discount_percentage($product['original_price'] ?? $product['price'], $product['price']);
?>

<div class="space-y-4">
    <!-- Header -->
    <div class="flex gap-4 mb-4">
        <div class="w-16 h-16 bg-gradient-to-br from-blue-400 to-blue-600 rounded-lg flex items-center justify-center text-4xl flex-shrink-0">
            <?php echo $product['icon'] ?? '📦'; ?>
        </div>
        <div>
            <h2 class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($product['name']); ?></h2>
            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($product['category']); ?></p>
        </div>
    </div>

    <!-- Price -->
    <div class="bg-blue-50 p-3 rounded-lg">
        <p class="text-2xl font-bold text-blue-600">₦<?php echo number_format($product['price'], 2); ?></p>
        <?php if ($discount > 0): ?>
            <p class="text-sm text-gray-600 line-through">₦<?php echo number_format($product['original_price'], 2); ?></p>
            <p class="text-xs text-green-600 font-semibold">💚 Save <?php echo $discount; ?>%</p>
        <?php endif; ?>
    </div>

    <!-- Description -->
    <div>
        <h3 class="font-semibold text-gray-800 mb-2">Description</h3>
        <p class="text-sm text-gray-700"><?php echo htmlspecialchars($product['description']); ?></p>
    </div>

    <!-- Stock & Rating -->
    <div class="grid grid-cols-2 gap-3">
        <div class="bg-green-50 p-3 rounded-lg">
            <p class="text-xs text-gray-600">Stock Available</p>
            <p class="text-lg font-bold text-green-600"><?php echo $product['stock_quantity']; ?> qty</p>
        </div>
        <div class="bg-yellow-50 p-3 rounded-lg">
            <p class="text-xs text-gray-600">Rating</p>
            <p class="text-lg font-bold text-yellow-600">★ <?php echo number_format($product['rating'] ?? 0, 1); ?></p>
        </div>
    </div>

    <!-- Delivery Info -->
    <?php if (!empty($product['delivery_time'])): ?>
        <div class="bg-purple-50 p-3 rounded-lg text-sm">
            <p class="text-gray-700">📦 <span class="font-semibold"><?php echo htmlspecialchars($product['delivery_time']); ?></span></p>
        </div>
    <?php endif; ?>

    <!-- Actions -->
    <div class="flex gap-2 pt-4 border-t">
        <?php if ($product['stock_quantity'] > 0): ?>
            <button type="button" onclick="addToCart(<?php echo $product['id']; ?>)" class="flex-1 bg-green-500 text-white px-4 py-2 rounded font-semibold hover:bg-green-600 transition">
                🛒 Add to Cart
            </button>
        <?php else: ?>
            <button type="button" disabled class="flex-1 bg-gray-400 text-white px-4 py-2 rounded font-semibold cursor-not-allowed">
                Out of Stock
            </button>
        <?php endif; ?>
        <button type="button" onclick="closeProductModal()" class="flex-1 bg-gray-300 text-gray-900 px-4 py-2 rounded font-semibold hover:bg-gray-400 transition">
            Close
        </button>
    </div>

    <!-- Reviews -->
    <?php if (!empty($product['reviews'])): ?>
        <div class="border-t pt-4">
            <h3 class="font-semibold text-gray-800 mb-3">Customer Reviews</h3>
            <div class="space-y-2 max-h-40 overflow-y-auto">
                <?php foreach ($product['reviews'] as $review): ?>
                    <div class="bg-gray-50 p-2 rounded text-sm">
                        <div class="flex justify-between items-start">
                            <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($review['reviewer_name'] ?? 'Anonymous'); ?></p>
                            <p class="text-yellow-500">★ <?php echo $review['rating']; ?></p>
                        </div>
                        <p class="text-gray-700"><?php echo htmlspecialchars($review['comment'] ?? ''); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function addToCart(productId) {
    if (!productId || productId <= 0) {
        alert('Invalid product ID');
        return;
    }
    
    // Add to cart logic here
    alert('Added to cart! Product ID: ' + productId);
    closeProductModal();
}
</script>