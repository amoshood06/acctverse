<?php
session_start();
require_once "./config.php";
require_once "./db/db.php";
require_once "./flash.php";

// Ensure a product ID is provided
if (!isset($_POST['product_id'])) {
    die("Invalid request: No product selected.");
}

$product_id = (int)$_POST['product_id'];

// Fetch product from DB
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die("Product not found.");
}

// Determine purchase type (gift or account)
$purchase_type = 'account'; // Default
if (isset($_POST['recipient_name'])) {
    $purchase_type = 'gift';
}

// --- Store Order Details in Session ---
// This makes the data available on the callback page after payment.

$tx_ref = APP_NAME . '_' . uniqid();
$_SESSION['payment_attempt'] = [
    'tx_ref' => $tx_ref,
    'product_id' => $product['id'],
    'product_name' => $product['product_name'],
    'amount' => (float)$product['price'],
    'currency' => FLW_CURRENCY,
    'purchase_type' => $purchase_type,
];

if ($purchase_type === 'gift') {
    $_SESSION['payment_attempt']['recipient_details'] = [
        'recipient_name' => $_POST['recipient_name'],
        'recipient_phone' => $_POST['recipient_phone'],
        'recipient_address' => $_POST['recipient_address'],
        'gift_message' => $_POST['gift_message'] ?? '',
    ];
}


// --- Initialize Flutterwave Payment ---

// Check if the SDK is installed
if (!class_exists('Flutterwave\Rave')) {
    set_flash('error', 'Payment gateway is not available. Please contact support.');
    header('Location: index.php');
    exit;
}

// Get user info for payment
$user_email = $_SESSION['user']['email'] ?? 'guest@example.com';
$user_name = $_SESSION['user']['full_name'] ?? 'Guest User';
$user_phone = $_SESSION['user']['mobile'] ?? 'N/A';

// Payment data payload
$paymentData = [
    'tx_ref' => $tx_ref,
    'amount' => (float)$product['price'],
    'currency' => FLW_CURRENCY,
    'redirect_url' => FLW_CALLBACK_URL,
    'customer' => [
        'email' => $user_email,
        'phonenumber' => $user_phone,
        'name' => $user_name,
    ],
    'customizations' => [
        'title' => APP_NAME . ' Checkout',
        'description' => 'Payment for: ' . htmlspecialchars($product['product_name']),
        'logo' => 'https://www.acctverse.com/assets/image/acctverse.png', // Optional: Replace with your logo URL
    ],
];

try {
    $rave = new \Flutterwave\Rave(FLW_SECRET_KEY, FLW_PUBLIC_KEY, FLW_ENVIRONMENT);
    $response = $rave->initializePayment($paymentData);

    if ($response->status === 'success') {
        // Redirect to Flutterwave's payment page
        header('Location: ' . $response->data->link);
        exit();
    } else {
        set_flash('error', 'Could not initialize payment. Please try again.');
        header('Location: ' . $_SERVER['HTTP_REFERER'] ?? 'index.php');
        exit();
    }
} catch (\Exception $e) {
    error_log("Flutterwave Init Error: " . $e->getMessage());
    set_flash('error', 'A critical error occurred with the payment gateway.');
    header('Location: index.php');
    exit();
}
?>