<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!function_exists('set_notification')) {
    function set_notification($message, $type = 'success') {
        $_SESSION['notification'] = [
            'message' => $message,
            'type' => $type
        ];
    }
}

if (!function_exists('display_notification')) {
    function display_notification() {
        if (isset($_SESSION['notification'])) {
            $notification = $_SESSION['notification'];
            $message = addslashes($notification['message']);
            $type = addslashes($notification['type']);
            
            // REVISED: The 'DOMContentLoaded' wrapper has been removed.
            // Since this script is called at the end of the page body (in footer.php),
            // the DOM is already loaded, and we can call the notification function directly.
            echo "<script>
                    if (typeof showNotification === 'function') {
                        showNotification('$message', '$type'); 
                    }
                  </script>";
            unset($_SESSION['notification']);
        }
    }
}

/**
 * REVISED: Added the missing isActive function for sidebar navigation.
 * Checks if the current page matches the given navigation link to set an 'active' class.
 * @param string $nav_link The partial path of the navigation link (e.g., 'dashboard.php' or 'ups/list.php').
 * @return string 'active' if it matches, otherwise an empty string.
 */
if (!function_exists('isActive')) {
    function isActive($nav_link) {
        $current_page = $_SERVER['SCRIPT_NAME'];
        if (strpos($current_page, $nav_link) !== false) {
            return 'active';
        }
        return '';
    }
}

