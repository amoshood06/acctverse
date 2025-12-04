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
    header("Location: add-product.php");
    exit;
}

// ==================================================
// GET FORM INPUTS
// ==================================================
$product_name = trim($_POST['product_name'] ?? '');
$category     = trim($_POST['category'] ?? '');
$description  = trim($_POST['description'] ?? '');
$price        = trim($_POST['price'] ?? ''); // Assuming price is always provided
$sub_category = trim($_POST['sub_category'] ?? ''); // Now directly getting the sub-category name
$admin_note   = trim($_POST['admin_note'] ?? '');

// Fetch main category name
$main_category_name = '';
if ($category) { // $category here is the ID
    $stmt_cat_name = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
    $stmt_cat_name->execute([$category]);
    $main_category_name = $stmt_cat_name->fetchColumn();
}

// ==================================================
// REQUIRED VALIDATION
// ==================================================
if ($product_name === '' || $category === '' || $description === '' || $price === '') {
    set_flash("error", "All fields are required.");
    header("Location: add-product.php");
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
        header("Location: add-product.php");
        exit;
    }

    if (!move_uploaded_file($fileTmp, $targetFile)) {
        set_flash("error", "Image upload failed.");
        header("Location: add-product.php");
        exit;
    }

    $imagePath = $fileName;
}

// ==================================================
// INSERT PRODUCT INTO DATABASE
// ==================================================
try {
    $stmt = $pdo->prepare("
        INSERT INTO products (product_name, category, sub_category, description, price, image, admin_note)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $product_name,
        $main_category_name,
        $sub_category, // Use the sub-category name directly
        $description,
        $price,
        $imagePath,
        $admin_note
    ]);

    set_flash("success", "Product added successfully!");
    header("Location: add-product.php");
    exit;

} catch (Exception $e) {

    set_flash("error", "Database error: " . $e->getMessage());
    header("Location: add-product.php");
    exit;
}
?>
