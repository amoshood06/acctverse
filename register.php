<?php
// Start session and enable errors
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database
require_once "./db/db.php";

// Flash message functions
function set_flash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function get_flash() {
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

$flash = get_flash();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $full_name     = trim($_POST['full_name'] ?? '');
    $username      = trim($_POST['username'] ?? '');
    $email         = trim($_POST['email'] ?? '');
    $mobile_code   = trim($_POST['mobile_code'] ?? '');
    $mobile        = trim($_POST['mobile'] ?? '');
    $country       = trim($_POST['country'] ?? '');
    $password      = $_POST['password'] ?? '';
    $confirmPass   = $_POST['confirm_password'] ?? '';

    // Validation
    if (!$full_name || !$username || !$email || !$mobile || !$password) {
        set_flash("error", "All fields are required.");
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        set_flash("error", "Invalid email format.");
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    if ($password !== $confirmPass) {
        set_flash("error", "Passwords do not match.");
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    try {
        // Check for duplicate email or username
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $username]);
        if ($stmt->fetch()) {
            set_flash("error", "Email or username already exists.");
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }

        // Hash password and generate token
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $token = bin2hex(random_bytes(32));

        // Insert user
        $stmt = $pdo->prepare("
            INSERT INTO users (full_name, username, email, mobile_code, mobile, country, password_hash, role, is_verified, verify_token)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'user', 0, ?)
        ");
        $stmt->execute([$full_name, $username, $email, $mobile_code, $mobile, $country, $password_hash, $token]);

        set_flash("success", "Registration successful! You can now login.");
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;

    } catch (Exception $e) {
        set_flash("error", "Registration failed: " . $e->getMessage());
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Register - AcctVerse</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

<div class="w-full max-w-lg bg-white p-8 rounded-lg shadow">
    <h2 class="text-2xl font-bold text-center mb-6">Create Account</h2>

    <form action="" method="POST" class="space-y-4">
        <div>
            <label class="block text-sm font-medium">Full Name</label>
            <input name="full_name" type="text" required class="w-full px-3 py-2 border rounded">
        </div>


        <div>
            <label class="block text-sm font-medium">Username</label>
            <input name="username" type="text" required class="w-full px-3 py-2 border rounded">
        </div>


        <div>
            <label class="block text-sm font-medium">Email</label>
            <input name="email" type="email" required class="w-full px-3 py-2 border rounded">
        </div>


        <div>
            <label class="block text-sm font-medium">Country</label>
            <select name="country" id="countrySelect" required class="w-full border px-3 py-2 rounded">
                <option value="">-- Select Country --</option>
                <option value="Nigeria" data-dial="234">Nigeria (+234)</option>
                <option value="Ghana" data-dial="233">Ghana (+233)</option>
                <option value="Kenya" data-dial="254">Kenya (+254)</option>
                <option value="South Africa" data-dial="27">South Africa (+27)</option>
                <option value="USA" data-dial="1">USA (+1)</option>
                <option value="UK" data-dial="44">UK (+44)</option>
            </select>
        </div>

  
        <div>
            <label class="block text-sm font-medium">Mobile Number</label>
            <div class="flex">
                <span id="mobileCode" class="px-4 py-2 bg-gray-200 border rounded-l">+</span>
                <input type="hidden" name="mobile_code" id="mobileCodeInput">
                <input type="text" name="mobile" required class="w-full px-3 py-2 border rounded-r">
            </div>
        </div>


        <div>
            <label class="block text-sm font-medium">Password</label>
            <input name="password" type="password" required class="w-full px-3 py-2 border rounded">
        </div>

        
        <div>
            <label class="block text-sm font-medium">Confirm Password</label>
            <input name="confirm_password" type="password" required class="w-full px-3 py-2 border rounded">
        </div>

        <button class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700">Register</button>
    </form>

    <p class="text-center text-sm mt-4">
        Already have an account? <a href="login.php" class="text-blue-600">Login</a>
    </p>
</div>

<?php if($flash): ?>
<script>
Toastify({
    text: <?= json_encode($flash['message']) ?>,
    duration: 4000,
    gravity: "top",
    position: "right",
    close: true,
    backgroundColor: <?= json_encode($flash['type'] === 'success'
        ? "linear-gradient(to right, #00b09b, #96c93d)"
        : "linear-gradient(to right, #ff5f6d, #ffc371)") ?>
}).showToast();
</script>
<?php endif; ?>

<script>
(function(){
    function setMobileCode(){
        const select = document.getElementById('countrySelect');
        const codeSpan = document.getElementById('mobileCode');
        const codeInput = document.getElementById('mobileCodeInput');
        const opt = select.options[select.selectedIndex];
        const dial = opt.dataset.dial || '';
        const display = dial ? "+" + dial : "+";
        codeSpan.textContent = display;
        codeInput.value = display;
    }

    document.addEventListener('DOMContentLoaded', function(){
        const select = document.getElementById('countrySelect');
        setMobileCode();
        select.addEventListener('change', setMobileCode);
    });
})();
</script>

</body>
</html>
