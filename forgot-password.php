<?php
require_once './db/db.php';

$message = '';
$message_type = ''; // 'success' or 'error'

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email_or_username = trim($_POST['email_or_username'] ?? '');

    if (empty($email_or_username)) {
        $message = 'Please enter your email or username.';
        $message_type = 'error';
    } else {
        try {
            // Find user by email or username
            $sql = "SELECT id, email, full_name FROM users WHERE email = ? OR username = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$email_or_username, $email_or_username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Generate a secure token and expiry date (e.g., 1 hour from now)
                $token = bin2hex(random_bytes(32));
                $expires_at = date('Y-m-d H:i:s', time() + 3600); // 1 hour

                // Store the token and expiry date in the database for the user
                $update_stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expires_at = ? WHERE id = ?");
                $update_stmt->execute([$token, $expires_at, $user['id']]);

                // Send the password reset email
                $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/acctv/reset-password.php?token=" . $token;
                $subject = "Password Reset Request for Acctverse";
                $email_message = "
                    Hello " . htmlspecialchars($user['full_name']) . ",<br><br>
                    Someone has requested a password reset for your account. If this was you, please click the link below to reset your password. The link will expire in 1 hour.<br><br>
                    <a href='" . $reset_link . "'>" . $reset_link . "</a><br><br>
                    If you did not request a password reset, you can safely ignore this email.<br><br>
                    Regards,<br>
                    The Acctverse Team
                ";

                $headers = "MIME-Version: 1.0\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8\r\n";
                $headers .= "From: noreply@acctverse.com\r\n";

                // Use mail() function to send email
                mail($user['email'], $subject, $email_message, $headers);
            }
        } catch (Exception $e) {
            // Log the error, but don't show it to the user for security.
            error_log("Forgot Password Error: " . $e->getMessage());
        }

        // Always show a generic success message to prevent user enumeration
        $message = 'If an account with that email or username exists, a password recovery link has been sent.';
        $message_type = 'success';
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-sm p-8 w-full max-w-md">
            <!-- Header -->
            <h1 class="text-4xl font-bold text-blue-900 text-center mb-6">Forgot Password</h1>

            <!-- Description -->
            <p class="text-gray-600 text-center mb-8">To recover your account please provide your email or username to find your account.</p>

            <?php if ($message): ?>
                <div class="p-4 mb-4 text-sm rounded-lg <?php echo $message_type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="space-y-6">
                <!-- Email or Username Field -->
                <div>
                    <label for="email_or_username" class="block text-sm font-semibold text-blue-900 mb-2">
                        Email or Username <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="email_or_username" name="email_or_username" placeholder="e.g. yourname or name@example.com" class="w-full px-4 py-3 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full bg-orange-500 text-white font-bold py-3 rounded hover:bg-orange-600 transition duration-200">
                    Send Recovery Link
                </button>

                <!-- Back to Login Link -->
                <p class="text-center text-gray-600">
                    Remember your password?
                    <a href="login.php" class="text-orange-500 font-semibold hover:underline">Login</a>
                </p>
            </form>
        </div>
    </div>
</body>
</html>
