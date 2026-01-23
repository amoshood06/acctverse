<?php
session_start();
require_once "./db/db.php";
require_once "./flash.php";

// Check if user is logged in
if (!isset($_SESSION['user']['id'])) {
    set_flash("error", "Please log in to complete your purchase.");
    header("Location: login.php");
    exit();
}

// Validate GET data
if (!isset($_GET['sub_category']) || !isset($_GET['quantity'])) {
    set_flash("error", "Invalid request: Missing product details for confirmation.");
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user']['id'];
$sub_category = urldecode($_GET['sub_category']);
$quantity = (int)$_GET['quantity'];

if ($quantity < 1) {
    set_flash("error", "Invalid quantity.");
    header("Location: index.php");
    exit();
}

try {
    // Fetch available products for the sub_category
    // We need their individual IDs, names, and prices
    $stmt = $pdo->prepare("
        SELECT id, product_name, price, category
        FROM products
        WHERE sub_category = ?
        LIMIT ?
    ");
    $stmt->bindValue(1, $sub_category);
    $stmt->bindValue(2, $quantity, PDO::PARAM_INT);
    $stmt->execute();
    $available_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($available_products) < $quantity) {
        set_flash("error", "Insufficient stock for " . htmlspecialchars($sub_category) . ". Available: " . count($available_products) . ". Requested: " . $quantity);
        header("Location: index.php");
        exit();
    }

    // Calculate total amount
    $total_amount = 0;
    $first_product_category = '';
    $first_product_id_for_template = 0; // To pass a representative product_id to buy-product.php if needed
    if (!empty($available_products)) {
        foreach($available_products as $product) {
            $total_amount += $product['price'];
        }
        $first_product_category = $available_products[0]['category'];
        $first_product_id_for_template = $available_products[0]['id']; // Using the first product ID as a template
    }


    // Get user wallet balance
    $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    $balance = (float)$userData['balance'];

    // Check if user has sufficient balance
    if ($balance < $total_amount) {
        set_flash("error", "Insufficient balance to complete the purchase. Current balance: ₦" . number_format($balance, 2));
        header("Location: ./user/create-wallet.php"); // Redirect to a page where user can add funds
        exit();
    }

} catch (Exception $e) {
    error_log("Multi-purchase confirmation error: " . $e->getMessage());
    set_flash("error", "An error occurred while preparing your purchase. Please try again.");
    header("Location: index.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Your Purchase - <?= htmlspecialchars($sub_category) ?> (x<?= $quantity ?>)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/mobile.css">
    <!-- Include your header if it contains necessary meta/styles -->
</head>
<body class="bg-gray-100 min-h-screen flex flex-col justify-between">

    <?php include 'header.php'; // Or main_header.php, depending on project structure ?>

    <main class="flex-grow container mx-auto p-4 md:p-6 mt-8">
        <div class="max-w-3xl mx-auto bg-white rounded-lg shadow-md p-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-4">Confirm Your Purchase</h1>

            <?php display_flash_message(); ?>

            <p class="text-gray-700 mb-4">You are about to purchase <strong class="text-blue-600"><?= $quantity ?></strong> unit(s) of <strong class="text-blue-600"><?= htmlspecialchars($sub_category) ?></strong>.</p>

            <div class="border rounded-lg p-4 mb-6 bg-gray-50">
                <h2 class="text-xl font-semibold text-gray-800 mb-3">Accounts to be purchased:</h2>
                <ul class="list-disc pl-5 space-y-2">
                    <?php foreach ($available_products as $product): ?>
                        <li class="text-gray-700">
                            <strong><?= htmlspecialchars($product['product_name']) ?></strong> 
                            <span class="text-sm text-gray-500">(Price: ₦<?= number_format($product['price'], 2) ?>)</span>
                            <!-- Assuming product_name is the "account detail" -->
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="border rounded-lg p-4 mb-6 bg-yellow-50 border-yellow-200">
                <h2 class="text-xl font-semibold text-gray-800 mb-3">Admin Note:</h2>
                <p class="text-gray-700">
                    <!-- Placeholder for Admin Note -->
                    Please review the account details carefully before confirming your purchase.
                    Once purchased, the account details will be accessible in your order history.
                </p>
            </div>

            <div class="flex justify-between items-center bg-gray-100 p-4 rounded-lg mb-6">
                <p class="text-lg font-bold text-gray-800">Total Price:</p>
                <p class="text-2xl font-bold text-green-600">₦<?= number_format($total_amount, 2) ?></p>
            </div>

            <form action="buy-product.php" method="POST" class="space-y-4">
                <?php foreach ($available_products as $product): ?>
                    <input type="hidden" name="product_ids[]" value="<?= $product['id'] ?>">
                <?php endforeach; ?>
                <!-- This is a representative product_id for the sub_category, mainly for consistency with buy-product.php's original logic -->
                <input type="hidden" name="product_id" value="<?= $first_product_id_for_template ?>">
                <input type="hidden" name="quantity" value="<?= count($available_products) ?>">
                <input type="hidden" name="total_amount" value="<?= $total_amount ?>">
                <input type="hidden" name="sub_category" value="<?= htmlspecialchars($sub_category) ?>">
                <input type="hidden" name="category" value="<?= htmlspecialchars($first_product_category) ?>">

                <button type="submit" class="w-full px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 font-semibold text-lg transition duration-200">
                    Confirm Purchase
                </button>
                <a href="javascript:history.back()" class="block w-full text-center px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-semibold text-lg transition duration-200">
                    Cancel
                </a>
            </form>
        </div>
    </main>

    <?php include 'footer.php'; // Or main_footer.php ?>

</body>
</html>
