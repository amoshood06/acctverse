<?php
session_start();
require_once "../db/db.php";
require_once "../flash.php";

// ==================================================
// ADMIN CHECK
// ==================================================
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    set_flash("error", "Unauthorized access.");
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['id'])) {
    set_flash("error", "Invalid product.");
    header("Location: manage-products.php");
    exit;
}

$id = $_GET['id'];

// Fetch image to delete it later
$stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    set_flash("error", "Product does not exist.");
    header("Location: manage-products.php");
    exit;
}

try {
    // Delete DB record
    $delete = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $delete->execute([$id]);

    // Delete image file
    if ($product['image'] && file_exists("../uploads/" . $product['image'])) {
        unlink("../uploads/" . $product['image']);
    }

    set_flash("success", "Product deleted successfully!");
    header("Location: manage-products.php");
    exit;

} catch (Exception $e) {
    set_flash("error", "Failed to delete product.");
    header("Location: manage-products.php");
    exit;
}
