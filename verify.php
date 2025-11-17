<?php
require_once "./db/db.php";
require_once "flash.php";
session_start();

if (!isset($_GET['token'])) {
    set_flash("error", "Invalid verification link.");
    header("Location: login.php");
    exit;
}

$token = $_GET['token'];

$stmt = $pdo->prepare("SELECT id FROM users WHERE verify_token = ? AND is_verified = 0");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    set_flash("error", "Invalid or expired verification token.");
    header("Location: login.php");
    exit;
}

$update = $pdo->prepare("UPDATE users SET is_verified = 1, verify_token = NULL WHERE id = ?");
$update->execute([$user['id']]);

set_flash("success", "Email verified successfully! You can now login.");
header("Location: login.php");
exit;
