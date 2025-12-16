<?php
session_start();
require_once "./db/db.php";
require_once "./flash.php";

// Check if user is logged in
if (!isset($_SESSION['user']['id'])) {
    header("Location: login.php");
    exit();
}

// Check for purchased product details in session
$purchased_products = null;
if (isset($_SESSION['purchased_products']) && is_array($_SESSION['purchased_products'])) {
    $purchased_products = $_SESSION['purchased_products'];
    // Unset session variables so they don't show again
    unset($_SESSION['purchased_products']);
} else {
    // If there are no purchased products in the session, redirect
    set_flash("error", "No recent order details to display.");
    header("Location: ./user/order-history.php");
    exit();
}

$user_id = $_SESSION['user']['id'];

include 'main_header.php';
include 'header.php';
?>

<main class="bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow-md p-8 text-center">
            <!-- Success Icon -->
            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>

            <!-- Message -->
            <h1 class="text-3xl font-bold text-blue-900 mb-4">Order Placed Successfully!</h1>
            <p class="text-gray-600 mb-8">
                Thank you for your purchase. A confirmation email has been sent to you with the order details.
            </p>

            <?php if (!empty($purchased_products)): ?>
            <!-- Purchased Account Details -->
            <div class="bg-blue-50 border-l-4 border-blue-500 text-left p-6 rounded-lg my-6">
                <h3 class="text-xl font-bold text-blue-900 mb-3">Your Purchased Account Details</h3>
                <?php foreach ($purchased_products as $product): ?>
                <div class="mb-6 pb-4 border-b border-blue-200 last:border-b-0">
                    <h4 class="font-semibold text-lg text-blue-800">"<?= htmlspecialchars($product['name']) ?>"</h4>
                    <div class="bg-gray-100 p-4 rounded-md mt-2">
                        <pre class="whitespace-pre-wrap text-sm text-gray-800"><?= htmlspecialchars($product['details']) ?></pre>
                    </div>
                </div>
                <?php endforeach; ?>
                <p class="text-sm text-red-600 mt-4 font-semibold">
                    <strong>Important:</strong> Please save these details immediately. They will not be shown again.
                </p>
            </div>
            <?php endif; ?>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center mt-8">
                <a href="products.php" class="w-full sm:w-auto bg-orange-500 text-white px-8 py-3 rounded font-semibold hover:bg-orange-600 transition">
                    Continue Shopping
                </a>
                <a href="./user/order-history.php" class="w-full sm:w-auto bg-gray-200 text-gray-800 px-8 py-3 rounded font-semibold hover:bg-gray-300 transition">
                    View Order History
                </a>
            </div>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>