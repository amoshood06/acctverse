<?php
session_start();

require_once "./db/db.php"; // PDO connection (matches products.php)

try {
    // Check if user is logged in
    if (!isset($_SESSION['user']['id'])) {
        header("Location: login.php");
        exit();
    }

    // Validate POST data
    if (!isset($_POST['product_id']) || !isset($_POST['quantity'])) {
        die("Invalid request: Missing product_id or quantity");
    }

    $user_id = $_SESSION['user']['id'];
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];

    // Validate quantity
    if ($quantity < 1) {
        die("Invalid quantity");
    }

    // Fetch product information
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        die("Product not found");
    }

    $price = (float)$product['price'];
    $total_amount = $price * $quantity;

    // Get user wallet balance
    $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
    // Get user wallet balance, email, and name
    $stmt = $pdo->prepare("SELECT balance, email, full_name FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$userData) {
        die("User not found");
    }

    $balance = (float)$userData['balance'];

    // Check if user has sufficient balance
    if ($balance < $total_amount) {
        header("Location: fund-wallet.php?error=insufficient_balance");
        exit();
    }

    // Deduct balance from user account
    $newBalance = $balance - $total_amount;
    $stmt = $pdo->prepare("UPDATE users SET balance = ? WHERE id = ?");
    $stmt->execute([$newBalance, $user_id]);

    // Insert order record
    $stmt = $pdo->prepare("
        INSERT INTO orders (user_id, product_id, price, quantity, total_amount, status, created_at) 
        VALUES (?, ?, ?, ?, ?, 'pending', NOW())
    ");
    $stmt->execute([$user_id, $product_id, $price, $quantity, $total_amount]);

    $order_id = $pdo->lastInsertId();

    // Send email notification
    if ($userData && isset($userData['email'])) {
        $userEmail = $userData['email'];
        $userName = $userData['full_name'] ?? 'Valued Customer';
        $productName = $product['product_name'] ?? 'N/A';

        $subject = "Your Order Confirmation from Acctverse (Order #{$order_id})";
        $message = "
            <html><body>
            <h2>Thank you for your order, {$userName}!</h2>
            <p>Your order has been successfully placed.</p>
            <h3>Order Details:</h3>
            <p><strong>Order ID:</strong> {$order_id}</p>
            <p><strong>Product:</strong> {$productName}</p>
            <p><strong>Quantity:</strong> {$quantity}</p>
            <p><strong>Total Amount:</strong> ₦" . number_format($total_amount, 2) . "</p>
            <p>You can view your order history in your dashboard.</p>
            <p>Thank you for shopping with Acctverse!</p>
            </body></html>";
        $headers = "MIME-Version: 1.0" . "\r\n" . "Content-type:text/html;charset=UTF-8" . "\r\n" . "From: no-reply@acctverse.com" . "\r\n";
        mail($userEmail, $subject, $message, $headers);
    }

    // Redirect to success page
    header("Location: order-success.php?order_id=" . $order_id);
    exit();

} catch (Exception $e) {
    error_log("Buy Product Error: " . $e->getMessage());
    die("An error occurred while processing your order: " . htmlspecialchars($e->getMessage()));
}
?>
