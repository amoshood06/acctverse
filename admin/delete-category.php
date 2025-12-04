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
    set_flash("error", "Invalid category ID provided.");
    header("Location: manage-categories.php");
    exit;
}

try {
    $pdo->beginTransaction();

    // First, delete all sub-categories linked to this parent category
    $stmt_sub = $pdo->prepare("DELETE FROM sub_categories WHERE category_id = ?");
    $stmt_sub->execute([$id]);

    // Then, delete the parent category itself
    $stmt_cat = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt_cat->execute([$id]);

    $pdo->commit();
    set_flash("success", "Category and all its sub-categories were deleted successfully.");
} catch (Exception $e) {
    $pdo->rollBack();
    set_flash("error", "Failed to delete the category. Error: " . $e->getMessage());
}

header("Location: manage-categories.php");
exit;