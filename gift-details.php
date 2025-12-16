<?php
require_once "./db/db.php";
require_once "./flash.php";

// main header
include 'main_header.php';
// header
include 'header.php';

if (!isset($_GET['id'])) {
    die("Product ID is missing.");
}

$product_id = (int)$_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM gift_products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die("Product not found.");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gift: <?= htmlspecialchars($product['name']) ?> - AcctVerse</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<div class="container mx-auto p-4 lg:p-8">
    <form action="process-gift-order.php" method="POST">
        <div class="lg:flex lg:gap-8">

            <!-- Left Side: Product and Recipient Info -->
            <div class="lg:w-2/3">
                <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                    <!-- Product Details -->
                    <div class="flex flex-col sm:flex-row gap-6">
                        <img src="./uploads/<?= htmlspecialchars($product['image']) ?>" 
                             alt="<?= htmlspecialchars($product['name']) ?>"
                             class="w-full sm:w-48 h-48 object-cover rounded-lg">
                        
                        <div class="flex-grow">
                            <h1 class="text-2xl font-bold text-gray-900 mb-2">
                                <?= htmlspecialchars($product['name']) ?>
                            </h1>
                            <p class="text-gray-700 mb-4">
                                <?= nl2br(htmlspecialchars($product['details'])) ?>
                            </p>
                            <p class="text-3xl font-extrabold text-blue-600">
                                ₦<?= number_format($product['price'], 2) ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Recipient Details -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Recipient's Details</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="recipient_name" class="block text-sm font-medium text-gray-700 mb-1">Recipient's Full Name</label>
                            <input type="text" id="recipient_name" name="recipient_name" required
                                   class="w-full p-2 border rounded-md">
                        </div>
                        <div>
                            <label for="recipient_phone" class="block text-sm font-medium text-gray-700 mb-1">Recipient's Phone Number</label>
                            <input type="tel" id="recipient_phone" name="recipient_phone" required
                                   class="w-full p-2 border rounded-md">
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <label for="recipient_address" class="block text-sm font-medium text-gray-700 mb-1">Recipient's Full Address</label>
                        <textarea id="recipient_address" name="recipient_address" rows="3" required
                                  class="w-full p-2 border rounded-md"></textarea>
                    </div>

                    <div class="mt-4">
                        <label for="gift_message" class="block text-sm font-medium text-gray-700 mb-1">Gift Message (Optional)</label>
                        <textarea id="gift_message" name="gift_message" rows="3"
                                  class="w-full p-2 border rounded-md"></textarea>
                    </div>
                </div>
            </div>

            <!-- Right Side: Order Summary and Checkout -->
            <div class="lg:w-1/3 mt-6 lg:mt-0">
                <div class="bg-white rounded-lg shadow-lg p-6 sticky top-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Order Summary</h2>
                    
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-gray-600"><?= htmlspecialchars($product['name']) ?> (x1)</span>
                        <span class="font-semibold">₦<?= number_format($product['price'], 2) ?></span>
                    </div>

                    <div class="border-t border-gray-200 mt-4 pt-4">
                        <div class="flex justify-between items-center font-bold text-lg">
                            <span>Total</span>
                            <span>₦<?= number_format($product['price'], 2) ?></span>
                        </div>
                    </div>

                    <div class="mt-6">
                        <button type="submit"
                                class="w-full bg-green-500 text-white font-bold py-3 px-6 rounded-lg hover:bg-green-600 transition duration-300">
                            Proceed to Checkout
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>

</body>
</html>

<?php
include 'footer.php';
?>