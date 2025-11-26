<?php 
require_once './db/db.php';
require_once 'flash.php';

// Handle Login Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $input = trim($_POST['email'] ?? ''); // email OR username
    $password = $_POST['password'] ?? '';

    if (!$input || !$password) {
        set_flash('error', 'Please enter your login details.');
        header('Location: login.php');
        exit;
    }

    try {
        // Detect email or username
        if (strpos($input, '@') !== false) {
            $stmt = $pdo->prepare("
                SELECT id, full_name, first_name, last_name, email, username, password_hash, role, is_verified 
                FROM users 
                WHERE email = ?
            ");
        } else {
            $stmt = $pdo->prepare("
                SELECT id, full_name, first_name, last_name, email, username, password_hash, role, is_verified 
                FROM users 
                WHERE username = ?
            ");
        }

        $stmt->execute([$input]);
        $user = $stmt->fetch();

        if (!$user) {
            set_flash('error', 'Invalid login details.');
            header('Location: login.php');
            exit;
        }

        // Check password
        if (!password_verify($password, $user['password_hash'])) {
            set_flash('error', 'Invalid login details.');
            header('Location: login.php');
            exit;
        }

        // Check email verification
        if (!$user['is_verified']) {
            set_flash('error', 'Please verify your email before logging in.');
            header('Location: login.php');
            exit;
        }

        // Set SESSION
        $_SESSION['user'] = [
            'id'   => $user['id'],
            'name' => $user['full_name'],
            'role' => $user['role']
        ];

        // -------------------------------
        // ✅ RULE: Admin goes straight to admin dashboard
        // -------------------------------
        if ($user['role'] === 'admin') {
            set_flash('success', 'Admin login successful!');
            header('Location: ./admin/index.php');
            exit;
        }

        // -------------------------------
        // ✅ RULE: Only USERS must complete profile
        // -------------------------------
        if (empty($user['first_name']) || empty($user['last_name'])) {
            set_flash('info', 'Please complete your profile.');
            header('Location: ./user/user_data.php');
            exit;
        }

        // -------------------------------
        // USER LOGIN SUCCESS
        // -------------------------------
        set_flash('success', 'Login successful!');
        header('Location: ./user/index.php');
        exit;

    } catch (Exception $e) {
        set_flash('error', 'An error occurred: ' . $e->getMessage());
        header('Location: login.php');
        exit;
    }
}

// GET request -> show login form
$flash = get_flash();
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acctverse-Login</title>
    <link rel="shortcut icon" href="assets/image/a.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-sm p-8 w-full max-w-md">
            <!-- Header -->
            <h1 class="text-4xl font-bold text-blue-900 text-center mb-2">Login</h1>
            <p class="text-center text-gray-600 mb-8">You are welcome back!</p>

            <form method="POST" action="login.php" class="space-y-6">
                <!-- Username or Email Row -->
                <div>
                    <label class="block text-sm font-semibold text-blue-900 mb-2">
                        Username or Email <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="email" type="email" placeholder="" class="w-full px-4 py-3 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
                </div>

                <!-- Password Row -->
                <div>
                    <label class="block text-sm font-semibold text-blue-900 mb-2">
                        Password <span class="text-red-500">*</span>
                    </label>
                    <input name="password" type="password" placeholder="" class="w-full px-4 py-3 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
                </div>

                <!-- Remember Me Checkbox and Forgot Password Link -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="remember" class="w-4 h-4 border-gray-300 rounded focus:ring-2 focus:ring-blue-500">
                        <label for="remember" class="text-sm text-gray-600">Remember Me</label>
                    </div>
                    <a href="#" class="text-sm text-red-500 font-semibold hover:underline">Forgot your password?</a>
                </div>

                <!-- Login Button -->
                <button type="submit" class="w-full bg-red-500 text-white font-bold py-3 rounded hover:bg-orange-600 transition duration-200">
                    Login
                </button>

                <!-- Register Link -->
                <p class="text-center text-gray-600">
                    Haven't an account?
                    <a href="#" class="text-red-500 font-semibold hover:underline">Register</a>
                </p>
            </form>
        </div>
    </div>
    <?php if ($flash): ?>
<script>
document.addEventListener('DOMContentLoaded', function(){
  Toastify({
    text: <?= json_encode($flash['message']) ?>,
    duration: 4000,
    gravity: 'top',
    position: 'right',
    close: true,
    backgroundColor: <?= json_encode($flash['type']==='success' ? 'linear-gradient(to right, #00b09b, #96c93d)' : 'linear-gradient(to right, #ff5f6d, #ffc371)') ?>
  }).showToast();
});
</script>
<?php endif; ?>
</body>
</html>
