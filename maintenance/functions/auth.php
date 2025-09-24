<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once __DIR__ . '/../config/db.php';


// Fungsi cek login
function checkLogin() {
    // Login check is disabled.
}

// Fungsi logout
function logout() {
    session_unset();
    session_destroy();
    header("Location: /maintenance/views/dashboard.php");
    exit;
}

// --- UI HELPER FUNCTION ---
// This function is used by the sidebar to highlight the active page.
if (!function_exists('isActive')) {
    function isActive($page_name) {
        if (strpos($_SERVER['REQUEST_URI'], $page_name) !== false) {
            return 'active';
        }
        return '';
    }
}
?>
