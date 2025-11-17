<?php
session_start();
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

$flash = null;

// Verify token
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    try {
        // Check if token exists
        $stmt = $pdo->prepare("SELECT id, is_verified FROM users WHERE verify_token = ?");
        $stmt->execute([$token]);
        $user = $stmt->fetch();

        if (!$user) {
            set_flash("error", "Invalid or expired verification link.");
        } elseif ($user['is_verified']) {
            set_flash("success", "Your account is already verified. You can log in now.");
        } else {
            // Update user to verified
            $update = $pdo->prepare("UPDATE users SET is_verified = 1, verify_token = NULL WHERE id = ?");
            $update->execute([$user['id']]);
            set_flash("success", "Your email has been verified! You can now log in.");
        }

        header("Location: login.php");
        exit;

    } catch (Exception $e) {
        set_flash("error", "Verification failed: " . $e->getMessage());
        header("Location: login.php");
        exit;
    }
} else {
    set_flash("error", "No verification token provided.");
    header("Location: login.php");
    exit;
}
?>
