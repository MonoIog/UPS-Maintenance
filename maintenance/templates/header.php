<?php
// Mendefinisikan path dasar agar tautan aset berfungsi dengan benar
$base_path = '/maintenance';
?>
<!DOCTYPE html>
<!-- Kelas 'dark-mode' akan ditambahkan di sini oleh skrip -->
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title ?? 'SIPUT - Dasbor'); ?></title>

    <link rel="icon" href="<?php echo $base_path; ?>/assets/images/SIPUT-Header.png" type="image/png">

    <!-- Stylesheet Font Awesome Lokal -->
    <link rel="stylesheet" href="<?php echo $base_path; ?>/assets/fontawesome/css/all.min.css">

    <!-- Stylesheet Utama -->
    <link rel="stylesheet" href="<?php echo $base_path; ?>/assets/css/style.css?v=1.5">

    <!-- Skrip langsung untuk mencegah kedipan saat memuat halaman -->
    <script>
        // Skrip ini berjalan sebelum halaman di-render untuk mencegah "kedipan"
        (function() {
            // Menerapkan status sidebar
            if (localStorage.getItem('sidebarState') === 'collapsed') {
                document.documentElement.classList.add('sidebar-collapsed');
            }
            // Menerapkan status visibilitas chart
            if (localStorage.getItem('chartVisibilityState') === 'hidden') {
                document.documentElement.classList.add('charts-hidden');
            }
            // Menerapkan status tema
            if (localStorage.getItem('theme') === 'dark') {
                document.documentElement.classList.add('dark-mode');
            }
        })();
    </script>
</head>

<body>
    <script>
        // Ini membuat path dasar tersedia untuk semua file JavaScript.
        const BASE_URL = '<?php echo $base_path; ?>';
    </script>

    <!-- REVISED: Corrected page structure -->
    <div class="app-container">
        <div class="page-wrapper">
            <?php include 'sidebar.php'; ?>
            <div class="main-content">
                <?php include 'top-header.php'; ?>
                <div class="content-body">
