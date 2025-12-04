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
    header("Location: add-category.php");
    exit;
}

$category_name = trim($_POST['category_name'] ?? '');

if (empty($category_name)) {
    set_flash("error", "Category name is required.");
    header("Location: add-category.php");
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO categories (name, parent_id) VALUES (?, NULL)");
    $stmt->execute([$category_name]);

    set_flash("success", "Category added successfully!");
    header("Location: add-category.php");
    exit;
} catch (Exception $e) {
    set_flash("error", "Database error: " . $e->getMessage());
    header("Location: add-category.php");
    exit;
}