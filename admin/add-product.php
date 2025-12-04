<?php
require_once "../flash.php";
require_once "../db/db.php"; // Include the database connection
include 'header.php';

// Fetch parent categories from the database
$categories = [];
$stmt_cat = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
if ($stmt_cat) {
    $categories = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch sub-categories from the database
$sub_categories = [];
$stmt_sub = $pdo->query("SELECT id, name FROM sub_categories ORDER BY name ASC");
if ($stmt_sub) {
    $sub_categories = $stmt_sub->fetchAll(PDO::FETCH_ASSOC);
}

$flash = get_flash();
?>
<?php if (!empty($flash)): ?>
<div id="toast"
     class="fixed top-5 right-5 z-50 px-6 py-3 rounded-lg shadow-lg text-white 
            <?= ($flash['type'] === 'success') ? 'bg-green-600' : 'bg-red-600' ?> 
            animate-slide-in">
    <?= htmlspecialchars($flash['message']); ?>
</div>

<script>
setTimeout(() => {
    const toast = document.getElementById('toast');
    toast.style.opacity = "0";
    toast.style.transition = "0.5s";
    setTimeout(() => toast.remove(), 600);
}, 3000);
</script>

<style>
@keyframes slideIn {
    from { transform: translateX(120%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
.animate-slide-in { animation: slideIn .4s ease-out; }
</style>
<?php endif; ?>

<div class="max-w-4xl mx-auto">
    <h1 class="text-3xl font-bold text-blue-900 mb-6">Add New Product</h1>

    <form action="process-add-product.php" method="POST" enctype="multipart/form-data" 
          class="bg-white p-8 rounded-lg shadow">

        <label class="block mb-2 font-semibold text-blue-900">Product Name *</label>
        <input type="text" name="product_name" required class="w-full border px-4 py-2 rounded mb-4">

        <label class="block mb-2 font-semibold text-blue-900">Category *</label>
        <select name="category" required class="w-full border px-4 py-2 rounded mb-4">
            <option value="">Select Category</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= htmlspecialchars($category['id']) ?>"><?= htmlspecialchars($category['name']) ?></option>
            <?php endforeach; ?>
        </select>        

        <label class="block mb-2 font-semibold text-blue-900">Sub-Category</label>
        <select name="sub_category" class="w-full border px-4 py-2 rounded mb-4">
            <option value="">Select Sub-Category (Optional)</option>
            <?php foreach ($sub_categories as $sub_category): ?>
                <option value="<?= htmlspecialchars($sub_category['name']) ?>"><?= htmlspecialchars($sub_category['name']) ?></option>
            <?php endforeach; ?>
        </select>

        <label class="block mb-2 font-semibold text-blue-900">Description *</label>
        <textarea name="description" required class="w-full border px-4 py-2 rounded mb-4"></textarea>

        <label class="block mb-2 font-semibold text-blue-900">Price (₦) *</label>
        <input type="number" name="price" required class="w-full border px-4 py-2 rounded mb-4">

        <label class="block mb-2 font-semibold text-blue-900">Admin Note (e.g. Account Details)</label>
        <textarea name="admin_note" placeholder="Enter account details or other admin-only notes here..." class="w-full border px-4 py-2 rounded mb-4"></textarea>

        <label class="block mb-2 font-semibold text-blue-900">Product Image *</label>
        <input type="file" name="image" accept="image/*" required class="w-full border px-4 py-2 rounded mb-6">

        <div class="flex gap-4">
            <button type="submit" 
                    class="bg-orange-500 text-white py-2 px-6 rounded hover:bg-orange-600 flex-1">
                Publish Product
            </button>

            <a href="manage-products.php" 
               class="border text-center py-2 px-6 rounded flex-1 hover:bg-gray-100">
                Cancel
            </a>
        </div>

    </form>
</div>
