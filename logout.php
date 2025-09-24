<?php
/**
 * This file handles the logout process.
 *
 * Since the login system has been removed, a traditional logout (destroying a session)
 * is no longer necessary. This file now simply redirects any requests
 * back to the main dashboard to ensure a smooth user experience if old
 * logout links are clicked.
 */

// Start the session just to be able to clear it.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Clear any old session data just in case.
session_unset();
session_destroy();

// Redirect to the dashboard.
header("Location: /maintenance/views/dashboard.php");
exit;
?>
