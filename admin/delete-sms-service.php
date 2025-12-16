<?php
require_once "../db/db.php";
require_once "../flash.php";

// Admin authentication check
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    set_flash("error", "Unauthorized access.");
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['id'])) {
    set_flash("error", "No SMS service ID provided.");
    header("Location: admin-sms-verification.php");
    exit;
}

$id = $_GET['id'];

try {
    $stmt = $pdo->prepare("DELETE FROM sms_services WHERE id = ?");
    if ($stmt->execute([$id])) {
        set_flash("success", "SMS service deleted successfully!");
    } else {
        set_flash("error", "Failed to delete SMS service.");
    }
} catch (Exception $e) {
    set_flash("error", "Database error: " . $e->getMessage());
}

header("Location: admin-sms-verification.php");
exit;
?>