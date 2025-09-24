<?php
// Set the default timezone to Asia/Jakarta for correct timestamps
date_default_timezone_set('Asia/Jakarta');

// 1. Memulai manajemen session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Menyertakan Konfigurasi Database
// Mengasumsikan db.php ada di direktori yang sama (config/)
require_once __DIR__ . '/db.php';

// 3. Menyertakan Pustaka Fungsi Inti
// Mengasumsikan folder functions/ berada satu level di atas config/
require_once __DIR__ . '/../functions/auth.php'; 
require_once __DIR__ . '/../functions/notifications.php';

?>

