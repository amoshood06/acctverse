<?php
$pdo = require_once "../db/db.php";
require_once "../flash.php";
include 'header.php';

// Validate product ID
if (!isset($_GET['id'])) {
    set_flash("error", "Gift product not found.");
    header("Location: manage-gift-products.php");
    exit;
}

$id = $_GET['id'];

// Fetch gift product
$stmt = $pdo->prepare("SELECT * FROM gift_products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    set_flash("error", "Gift product not found.");
    header("Location: manage-gift-products.php");
    exit;
}
?>
<div class="max-w-3xl mx-auto bg-white shadow p-6 rounded">
    <h2 class="text-2xl font-bold text-blue-900 mb-4">Edit Gift Product</h2>

    <form action="update-gift-product.php" method="POST" enctype="multipart/form-data">

        <input type="hidden" name="id" value="<?= $product['id']; ?>">

        <label class="block font-semibold mt-3">Product Name</label>
        <input type="text" name="name" class="w-full border rounded p-2"
               value="<?= htmlspecialchars($product['name']); ?>" required>

        <label class="block font-semibold mt-3">Category</label>
        <input type="text" name="category" class="w-full border rounded p-2"
               value="<?= htmlspecialchars($product['category']); ?>" required>

        <label class="block font-semibold mt-3">Details</label>
        <textarea name="details" class="w-full border rounded p-2" required rows="4"><?= htmlspecialchars($product['details']); ?></textarea>

        <label class="block font-semibold mt-3">Price (₦)</label>
        <input type="number" name="price" step="0.01" class="w-full border rounded p-2"
               value="<?= $product['price']; ?>" required>

        <label class="block font-semibold mt-3">Stock</label>
        <input type="number" name="stock" class="w-full border rounded p-2"
               value="<?= $product['stock']; ?>" required min="0">

        <!-- Current Image -->
        <p class="mt-4 font-semibold">Current Image:</p>
        <?php if (!empty($product['image'])): ?>
        <img src="../uploads/<?= htmlspecialchars($product['image']); ?>" class="w-32 h-32 border rounded object-cover">
        <?php else: ?>
        <span class="text-gray-400 italic">No Image</span>
        <?php endif; ?>

        <label class="block font-semibold mt-3">New Image (optional)</label>
        <input type="file" name="image" accept="image/*" class="border p-2 w-full">

        <button class="mt-6 bg-blue-700 text-white px-5 py-2 rounded hover:bg-blue-800">
            Update Gift Product
        </button>

        <a href="manage-gift-products.php" class="ml-4 text-gray-600">Cancel</a>

    </form>
</div>

</body>
</html>