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
        $formattedTotal = "₦" . number_format($total_amount, 2);

        $message = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Order Confirmation</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table align="center" border="0" cellpadding="0" cellspacing="0" width="600" style="border-collapse: collapse; margin: 20px auto; border: 1px solid #dddddd; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
        <!-- Header -->
        <tr>
            <td align="center" bgcolor="#001957" style="padding: 25px 0; color: #ffffff; font-size: 28px; font-weight: bold;">
                Acctverse
            </td>
        </tr>
        <!-- Body -->
        <tr>
            <td bgcolor="#ffffff" style="padding: 40px 30px;">
                <h1 style="font-size: 24px; margin: 0; color: #333333;">Thank you for your order, {$userName}!</h1>
                <p style="margin: 20px 0; font-size: 16px; line-height: 1.5; color: #555555;">
                    We've received your order and will process it shortly. Here are the details:
                </p>
                
                <!-- Order Details Table -->
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse; margin-top: 20px; border: 1px solid #eeeeee;">
                    <tr><td colspan="2" style="padding: 12px 15px; background-color: #f8f8f8; border-bottom: 1px solid #eeeeee;"><h2 style="font-size: 18px; margin: 0; color: #333333;">Order #{$order_id}</h2></td></tr>
                    <tr><td style="padding: 12px 15px; color: #555555; font-size: 16px; border-bottom: 1px solid #eeeeee;"><strong>Product:</strong></td><td align="right" style="padding: 12px 15px; color: #333333; font-size: 16px; border-bottom: 1px solid #eeeeee;">{$productName}</td></tr>
                    <tr><td style="padding: 12px 15px; color: #555555; font-size: 16px; border-bottom: 1px solid #eeeeee;"><strong>Quantity:</strong></td><td align="right" style="padding: 12px 15px; color: #333333; font-size: 16px; border-bottom: 1px solid #eeeeee;">{$quantity}</td></tr>
                    <tr style="background-color: #f8f8f8;"><td style="padding: 15px; color: #333333; font-size: 18px; font-weight: bold;"><strong>Total Amount:</strong></td><td align="right" style="padding: 15px; color: #333333; font-size: 18px; font-weight: bold;">{$formattedTotal}</td></tr>
                </table>

                <p style="margin: 30px 0 0; text-align: center;">
                    <a href="https://acctverse.com/user/order-history.php" style="background-color: #e85d04; color: #ffffff; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">View Your Orders</a>
                </p>
            </td>
        </tr>
        <!-- Footer -->
        <tr>
            <td bgcolor="#f4f4f4" style="padding: 20px 30px;">
                <p style="margin: 0; color: #888888; font-size: 12px; text-align: center;">
                    You are receiving this email because you made a purchase on Acctverse.
                    <br>
                    &copy; 2025 Acctverse. All rights reserved.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;

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
