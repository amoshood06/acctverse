<?php
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
    set_flash("error", "Invalid gift product.");
    header("Location: manage-gift-products.php");
    exit;
}

$id = $_GET['id'];

// Fetch image to delete it later
$stmt = $pdo->prepare("SELECT image FROM gift_products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    set_flash("error", "Gift product does not exist.");
    header("Location: manage-gift-products.php");
    exit;
}

try {
    // Delete DB record
    $delete = $pdo->prepare("DELETE FROM gift_products WHERE id = ?");
    $delete->execute([$id]);

    // Delete image file
    if ($product['image'] && file_exists("../uploads/" . $product['image'])) {
        unlink("../uploads/" . $product['image']);
    }

    set_flash("success", "Gift product deleted successfully!");
    header("Location: manage-gift-products.php");
    exit;

} catch (Exception $e) {
    set_flash("error", "Failed to delete gift product.");
    header("Location: manage-gift-products.php");
    exit;
}
?>