<?php
require_once '../config/init.php';
checkLogin();

// --- Main Stats ---
$total_ups_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM ups");
$total_ups = $total_ups_result ? mysqli_fetch_assoc($total_ups_result)['count'] : 0;
$scheduled_maintenance_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM maintenance_ups WHERE status = 'terjadwal'");
$scheduled_maintenance = $scheduled_maintenance_result ? mysqli_fetch_assoc($scheduled_maintenance_result)['count'] : 0;
$overdue_tasks_query = "SELECT COUNT(*) as count FROM maintenance_ups WHERE status = 'terlambat' OR (status = 'terjadwal' AND tanggal_jadwal < CURDATE())";
$overdue_tasks_result = mysqli_query($conn, $overdue_tasks_query);
$overdue_tasks = $overdue_tasks_result ? mysqli_fetch_assoc($overdue_tasks_result)['count'] : 0;

// --- Chart 2: Maintenance Status Overview (Donut Chart) ---
$status_query = "SELECT status, COUNT(*) as count FROM maintenance_ups WHERE status IN ('selesai', 'terjadwal', 'terlambat', 'ditunda') GROUP BY status";
$status_result = mysqli_query($conn, $status_query);
$status_data = [];
$total_status_count = 0;
$status_colors = [
    'selesai' => '#27ae60',
    'terjadwal' => '#2980b9',
    'terlambat' => '#f39c12',
    'ditunda' => '#8e44ad',
    'selesai (terlambat)' => '#f39c12',
];

if ($status_result) {
    while ($row = mysqli_fetch_assoc($status_result)) {
        $status_data[] = $row;
        $total_status_count += $row['count'];
    }
}
$gradient_parts = [];
$current_percentage = 0;
foreach ($status_data as $status) {
    $percentage = ($total_status_count > 0) ? ($status['count'] / $total_status_count) * 100 : 0;
    $color = $status_colors[strtolower($status['status'])] ?? '#bdc3c7';
    $gradient_parts[] = "$color $current_percentage% " . ($current_percentage + $percentage) . "%";
    $current_percentage += $percentage;
}
$conic_gradient_css = "conic-gradient(" . implode(', ', $gradient_parts) . ")";

// --- Maintenance Log Data ---
$log_query = "
SELECT 'completed' as type, m.tanggal_pelaksanaan as event_date, u.nama_ups, u.lokasi
FROM maintenance_ups m JOIN ups u ON m.ups_id = u.ups_id
WHERE m.status IN ('Selesai', 'Selesai (Terlambat)')
UNION ALL
SELECT 'scheduled' as type, m.tanggal_jadwal as event_date, u.nama_ups, u.lokasi
FROM maintenance_ups m JOIN ups u ON m.ups_id = u.ups_id
WHERE m.status = 'Terjadwal'
ORDER BY event_date DESC
LIMIT 5";
$log_result = mysqli_query($conn, $log_query);

// Data fetching logic for reports section is now handled by reports.php
if (isset($_GET['ajax_reports'])) {
    include 'reports.php';
    exit;
}

$page_title = "UPS Maintenance - Dashboard ";
include '../templates/header.php';
?>

<main>
    <div class="page-header">
        <div>
            <h1>Dashboard Pemeliharaan UPS</h1>
            <p>PT. Bukit Asam</p>
        </div>
        <div class="header-actions">
            <button type="button" id="chartVisibilityToggleBtn" class="btn-icon" title="Tampilkan/Sembunyikan Grafik">
                <i class="fas fa-eye"></i>
            </button>
        </div>
    </div>

    <!-- Chart Container -->
    <div class="charts-container">
        <!-- Maintenance Status Overview -->
        <div class="chart-card">
            <div class="chart-header">
                <h3>Status Pemeliharaan</h3>
            </div>
            <div class="chart-body donut-chart-container">
                <?php if (!empty($status_data)): ?>
                    <div class="donut-chart" style="background: <?php echo $conic_gradient_css; ?>;">
                        <div class="donut-center">
                            <span>Total</span>
                            <strong><?php echo $total_status_count; ?></strong>
                        </div>
                    </div>
                    <ul class="donut-legend">
                        <?php foreach ($status_data as $status): ?>
                            <li data-status="<?php echo htmlspecialchars(strtolower($status['status'])); ?>">
                                <span class="legend-color" style="background-color: <?php echo $status_colors[strtolower($status['status'])] ?? '#bdc3c7'; ?>"></span>
                                <span class="legend-label"><?php echo htmlspecialchars(ucfirst($status['status'])); ?></span>
                                <span class="legend-value"><?php echo $status['count']; ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="no-data">Tidak ada data status yang tersedia.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Mini Calendar -->
        <div class="chart-card">
            <div class="chart-header">
                <h3 id="mini-calendar-month-year"></h3>
                <div>
                    <button id="openCalendarModalBtn" class="btn-secondary btn-sm" style="padding: 5px 10px; font-size: 0.8rem;">
                        <i class="fas fa-expand-alt"></i> Lihat Kalender Penuh
                    </button>
                </div>
            </div>
            <div class="chart-body">
                <div id="mini-calendar-container" class="mini-calendar">
                    <div class="mini-calendar-weekdays">
                        <div>M</div>
                        <div>S</div>
                        <div>S</div>
                        <div>R</div>
                        <div>K</div>
                        <div>J</div>
                        <div>S</div>
                    </div>
                    <div id="mini-calendar-days-grid" class="mini-calendar-days-grid">
                        <!-- Days will be generated by JavaScript -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Maintenance Log -->
        <div class="chart-card">
            <div class="chart-header">
                <h3>Log Pemeliharaan</h3>
            </div>
            <div class="chart-body">
                <ul class="maintenance-log">
                    <?php if ($log_result && mysqli_num_rows($log_result) > 0): ?>
                        <?php while ($log = mysqli_fetch_assoc($log_result)): ?>
                            <li class="log-item log-item-<?php echo htmlspecialchars($log['type']); ?>">
                                <div class="log-icon">
                                    <i class="fas <?php echo ($log['type'] == 'completed') ? 'fa-check-circle' : 'fa-calendar-alt'; ?>"></i>
                                </div>
                                <div class="log-content">
                                    <p class="log-title">
                                        <?php echo htmlspecialchars($log['nama_ups']); ?>
                                        <span class="log-subtitle"><?php echo ($log['type'] == 'completed') ? 'Selesai' : 'Terjadwal'; ?></span>
                                    </p>
                                    <p class="log-meta">
                                        <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($log['lokasi']); ?></span>
                                        <span><i class="fas fa-clock"></i> <?php echo htmlspecialchars(date('d M Y, H:i', strtotime($log['event_date']))); ?></span>
                                    </p>
                                </div>
                            </li>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <li class="log-item-empty">Tidak ada aktivitas pemeliharaan terbaru.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>

    <div id="reports-section-container" class="ajax-loading">
        <div class="loading-overlay">
            <div class="spinner"></div>
        </div>
        <?php include 'reports.php'; ?>
    </div>

</main>

<div id="detailsModal" class="modal">
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <h2 id="modal-title">Detail Laporan Pemeliharaan</h2>
            <button class="close-modal-btn">&times;</button>
        </div>
        <div class="modal-body">
            <div class="details-container">
                <div class="details-section">
                    <h2 class="section-title">Informasi UPS</h2>
                    <div class="details-grid">
                        <div><strong>Nama UPS:</strong> <span id="modal-nama_ups"></span></div>
                        <div><strong>Lokasi:</strong> <span id="modal-lokasi"></span></div>
                        <div><strong>Merk:</strong> <span id="modal-merk"></span></div>
                        <div><strong>Tipe:</strong> <span id="modal-tipe_ups"></span></div>
                        <div><strong>Kapasitas:</strong> <span id="modal-ukuran_kapasitas"></span></div>
                        <div><strong>Jumlah Baterai:</strong> <span id="modal-jumlah_baterai"></span></div>
                        <div><strong>Alamat IP:</strong> <span id="modal-ip_address"></span></div>
                        <div><strong>Jasa Perusahaan:</strong> <span id="modal-perusahaan_maintenance"></span></div>
                    </div>
                </div>
                <div class="details-section">
                    <h2 class="section-title">Detail Pemeliharaan</h2>
                    <div class="details-grid">
                        <div><strong>Status:</strong> <span id="modal-status"></span></div>
                        <div><strong>Jenis Pemeliharaan:</strong> <span id="modal-jenis"></span></div>
                        <div><strong>Tanggal Jadwal:</strong> <span id="modal-tanggal_jadwal"></span></div>
                        <div><strong>Tanggal Pelaksanaan:</strong> <span id="modal-tanggal_pelaksanaan"></span></div>
                        <div><strong>Teknisi:</strong> <span id="modal-teknisi"></span></div>
                    </div>
                </div>
                <div class="details-section">
                    <h2 class="section-title">Temuan & Tindakan</h2>
                    <div class="details-prose">
                        <h3>Hasil Pengecekan (Temuan)</h3>
                        <p id="modal-hasil_pengecekan"></p>
                    </div>
                    <div class="details-prose">
                        <h3>Penggantian (Tindakan / Komponen Diganti)</h3>
                        <p id="modal-pengubahan"></p>
                    </div>
                </div>
                <div id="modal-attachment-section" class="details-section" style="display: none;">
                    <div class="section-header-action">
                        <h2 class="section-title">Lampiran</h2>
                        <button type="button" class="btn btn-view-attachment view-attachment-btn"><i class="fas fa-eye"></i> Lihat Lampiran</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary close-modal-footer-btn">Tutup</button>
        </div>
    </div>
</div>

<div id="fullCalendarModal" class="modal">
    <div class="modal-content modal-xl">
        <div class="modal-header">
            <h2 id="full-calendar-modal-title">Kalender Pemeliharaan</h2>
            <button class="close-modal-btn">&times;</button>
        </div>
        <div class="modal-body" style="padding: 0; display: flex; flex-direction: column;">
            <div id="full-calendar-container" class="calendar-container">
                <div class="calendar-header">
                    <a href="#" id="modal-prev-month-btn" class="nav-arrow"><i class="fas fa-chevron-left"></i></a>
                    <h2 id="modal-calendar-month-year"></h2>
                    <a href="#" id="modal-next-month-btn" class="nav-arrow"><i class="fas fa-chevron-right"></i></a>
                </div>
                <div class="calendar-weekdays">
                    <div>Minggu</div>
                    <div>Senin</div>
                    <div>Selasa</div>
                    <div>Rabu</div>
                    <div>Kamis</div>
                    <div>Jumat</div>
                    <div>Sabtu</div>
                </div>
                <div id="modal-calendar-days-grid" class="calendar-days-grid"></div>
            </div>
        </div>
    </div>
</div>

<div id="fullCalendarEventModal" class="modal">
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <h2 id="full-event-modal-title">Detail Pemeliharaan</h2>
            <button class="close-modal-btn">&times;</button>
        </div>
        <div class="modal-body">
            <div class="details-container">
                <div class="details-section">
                    <h2 class="section-title">Informasi UPS</h2>
                    <div class="details-grid">
                        <div><strong>Nama UPS:</strong> <span id="cal-modal-nama_ups"></span></div>
                        <div><strong>Lokasi:</strong> <span id="cal-modal-lokasi"></span></div>
                        <div><strong>Merk:</strong> <span id="cal-modal-merk"></span></div>
                        <div><strong>Tipe:</strong> <span id="cal-modal-tipe_ups"></span></div>
                        <div><strong>Kapasitas:</strong> <span id="cal-modal-ukuran_kapasitas"></span></div>
                        <div><strong>Jumlah Baterai:</strong> <span id="cal-modal-jumlah_baterai"></span></div>
                        <div><strong>Alamat IP:</strong> <span id="cal-modal-ip_address"></span></div>
                        <div><strong>Jasa Perusahaan:</strong> <span id="cal-modal-perusahaan_maintenance"></span></div>
                    </div>
                </div>
                <div class="details-section">
                    <h2 class="section-title">Detail Pemeliharaan</h2>
                    <div class="details-grid">
                        <div><strong>Status:</strong> <span id="cal-modal-status"></span></div>
                        <div><strong>Jenis Pemeliharaan:</strong> <span id="cal-modal-jenis"></span></div>
                        <div><strong>Tanggal Jadwal:</strong> <span id="cal-modal-tanggal_jadwal"></span></div>
                        <div><strong>Teknisi:</strong> <span id="cal-modal-teknisi"></span></div>
                    </div>
                </div>
                <!-- REVISED: Added attachment section -->
                <div id="cal-modal-attachment-section" class="details-section" style="display: none;">
                    <div class="section-header-action">
                        <h2 class="section-title">Lampiran</h2>
                        <button type="button" class="btn btn-view-attachment view-attachment-btn"><i class="fas fa-eye"></i> Lihat Lampiran</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary close-modal-footer-btn">Tutup</button>
        </div>
    </div>
</div>

<?php
include '../templates/footer.php';
?>

<script>
    const donutStatusData = <?php echo json_encode($status_data); ?>;
    const donutStatusColors = <?php echo json_encode($status_colors); ?>;
    const totalStatusCount = <?php echo $total_status_count; ?>;
</script>