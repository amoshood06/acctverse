<?php
session_start();
require_once "../db/db.php";
require_once "../flash.php";

// ==================================================
//  ADMIN AUTH CHECK
// ==================================================

// Ensure user exists AND is admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    set_flash("error", "Unauthorized access.");
    header("Location: ../login.php");
    exit;
}

// Current admin info
$admin = $_SESSION['user'];

// Fetch products
$stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Flash message (load once)
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Products - Admin</title>
<script src="https://cdn.tailwindcss.com"></script>

<style>
@keyframes slideIn {
    from { transform: translateX(120%); opacity: 0; }
    to   { transform: translateX(0); opacity: 1; }
}
.animate-slide-in { animation: slideIn .4s ease-out; }
</style>

</head>
<body class="bg-gray-100">

<!-- FLASH TOAST -->
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
<?php endif; ?>



<!-- PAGE HEADER -->
<div class="max-w-6xl mx-auto px-4 py-6 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-blue-900">Manage Products</h1>

    <a href="add-product.php" 
       class="hidden sm:block bg-orange-500 text-white px-4 py-2 rounded-lg font-semibold shadow hover:bg-orange-600">
        ➕ Add Product
    </a>
</div>


<!-- SEARCH + ADD BUTTON -->
<div class="max-w-6xl mx-auto px-4 mb-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">

    <!-- Search Bar -->
    <input id="searchInput" 
           type="text" 
           placeholder="Search products..." 
           class="border border-gray-300 rounded px-4 py-2 w-full sm:w-1/3 
                  focus:outline-none focus:ring-2 focus:ring-orange-500">

    <!-- Add Button (mobile) -->
    <a href="add-product.php"
       class="sm:hidden bg-orange-500 text-white px-4 py-2 rounded-lg font-semibold shadow hover:bg-orange-600">
        ➕ Add Product
    </a>
</div>




<!-- PRODUCT TABLE -->
<div class="max-w-6xl mx-auto px-4">
    <div class="overflow-x-auto bg-white shadow rounded-lg">
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-blue-900 text-white">
                    <th class="p-3 text-left">Image</th>
                    <th class="p-3 text-left">Name</th>
                    <th class="p-3 text-left">Category</th>
                    <th class="p-3 text-left">Price</th>
                    <th class="p-3 text-left">Stock</th>
                    <th class="p-3 text-left">Actions</th>
                </tr>
            </thead>

            <tbody id="productTable">
            <?php foreach ($products as $p): ?>
                <tr class="border-b hover:bg-gray-50 transition">
                    <td class="p-3">
                        <?php if (!empty($p['image'])): ?>
                        <img src="../uploads/<?= htmlspecialchars($p['image']); ?>" 
                             class="w-14 h-14 rounded object-cover border" alt="Product Image">
                        <?php else: ?>
                            <span class="text-gray-400 italic">No Image</span>
                        <?php endif; ?>
                    </td>

                    <td class="p-3"><?= htmlspecialchars($p['product_name']); ?></td>
                    <td class="p-3"><?= htmlspecialchars($p['category']); ?></td>
                    <td class="p-3">₦<?= number_format($p['price']); ?></td>
                    <td class="p-3"><?= htmlspecialchars($p['stock']); ?></td>

                    <td class="p-3 flex gap-2">
                        <!-- Edit -->
                        <a href="edit-product.php?id=<?= $p['id']; ?>" 
                           class="px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                            Edit
                        </a>

                        <!-- Delete -->
                        <a href="delete-product.php?id=<?= $p['id']; ?>"
                           onclick="return confirm('Are you sure you want to delete this product?');"
                           class="px-3 py-2 bg-red-600 text-white rounded hover:bg-red-700 text-sm">
                            Delete
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>

        </table>
    </div>
</div>



<!-- FLOATING ADD PRODUCT BUTTON (MOBILE ONLY) -->
<a href="add-product.php"
   class="md:hidden fixed bottom-5 right-5 bg-orange-600 text-white w-16 h-16 
          rounded-full flex items-center justify-center shadow-xl text-3xl
          hover:bg-orange-700 active:scale-95 transition">
    +
</a>


<!-- SEARCH FILTER SCRIPT -->
<script>
document.getElementById("searchInput").addEventListener("keyup", function () {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll("#productTable tr");

    rows.forEach(row => {
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? "" : "none";
    });
});
</script>

</body>
</html>
