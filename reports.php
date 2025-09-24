<?php
// REVISED: Data fetching logic from reports.php is now here
$view = $_GET['view'] ?? 'table';
$limit = 6; // Set a consistent limit for both table and card views
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// REVISED: Ensure current page is passed to the view switcher
$current_page_for_switcher = $page;

$location_filter = $_GET['location'] ?? 'all';
$capacity_filter = $_GET['capacity'] ?? 'all';
$brand_filter = $_GET['brand'] ?? 'all';

$where_clauses = ["m.status IN ('selesai', 'Selesai (Terlambat)', 'ditunda')", "m.tanggal_pelaksanaan IS NOT NULL"];
$params = [];
$types = '';

if ($location_filter !== 'all') {
    $where_clauses[] = "u.lokasi = ?";
    $params[] = $location_filter;
    $types .= 's';
}
if ($capacity_filter !== 'all') {
    $where_clauses[] = "u.ukuran_kapasitas = ?";
    $params[] = $capacity_filter;
    $types .= 's';
}
if ($brand_filter !== 'all') {
    $where_clauses[] = "u.merk = ?";
    $params[] = $brand_filter;
    $types .= 's';
}

$where_sql = "WHERE " . implode(' AND ', $where_clauses);

$total_reports_query = "SELECT COUNT(*) as total FROM maintenance_ups m JOIN ups u ON m.ups_id = u.ups_id $where_sql";
$stmt_total = mysqli_prepare($conn, $total_reports_query);
if ($stmt_total && !empty($types)) {
    mysqli_stmt_bind_param($stmt_total, $types, ...$params);
}
if ($stmt_total) {
    mysqli_stmt_execute($stmt_total);
    $total_result = mysqli_stmt_get_result($stmt_total);
    $total_rows = mysqli_fetch_assoc($total_result)['total'];
    mysqli_stmt_close($stmt_total);
} else {
    $total_rows = 0;
}
$total_pages = ceil($total_rows / $limit);

$recent_reports_query = "
SELECT
m.maintenance_id, m.tanggal_jadwal, m.tanggal_pelaksanaan, m.jenis, m.status,
m.teknisi, m.hasil_pengecekan, m.pengubahan, m.catatan, m.attachment_path,
u.nama_ups, u.lokasi, u.merk, u.tipe_ups, u.ip_address,
u.ukuran_kapasitas, u.jumlah_baterai, u.perusahaan_maintenance
FROM maintenance_ups m
JOIN ups u ON m.ups_id = u.ups_id
$where_sql
ORDER BY m.tanggal_pelaksanaan DESC, m.maintenance_id DESC
LIMIT ? OFFSET ?";

$page_types = $types . 'ii';
$page_params = [...$params, $limit, $offset];
$stmt_reports = mysqli_prepare($conn, $recent_reports_query);
// REVISED: Add a check to ensure the statement prepared successfully before executing.
if ($stmt_reports) {
    mysqli_stmt_bind_param($stmt_reports, $page_types, ...$page_params);
    mysqli_stmt_execute($stmt_reports);
    $recent_reports_result = mysqli_stmt_get_result($stmt_reports);
} else {
    $recent_reports_result = false;
}
?>

<div class="reports-container">
    <div class="table-header">
        <div class="search-container">
            <i class="fas fa-search"></i>
            <input type="text" id="dashboardSearchInput" placeholder="Cari Hasil Laporan">
        </div>
        <div class="view-slider">
            <?php // REVISED: Added current page to the query params
            $query_params_for_view_switcher = "location=$location_filter&brand=$brand_filter&capacity=$capacity_filter&page=$current_page_for_switcher"; ?>

            <input type="radio" id="view-table" name="view_mode" value="table" <?php echo ($view === 'table') ? 'checked' : ''; ?>>
            <label for="view-table"><i class="fas fa-table"></i>Table</label>

            <input type="radio" id="view-card" name="view_mode" value="card" <?php echo ($view === 'card') ? 'checked' : ''; ?>>
            <label for="view-card"><i class="fas fa-th-large"></i>Card</label>

            <div class="slider-thumb"></div>
        </div>
    </div>

    <?php
    // REVISED: Check if the query was successful AND returned rows.
    if ($recent_reports_result && mysqli_num_rows($recent_reports_result) > 0): ?>
        <div class="table-container <?php echo ($view !== 'table') ? 'view-hidden' : ''; ?>" style="margin-top: 0;">
            <table id="reportsTable">
                <thead>
                    <tr>
                        <th>No. Laporan</th>
                        <th>Lokasi</th>
                        <th>Kapasitas</th>
                        <th>Teknisi</th>
                        <th>Tanggal Pelaksanaan</th>
                        <th>Jenis</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php mysqli_data_seek($recent_reports_result, 0);
                    while ($row = mysqli_fetch_assoc($recent_reports_result)): ?>
                        <tr>
                            <!-- SECURITY: Escaped output -->
                            <td>RL-<?php echo str_pad($row['maintenance_id'], 4, '0', STR_PAD_LEFT); ?></td>
                            <td><?php echo htmlspecialchars($row['lokasi']); ?></td>
                            <td><?php echo htmlspecialchars($row['ukuran_kapasitas']); ?> KVA</td>
                            <td><?php echo htmlspecialchars($row['teknisi'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars(date('d M Y, H:i', strtotime($row['tanggal_pelaksanaan']))); ?></td>
                            <td>
                                <?php
                                $jenis_class_dashboard = '';
                                if (strpos(strtolower($row['jenis']), 'preventive') !== false) {
                                    $jenis_class_dashboard = 'status-preventive';
                                } elseif (strpos(strtolower($row['jenis']), 'corrective') !== false) {
                                    $jenis_class_dashboard = 'status-corrective';
                                }

                                $jenis_text = htmlspecialchars(ucfirst($row['jenis']));
                                $main_type = $jenis_text;
                                $sub_type = '';

                                if (preg_match('/^(.*?)\s*(\(.*\))$/', $jenis_text, $matches)) {
                                    $main_type = trim($matches[1]);
                                    $sub_type = $matches[2];
                                }
                                ?>
                                <div class="maintenance-type-wrapper status-badge <?php echo htmlspecialchars($jenis_class_dashboard); ?>">
                                    <span class="type-main"><?php echo $main_type; ?></span>
                                    <?php if (!empty($sub_type)): ?>
                                        <span class="type-sub"><?php echo htmlspecialchars($sub_type); ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <?php
                                $status_text_dashboard = htmlspecialchars(ucfirst($row['status']));
                                $status_class_dashboard = '';
                                $main_status = $status_text_dashboard;
                                $sub_status = '';

                                if (preg_match('/^(.*?)\s*(\(.*\))$/', $status_text_dashboard, $matches)) {
                                    $main_status = trim($matches[1]);
                                    $sub_status = $matches[2];
                                }

                                switch (strtolower($row['status'])) {
                                    case 'selesai':
                                        $status_class_dashboard = 'status-completed';
                                        break;
                                    case 'selesai (terlambat)':
                                        $status_class_dashboard = 'status-completed-late';
                                        break;
                                    case 'ditunda':
                                    default:
                                        $status_class_dashboard = 'status-pending';
                                        break;
                                }
                                ?>
                                <div class="maintenance-type-wrapper status-badge <?php echo htmlspecialchars($status_class_dashboard); ?>">
                                    <span class="type-main"><?php echo $main_status; ?></span>
                                    <?php if (!empty($sub_status)): ?>
                                        <span class="type-sub"><?php echo htmlspecialchars($sub_status); ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="table-actions">
                                <button class="btn-details open-details-btn" title="View Details"
                                    data-id="<?php echo htmlspecialchars($row['maintenance_id']); ?>"
                                    <?php
                                    // SECURITY: Escaped output for all data attributes
                                    foreach ($row as $key => $value) {
                                        if ($value !== null) {
                                            echo "data-{$key}='" . htmlspecialchars($value, ENT_QUOTES) . "' ";
                                        }
                                    }
                                    ?>>
                                    <i class="fas fa-eye"></i>
                                    <span>Details</span>
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="report-cards-wrapper <?php echo ($view !== 'card') ? 'view-hidden' : ''; ?>">
            <div class="report-cards-container">
                <?php mysqli_data_seek($recent_reports_result, 0);
                while ($row = mysqli_fetch_assoc($recent_reports_result)): ?>
                    <div class="report-card">
                        <div class="report-card-content">
                            <div class="report-card-header">
                                <!-- SECURITY: Escaped output -->
                                <h3>RL-00<?php echo htmlspecialchars($row['maintenance_id']); ?></h3>
                                <span class="report-card-subtitle"><?php echo htmlspecialchars($row['nama_ups'] . ' / ' . $row['perusahaan_maintenance']); ?></span>
                            </div>
                            <div class="report-card-body">
                                <p><strong>Lokasi:</strong> <?php echo htmlspecialchars($row['lokasi']); ?></p>
                                <p><strong>Tanggal:</strong> <?php echo htmlspecialchars(date('d M Y', strtotime($row['tanggal_pelaksanaan']))); ?></p>
                                <p><strong>Teknisi:</strong> <?php echo htmlspecialchars($row['teknisi'] ?? 'N/A'); ?></p>
                            </div>
                        </div>
                        <div class="hover-overlay">
                            <?php
                            $status_text_card = htmlspecialchars(ucfirst($row['status']));
                            $status_class_card = '';
                            $main_status_card = $status_text_card;
                            $sub_status_card = '';

                            if (preg_match('/^(.*?)\s*(\(.*\))$/', $status_text_card, $matches)) {
                                $main_status_card = trim($matches[1]);
                                $sub_status_card = $matches[2];
                            }

                            if (strpos(strtolower($row['status']), 'selesai (terlambat)') !== false) {
                                $status_class_card = 'status-completed-late';
                            } elseif (strpos(strtolower($row['status']), 'selesai') !== false) {
                                $status_class_card = 'status-completed';
                            } else {
                                $status_class_card = 'status-pending';
                            }
                            ?>
                            <div class="status-stamp <?php echo htmlspecialchars($status_class_card); ?>">
                                <div class="stamp-main-text"><i class="fas fa-check-circle"></i> <?php echo strtoupper($main_status_card); ?></div>
                                <?php if (!empty($sub_status_card)): ?>
                                    <div class="stamp-sub-text"><?php echo strtoupper($sub_status_card); ?></div>
                                <?php endif; ?>
                            </div>

                            <button class="btn-details-card open-details-btn"
                                data-id="<?php echo htmlspecialchars($row['maintenance_id']); ?>"
                                <?php
                                // SECURITY: Escaped output for all data attributes
                                foreach ($row as $key => $value) {
                                    if ($value !== null) {
                                        echo "data-{$key}='" . htmlspecialchars($value, ENT_QUOTES) . "' ";
                                    }
                                }
                                ?>>View Details</button>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <div class="pagination-footer">
            <p>Menampilkan <?php echo mysqli_num_rows($recent_reports_result); ?> dari <?php echo htmlspecialchars($total_rows); ?> hasil</p>
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php $query_params = "view=$view&location=$location_filter&brand=$brand_filter&capacity=$capacity_filter"; ?>
                    <a href="?page=<?php echo max(1, $page - 1); ?>&<?php echo htmlspecialchars($query_params, ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo ($page <= 1) ? 'disabled' : ''; ?>">Sebelumnya</a>
                    <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                        <a href="?page=<?php echo $i; ?>&<?php echo htmlspecialchars($query_params, ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo ($page == $i) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                    <a href="?page=<?php echo min($total_pages, $page + 1); ?>&<?php echo htmlspecialchars($query_params, ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">Selanjutnya</a>
                </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 40px; background-color: #fff; border-radius: 8px;">Tidak ada hasil laporan baru.</div>
    <?php endif; ?>
</div>