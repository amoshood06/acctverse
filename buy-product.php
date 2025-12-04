<?php
require_once "./db/db.php"; 
require_once "./flash.php";

try {
    $pdo->beginTransaction();
    // Check if user is logged in
    if (!isset($_SESSION['user']['id'])) {
        header("Location: login.php"); 
        // No transaction started, safe to exit
        exit();
    }

    // Validate POST data
    if (!isset($_POST['product_id']) || !isset($_POST['quantity'])) {
        die("Invalid request: Missing product_id or quantity");
    }

    $user_id = $_SESSION['user']['id'];
    $product_id = (int)$_POST['product_id'];
    $quantity = 1; // Set quantity to 1

    // Validate quantity
    if ($quantity < 1) {
        die("Invalid quantity");
    }

    // Fetch product information and lock the row for this transaction
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? FOR UPDATE");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        die("Product not found");
    }

    $price = (float)$product['price'];
    $total_amount = $price * $quantity;

    // Get user wallet balance and lock the row
    $stmt = $pdo->prepare("SELECT balance, email, full_name FROM users WHERE id = ? FOR UPDATE");
    $stmt->execute([$user_id]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$userData) {
        die("User not found");
    }

    $balance = (float)$userData['balance'];

    // Check if user has sufficient balance
    if ($balance < $total_amount) {
        set_flash("error", "Insufficient balance to complete the purchase.");
        header("Location: ./user/create-wallet.php");
        exit();
    }

    // Deduct balance from user account
    $newBalance = $balance - $total_amount;
    $stmt = $pdo->prepare("UPDATE users SET balance = ? WHERE id = ?");
    $stmt->execute([$newBalance, $user_id]);

    // Delete the product to remove it from stock
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$product_id]);

    // Insert order record
    $stmt = $pdo->prepare("
        INSERT INTO orders (user_id, product_id, product_name, image, price, quantity, total_amount, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'completed', NOW())
    ");
    $stmt->execute([$user_id, $product_id, $product['product_name'], $product['image'], $price, $quantity, $total_amount]);

    // Also insert into transactions table for a complete financial record
    $stmt_trans = $pdo->prepare("INSERT INTO transactions (user_id, type, amount, description, status) VALUES (?, 'purchase', ?, ?, 'completed')");
    $stmt_trans->execute([$user_id, $total_amount, "Purchase of " . $product['product_name']]);

    $order_id = $pdo->lastInsertId();

    // Send order confirmation email
    $user_email = $userData['email'];
    $user_full_name = $userData['full_name'];
    $product_name = $product['product_name'];

    $subject = "Your AcctVerse Order Confirmation (#" . $order_id . ")";
    $message = "
        Hello " . htmlspecialchars($user_full_name) . ",<br><br>
        Thank you for your order. Here are the details:<br><br>
        <strong>Order ID:</strong> #" . $order_id . "<br>
        <strong>Product:</strong> " . htmlspecialchars($product_name) . "<br>
        <strong>Quantity:</strong> 1<br>
        <strong>Total Amount:</strong> ₦" . number_format($total_amount, 2) . "<br><br>
        You can view your order history in your dashboard.<br><br>
        Regards,<br>The AcctVerse Team
    ";
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: noreply@acctverse.com\r\n";
    mail($user_email, $subject, $message, $headers);

    // If all steps are successful, commit the transaction
    $pdo->commit();

    // Redirect to success page
    header("Location: order-success.php?order_id=" . $order_id);
    exit();

} catch (Exception $e) {
    // If any step fails, roll back all database changes
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Buy Product Error: " . $e->getMessage());
    set_flash("error", "An error occurred while processing your order. Please try again.");
    header("Location: index.php"); // Redirect to a safe page
}
?>
