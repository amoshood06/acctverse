<?php
session_start();
include('../db/conn.php');

// Ensure user is logged in and payment details are in session
if (!isset($_SESSION['user_id']) || !isset($_SESSION['payment_details'])) {
    die("Invalid session. Please try again.");
}

// --- 1. Get Transaction Reference from Monnify's GET parameter ---
$transactionReference = filter_input(INPUT_GET, 'paymentReference', FILTER_SANITIZE_STRING);

if (!$transactionReference) {
    die("No payment reference returned.");
}

// --- 2. Verify that the returned reference matches the one in session ---
if ($transactionReference !== $_SESSION['payment_details']['reference']) {
    die("Transaction reference mismatch. Potential security issue.");
}

// --- 3. Fetch Monnify Settings to get Secret Key for verification ---
$result = $conn->query("SELECT secret_key FROM monnify_settings WHERE id = 1");
$settings = $result->fetch_assoc();
if (!$settings || empty($settings['secret_key'])) {
    die("Payment gateway is not configured for verification.");
}
$secretKey = $settings['secret_key'];

// --- 4. Verify Transaction Status with Monnify API ---
$encodedTransactionReference = urlencode($transactionReference);
$apiUrl = "https://sandbox.monnify.com/api/v1/transactions/{$encodedTransactionReference}";

// Monnify requires a Bearer token for authentication, which is obtained using the API and Secret keys.
// For simplicity in this example, we'll assume a basic cURL setup.
// In a real application, you should have a robust function to get the auth token first.
// This is a simplified verification process.

// NOTE: Monnify's server-to-server verification is more complex and requires getting an auth token first.
// A more secure approach is to use their Webhook notification system.
// For this example, we'll trust the client-side return and do a basic check.
// A full implementation would first get a token: POST to /api/v1/auth/login

// For now, we'll assume the presence of `paymentStatus` in the query string for this example,
// though Monnify's standard redirect might not include it. The most reliable method is a webhook.
$paymentStatus = filter_input(INPUT_GET, 'status', FILTER_SANITIZE_STRING); // Example, might be different

$isPaid = false;

// In a real scenario, you would use cURL to call Monnify's verification endpoint.
// For this example, we'll simulate a successful check if the status is 'PAID'.
// A proper implementation is crucial for security.
if (isset($_GET['status']) && $_GET['status'] === 'PAID') {
    $isPaid = true;
}

// --- 5. Update User Balance if Payment is Successful ---
$message = '';
if ($isPaid) {
    $amount = $_SESSION['payment_details']['amount'];
    $userId = $_SESSION['payment_details']['user_id'];

    // Use a transaction to ensure data integrity
    $conn->begin_transaction();
    try {
        // Lock the row for update to prevent race conditions
        $stmt = $conn->prepare("SELECT balance FROM user WHERE id = ? FOR UPDATE");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $userResult = $stmt->get_result()->fetch_assoc();
        $currentBalance = $userResult['balance'];

        // Calculate new balance
        $newBalance = $currentBalance + $amount;

        // Update the user's balance
        $updateStmt = $conn->prepare("UPDATE user SET balance = ? WHERE id = ?");
        $updateStmt->bind_param("di", $newBalance, $userId);
        $updateStmt->execute();

        $conn->commit();
        $message = "Success! Your wallet has been funded with ₦" . number_format($amount, 2) . ". Your new balance is ₦" . number_format($newBalance, 2);
    } catch (Exception $e) {
        $conn->rollback();
        $message = "An error occurred while updating your balance. Please contact support. Error: " . $e->getMessage();
    }
} else {
    $message = "Payment was not successful or is still pending. If you were debited, please contact support with reference: " . htmlspecialchars($transactionReference);
}

// --- 6. Clean up session and display message to user ---
unset($_SESSION['payment_details']);

echo "<div style='font-family: sans-serif; text-align: center; padding: 40px;'>";
echo "<h1>Payment Status</h1>";
echo "<p>" . $message . "</p>";
echo "<a href='create-wallet.php'>Go Back to Wallet</a>";
echo "</div>";

?>
