<?php
$pdo = require_once "../db/db.php";
require_once "../flash.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    set_flash("error", "Unauthorized.");
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    set_flash("error", "Invalid request.");
    header("Location: manage-gift-products.php");
    exit;
}

$id       = $_POST['id'];
$name     = trim($_POST['name']);
$price    = trim($_POST['price']);
$category = trim($_POST['category']);
$details  = trim($_POST['details']);
$stock    = trim($_POST['stock']);

// Fetch existing gift product
$stmt = $pdo->prepare("SELECT image FROM gift_products WHERE id = ?");
$stmt->execute([$id]);
$oldProduct = $stmt->fetch();

if (!$oldProduct) {
    set_flash("error", "Gift product not found.");
    header("Location: manage-gift-products.php");
    exit;
}

// Handle image upload
$imageName = $oldProduct["image"];

if (!empty($_FILES["image"]["name"])) {
    $targetDir = "../uploads/";

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $fileName = time() . "_" . basename($_FILES["image"]["name"]);
    $targetFile = $targetDir . $fileName;

    $allowed = ["image/png", "image/jpg", "image/jpeg"];

    if (!in_array($_FILES["image"]["type"], $allowed)) {
        set_flash("error", "Only JPG/PNG images allowed.");
        header("Location: edit-gift-product.php?id=$id");
        exit;
    }

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
        $imageName = $fileName;
    }
}

try {
    $update = $pdo->prepare("
        UPDATE gift_products
        SET name=?, price=?, details=?, category=?, image=?, stock=?
        WHERE id=?
    ");

    $update->execute([
        $name, $price, $details, $category, $imageName, $stock, $id
    ]);

    set_flash("success", "Gift product updated successfully!");
    header("Location: manage-gift-products.php");
    exit;

} catch (Exception $e) {
    set_flash("error", "Error updating: " . $e->getMessage());
    header("Location: edit-gift-product.php?id=$id");
    exit;
}
?>