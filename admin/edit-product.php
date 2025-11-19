<?php
session_start();
require_once "../db/db.php";
require_once "../flash.php";

// Only admin
if (!isset($_SESSION['admin'])) {
    set_flash("error", "Access denied.");
    header("Location: ../login.php");
    exit;
}

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
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Product</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<div class="max-w-3xl mx-auto mt-10 bg-white shadow p-6 rounded">
    <h2 class="text-2xl font-bold text-blue-900 mb-4">Edit Product</h2>

    <form action="update-product.php" method="POST" enctype="multipart/form-data">

        <input type="hidden" name="id" value="<?= $product['id']; ?>">

        <label class="block font-semibold mt-3">Product Name</label>
        <input type="text" name="product_name" class="w-full border rounded p-2"
               value="<?= htmlspecialchars($product['product_name']); ?>" required>

        <label class="block font-semibold mt-3">Category</label>
        <input type="text" name="category" class="w-full border rounded p-2"
               value="<?= htmlspecialchars($product['category']); ?>" required>

        <label class="block font-semibold mt-3">Price (₦)</label>
        <input type="number" name="price" class="w-full border rounded p-2"
               value="<?= $product['price']; ?>" required>

        <label class="block font-semibold mt-3">Stock</label>
        <input type="number" name="stock" class="w-full border rounded p-2"
               value="<?= $product['stock']; ?>" required>

        <label class="block font-semibold mt-3">Description</label>
        <textarea name="description" class="w-full border rounded p-2" required><?= htmlspecialchars($product['description']); ?></textarea>

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
