<?php
require_once "../db/db.php";
require_once "../flash.php";

// Admin authentication check
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    set_flash("error", "Unauthorized access.");
    header("Location: ../login.php");
    exit;
}

// Ensure it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin-sms-orders.php");
    exit;
}

$order_id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
$status = trim($_POST['status'] ?? '');

// Validate inputs
$allowed_statuses = ['pending', 'completed', 'cancelled'];
if (!$order_id || !in_array($status, $allowed_statuses)) {
    set_flash("error", "Invalid data provided.");
    header("Location: admin-sms-orders.php");
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE sms_orders SET status = ? WHERE id = ?");
    $stmt->execute([$status, $order_id]);

    set_flash("success", "SMS Order #" . $order_id . " has been updated successfully.");
} catch (Exception $e) {
    set_flash("error", "Failed to update SMS order. " . $e->getMessage());
}

header("Location: admin-sms-orders.php");
exit;