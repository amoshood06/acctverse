<?php
session_start();
require_once "./db/db.php"; // MySQLi connection ($conn)

// ---- CHECK IF USER IS LOGGED IN ----
if (!isset($_SESSION['user']['id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user']['id'];

// ---- CHECK PRODUCT ID FROM POST ----
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

if ($product_id <= 0) {
    die("Invalid product");
}

// ---- FETCH PRODUCT INFORMATION ----
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    die("Product not found");
}

$price = (float)$product['price'];
$total_amount = $price * $quantity;

// ---- GET USER WALLET BALANCE ----
$stmt2 = $conn->prepare("SELECT balance FROM users WHERE id = ?");
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$userData = $stmt2->get_result()->fetch_assoc();
$stmt2->close();

$balance = (float)$userData['balance'];

// ---- CHECK IF BALANCE IS ENOUGH ----
if ($balance < $total_amount) {
    header("Location: fund-wallet.php?error=insufficient_balance");
    exit();
}

// ---- DEDUCT FROM WALLET ----
$newBalance = $balance - $total_amount;
$stmt3 = $conn->prepare("UPDATE users SET balance = ? WHERE id = ?");
$stmt3->bind_param("di", $newBalance, $user_id);
$stmt3->execute();
$stmt3->close();

// ---- INSERT INTO ORDERS TABLE ----
$stmt4 = $conn->prepare("
    INSERT INTO orders (user_id, product_id, price, quantity, total_amount, status, created_at) 
    VALUES (?, ?, ?, ?, ?, 'pending', NOW())
");
$stmt4->bind_param("iidid", $user_id, $product_id, $price, $quantity, $total_amount);
$stmt4->execute();

$order_id = $stmt4->insert_id;
$stmt4->close();

// ---- REDIRECT TO SUCCESS PAGE ----
header("Location: order-success.php?order_id=" . $order_id);
exit();
?>
