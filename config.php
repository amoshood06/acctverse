<?php session_start();

// =================================================================
//  FLUTTERWAVE PAYMENT GATEWAY CONFIGURATION
// =================================================================
// 
// IMPORTANT:
// 1. Please replace the placeholder values below with your actual
//    Flutterwave API keys. You can get these from your Flutterwave
//    dashboard: https://dashboard.flutterwave.com/dashboard/settings/apis
// 2. Make sure you have installed the Flutterwave PHP SDK via Composer:
//    composer require flutterwavedev/flutterwave-v3
// 

// Set to 'staging' for testing or 'production' for live payments
define('FLW_ENVIRONMENT', 'staging'); // or 'production'

// Your API keys
define('FLW_PUBLIC_KEY', 'YOUR_FLUTTERWAVE_PUBLIC_KEY');
define('FLW_SECRET_KEY', 'YOUR_FLUTTERWAVE_SECRET_KEY');
define('FLW_ENCRYPTION_KEY', 'YOUR_FLUTTERWAVE_ENCRYPTION_KEY');

// The currency to use for payments (e.g., 'NGN', 'USD', 'GHS')
define('FLW_CURRENCY', 'NGN');

// Your website name (used in payment page customization)
define('APP_NAME', 'AcctVerse');

// The URL for your payment callback script
// Make sure this is the full, correct URL
define('FLW_CALLBACK_URL', 'http://localhost/acctv/payment-callback.php');

?>