<?php
require_once "./db/db.php";
require_once "./flash.php";
session_start();

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
        header("Location: register.php");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        set_flash("error", "Invalid email format.");
        header("Location: register.php");
        exit;
    }

    if ($password !== $confirmPass) {
        set_flash("error", "Passwords do not match.");
        header("Location: register.php");
        exit;
    }

    try {
        // Check if email exists
        $emailCheck = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $emailCheck->execute([$email]);
        if ($emailCheck->fetch()) {
            set_flash("error", "Email already exists.");
            header("Location: register.php");
            exit;
        }

        // Check if username exists
        $userCheck = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $userCheck->execute([$username]);
        if ($userCheck->fetch()) {
            set_flash("error", "Username already taken.");
            header("Location: register.php");
            exit;
        }

        // Hash password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Generate email verification token
        $token = bin2hex(random_bytes(32));

        $stmt = $pdo->prepare("
            INSERT INTO users (full_name, username, email, mobile_code, mobile, country, password_hash, role, is_verified, verify_token)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'user', 0, ?)
        ");

        $stmt->execute([
            $full_name,
            $username,
            $email,
            $mobile_code,
            $mobile,
            $country,
            $password_hash,
            $token
        ]);

        // Send verification email
        $subject = "Verify Your Account";
        $message = "
            Hello $full_name,<br><br>
            Please verify your account by clicking the link below:<br>
            <a href='https://acctverse.com/verify.php?token=$token'>
            CLICK HERE TO VERIFY
            </a><br><br>
            Thank you.
        ";

        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
        $headers .= "From: noreply@acctverse.com\r\n";

        mail($email, $subject, $message, $headers);

        set_flash("success", "Registration successful! Check your email to verify your account.");

        header("Location: login.php");
        exit;

    } catch (Exception $e) {
        set_flash("error", "Registration failed: " . $e->getMessage());
        header("Location: register.php");
        exit;
    }
}
?>