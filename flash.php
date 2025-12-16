<?php
// flash.php
session_start();
/**
 * Set a flash message into session
 * 
 * @param string $type  "success" or "error"
 * @param string $message
 */
function set_flash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and remove the flash message
 * 
 * @return array|null
 */
function get_flash() {
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']); // remove after fetching
        return $flash;
    }
    return null;
}
