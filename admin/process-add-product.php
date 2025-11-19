<?php
session_start();
require_once "../db/db.php";
require_once "../flash.php";

// Admin check
if (!isset($_SESSION['admin'])) {
    set_flash("error", "You must login as Admin.");
    header("Location: login.php");
    exit;
}

// Only POST request
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    set_flash("error", "Invalid request method.");
    header("Location: add-product.php");
    exit;
}

// Get form values
$product_name = trim($_POST['product_name'] ?? '');
$category = trim($_POST['category'] ?? '');
$description = trim($_POST['description'] ?? '');
$price = trim($_POST['price'] ?? '');
$stock = trim($_POST['stock'] ?? '');

// Validate required fields
if ($product_name === '' || $category === '' || $description === '' || $price === '' || $stock === '') {
    set_flash("error", "All fields are required.");
    header("Location: add-product.php");
    exit;
}

// Handle image upload
$imagePath = null;
if (!empty($_FILES['image']['name'])) {
    $targetDir = "../uploads/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

    $fileName = time() . "_" . basename($_FILES['image']['name']);
    $targetFile = $targetDir . $fileName;
    $allowed = ['image/jpeg','image/png','image/jpg'];

    if (!in_array($_FILES['image']['type'], $allowed)) {
        set_flash("error", "Only JPG and PNG images allowed.");
        header("Location: add-product.php");
        exit;
    }

    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
        $imagePath = $fileName;
    } else {
        set_flash("error", "Failed to upload image.");
        header("Location: add-product.php");
        exit;
    }
}

// Insert into database
try {
    $stmt = $pdo->prepare("INSERT INTO products (product_name, category, description, price, stock, image) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$product_name, $category, $description, $price, $stock, $imagePath]);

    set_flash("success", "Product added successfully!");
    header("Location: add-product.php");
    exit;

} catch (Exception $e) {
    set_flash("error", "Database error: " . $e->getMessage());
    header("Location: add-product.php");
    exit;
}
?>
