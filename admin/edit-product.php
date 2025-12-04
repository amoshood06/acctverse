<?php
$pdo = require_once "../db/db.php";
require_once "../flash.php";
include 'header.php';

// Validate product ID
if (!isset($_GET['id'])) {
    set_flash("error", "Product not found.");
    header("Location: manage-products.php");
    exit;
}

$id = $_GET['id'];

// Fetch product
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    set_flash("error", "Product not found.");
    header("Location: manage-products.php");
    exit;
}

// Fetch sub-categories from the database
$sub_categories = [];
$stmt_sub = $pdo->query("SELECT id, name FROM sub_categories ORDER BY name ASC");
if ($stmt_sub) {
    $sub_categories = $stmt_sub->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch parent categories from the database
$categories = [];
$stmt_cat = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
if ($stmt_cat) {
    $categories = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);
}
?>    
<div class="max-w-3xl mx-auto bg-white shadow p-6 rounded">
    <h2 class="text-2xl font-bold text-blue-900 mb-4">Edit Product</h2>

    <form action="update-product.php" method="POST" enctype="multipart/form-data">

        <input type="hidden" name="id" value="<?= $product['id']; ?>">

        <label class="block font-semibold mt-3">Product Name</label>
        <input type="text" name="product_name" class="w-full border rounded p-2"
               value="<?= htmlspecialchars($product['product_name']); ?>" required>

        <label class="block font-semibold mt-3">Category</label>
        <select name="category" class="w-full border rounded p-2" required>
            <option value="">Select Category</option>
            <?php foreach ($categories as $cat_option): ?>
                <option value="<?= htmlspecialchars($cat_option['id']) ?>" 
                    <?php 
                        $selected = '';
                        if (isset($product['category']) && $product['category'] === $cat_option['name']) {
                            $selected = 'selected';
                        }
                    ?>
                    <?= $selected ?>
                ><?= htmlspecialchars($cat_option['name']) ?></option>
            <?php endforeach; ?>
        </select>

        <label class="block font-semibold mt-3">Sub-Category</label>
        <select name="sub_category" class="w-full border rounded p-2">
            <option value="">Select Sub-Category (Optional)</option>
            <?php foreach ($sub_categories as $sub_category): ?>
                <option value="<?= htmlspecialchars($sub_category['name']) ?>" 
                    <?= (isset($product['sub_category']) && $product['sub_category'] === $sub_category['name']) ? 'selected' : '' ?>
                ><?= htmlspecialchars($sub_category['name']) ?></option>
            <?php endforeach; ?>
        </select>

        <label class="block font-semibold mt-3">Price (₦)</label>
        <input type="number" name="price" class="w-full border rounded p-2"
               value="<?= $product['price']; ?>" required>

        <label class="block font-semibold mt-3">Description</label>
        <textarea name="description" class="w-full border rounded p-2" required><?= htmlspecialchars($product['description']); ?></textarea>

        <label class="block font-semibold mt-3">Stock</label>
        <input type="number" name="stock" class="w-full border rounded p-2" value="<?= $product['stock']; ?>" required>

        <label class="block font-semibold mt-3">Admin Note (e.g. Account Details)</label>
        <textarea name="admin_note" class="w-full border rounded p-2"><?= htmlspecialchars($product['admin_note'] ?? ''); ?></textarea>

        <!-- Current Image -->
        <p class="mt-4 font-semibold">Current Image:</p>
        <img src="../uploads/<?= $product['image']; ?>" class="w-32 h-32 border rounded object-cover">

        <label class="block font-semibold mt-3">New Image (optional)</label>
        <input type="file" name="image" class="border p-2 w-full">

        <button class="mt-6 bg-blue-700 text-white px-5 py-2 rounded hover:bg-blue-800">
            Update Product
        </button>

        <a href="manage-products.php" class="ml-4 text-gray-600">Cancel</a>

    </form>
</div>

</body>
</html>
