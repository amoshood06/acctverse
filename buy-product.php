<?php
// ------------------------
// buy-product.php
// ------------------------

// Enable error reporting (for debugging)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once "./db/db.php"; // Make sure $conn is your MySQLi connection

// ---- CHECK IF USER IS LOGGED IN ----
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

// ---- CHECK PRODUCT ID FROM POST ----
if (!isset($_POST['product_id'])) {
    die("Invalid product");
}

$product_id = (int)$_POST['product_id'];
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

// ---- FETCH PRODUCT INFORMATION ----
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
if (!$stmt) { die("Prepare failed: " . $conn->error); }

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
if (!$stmt2) { die("Prepare failed: " . $conn->error); }

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
if (!$stmt3) { die("Prepare failed: " . $conn->error); }

$stmt3->bind_param("di", $newBalance, $user_id);
$stmt3->execute();
$stmt3->close();

// ---- INSERT INTO ORDERS TABLE ----
$stmt4 = $conn->prepare("
    INSERT INTO orders (user_id, product_id, price, quantity, total_amount, status, created_at)
    VALUES (?, ?, ?, ?, ?, 'pending', NOW())
");
if (!$stmt4) { die("Prepare failed: " . $conn->error); }

$stmt4->bind_param("iidid", $user_id, $product_id, $price, $quantity, $total_amount);
$stmt4->execute();

$order_id = $stmt4->insert_id;
$stmt4->close();

// ---- REDIRECT TO SUCCESS PAGE ----
header("Location: order-success.php?order_id=" . $order_id);
exit();
?>
