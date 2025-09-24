<?php
// Variabel ini didefinisikan di header.php, yang menyertakan file ini.
global $base_path;
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-content">
        <div> <!-- Mengelompokkan konten atas -->
            <!-- Sidebar Header -->
            <div class="sidebar-header">
                <div class="logo-container">
                    <a href="<?php echo $base_path; ?>/views/dashboard" class="nav-link">
                        <img src="<?php echo $base_path; ?>/assets/images/SIPUT.png" alt="SIPUT Logo" class="logo-icon">
                    </a>
                    <a href="<?php echo $base_path; ?>/views/dashboard" class="nav-link">
                        <div class="logo-text">
                            <h2>SIPUT</h2>
                            <span>Sistem Maintenance UPS</span>
                        </div>
                    </a>
                </div>
            </div>

            <nav class="sidebar-nav" role="navigation">
                <ul>
                    <li class="nav-section-title"><span>Beranda</span></li>

                    <li class="nav-item <?php echo isActive('dashboard'); ?>">
                        <a href="<?php echo $base_path; ?>/views/dashboard" class="nav-link">
                            <div class="nav-icon"><i class="fas fa-tachometer-alt"></i></div>
                            <span class="nav-text">Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-section-title"><span>Teknis</span></li>
                    <li class="nav-item <?php echo isActive('ups/list'); ?>">
                        <a href="<?php echo $base_path; ?>/views/ups/list" class="nav-link">
                            <div class="nav-icon"><i class="fas fa-server"></i></div>
                            <span class="nav-text">Unit UPS</span>
                        </a>
                    </li>
                    <li class="nav-item <?php echo isActive('hasil/list'); ?>">
                        <a href="<?php echo $base_path; ?>/views/hasil/list" class="nav-link">
                            <div class="nav-icon"><i class="fas fa-history"></i></div>
                            <span class="nav-text">Riwayat Laporan</span>
                        </a>
                    </li>

                </ul>
            </nav>
        </div>
    </div>
</aside>