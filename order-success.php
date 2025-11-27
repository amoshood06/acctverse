<?php
session_start();
require_once "./db/db.php";
require_once "./flash.php";

// Check if user is logged in
if (!isset($_SESSION['user']['id'])) {
    header("Location: login.php");
    exit();
}

// Get order ID from URL
$order_id = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);
if (!$order_id) {
    set_flash("error", "Invalid order ID.");
    header("Location: ./user/order-history.php");
    exit();
}

$user_id = $_SESSION['user']['id'];

try {
    // Fetch order details
    $stmt = $pdo->prepare("
        SELECT 
            o.id, o.total_amount, o.quantity, o.created_at,
            p.product_name, p.image
        FROM orders o
        JOIN products p ON o.product_id = p.id
        WHERE o.id = ? AND o.user_id = ?
    ");
    $stmt->execute([$order_id, $user_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        set_flash("error", "Order not found or you do not have permission to view it.");
        header("Location: ./user/order-history.php");
        exit();
    }
} catch (Exception $e) {
    set_flash("error", "An error occurred while fetching your order details.");
    header("Location: ./user/order-history.php");
    exit();
}

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

            <!-- Order Summary -->
            <div class="border-t border-b border-gray-200 py-6 my-6 text-left">
                <h2 class="text-xl font-semibold text-blue-900 mb-4">Order Summary</h2>
                <div class="flex items-center gap-4">
                    <img src="assets/image/<?= htmlspecialchars($order['image']) ?>" alt="<?= htmlspecialchars($order['product_name']) ?>" class="w-20 h-20 object-cover rounded-lg">
                    <div class="flex-grow">
                        <p class="font-semibold text-gray-800"><?= htmlspecialchars($order['product_name']) ?></p>
                        <p class="text-sm text-gray-500">Quantity: <?= htmlspecialchars($order['quantity']) ?></p>
                    </div>
                    <p class="font-bold text-lg text-blue-900">₦<?= number_format($order['total_amount'], 2) ?></p>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 flex justify-between text-sm">
                    <span class="text-gray-500">Order ID:</span>
                    <span class="font-mono text-gray-700">#<?= htmlspecialchars($order['id']) ?></span>
                </div>
                <div class="mt-2 flex justify-between text-sm">
                    <span class="text-gray-500">Date:</span>
                    <span class="text-gray-700"><?= date("F j, Y, g:i a", strtotime($order['created_at'])) ?></span>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
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