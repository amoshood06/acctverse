<?php
require_once './db/db.php';

$token = $_GET['token'] ?? '';
$message = '';
$message_type = ''; // 'success' or 'error'
$show_form = false;
$password_updated = false;
$user_id = null;

// --- 1. Token Validation ---
if (empty($token)) {
    $message = 'Invalid or missing password reset token.';
    $message_type = 'error';
} else {
    try {
        // Find the user by the reset token
        $sql = "SELECT id, reset_token_expires_at FROM users WHERE reset_token = ? LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if user exists and token is not expired
        if ($user && isset($user['reset_token_expires_at']) && strtotime($user['reset_token_expires_at']) > time()) {
            $show_form = true;
            $user_id = $user['id']; // Store user ID for the update step
        } else {
            $message = 'This password reset link is invalid or has expired.';
            $message_type = 'error';
        }
    } catch (Exception $e) {
        error_log("Reset Password Token Error: " . $e->getMessage());
        $message = 'An unexpected error occurred. Please try again later.';
        $message_type = 'error';
    }
}

// --- 2. Form Submission Handling ---
if ($show_form && $_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (empty($password) || empty($password_confirm)) {
        $message = 'Please fill in both password fields.';
        $message_type = 'error';
    } elseif ($password !== $password_confirm) {
        $message = 'The passwords do not match.';
        $message_type = 'error';
    } else {
        try {
            // Hash the new password securely
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Update the user's password and invalidate the reset token
            $sql = "UPDATE users SET password_hash = ?, reset_token = NULL, reset_token_expires_at = NULL WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$password_hash, $user_id]);

            $message = 'Your password has been successfully updated! You can now log in.';
            $message_type = 'success';
            $show_form = false;
            $password_updated = true;
        } catch (Exception $e) {
            error_log("Reset Password Update Error: " . $e->getMessage());
            $message = 'An unexpected error occurred while updating your password.';
            $message_type = 'error';
        }
    }
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-sm p-8 w-full max-w-md">
            <h1 class="text-4xl font-bold text-blue-900 text-center mb-6">Reset Your Password</h1>

            <?php if ($message): ?>
                <div class="p-4 mb-4 text-sm rounded-lg <?php echo $message_type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if ($show_form): ?>
                <p class="text-gray-600 text-center mb-8">Please enter your new password below.</p>
                <form action="<?php echo htmlspecialchars($_SERVER["REQUEST_URI"]); ?>" method="POST" class="space-y-6">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    
                    <div>
                        <label for="password" class="block text-sm font-semibold text-blue-900 mb-2">New Password <span class="text-red-500">*</span></label>
                        <input type="password" id="password" name="password" class="w-full px-4 py-3 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
                    </div>

                    <div>
                        <label for="password_confirm" class="block text-sm font-semibold text-blue-900 mb-2">Confirm New Password <span class="text-red-500">*</span></label>
                        <input type="password" id="password_confirm" name="password_confirm" class="w-full px-4 py-3 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
                    </div>

                    <button type="submit" class="w-full bg-orange-500 text-white font-bold py-3 rounded hover:bg-orange-600 transition duration-200">
                        Reset Password
                    </button>
                </form>
            <?php endif; ?>

            <?php if ($password_updated): ?>
                <div class="text-center">
                    <a href="login.php" class="w-full inline-block bg-blue-900 text-white font-bold py-3 px-4 rounded hover:bg-blue-800 transition duration-200">
                        Go to Login
                    </a>
                </div>
            <?php elseif (!$show_form && !$password_updated): ?>
                 <div class="text-center">
                    <a href="forgot-password.php" class="text-orange-500 font-semibold hover:underline">Request a new link</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>