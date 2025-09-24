<?php
// Start session if not already started, as this file might be included standalone.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Sets a notification message in the session for display on the next page load.
 * @param string $message The message to display.
 * @param string $type The type of notification ('success', 'error', 'warning').
 */
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
            // Use json_encode for safe transport to JS and to prevent XSS.
            $json_notification = json_encode($notification); 
            
            // Echo a data script block instead of an executable script.
            // This is CSP-friendly.
            echo "<script id='notification-data' type='application/json'>$json_notification</script>";
            
            // Unset the notification so it doesn't show again on refresh
            unset($_SESSION['notification']);
        }
    }
}
