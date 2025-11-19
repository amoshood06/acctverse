<?php
session_start();
require_once "../db/db.php";
require_once "../flash.php";

// ==================================================
//  ADMIN AUTH CHECK
// ==================================================
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    set_flash("error", "Unauthorized access.");
    header("Location: ../login.php");
    exit;
}

// Get logged-in admin data
$admin = $_SESSION['user']; 
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Product - AcctGlobe Admin</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">

<!-- ================================================== -->
<!-- NAVBAR -->
<!-- ================================================== -->
<nav class="bg-blue-900 shadow-lg p-4 flex justify-between items-center">
    <span class="text-white font-bold text-lg">AcctGlobe Admin</span>
    <a href="../logout.php" class="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600">
        Logout
    </a>
</nav>

<!-- ================================================== -->
<!-- FLASH TOAST -->
<!-- ================================================== -->
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


<!-- ================================================== -->
<!-- ADD PRODUCT FORM -->
<!-- ================================================== -->
<div class="max-w-4xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-blue-900 mb-6">Add New Product</h1>

    <form action="process-add-product.php" method="POST" enctype="multipart/form-data" 
          class="bg-white p-8 rounded-lg shadow">

        <label class="block mb-2 font-semibold text-blue-900">Product Name *</label>
        <input type="text" name="product_name" required class="w-full border px-4 py-2 rounded mb-4">

        <label class="block mb-2 font-semibold text-blue-900">Category *</label>
        <select name="category" required class="w-full border px-4 py-2 rounded mb-4">
            <option value="">Select Category</option>
            <option>Facebook</option>
            <option>Twitter</option>
            <option>Instagram</option>
            <option>TikTok</option>
            <option>YouTube</option>
            <option>LinkedIn</option>
            <option>Other</option>
        </select>

        <label class="block mb-2 font-semibold text-blue-900">Description *</label>
        <textarea name="description" required class="w-full border px-4 py-2 rounded mb-4"></textarea>

        <label class="block mb-2 font-semibold text-blue-900">Price (₦) *</label>
        <input type="number" name="price" required class="w-full border px-4 py-2 rounded mb-4">

        <label class="block mb-2 font-semibold text-blue-900">Stock Quantity *</label>
        <input type="number" name="stock" required class="w-full border px-4 py-2 rounded mb-4">

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


</body>
</html>
