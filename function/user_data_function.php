<?php
require_once "db.php";
require_once "flash.php";
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$first = trim($_POST['first_name'] ?? '');
$last  = trim($_POST['last_name'] ?? '');

if (!$first || !$last) {
    set_flash("error", "All fields are required.");
    header("Location: user_data.php");
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ? WHERE id = ?");
    $stmt->execute([$first, $last, $_SESSION['user']['id']]);

    set_flash("success", "Profile completed successfully!");
    header("Location: dashboard.php");
    exit;

} catch (Exception $e) {
    set_flash("error", "Error updating profile.");
    header("Location: user_data.php");
    exit;
}
