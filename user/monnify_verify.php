<?php
session_start();
$pdo = require_once "../db/db.php";
require_once "../flash.php";

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

$user = $_SESSION['user'];

$transactionReference = $_GET['reference'] ?? null;

if (!$transactionReference) {
    set_flash("error", "Transaction reference not provided.");
    header("Location: index.php");
    exit;
}

// Fetch Monnify settings from the database
$monnifySettings = [];
try {
    $stmt = $pdo->query("SELECT api_key, secret_key, contract_code FROM monnify_settings WHERE id = 1");
    $monnifySettings = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    set_flash("error", "Error fetching Monnify settings: " . $e->getMessage());
    header("Location: index.php");
    exit;
}

if (!$monnifySettings || empty($monnifySettings['api_key']) || empty($monnifySettings['secret_key'])) {
    set_flash("error", "Monnify payment gateway is not configured. Please contact support.");
    header("Location: index.php");
    exit;
}

$apiKey = $monnifySettings['api_key'];
$secretKey = $monnifySettings['secret_key'];
$authString = base64_encode($apiKey . ':' . $secretKey);

// MONNIFY_API_BASE_URL should match what's in fund.php
define('MONNIFY_API_BASE_URL', 'https://api.monnify.com/api/v1/'); // For production

// Monnify Transaction Verification
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, MONNIFY_API_BASE_URL . "transactions/" . $transactionReference);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Basic " . $authString,
    "Content-Type: application/json",
]);

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$transactionData = json_decode($response, true);

if ($httpcode == 200 && $transactionData['requestSuccessful'] && $transactionData['responseBody']['paymentStatus'] === 'PAID') {
    $amountPaid = $transactionData['responseBody']['amount'];
    $monnifyTransactionReference = $transactionData['responseBody']['transactionReference']; // Monnify's unique reference
    $paymentStatus = $transactionData['responseBody']['paymentStatus'];

    // Update the transaction in your database
    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("SELECT id, amount, status FROM transactions WHERE transaction_reference = ? AND user_id = ?");
        $stmt->execute([$transactionReference, $user['id']]);
        $localTransaction = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($localTransaction && $localTransaction['status'] === 'pending') {
            // Update local transaction status
            $updateStmt = $pdo->prepare("UPDATE transactions SET status = ?, updated_at = NOW() WHERE id = ?");
            $updateStmt->execute(['completed', $localTransaction['id']]);

            // Update user balance
            $updateBalanceStmt = $pdo->prepare("UPDATE users SET balance = balance + ?, updated_at = NOW() WHERE id = ?");
            $updateBalanceStmt->execute([$amountPaid, $user['id']]);

            // Update user session balance
            $_SESSION['user']['balance'] += $amountPaid;

            $pdo->commit();
            set_flash("success", "Wallet funded successfully! Amount: ₦" . number_format($amountPaid, 2));
        } elseif ($localTransaction && $localTransaction['status'] === 'completed') {
            $pdo->rollBack();
            set_flash("info", "Transaction already processed. Amount: ₦" . number_format($amountPaid, 2));
        } else {
            $pdo->rollBack();
            set_flash("error", "Transaction not found or mismatched user. Please contact support. Ref: " . $transactionReference);
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        set_flash("error", "Database error during transaction update: " . $e->getMessage());
    }
} else {
    // Transaction failed or could not be verified
    $errorMsg = $transactionData['responseMessage'] ?? "Failed to verify transaction with Monnify.";
    // Optionally update the local transaction to 'failed'
    try {
        $stmt = $pdo->prepare("UPDATE transactions SET status = ?, updated_at = NOW() WHERE transaction_reference = ? AND user_id = ? AND status = 'pending'");
        $stmt->execute(['failed', $transactionReference, $user['id']]);
    } catch (PDOException $e) {
        error_log("Error updating failed transaction status: " . $e->getMessage());
    }
    set_flash("error", "Payment failed or could not be verified. " . $errorMsg);
}

header("Location: index.php");
exit;
?>