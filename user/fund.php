<?php
session_start();
$pdo = require_once "../db/db.php";
require_once "../flash.php";

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

$user = $_SESSION['user'];
$flash = get_flash();

// Fetch Monnify settings from the database
$monnifySettings = [];
try {
    $stmt = $pdo->query("SELECT api_key, secret_key, contract_code FROM monnify_settings WHERE id = 1");
    $monnifySettings = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    set_flash("error", "Error fetching Monnify settings: " . $e->getMessage());
}

if (!$monnifySettings || empty($monnifySettings['api_key']) || empty($monnifySettings['secret_key']) || empty($monnifySettings['contract_code'])) {
    set_flash("error", "Monnify payment gateway is not configured. Please contact support.");
    header("Location: index.php");
    exit;
}

// Monnify API Configuration (using fetched settings)
define('MONNIFY_API_BASE_URL', 'https://api.monnify.com/api/v1/'); // For production
// define('MONNIFY_API_BASE_URL', 'https://sandbox.monnify.com/api/v1/'); // For sandbox/testing

// Get environment from settings if available, else default to production
$is_sandbox = false; // Default to production
// You might want to add a column in monnify_settings for environment (e.g., 'is_sandbox' TINYINT(1))
// For now, let's assume if the API keys are present, it's configured for production.
// If you use sandbox API keys, change MONNIFY_API_BASE_URL accordingly.


// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['amount'])) {
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);

    if ($amount === false || $amount <= 0) {
        set_flash("error", "Please enter a valid amount.");
        header("Location: fund.php");
        exit;
    }

    $reference = "MONNIFY_" . strtoupper(uniqid()); // Generate a unique transaction reference
    $customerEmail = $user['email'];
    $customerName = $user['first_name'] . ' ' . $user['last_name'];
    $currency = 'NGN'; // Assuming NGN as per config.php's FLW_CURRENCY

    // Initialize Monnify Transaction
    // This is a simplified example. In a real application, you would make a server-side
    // API call to Monnify to initiate the transaction and get a payment URL.
    // For client-side initiation, Monnify.js would be used.

    // For demonstration, we will directly construct the Monnify.js payload
    // and rely on client-side JS to open the payment modal.
    // The actual payment processing and status update will happen via a webhook callback.

    // Store transaction details temporarily before redirecting to Monnify or showing modal
    // In a real scenario, you'd save this to your `transactions` table with a 'pending' status
    // and update it on Monnify's webhook callback.

    try {
        $stmt = $pdo->prepare("INSERT INTO transactions (user_id, type, amount, transaction_reference, payment_gateway, description, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $user['id'],
            'fund_wallet',
            $amount,
            $reference,
            'Monnify',
            'Wallet funding via Monnify',
            'pending'
        ]);
        $transaction_id = $pdo->lastInsertId();

        // Redirect to a page that initializes Monnify.js or directly inject the script
        // For simplicity, we'll embed the Monnify.js initiation logic in this file.
        // In production, consider separating this to a dedicated payment initiation page.

    } catch (PDOException $e) {
        set_flash("error", "Error recording transaction: " . $e->getMessage());
        header("Location: fund.php");
        exit;
    }
}
?>

<?php require_once "header.php"; ?>

<div class="container mx-auto px-4 py-8">
    <h2 class="text-3xl font-bold text-gray-800 mb-6">Fund Your Wallet</h2>

    <?php if($flash): ?>
    <script>
    Toastify({
        text: <?= json_encode($flash['message']); ?>,
        duration: 4000,
        gravity: "top",
        position: "right",
        close: true,
        backgroundColor: <?= json_encode($flash['type']==='success' ? "linear-gradient(to right, #00b09b, #96c93d)" : "linear-gradient(to right, #ff5f6d, #ffc371)"); ?>
    }).showToast();
    </script>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow-sm p-6 mb-8 max-w-md mx-auto">
        <p class="text-lg font-semibold text-gray-700 mb-4">Current Balance: ₦<?= number_format($user['balance'], 2); ?></p>
        
        <form id="monnifyPaymentForm" method="POST" action="fund.php">
            <div class="mb-4">
                <label for="amount" class="block text-gray-700 text-sm font-bold mb-2">Amount (NGN)</label>
                <input type="number" step="any" min="100" id="amount" name="amount" placeholder="Enter amount" required
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <button type="submit"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full">
                Proceed to Pay
            </button>
        </form>
    </div>
</div>

<script src="https://sdk.monnify.com/plugin/monnify.js"></script>
<script>
<?php if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($amount) && isset($reference)): ?>
    window.onload = function() {
        MonnifySDK.initialize({
            amount: <?= $amount; ?>,
            currency: "<?= $currency; ?>",
            reference: "<?= $reference; ?>",
            customerName: "<?= $customerName; ?>",
            customerEmail: "<?= $customerEmail; ?>",
            apiKey: "<?= $monnifySettings['api_key']; ?>",
            contractCode: "<?= $monnifySettings['contract_code']; ?>",
            paymentDescription: "Wallet funding for <?= $customerEmail; ?>",
            onComplete: function(response) {
                // Implement what happens when the transaction is completed.
                // This function is called after the Monnify payment modal is closed.
                // You should verify the transaction status on your server using the transaction reference.
                console.log("Payment complete:", response);
                // Redirect to a verification page or trigger an AJAX call to your backend
                window.location.href = 'monnify_verify.php?reference=' + response.paymentReference;
            },
            onClose: function(data) {
                // Implement what should occur when the modal is closed.
                console.log("Payment modal closed:", data);
                // Redirect back or show a message
                Toastify({
                    text: "Payment cancelled by user.",
                    duration: 4000,
                    gravity: "top",
                    position: "right",
                    close: true,
                    backgroundColor: "linear-gradient(to right, #ff5f6d, #ffc371)"
                }).showToast();
            }
        });
    };
<?php endif; ?>
</script>

<?php require_once "footer.php"; // Assuming you have a footer.php in user directory or root ?>
