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

// ==================================================
// ONLY POST REQUEST
// ==================================================
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    set_flash("error", "Invalid request method.");
    header("Location: add-gift-product.php");
    exit;
}

// ==================================================
// GET FORM INPUTS
// ==================================================
$name = trim($_POST['name'] ?? '');
$category = trim($_POST['category'] ?? '');
$details = trim($_POST['details'] ?? '');
$price = trim($_POST['price'] ?? '');
$stock = trim($_POST['stock'] ?? '');

// ==================================================
// REQUIRED VALIDATION
// ==================================================
if ($name === '' || $category === '' || $details === '' || $price === '' || $stock === '') {
    set_flash("error", "All fields are required.");
    header("Location: add-gift-product.php");
    exit;
}

// Validate price and stock
if (!is_numeric($price) || $price < 0) {
    set_flash("error", "Invalid price.");
    header("Location: add-gift-product.php");
    exit;
}

if (!is_numeric($stock) || $stock < 0) {
    set_flash("error", "Invalid stock quantity.");
    header("Location: add-gift-product.php");
    exit;
}

// ==================================================
// IMAGE UPLOAD
// ==================================================
$imagePath = null;

if (!empty($_FILES['image']['name'])) {

    $targetDir = "../uploads/";
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $fileTmp  = $_FILES['image']['tmp_name'];
    $fileName = time() . "_" . basename($_FILES['image']['name']);
    $targetFile = $targetDir . $fileName;

    $allowed = ["image/jpeg", "image/png", "image/jpg"];

    if (!in_array($_FILES['image']['type'], $allowed)) {
        set_flash("error", "Only JPG and PNG images allowed.");
        header("Location: add-gift-product.php");
        exit;
    }

    if (!move_uploaded_file($fileTmp, $targetFile)) {
        set_flash("error", "Image upload failed.");
        header("Location: add-gift-product.php");
        exit;
    }

    $imagePath = $fileName;
}

// ==================================================
// INSERT GIFT PRODUCT INTO DATABASE
// ==================================================
try {
    $stmt = $pdo->prepare("
        INSERT INTO gift_products (name, category, details, price, image, stock)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $name,
        $category,
        $details,
        $price,
        $imagePath,
        $stock
    ]);

    set_flash("success", "Gift product added successfully!");
    header("Location: add-gift-product.php");
    exit;

} catch (Exception $e) {

    set_flash("error", "Database error: " . $e->getMessage());
    header("Location: add-gift-product.php");
    exit;
}
?>