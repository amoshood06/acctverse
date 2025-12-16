<?php
$pdo = require_once "../db/db.php";
require_once "../flash.php";

// Redirect if user not logged in
if (!isset($_SESSION['user'])) {
    set_flash("error", "You must be logged in to view gift products.");
    header("Location: ../login.php");
    exit;
}

$user = $_SESSION['user'];
$flash = get_flash();

// Fetch categories for the filter dropdown
try {
    $cat_stmt = $pdo->query("SELECT DISTINCT category FROM gift_products WHERE name <> '' ORDER BY category ASC");
    $categories = $cat_stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $categories = [];
}

// Base query for fetching products
$sql = "SELECT * FROM gift_products WHERE name <> '' AND id > 0 AND stock > 0";
$params = [];

// Handle search term
$search = trim($_GET['search'] ?? '');
if ($search) {
    $sql .= " AND name LIKE ?";
    $params[] = "%$search%";
}

// Handle category filter
$category_filter = trim($_GET['category'] ?? '');
if ($category_filter) {
    $sql .= " AND category = ?";
    $params[] = $category_filter;
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$products_per_page = 10;
$offset = ($page - 1) * $products_per_page;

// Count total products for pagination
$count_sql = "SELECT COUNT(*) FROM gift_products WHERE name <> '' AND id > 0 AND stock > 0";
if ($search) {
    $count_sql .= " AND name LIKE ?";
}
if ($category_filter) {
    $count_sql .= " AND category = ?";
}

$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute(array_slice($params, 0)); // Use the same params as the main query
$total_products = (int)$count_stmt->fetchColumn();
$total_pages = ceil($total_products / $products_per_page);

$sql .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";

// Fetch products from the database
try {
    $stmt = $pdo->prepare($sql);
    // Bind search and category params
    foreach ($params as $key => $param) {
        $stmt->bindValue($key + 1, $param);
    }
    // Bind pagination params
    $stmt->bindValue(':limit', $products_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    $products = [];
    set_flash("error", "Could not load gift products.");
}
?>
<?php
require_once "header.php";
?>


    <!-- Main Content -->
    <!-- Category and Search -->
        <form method="GET" action="gifts-products.php">
            <div class="flex flex-col md:flex-row gap-4 mb-8">
                <select name="category" onchange="this.form.submit()" class="bg-white border border-gray-300 px-4 py-2 rounded focus:outline-none focus:border-orange-500">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat) ?>" <?= ($category_filter === $cat) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="flex-1 flex gap-2">
                    <input type="text" name="search" placeholder="Search gift products..." value="<?= htmlspecialchars($search) ?>" class="flex-1 px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-orange-500">
                    <button type="submit" class="bg-orange-500 text-white px-6 py-2 rounded hover:bg-orange-600">🔍</button>
                </div>
            </div>
        </form>

        <form action="process-gift-order.php" method="POST">
            <!-- Selected Products Section -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-8 sticky top-20 z-10">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Selected Gift Products</h3>
                <div class="flex justify-between items-center mb-4 pb-4 border-b border-gray-200">
                    <p class="text-gray-600">Total Products: <span id="total-products" class="font-bold">0</span></p>
                    <p class="text-gray-600">Total Price: <span id="total-price" class="font-bold">₦0.00</span></p>
                </div>
                <button type="submit" class="bg-orange-500 text-white px-6 py-3 rounded font-bold hover:bg-orange-600 float-right">
                    🛒 Purchase Selected
                </button>
            </div>

            <!-- Products List -->
            <div class="space-y-4">
                <?php if (empty($products)): ?>
                    <div class="text-center py-16 text-gray-500">
                        <p class="text-2xl">😕</p>
                        <p>No gift products found matching your criteria.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <div class="bg-white rounded-lg shadow-sm p-4 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <img src="../uploads/<?= htmlspecialchars($product['image'] ?? 'placeholder.png') ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="w-16 h-16 rounded-lg object-cover border">
                                <div>
                                    <h4 class="font-bold text-gray-800"><?= htmlspecialchars($product['name']) ?></h4>
                                    <p class="text-gray-600 text-sm">Price: <span class="font-semibold">₦<?= number_format($product['price'], 2) ?></span></p>
                                </div>
                            </div>
                                                        <div class="flex items-center gap-2">
                                                            <?php if (isset($product['id']) && (int)$product['id'] > 0): // Ensure product ID is valid ?>
                                                                <input
                                                                    type="checkbox"
                                                                    name="product_ids[]"
                                                                    value="<?= (int)$product['id'] ?>"
                                                                    class="h-6 w-6 rounded border-gray-300 text-orange-600 focus:ring-orange-500"
                                                                    data-price="<?= $product['price'] ?>"
                                                                    onchange="updateTotals()"
                                                                >
                                                            <?php else: ?>
                                                                <!-- Optionally, display a message or a disabled checkbox for invalid products -->
                                                                <span class="text-red-500 text-sm">Invalid Product ID</span>
                                                            <?php endif; ?>
                                                        </div>                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <div class="mt-8 flex justify-center items-center space-x-2">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category_filter) ?>" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Previous</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category_filter) ?>" class="px-4 py-2 <?= ($i == $page) ? 'bg-orange-500 text-white' : 'bg-gray-200 text-gray-700' ?> rounded hover:bg-gray-300"><?= $i ?></a>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category_filter) ?>" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Next</a>
                <?php endif; ?>
            </div>
        </form>

    <?php if ($flash): ?>
    <script>
    Toastify({
        text: <?= json_encode($flash['message']); ?>,
        duration: 4000,
        gravity: "top",
        position: "right",
        close: true,
        backgroundColor: <?= json_encode($flash['type']==='success' ? "linear-gradient(to right, #00b09b, #96c93d)" : "linear-gradient(to right, #ff5f6d, #ffc371)") ?>
    }).showToast();
    </script>
    <?php endif; ?>

    <script>
    function updateTotals() {
        let totalProducts = 0;
        let totalPrice = 0;
        const checkboxes = document.querySelectorAll('input[type="checkbox"][name="product_ids[]"]');

        checkboxes.forEach(checkbox => {
            if (checkbox.checked) {
                totalProducts++;
                totalPrice += parseFloat(checkbox.dataset.price) || 0;
            }
        });

        document.getElementById('total-products').textContent = totalProducts;
        document.getElementById('total-price').textContent = '₦' + totalPrice.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    </script>
</main>
</body>
</html>