<?php
require_once "../db/db.php";
require_once "../flash.php";

// Admin authentication check
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    set_flash("error", "Unauthorized access.");
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: add-gift-slider.php");
    exit;
}

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$link = trim($_POST['link'] ?? '');
$order_num = filter_var($_POST['order_num'] ?? 0, FILTER_VALIDATE_INT);
$status = $_POST['status'] ?? 'active';

// Handle image upload
$image_url = '';
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $target_dir = "../uploads/"; // Assuming 'uploads' folder is in the root
    $image_name = time() . '_' . basename($_FILES["image"]["name"]);
    $target_file = $target_dir . $image_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is a actual image or fake image
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if ($check === false) {
        set_flash("error", "File is not an image.");
        header("Location: add-gift-slider.php");
        exit;
    }

    // Check file size (e.g., 5MB limit)
    if ($_FILES["image"]["size"] > 5000000) {
        set_flash("error", "Sorry, your file is too large.");
        header("Location: add-gift-slider.php");
        exit;
    }

    // Allow certain file formats
    if (!in_array($imageFileType, ['jpg', 'png', 'jpeg', 'gif'])) {
        set_flash("error", "Sorry, only JPG, JPEG, PNG & GIF files are allowed.");
        header("Location: add-gift-slider.php");
        exit;
    }

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        $image_url = "uploads/" . $image_name; // Store path relative to the root
    } else {
        set_flash("error", "Sorry, there was an error uploading your file.");
        header("Location: add-gift-slider.php");
        exit;
    }
} else {
    set_flash("error", "No image uploaded or an upload error occurred.");
    header("Location: add-gift-slider.php");
    exit;
}

if (empty($image_url)) {
    set_flash("error", "Image upload failed, image URL is empty.");
    header("Location: add-gift-slider.php");
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO gift_sliders (image_url, title, description, link, order_num, status) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$image_url, $title, $description, $link, $order_num, $status])) {
        set_flash("success", "Gift slider added successfully!");
    } else {
        set_flash("error", "Failed to add gift slider to database.");
    }
} catch (Exception $e) {
    set_flash("error", "Database error: " . $e->getMessage());
}

header("Location: add-gift-slider.php");
exit;
?>