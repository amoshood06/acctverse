<?php
$pdo = require_once "../db/db.php"; // Use the correct db file which starts the session

// 1. User Authentication: Ensure user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: ../login.php'); // Redirect to login page if not logged in
    exit();
}

$userId = $_SESSION['user']['id'];

// 2. Fetch user details from the database
try {
    $userStmt = $pdo->prepare("SELECT CONCAT(first_name, ' ', last_name) AS name, email FROM users WHERE id = ?");
    $userStmt->execute([$userId]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Error: Could not fetch user details. " . $e->getMessage());
}

if (!$user) {
    // Handle case where user is not found
    die("Error: User not found.");
}

$customerName = $user['name'];
$customerEmail = $user['email'];
?>
<?php
require_once "header.php";
?>


    <!-- Main Content -->
    <div class="max-w-2xl mx-auto px-4 py-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="w-20 h-20 bg-gradient-to-br from-orange-400 to-orange-600 rounded-full mx-auto mb-4 flex items-center justify-center shadow-lg">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                </svg>
            </div>
            <h1 class="text-2xl md:text-3xl font-bold text-blue-900 mb-2">Create Your Virtual Wallet</h1>
            <p class="text-gray-600">Set up your wallet with an initial deposit amount</p>
        </div>

        <!-- Info Alert -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-8 flex gap-3">
            <div class="text-blue-500 flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div>
                <p class="text-blue-800 font-medium">How it works</p>
                <p class="text-blue-700 text-sm">Enter your desired initial deposit amount. A virtual account will be generated for you to receive instant deposits.</p>
            </div>
        </div>

        <!-- Create Wallet Form -->
        <div class="bg-white rounded-xl shadow-sm p-6 md:p-8">
            <form id="paymentForm" action="initialize-payment.php" method="POST" class="space-y-6">
                <!-- Amount Input -->
                <div>
                    <label class="block text-sm font-semibold text-blue-900 mb-2">
                        Initial Deposit Amount <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-bold">₦</span>
                        <input 
                            type="number" 
                            name="amount"
                            id="amount"
                            placeholder="0.00" 
                            min="100"
                            class="w-full pl-10 pr-4 py-4 text-xl border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                            oninput="updateSummary()"
                        >
                    </div>
                    <p class="text-gray-500 text-sm mt-2">Minimum amount: ₦100.00</p>
                </div>

                <!-- Quick Amount Selection -->
                <div>
                    <label class="block text-sm font-semibold text-blue-900 mb-3">Quick Select Amount</label>
                    <div class="grid grid-cols-3 md:grid-cols-6 gap-2">
                        <button type="button" onclick="setAmount(500)" class="py-2 px-3 border border-gray-300 rounded-lg hover:border-orange-500 hover:bg-orange-50 text-sm font-medium transition">₦500</button>
                        <button type="button" onclick="setAmount(1000)" class="py-2 px-3 border border-gray-300 rounded-lg hover:border-orange-500 hover:bg-orange-50 text-sm font-medium transition">₦1,000</button>
                        <button type="button" onclick="setAmount(2000)" class="py-2 px-3 border border-gray-300 rounded-lg hover:border-orange-500 hover:bg-orange-50 text-sm font-medium transition">₦2,000</button>
                        <button type="button" onclick="setAmount(5000)" class="py-2 px-3 border border-gray-300 rounded-lg hover:border-orange-500 hover:bg-orange-50 text-sm font-medium transition">₦5,000</button>
                        <button type="button" onclick="setAmount(10000)" class="py-2 px-3 border border-gray-300 rounded-lg hover:border-orange-500 hover:bg-orange-50 text-sm font-medium transition">₦10,000</button>
                        <button type="button" onclick="setAmount(20000)" class="py-2 px-3 border border-gray-300 rounded-lg hover:border-orange-500 hover:bg-orange-50 text-sm font-medium transition">₦20,000</button>
                    </div>
                </div>

    

                <!-- Wallet Summary -->
                <div class="bg-gray-50 rounded-lg p-5 border border-gray-200">
                    <h4 class="font-semibold text-blue-900 mb-4">Wallet Summary</h4>
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Initial Deposit</span>
                            <span class="font-medium text-gray-800" id="depositAmount">₦0.00</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Processing Fee</span>
                            <span class="font-medium text-green-600">FREE</span>
                        </div>
                        <hr class="border-gray-300">
                        <div class="flex justify-between">
                            <span class="font-semibold text-blue-900">Total Amount</span>
                            <span class="font-bold text-xl text-orange-500" id="totalAmount">₦0.00</span>
                        </div>
                    </div>
                </div>

                <!-- Terms Agreement -->
                <div class="flex items-start gap-3">
                    <input type="checkbox" id="agree" class="w-5 h-5 mt-0.5 rounded border-gray-300 text-orange-500 focus:ring-orange-500">
                    <label for="agree" class="text-sm text-gray-600">
                        I agree to the <a href="terms.html" class="text-orange-500 hover:underline">Terms of Service</a> and 
                        <a href="privacy.html" class="text-orange-500 hover:underline">Privacy Policy</a> for creating a virtual wallet.
                    </label>
                </div>

                <!-- Submit Button -->
                <input type="hidden" name="customerName" value="<?php echo htmlspecialchars($customerName); ?>">
                <input type="hidden" name="customerEmail" value="<?php echo htmlspecialchars($customerEmail); ?>">

                <button type="submit" id="submitBtn" class="w-full bg-orange-500 text-white py-4 rounded-lg font-bold text-lg hover:bg-orange-600 transition shadow-lg hover:shadow-xl disabled:bg-gray-400 disabled:cursor-not-allowed">
                    Proceed to Pay
                </button>
            </form>
        </div>

        <!-- Features -->
        <div class="grid grid-cols-3 gap-4 mt-8">
            <div class="bg-white rounded-lg p-4 text-center shadow-sm">
                <div class="w-12 h-12 bg-blue-100 rounded-full mx-auto mb-3 flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <p class="text-xs font-semibold text-gray-700">24/7</p>
                <p class="text-xs text-gray-500">Available</p>
            </div>
            <div class="bg-white rounded-lg p-4 text-center shadow-sm">
                <div class="w-12 h-12 bg-yellow-100 rounded-full mx-auto mb-3 flex items-center justify-center">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <p class="text-xs font-semibold text-gray-700">Instant</p>
                <p class="text-xs text-gray-500">Credit</p>
            </div>
            <div class="bg-white rounded-lg p-4 text-center shadow-sm">
                <div class="w-12 h-12 bg-green-100 rounded-full mx-auto mb-3 flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
                <p class="text-xs font-semibold text-gray-700">Secure</p>
                <p class="text-xs text-gray-500">Banking</p>
            </div>
        </div>

        <!-- Already Have Wallet -->
        <div class="text-center mt-8">
            <p class="text-gray-600">
                Already have a wallet? 
                <a href="fund-wallet.html" class="text-orange-500 font-semibold hover:underline">Fund your wallet</a>
            </p>
        </div>
    </div>

    <script>
        function setAmount(value) {
            document.getElementById('amount').value = value;
            updateSummary();
        }

        function updateSummary() {
            const amount = parseFloat(document.getElementById('amount').value) || 0;
            const formattedAmount = new Intl.NumberFormat('en-NG', {
                style: 'currency',
                currency: 'NGN',
                minimumFractionDigits: 2
            }).format(amount).replace('NGN', '₦');
            
            document.getElementById('depositAmount').textContent = formattedAmount;
            document.getElementById('totalAmount').textContent = formattedAmount;
            validateForm();
        }


        function validateForm() {
            const amount = parseFloat(document.getElementById('amount').value) || 0;
            const agree = document.getElementById('agree').checked;
            const submitBtn = document.getElementById('submitBtn');

            // Enable button if amount is >= 100 and terms are agreed
            submitBtn.disabled = !(amount >= 100 && agree);
        }
    </script>
</main>
</body>
</html>
