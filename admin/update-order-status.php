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
    header("Location: admin-orders.php");
    exit;
}

$order_id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
$status = trim($_POST['status'] ?? '');
$admin_note = trim($_POST['admin_note'] ?? '');

// Validate inputs
$allowed_statuses = ['pending', 'completed', 'cancelled'];
if (!$order_id || !in_array($status, $allowed_statuses)) {
    set_flash("error", "Invalid data provided.");
    header("Location: admin-orders.php");
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE orders SET status = ?, admin_note = ? WHERE id = ?");
    $stmt->execute([$status, $admin_note, $order_id]);

    set_flash("success", "Order #" . $order_id . " has been updated successfully.");
} catch (Exception $e) {
    set_flash("error", "Failed to update order. " . $e->getMessage());
}

header("Location: admin-orders.php");
exit;
