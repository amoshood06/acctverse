<?php
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

    // Send order confirmation email
    $user_email = $_SESSION['user']['email'];
    $user_full_name = $user['full_name'];
    $product_name = $product['product_name'];

    $subject = "Your AcctVerse Order Confirmation (#" . $order_id . ")";
    $message = "
        Hello " . htmlspecialchars($user_full_name) . ",<br><br>
        Thank you for your order. Here are the details:<br><br>
        <strong>Order ID:</strong> #" . $order_id . "<br>
        <strong>Product:</strong> " . htmlspecialchars($product_name) . "<br>
        <strong>Quantity:</strong> " . htmlspecialchars($quantity) . "<br>
        <strong>Total Amount:</strong> ₦" . number_format($total_amount, 2) . "<br><br>
        You can view your order history in your dashboard.<br><br>
        Regards,<br>The AcctVerse Team
    ";
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: noreply@acctverse.com\r\n";
    mail($user_email, $subject, $message, $headers);

    // Redirect to success page
    header("Location: order-success.php?order_id=" . $order_id);
    exit();

} catch (Exception $e) {
    error_log("Buy Product Error: " . $e->getMessage());
    die("An error occurred while processing your order: " . htmlspecialchars($e->getMessage()));
}
?>
