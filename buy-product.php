<?php
session_start();

require_once "./db/db.php"; // MySQLi connection ($conn)

try {
    // ---- CHECK IF USER IS LOGGED IN ----
    if (!isset($_SESSION['user']['id'])) {
        header("Location: login.php");
        exit();
    }

    if (!isset($_POST['product_id']) || !isset($_POST['quantity'])) {
        die("Invalid request: Missing product_id or quantity");
    }

    $user_id = $_SESSION['user']['id'];
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];

    if ($quantity < 1) {
        die("Invalid quantity");
    }

    // ---- FETCH PRODUCT INFORMATION ----
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("i", $product_id);
    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }
    $product = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$product) {
        die("Product not found");
    }

    $price = (float)$product['price'];
    $total_amount = $price * $quantity;

    // ---- GET USER WALLET BALANCE ----
    $stmt2 = $conn->prepare("SELECT balance FROM users WHERE id = ?");
    if (!$stmt2) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt2->bind_param("i", $user_id);
    if (!$stmt2->execute()) {
        die("Execute failed: " . $stmt2->error);
    }
    $userData = $stmt2->get_result()->fetch_assoc();
    $stmt2->close();

    if (!$userData) {
        die("User not found");
    }

    $balance = (float)$userData['balance'];

    // ---- CHECK BALANCE ----
    if ($balance < $total_amount) {
        header("Location: fund-wallet.php?error=insufficient_balance");
        exit();
    }

    // ---- DEDUCT BALANCE ----
    $newBalance = $balance - $total_amount;
    $stmt3 = $conn->prepare("UPDATE users SET balance = ? WHERE id = ?");
    if (!$stmt3) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt3->bind_param("di", $newBalance, $user_id);
    if (!$stmt3->execute()) {
        die("Execute failed: " . $stmt3->error);
    }
    $stmt3->close();

    // ---- INSERT ORDER ----
    $stmt4 = $conn->prepare("
        INSERT INTO orders (user_id, product_id, price, quantity, total_amount, status, created_at) 
        VALUES (?, ?, ?, ?, ?, 'pending', NOW())
    ");
    if (!$stmt4) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt4->bind_param("iidid", $user_id, $product_id, $price, $quantity, $total_amount);
    if (!$stmt4->execute()) {
        die("Execute failed: " . $stmt4->error);
    }

    $order_id = $stmt4->insert_id;
    $stmt4->close();

    // ---- REDIRECT ----
    header("Location: order-success.php?order_id=" . $order_id);
    exit();

} catch (Exception $e) {
    error_log("Buy Product Error: " . $e->getMessage());
    die("An error occurred while processing your order. Please try again.");
}
?>
