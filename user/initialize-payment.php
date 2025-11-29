<?php
session_start();
include('../db/conn.php');

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 403 Forbidden');
    exit('Access denied.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    exit('Invalid request method.');
}

// --- 1. Fetch Monnify Settings ---
$result = $conn->query("SELECT api_key, secret_key, contract_code FROM monnify_settings WHERE id = 1");
$settings = $result->fetch_assoc();
if (!$settings || empty($settings['api_key']) || empty($settings['secret_key']) || empty($settings['contract_code'])) {
    die("Payment gateway is not configured. Please contact support.");
}

// --- 2. Get Data from Form ---
$amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
$customerName = filter_input(INPUT_POST, 'customerName', FILTER_SANITIZE_STRING);
$customerEmail = filter_input(INPUT_POST, 'customerEmail', FILTER_VALIDATE_EMAIL);

if (!$amount || $amount < 100 || !$customerName || !$customerEmail) {
    die("Invalid input data. Please go back and try again.");
}

// --- 3. Generate Unique Transaction Reference ---
$paymentReference = "ACCTV-" . time() . "-" . uniqid();

// --- 4. Store Transaction Details in Session ---
// We store it in the session to verify it on the callback page.
$_SESSION['payment_details'] = [
    'reference' => $paymentReference,
    'amount' => $amount,
    'user_id' => $_SESSION['user_id']
];

// --- 5. Prepare data for Monnify ---
// The URL to redirect to on your site after payment
$redirectUrl = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/payment-callback.php';

// Construct the query parameters for Monnify's payment page
$queryParams = http_build_query([
    'amount' => $amount,
    'customerName' => $customerName,
    'customerEmail' => $customerEmail,
    'paymentReference' => $paymentReference,
    'apiKey' => $settings['api_key'],
    'contractCode' => $settings['contract_code'],
    'currencyCode' => 'NGN',
    'paymentDescription' => 'Wallet Funding',
    'redirectUrl' => $redirectUrl
]);

// --- 6. Redirect to Monnify ---
header('Location: https://sandbox.monnify.com/sdk/web/pay?' . $queryParams);
exit();