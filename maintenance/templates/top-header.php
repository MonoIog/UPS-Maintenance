<header class="top-header">
    <div class="header-left-items">
        <button class="hamburger-menu" id="hamburger-menu" title="Toggle Menu">
            <i class="fas fa-bars"></i>
        </button>
        <!-- Kontainer Tampilan Tanggal dan Waktu -->
        <div id="header-date-container" class="header-date"></div>
        <div id="header-time-container" class="header-time"></div>
    </div>

    <div class="header-right-items">
        <!-- Bilah Pencarian Global -->
        <div class="global-search-container">
            <i class="fas fa-search"></i>
            <input type="search" id="globalSearchInput" placeholder="Cari di mana saja...">
        </div>

        <!-- Dropdown Aksi Cepat -->
        <div class="dropdown-container">
            <button class="header-action-btn" id="quickActionsBtn" title="Aksi Cepat">
                <i class="fas fa-plus"></i>
            </button>
            <div class="dropdown-menu header-dropdown" id="quickActionsMenu">
                <a href="<?php echo $base_path; ?>/views/hasil/add" class="dropdown-item">
                    <i class="fas fa-calendar-plus"></i>
                    <span>Jadwalkan Tugas</span>
                </a>
                <a href="<?php echo $base_path; ?>/views/ups/list#add" class="dropdown-item" id="quickAddUpsBtn">
                    <i class="fas fa-server"></i>
                    <span>Tambah UPS Baru</span>
                </a>
            </div>
        </div>

        <!-- Info Pengguna -->
        <div class="user-info">
            <span><?php echo htmlspecialchars($_SESSION['nama'] ?? 'Admin'); ?></span>
        </div>
    </div>
</header>