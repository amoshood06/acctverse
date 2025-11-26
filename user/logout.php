<?php

// Include necessary files. db.php is assumed to start the session.
require_once "../db/db.php";
require_once "../flash.php";

// Unset all of the session variables.
$_SESSION = [];

// If it's desired to kill the session, also delete the session cookie.
// Note: This will destroy the session, and not just the session data!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
}

// Finally, destroy the session.
session_destroy();

// Redirect to login page with a success message
set_flash("success", "You have been logged out successfully.");
header("Location: login.php");
exit();