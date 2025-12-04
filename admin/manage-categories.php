<?php
$pdo = require_once "../db/db.php";
require_once "../flash.php";
include 'header.php';

$flash = get_flash();

// Fetch all parent categories
$stmt_cat = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);

// Fetch all sub-categories and group them by their parent category_id
$stmt_sub = $pdo->query("SELECT * FROM sub_categories ORDER BY name ASC");
$all_sub_categories = $stmt_sub->fetchAll(PDO::FETCH_ASSOC);

$sub_categories_by_parent = [];
$orphan_sub_categories = [];
foreach ($all_sub_categories as $sub) {
    if (!empty($sub['category_id'])) {
        $sub_categories_by_parent[$sub['category_id']][] = $sub;
    } else {
        // Collect sub-categories with no parent
        $orphan_sub_categories[] = $sub;
    }
}
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
    if (toast) {
        toast.style.opacity = "0";
        toast.style.transition = "0.5s";
        setTimeout(() => toast.remove(), 600);
    }
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

<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-blue-900">Manage Categories</h1>
        <div class="flex gap-2">
            <a href="add-category.php" class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">Add Category</a>
            <a href="add-sub-category.php" class="bg-green-600 text-white py-2 px-4 rounded hover:bg-green-700">Add Sub-Category</a>
        </div>
    </div>

    <div class="bg-white p-8 rounded-lg shadow">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-4 font-semibold text-blue-900">Category Name</th>
                        <th class="p-4 font-semibold text-blue-900">Type</th>
                        <th class="p-4 font-semibold text-blue-900 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categories) && empty($all_sub_categories)): ?>
                        <tr>
                            <td colspan="3" class="p-4 text-center text-gray-500">No categories or sub-categories found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($categories as $category): ?>
                            <tr class="border-b">
                                <td class="p-4 font-medium text-gray-800"><?= htmlspecialchars($category['name']) ?></td>
                                <td class="p-4 text-gray-600"><span class="bg-blue-100 text-blue-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded">Parent</span></td>
                                <td class="p-4 text-right">
                                    <a href="delete-category.php?id=<?= $category['id'] ?>" onclick="return confirm('Are you sure you want to delete this category and all its sub-categories?');" class="text-red-600 hover:text-red-800">Delete</a>
                                </td>
                            </tr>
                            <?php if (isset($sub_categories_by_parent[$category['id']])): ?>
                                <?php foreach ($sub_categories_by_parent[$category['id']] as $sub_category): ?>
                                    <tr class="border-b bg-gray-50">
                                        <td class="p-4 pl-10 text-gray-700">
                                            <span class="mr-2">&#9492;</span><?= htmlspecialchars($sub_category['name']) ?>
                                        </td>
                                        <td class="p-4 text-gray-600"><span class="bg-green-100 text-green-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded">Sub</span></td>
                                        <td class="p-4 text-right">
                                            <a href="delete-sub-category.php?id=<?= $sub_category['id'] ?>" onclick="return confirm('Are you sure you want to delete this sub-category?');" class="text-red-600 hover:text-red-800">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>

                        <?php if (!empty($orphan_sub_categories)): ?>
                            <?php foreach ($orphan_sub_categories as $sub_category): ?>
                                <tr class="border-b bg-red-50">
                                    <td class="p-4 pl-10 text-gray-700">
                                        <span class="mr-2">&#9492;</span><?= htmlspecialchars($sub_category['name']) ?>
                                    </td>
                                    <td class="p-4 text-gray-600"><span class="bg-yellow-100 text-yellow-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded">Orphan Sub</span></td>
                                    <td class="p-4 text-right">
                                        <a href="delete-sub-category.php?id=<?= $sub_category['id'] ?>" onclick="return confirm('Are you sure you want to delete this sub-category?');" class="text-red-600 hover:text-red-800">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
