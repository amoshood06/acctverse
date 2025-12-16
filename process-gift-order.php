<?php
require_once "./db/db.php"; 
require_once "./flash.php";

try {
    $pdo->beginTransaction();
    // Check if user is logged in
    if (!isset($_SESSION['user']['id'])) {
        header("Location: login.php"); 
        exit();
    }

    // Validate POST data
    if (!isset($_POST['product_id'], $_POST['recipient_name'], $_POST['recipient_phone'], $_POST['recipient_address'])) {
        die("Invalid request: Missing required gift details.");
    }

    $user_id = $_SESSION['user']['id'];
    $product_id = (int)$_POST['product_id'];
    $quantity = 1; // For gifts, we process one at a time for now.

    // Recipient details
    $recipient_name = trim($_POST['recipient_name']);
    $recipient_phone = trim($_POST['recipient_phone']);
    $recipient_address = trim($_POST['recipient_address']);
    $gift_message = isset($_POST['gift_message']) ? trim($_POST['gift_message']) : '';

    // Fetch product information and lock the row
    $stmt = $pdo->prepare("SELECT * FROM gift_products WHERE id = ? FOR UPDATE");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        $pdo->rollBack();
        set_flash("error", "The requested gift was not found.");
        header("Location: gift.php");
        exit();
    }

    // Check stock
    if ($product['stock'] < $quantity) {
        set_flash("error", "Sorry, this gift is out of stock.");
        header("Location: gift-details.php?id=" . $product_id);
        exit();
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

    // Decrement product stock
    $newStock = $product['stock'] - $quantity;
    $stmt = $pdo->prepare("UPDATE gift_products SET stock = ? WHERE id = ?");
    $stmt->execute([$newStock, $product_id]);

    // Create JSON for recipient details
    $recipient_details_json = json_encode([
        'recipient_name' => $recipient_name,
        'recipient_phone' => $recipient_phone,
        'recipient_address' => $recipient_address,
        'gift_message' => $gift_message,
    ]);

    // Insert order record
    $stmt = $pdo->prepare(
        "INSERT INTO orders (user_id, product_id, product_name, image, price, quantity, total_amount, status, admin_note, created_at)"
        ."VALUES (?, ?, ?, ?, ?, ?, ?, 'completed', ?, NOW())"
    );
    $stmt->execute([$user_id, $product_id, $product['name'], $product['image'], $price, $quantity, $total_amount, $recipient_details_json]);

    // Also insert into transactions table
    $stmt_trans = $pdo->prepare("INSERT INTO transactions (user_id, type, amount, description, status) VALUES (?, 'purchase', ?, ?, 'completed')");
    $stmt_trans->execute([$user_id, $total_amount, "Gift purchase: " . $product['name']]);

    $order_id = $pdo->lastInsertId();

    // Send order confirmation email
    $user_email = $userData['email'];
    $user_full_name = $userData['full_name'];
    $product_name = $product['name'];

    $subject = "Your AcctVerse Gift Order Confirmation (#" . $order_id . ")";
    $message = "
        Hello " . htmlspecialchars($user_full_name) . ",<br><br>
        Thank you for your gift order. Here are the details:<br><br>
        <strong>Order ID:</strong> #" . $order_id . "<br>
        <strong>Gift:</strong> " . htmlspecialchars($product_name) . "<br>
        <strong>Total Amount:</strong> ₦" . number_format($total_amount, 2) . "<br><br>
        <strong>Recipient:</strong> " . htmlspecialchars($recipient_name) . "<br>
        <strong>Recipient Address:</strong> " . htmlspecialchars($recipient_address) . "<br><br>
        We have started processing your gift order. You can view your order history in your dashboard.<br><br>
        Regards,<br>The AcctVerse Team
    ";
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: noreply@acctverse.com\r\n";
    mail($user_email, $subject, $message, $headers);

    // Commit the transaction
    $pdo->commit();

    // Redirect to success page
    header("Location: order-success.php?order_id=" . $order_id);
    exit();

} catch (Exception $e) {
    // If any step fails, roll back
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Gift Order Error: " . $e->getMessage());
    set_flash("error", "An error occurred while processing your gift order. Please try again.");
    header("Location: gift.php");
}
?>
