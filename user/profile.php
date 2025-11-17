<?php
session_start();
require_once "../db/db.php";  // your PDO connection
require_once "../flash.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Get current user info
$userId = $_SESSION['user']['id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    set_flash("error", "User not found.");
    header("Location: dashboard.php");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName  = trim($_POST['last_name'] ?? '');
    $mobile    = trim($_POST['mobile'] ?? '');
    $address   = trim($_POST['address'] ?? '');
    $state     = trim($_POST['state'] ?? '');
    $zip       = trim($_POST['zip'] ?? '');
    $city      = trim($_POST['city'] ?? '');
    $country   = trim($_POST['country'] ?? '');

    if (!$firstName || !$lastName || !$mobile) {
        set_flash("error", "First Name, Last Name and Mobile are required.");
        header("Location: profile.php");
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE users SET first_name=?, last_name=?, mobile=?, address=?, state=?, zip_code=?, city=?, country=? WHERE id=?");
        $stmt->execute([$firstName, $lastName, $mobile, $address, $state, $zip, $city, $country, $userId]);
        set_flash("success", "Profile updated successfully!");
        header("Location: profile.php");
        exit;
    } catch (Exception $e) {
        set_flash("error", "Failed to update profile.");
        header("Location: profile.php");
        exit;
    }
}

$flash = get_flash();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profile - AcctGlobe</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
</head>
<body class="bg-gray-50">

<!-- Navigation -->
<nav class="bg-white shadow-sm sticky top-0 z-10">
    <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
        <img src="assets/image/acctverse.png" alt="" class="w-[150px]">
        <a href="index.php" class="text-orange-500 font-medium">← Back to Dashboard</a>
    </div>
</nav>

<!-- Main Content -->
<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-sm p-6 md:p-8">
        <h1 class="text-3xl font-bold text-blue-900 text-center mb-8 pb-6 border-b border-gray-200">Profile</h1>

        <form class="space-y-8" method="POST">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-blue-900 mb-2">First Name <span class="text-red-500">*</span></label>
                    <input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name']); ?>" class="w-full px-4 py-3 border border-gray-300 rounded focus:outline-none focus:border-orange-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-blue-900 mb-2">Last Name <span class="text-red-500">*</span></label>
                    <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name']); ?>" class="w-full px-4 py-3 border border-gray-300 rounded focus:outline-none focus:border-orange-500">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-blue-900 mb-2">E-mail Address</label>
                    <input type="email" value="<?= htmlspecialchars($user['email']); ?>" class="w-full px-4 py-3 border border-gray-300 rounded bg-gray-100 cursor-not-allowed" disabled>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-blue-900 mb-2">Mobile Number <span class="text-red-500">*</span></label>
                    <div class="flex gap-2">
                        <input type="text" id="phone_code" class="w-20 px-4 py-3 border border-gray-300 rounded bg-gray-100 cursor-not-allowed" readonly>
                        <input type="text" name="mobile" value="<?= htmlspecialchars($user['mobile'] ?? ''); ?>" class="flex-1 px-4 py-3 border border-gray-300 rounded focus:outline-none focus:border-orange-500">
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-blue-900 mb-2">Country</label>
                    <select name="country" id="country" class="w-full px-4 py-3 border border-gray-300 rounded focus:outline-none focus:border-orange-500">
                        <option value="Nigeria" <?= $user['country']==='Nigeria' ? 'selected' : ''; ?>>Nigeria</option>
                        <option value="Ghana" <?= $user['country']==='Ghana' ? 'selected' : ''; ?>>Ghana</option>
                        <option value="Kenya" <?= $user['country']==='Kenya' ? 'selected' : ''; ?>>Kenya</option>
                        <option value="South Africa" <?= $user['country']==='South Africa' ? 'selected' : ''; ?>>South Africa</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-blue-900 mb-2">Address</label>
                    <input type="text" name="address" value="<?= htmlspecialchars($user['address'] ?? ''); ?>" class="w-full px-4 py-3 border border-gray-300 rounded focus:outline-none focus:border-orange-500">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-blue-900 mb-2">State</label>
                    <input type="text" name="state" value="<?= htmlspecialchars($user['state'] ?? ''); ?>" class="w-full px-4 py-3 border border-gray-300 rounded focus:outline-none focus:border-orange-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-blue-900 mb-2">Zip Code</label>
                    <input type="text" name="zip" value="<?= htmlspecialchars($user['zip_code'] ?? ''); ?>" class="w-full px-4 py-3 border border-gray-300 rounded focus:outline-none focus:border-orange-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-blue-900 mb-2">City</label>
                <input type="text" name="city" value="<?= htmlspecialchars($user['city'] ?? ''); ?>" class="w-full px-4 py-3 border border-gray-300 rounded focus:outline-none focus:border-orange-500">
            </div>

            <button type="submit" class="w-full bg-red-500 text-white font-bold py-3 rounded hover:bg-orange-600 transition">Update Profile</button>
        </form>
    </div>
</div>

<?php if($flash): ?>
<script>
Toastify({
    text: <?= json_encode($flash['message']); ?>,
    duration: 4000,
    gravity: "top",
    position: "right",
    close: true,
    backgroundColor: <?= json_encode($flash['type']==='success' ? "linear-gradient(to right,#00b09b,#96c93d)" : "linear-gradient(to right,#ff5f6d,#ffc371)"); ?>
}).showToast();
</script>
<?php endif; ?>

<script>
// Auto-update phone code based on country
const phoneCode = document.getElementById('phone_code');
const countrySelect = document.getElementById('country');

const countryCodes = {
    "Nigeria": "+234",
    "Ghana": "+233",
    "Kenya": "+254",
    "South Africa": "+27"
};

function updatePhoneCode() {
    phoneCode.value = countryCodes[countrySelect.value] || '';
}

// Initialize on page load
updatePhoneCode();
countrySelect.addEventListener('change', updatePhoneCode);
</script>

</body>
</html>
