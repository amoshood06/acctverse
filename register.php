<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "./db/db.php";

// Flash message functions
function set_flash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
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


// -----------------------------------------------------------
// ✅ REFERRAL SYSTEM
// -----------------------------------------------------------

// Generate new user referral code
$refCode = substr(sha1(uniqid()), 0, 10);

// Detect referral link: ?ref=ABCDE12345
if (isset($_GET['ref']) && !empty($_GET['ref'])) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE referral_code = ?");
    $stmt->execute([$_GET['ref']]);
    $refUser = $stmt->fetch();

    if ($refUser) {
        // Prevent users from referring themselves if they are somehow logged in
        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $refUser['id']) {
            // Optional: set a flash message
        } else {
            // Store referrer in session to persist across reloads
            $_SESSION['referred_by'] = $refUser['id'];
        }
    }
}

// Use the referrer from session if available
$referredBy = $_SESSION['referred_by'] ?? null;

// -----------------------------------------------------------
// ✅ HANDLE REGISTRATION FORM
// -----------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $full_name     = trim($_POST['full_name'] ?? '');
    $username      = trim($_POST['username'] ?? '');
    $email         = trim($_POST['email'] ?? '');
    $mobile_code   = trim($_POST['mobile_code'] ?? '');
    $mobile        = trim($_POST['mobile'] ?? '');
    $country       = trim($_POST['country'] ?? '');
    $password      = $_POST['password'] ?? '';
    $confirmPass   = $_POST['confirm_password'] ?? '';

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
        // Check for duplicate email/username
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $username]);
        if ($stmt->fetch()) {
            set_flash("error", "Email or username already exists.");
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }

        // Hash Password + Generate Verification Token
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user with referral code + referredBy
        $stmt = $pdo->prepare("
            INSERT INTO users 
            (full_name, username, email, mobile_code, mobile, country, password_hash, role, referral_code, referred_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'user', ?, ?)
        ");

        $stmt->execute([
            $full_name, $username, $email,
            $mobile_code, $mobile, $country,
            $password_hash, $refCode, $referredBy
        ]);

        // Clear the referrer from session after successful registration
        unset($_SESSION['referred_by']);

        set_flash("success", "Registration successful! You can now log in.");
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;

    } catch (Exception $e) {
        set_flash("error", "Registration failed: " . $e->getMessage());
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}
?>


<!-- HTML Registration Form -->
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Register - AcctVerse</title>
<link rel="shortcut icon" href="assets/image/a.png" type="image/x-icon">
<meta name="viewport" content="width=device-width, initial-scale=1">
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

<div class="w-full max-w-lg bg-white p-8 rounded-lg shadow">
    <h2 class="text-2xl font-bold text-center mb-6">Create Account</h2>

    <form action="" method="POST" class="space-y-4">
        <input name="full_name" type="text" placeholder="Full Name" required class="w-full px-3 py-2 border rounded">
        <input name="username" type="text" placeholder="Username" required class="w-full px-3 py-2 border rounded">
        <input name="email" type="email" placeholder="Email" required class="w-full px-3 py-2 border rounded">
        
        <select name="country" id="countrySelect" required class="w-full border px-3 py-2 rounded">
            <option value="">-- Select Country --</option>
            <option value="Nigeria" data-dial="234">Nigeria (+234)</option>
            <option value="Ghana" data-dial="233">Ghana (+233)</option>
            <option value="Kenya" data-dial="254">Kenya (+254)</option>
            <option value="South Africa" data-dial="27">South Africa (+27)</option>
            <option value="USA" data-dial="1">USA (+1)</option>
            <option value="UK" data-dial="44">UK (+44)</option>
        </select>

        <div class="flex">
            <span id="mobileCode" class="px-4 py-2 bg-gray-200 border rounded-l">+</span>
            <input type="hidden" name="mobile_code" id="mobileCodeInput">
            <input type="text" name="mobile" placeholder="Mobile Number" required class="w-full px-3 py-2 border rounded-r">
        </div>

        <input name="password" type="password" placeholder="Password" required class="w-full px-3 py-2 border rounded">
        <input name="confirm_password" type="password" placeholder="Confirm Password" required class="w-full px-3 py-2 border rounded">

        <button class="w-full bg-red-500 text-white py-2 rounded hover:bg-green-700">Register</button>
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
