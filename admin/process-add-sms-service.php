<?php
session_start();
require_once "../db/db.php";
require_once "../flash.php";

// Admin authentication check
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    set_flash("error", "Unauthorized access.");
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin-sms-verification.php");
    exit;
}

// Sanitize and retrieve form data
$service_name = trim($_POST['service_name']);
$country = trim($_POST['country']);
$country_code = trim($_POST['country_code']);
$description = trim($_POST['description']);
$price_per_sms = filter_var($_POST['price_per_sms'], FILTER_VALIDATE_FLOAT);
$available_credits = filter_var($_POST['available_credits'], FILTER_VALIDATE_INT);
$is_active = isset($_POST['is_active']) ? 1 : 0;
$service_provider = trim($_POST['service_provider']);
$min_sms_per_order = filter_var($_POST['min_sms_per_order'], FILTER_VALIDATE_INT, ['options' => ['default' => 1]]);
$max_sms_per_order = filter_var($_POST['max_sms_per_order'], FILTER_VALIDATE_INT, ['options' => ['default' => null]]);
$avg_delivery_time = filter_var($_POST['avg_delivery_time'], FILTER_VALIDATE_INT, ['options' => ['default' => null]]);
$availability = trim($_POST['availability']);
$restock_alert_level = filter_var($_POST['restock_alert_level'], FILTER_VALIDATE_INT, ['options' => ['default' => 10]]);

if (empty($service_name) || empty($country) || empty($country_code) || $price_per_sms === false || $available_credits === false) {
    set_flash("error", "Please fill all required fields correctly.");
    header("Location: admin-sms-verification.php");
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO sms_services (service_name, country, country_code, description, price_per_sms, available_credits, is_active, service_provider, min_sms_per_order, max_sms_per_order, avg_delivery_time, availability, restock_alert_level) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$service_name, $country, $country_code, $description, $price_per_sms, $available_credits, $is_active, $service_provider, $min_sms_per_order, $max_sms_per_order, $avg_delivery_time, $availability, $restock_alert_level]);

    set_flash("success", "SMS service added successfully!");
    header("Location: admin-sms-verification.php");
    exit;
} catch (Exception $e) {
    set_flash("error", "Database error: " . $e->getMessage());
    header("Location: admin-sms-verification.php");
    exit;
}