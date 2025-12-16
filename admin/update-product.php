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
    header("Location: manage-products.php");
    exit;
}

$id            = $_POST['id'];
$name          = trim($_POST['product_name']);
$price         = trim($_POST['price']);
$category      = trim($_POST['category']);
$description   = trim($_POST['description']); // Assuming description is always provided
$sub_category  = trim($_POST['sub_category'] ?? ''); // Now directly getting the sub-category name
$admin_note    = trim($_POST['admin_note'] ?? '');

// Fetch main category name
$main_category_name = '';
if ($category) { // $category here is the ID
    $stmt_cat_name = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
    $stmt_cat_name->execute([$category]);
    $main_category_name = $stmt_cat_name->fetchColumn();
}

// Fetch existing product
$stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
$stmt->execute([$id]);
$oldProduct = $stmt->fetch();

if (!$oldProduct) {
    set_flash("error", "Product not found.");
    header("Location: manage-products.php");
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
        header("Location: edit-product.php?id=$id");
        exit;
    }

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
        $imageName = $fileName;
    }
}

try {
    $update = $pdo->prepare("
        UPDATE products
        SET product_name=?, price=?, description=?, category=?, sub_category=?, image=?, admin_note=?
        WHERE id=?
    ");

    $update->execute([
        $name, $price, $description, $main_category_name, $sub_category, $imageName, $admin_note, $id
    ]);

    set_flash("success", "Product updated successfully!");
    header("Location: manage-products.php");
    exit;

} catch (Exception $e) {
    set_flash("error", "Error updating: " . $e->getMessage());
    header("Location: edit-product.php?id=$id");
    exit;
}
