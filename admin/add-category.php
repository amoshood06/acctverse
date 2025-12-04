<?php
require_once "../flash.php";
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
    <h1 class="text-3xl font-bold text-blue-900 mb-6">Add New Category</h1>

    <form action="process-add-category.php" method="POST" class="bg-white p-8 rounded-lg shadow">

        <label class="block mb-2 font-semibold text-blue-900">Category Name *</label>
        <input type="text" name="category_name" required class="w-full border px-4 py-2 rounded mb-4">

        <div class="flex gap-4 mt-6">
            <button type="submit" class="bg-orange-500 text-white py-2 px-6 rounded hover:bg-orange-600 flex-1">
                Add Category
            </button>

            <a href="manage-categories.php" class="border text-center py-2 px-6 rounded flex-1 hover:bg-gray-100">
                Cancel
            </a>
        </div>
    </form>
</div>