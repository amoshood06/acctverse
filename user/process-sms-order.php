<?php
session_start();
require_once "../db/db.php";
require_once "../flash.php";

// User authentication check
if (!isset($_SESSION['user'])) {
    set_flash("error", "You must be logged in to place an order.");
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: sms-verification.php");
    exit;
}

$user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
$service_id = filter_input(INPUT_POST, 'service_id', FILTER_VALIDATE_INT);
$quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT, ['options' => ['default' => 1, 'min_range' => 1]]);

if (!$user_id || !$service_id || !$quantity || $user_id !== $_SESSION['user']['id']) {
    set_flash("error", "Invalid order details. Please try again.");
    header("Location: sms-verification.php");
    exit;
}

$pdo->beginTransaction();

try {
    // 1. Get service details and lock the row to prevent race conditions
    $stmt = $pdo->prepare("SELECT * FROM sms_services WHERE id = ? AND is_active = 1 FOR UPDATE");
    $stmt->execute([$service_id]);
    $service = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$service || $service['available_credits'] < $quantity) {
        throw new Exception("This service is currently unavailable or out of stock.");
    }

    // 2. Get user balance and lock the row
    $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ? FOR UPDATE");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $total_cost = $service['price_per_sms'] * $quantity;

    if (!$user || $user['balance'] < $total_cost) {
        throw new Exception("You have insufficient balance to complete this purchase.");
    }

    // 3. Deduct from user balance
    $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?")->execute([$total_cost, $user_id]);

    // 4. Decrement service credits
    $pdo->prepare("UPDATE sms_services SET available_credits = available_credits - ? WHERE id = ?")->execute([$quantity, $service_id]);

    // 5. Create order record
    $stmt = $pdo->prepare("INSERT INTO sms_orders (user_id, service_id, quantity, cost_per_sms, total_cost, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
    $stmt->execute([$user_id, $service_id, $quantity, $service['price_per_sms'], $total_cost]);

    $pdo->commit();
    set_flash("success", "Your order has been placed successfully! Your number will appear in the 'Latest Orders' table shortly.");
    header("Location: sms-verification.php#latest-orders");

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    set_flash("error", "Order failed: " . $e->getMessage());
    header("Location: sms-verification.php");
}

exit();
