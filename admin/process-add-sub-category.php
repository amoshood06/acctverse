<?php
$pdo = require_once "../db/db.php";
require_once "../flash.php";

// ==================================================
// ADMIN CHECK
// ==================================================
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    set_flash("error", "Unauthorized access.");
    header("Location: ../login.php");
    exit;
}

// ==================================================
// ONLY POST REQUEST
// ==================================================
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    set_flash("error", "Invalid request method.");
    header("Location: add-sub-category.php");
    exit;
}

$category_name = trim($_POST['sub_category_name'] ?? '');

if (empty($category_name)) {
    set_flash("error", "Sub-category name is required.");
    header("Location: add-sub-category.php");
    exit;
}

try {
    // Insert into the `sub_categories` table, linking to the parent category.
    $stmt = $pdo->prepare("INSERT INTO sub_categories (name) VALUES (?)");
    $stmt->execute([$category_name]);

    set_flash("success", "Sub-category added successfully!");
    header("Location: add-sub-category.php");
    exit;
} catch (Exception $e) {
    set_flash("error", "Database error: " . $e->getMessage());
    header("Location: add-sub-category.php");
    exit;
}