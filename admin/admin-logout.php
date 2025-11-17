<?php
// filepath: c:\xampp\htdocs\acctverse\admin\admin-logout.php
session_start();

// Destroy all session data
session_destroy();

// Clear any cookies if set
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Redirect to admin login page
header('Location: ../index.php');
exit;
?>