<?php
require_once "../flash.php";
require_once "../db/db.php"; // Include the database connection
include 'header.php';

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
    <h1 class="text-3xl font-bold text-blue-900 mb-6">Add New Gift Product</h1>

    <form action="process-add-gift-product.php" method="POST" enctype="multipart/form-data"
          class="bg-white p-8 rounded-lg shadow">

        <label class="block mb-2 font-semibold text-blue-900">Product Name *</label>
        <input type="text" name="name" required class="w-full border px-4 py-2 rounded mb-4">

        <label class="block mb-2 font-semibold text-blue-900">Category *</label>
        <input type="text" name="category" required class="w-full border px-4 py-2 rounded mb-4" placeholder="e.g., Electronics, Clothing">

        <label class="block mb-2 font-semibold text-blue-900">Details *</label>
        <textarea name="details" required class="w-full border px-4 py-2 rounded mb-4" rows="4"></textarea>

        <label class="block mb-2 font-semibold text-blue-900">Price (₦) *</label>
        <input type="number" name="price" step="0.01" required class="w-full border px-4 py-2 rounded mb-4">

        <label class="block mb-2 font-semibold text-blue-900">Stock *</label>
        <input type="number" name="stock" required class="w-full border px-4 py-2 rounded mb-4" min="0">

        <label class="block mb-2 font-semibold text-blue-900">Product Image *</label>
        <input type="file" name="image" accept="image/*" required class="w-full border px-4 py-2 rounded mb-6">

        <div class="flex gap-4">
            <button type="submit"
                    class="bg-orange-500 text-white py-2 px-6 rounded hover:bg-orange-600 flex-1">
                Add Gift Product
            </button>

            <a href="manage-gift-products.php"
               class="border text-center py-2 px-6 rounded flex-1 hover:bg-gray-100">
                Cancel
            </a>
        </div>

    </form>
</div>