<?php
session_start();
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

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die("Product not found.");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($product['product_name']) ?> - AcctVerse</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<div class="container mx-auto p-4 lg:p-8">
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="md:flex">
            <!-- Product Image -->
            <div class="md:w-1/2">
                <img src="./uploads/<?= htmlspecialchars($product['image']) ?>" 
                     alt="<?= htmlspecialchars($product['product_name']) ?>"
                     class="w-full h-full object-cover">
            </div>

            <!-- Product Details -->
            <div class="md:w-1/2 p-6 flex flex-col justify-between">
                <div>
                    <!-- Product Name -->
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">
                        <?= htmlspecialchars($product['product_name']) ?>
                    </h1>

                    <!-- Product Description -->
                    <p class="text-gray-700 mb-4">
                        <?= nl2br(htmlspecialchars($product['description'])) ?>
                    </p>

                    <!-- Price -->
                    <p class="text-4xl font-extrabold text-blue-600 mb-6">
                        ₦<?= number_format($product['price'], 2) ?>
                    </p>
                </div>

                <!-- Buy Button -->
                <form action="checkout.php" method="POST">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" 
                            class="w-full bg-green-500 text-white font-bold py-3 px-6 rounded-lg hover:bg-green-600 transition duration-300 ease-in-out transform hover:scale-105">
                        Buy Now
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>

<?php
include 'footer.php';
?>