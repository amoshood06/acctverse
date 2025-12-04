<?php
$pdo = require_once "../db/db.php";
require_once "../flash.php";

// ==================================================
// ADMIN CHECK
// ==================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    set_flash("error", "Unauthorized access.");
    header("Location: ../login.php");
    exit;
}

// ==================================================
// VALIDATE ID
// ==================================================
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    set_flash("error", "Invalid sub-category ID provided.");
    header("Location: manage-categories.php");
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM sub_categories WHERE id = ?");
    $stmt->execute([$id]);
    set_flash("success", "Sub-category deleted successfully.");
} catch (Exception $e) {
    set_flash("error", "Failed to delete the sub-category. Error: " . $e->getMessage());
}

header("Location: manage-categories.php");
exit;