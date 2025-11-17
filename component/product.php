<?php
// filepath: c:\xampp\htdocs\acctverse\component\product.php
require_once 'db/db.php';
require_once 'function/product_functions.php';

$pdo = get_pdo();
$user_id = $_SESSION['user_id'] ?? null;

// Create tables if not exist
create_product_tables($pdo);

// Get categories
$categories = get_product_categories($pdo);

// Sample data - Insert into database if empty
$check = $pdo->query("SELECT COUNT(*) as count FROM products");
$product_count = $check->fetch(PDO::FETCH_ASSOC)['count'];

if ($product_count == 0) {
    // Insert sample products
    $sample_products = [
        ['🎁 ACCTGLOBE GIVEAWAY', 'Facebook', 'USA vs FB | 0-100 friends | 2025', 590.00, null, 55, '🎁'],
        ['🎁 ACCTGLOBE GIVEAWAY', 'Twitter', 'Twitter | 2024-2025 | 0-20 followers', 490.00, null, 139, '🐦'],
        ['🎁 Facebook', 'Facebook', 'USA vs FB | 0-100 friends | 2025', 590.00, null, 55, '👥'],
        ['🎁 Facebook', 'Twitter', 'Twitter | 2024-2025 | 0-20 followers', 490.00, null, 139, '🐦'],
        ['🎁 Facebook (Other Countries)', 'Facebook', 'USA vs FB | 0-100 friends | 2025', 590.00, null, 55, '🌍'],
        ['🎁 Facebook (Random Countries)', 'Facebook', 'Random Country FB accounts', 550.00, 700.00, 40, '🌎'],
        ['💬 Facebook Dating', 'Facebook', 'Dating accounts ready to use', 690.00, null, 25, '💕'],
        ['🔊 Google Voice', 'Google', 'USA Google Voice Numbers', 1290.00, 1500.00, 30, '📞'],
        ['📷 Instagram', 'Instagram', 'Instagram accounts with followers', 890.00, 1200.00, 60, '📸'],
        ['🎵 TikTok', 'TikTok', 'TikTok accounts with engagement', 990.00, 1400.00, 45, '🎬'],
        ['🐦 Twitter', 'Twitter', 'Twitter accounts aged 2024-2025', 490.00, null, 139, '🐦'],
        ['🔒 VPN (Premium)', 'VPN', 'Premium VPN subscription 1 year', 2990.00, 3500.00, 20, '🔐'],
    ];

    foreach ($sample_products as $product) {
        create_product($pdo, [
            'name' => $product[0],
            'category' => $product[1],
            'description' => $product[2],
            'price' => $product[3],
            'original_price' => $product[4],
            'stock_quantity' => $product[5],
            'icon' => $product[6],
            'delivery_time' => 'Instant delivery',
            'is_featured' => rand(0, 1)
        ]);
    }
}

// Get filter parameters
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? 'created_at';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

$filters = [
    'search' => $search,
    'category' => $category,
    'sort' => $sort
];

// Get products
$products_data = get_all_products($pdo, $page, 12, $filters);
$featured = get_featured_products($pdo, 8);
?>

<div class="acctverse_product">
    <!-- GIVEAWAY SECTION -->
    <div class="mx-auto p-4 md:p-6">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3 mb-6">
            <h1 class="text-lg sm:text-xl md:text-2xl font-bold text-white bg-red-700 px-4 py-2 rounded-lg whitespace-normal break-words inline-block">
                🎁 ACCTGLOBE GIVEAWAY
            </h1>
            <a href="?category=giveaway&category=Facebook&category=Google&category=Instagram&category=TikTok&category=Twitter&category=VPN" class="w-full md:w-auto px-4 py-2 border-2 border-green-500 text-green-500 rounded-lg hover:bg-green-50 text-sm font-semibold text-center transition">
                View All
            </a>
        </div>

        <!-- Products Container -->
        <div class="space-y-4">
            <?php
            $giveaway_products = array_slice($products_data['products'], 0, 2);
            if (!empty($giveaway_products)):
                foreach ($giveaway_products as $product):
            ?>
                <div class="bg-white rounded-lg border border-gray-200 p-4 md:p-6 hover:shadow-lg transition">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <!-- Left Content -->
                        <div class="flex gap-4 flex-1">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-xl">
                                    <?php echo $product['icon'] ?? '📦'; ?>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-gray-900 text-sm md:text-base line-clamp-2"><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p class="text-green-600 text-sm font-semibold mt-1">✓ In Stock: <span class="font-bold"><?php echo $product['stock_quantity']; ?></span> qty.</p>
                                <p class="text-gray-700 text-sm mt-1">Per Quantity: <span class="font-bold text-gray-900">₦<?php echo number_format($product['price'], 2); ?></span></p>
                            </div>
                        </div>

                        <!-- Right Button -->
                        <div class="flex-shrink-0 w-full md:w-auto">
                            <button type="button" onclick="viewProductDetail(<?php echo $product['id']; ?>)" class="w-full md:w-auto px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 text-sm font-semibold transition">
                                ✓ View Details
                            </button>
                        </div>
                    </div>
                </div>
            <?php
                endforeach;
            endif;
            ?>
        </div>
    </div>

    <!-- DYNAMIC CATEGORY SECTIONS -->
    <?php
    $category_groups = [];
    foreach ($products_data['products'] as $product) {
        if (!isset($category_groups[$product['category']])) {
            $category_groups[$product['category']] = [];
        }
        $category_groups[$product['category']][] = $product;
    }

    foreach ($category_groups as $cat_name => $cat_products):
    ?>
        <div class="mx-auto p-4 md:p-6">
            <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3 mb-6">
                <h1 class="text-lg sm:text-xl md:text-2xl font-bold text-white bg-red-700 px-4 py-2 rounded-lg whitespace-normal break-words inline-block">
                    🎁 <?php echo htmlspecialchars(strtoupper($cat_name)); ?>
                </h1>
                <a href="?category=<?php echo urlencode($cat_name); ?>" class="w-full md:w-auto px-4 py-2 border-2 border-green-500 text-green-500 rounded-lg hover:bg-green-50 text-sm font-semibold text-center transition">
                    View All
                </a>
            </div>

            <div class="space-y-4">
                <?php foreach (array_slice($cat_products, 0, 2) as $product): ?>
                    <div class="bg-white rounded-lg border border-gray-200 p-4 md:p-6 hover:shadow-lg transition">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <!-- Left Content -->
                            <div class="flex gap-4 flex-1">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-xl">
                                        <?php echo $product['icon'] ?? '📦'; ?>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-gray-900 text-sm md:text-base line-clamp-2"><?php echo htmlspecialchars($product['name']); ?></h3>
                                    <p class="text-green-600 text-sm font-semibold mt-1">✓ In Stock: <span class="font-bold"><?php echo $product['stock_quantity']; ?></span> qty.</p>
                                    <p class="text-gray-700 text-sm mt-1">Per Quantity: <span class="font-bold text-gray-900">₦<?php echo number_format($product['price'], 2); ?></span></p>
                                    <?php if ($product['rating'] > 0): ?>
                                        <p class="text-yellow-500 text-xs mt-1">★ <?php echo number_format($product['rating'], 1); ?> (<?php echo $product['total_ratings']; ?> reviews)</p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Right Button -->
                            <div class="flex-shrink-0 w-full md:w-auto">
                                <button type="button" onclick="viewProductDetail(<?php echo $product['id']; ?>)" class="w-full md:w-auto px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 text-sm font-semibold transition">
                                    ✓ View Details
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- FEATURED PRODUCTS -->
    <?php if (!empty($featured)): ?>
        <div class="mx-auto p-4 md:p-6">
            <h1 class="text-lg sm:text-xl md:text-2xl font-bold text-white bg-orange-600 px-4 py-2 rounded-lg whitespace-normal break-words inline-block mb-6">
                ⭐ FEATURED PRODUCTS
            </h1>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <?php foreach ($featured as $product): ?>
                    <div class="bg-white rounded-lg border border-gray-200 p-4 hover:shadow-lg transition cursor-pointer" onclick="viewProductDetail(<?php echo $product['id']; ?>)">
                        <div class="w-full h-20 bg-gradient-to-br from-orange-400 to-orange-600 rounded-lg flex items-center justify-center text-2xl mb-3">
                            <?php echo $product['icon'] ?? '📦'; ?>
                        </div>
                        <h4 class="font-semibold text-gray-800 mb-2 line-clamp-2 text-sm"><?php echo htmlspecialchars($product['name']); ?></h4>
                        <p class="text-orange-500 font-bold mb-2">₦<?php echo number_format($product['price'], 2); ?></p>
                        <p class="text-xs text-green-600 font-semibold">✓ <?php echo $product['stock_quantity']; ?> in stock</p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Product Detail Modal -->
<div id="productModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-lg max-w-2xl w-full max-h-96 overflow-y-auto">
        <div class="p-6">
            <button type="button" onclick="closeProductModal()" class="float-right text-gray-500 hover:text-gray-700 text-2xl font-bold">&times;</button>
            
            <div id="productContent">
                <!-- Content loaded via AJAX -->
            </div>
        </div>
    </div>
</div>

<script>
function viewProductDetail(productId) {
    if (!productId || productId <= 0) {
        alert('Invalid product ID');
        return;
    }
    
    // Load product details via AJAX
    fetch(`product-detail-ajax.php?id=${productId}`)
        .then(response => response.text())
        .then(data => {
            document.getElementById('productContent').innerHTML = data;
            document.getElementById('productModal').classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load product details');
        });
}

function closeProductModal() {
    document.getElementById('productModal').classList.add('hidden');
    document.getElementById('productContent').innerHTML = '';
}

// Close modal when clicking outside
document.getElementById('productModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeProductModal();
    }
});
</script>