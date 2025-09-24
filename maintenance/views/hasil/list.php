<?php
require_once '../../config/init.php';
checkLogin();

// --- REGULAR PAGE LOGIC ---
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$location_filter = $_GET['location'] ?? 'all';
$teknisi_filter = $_GET['teknisi'] ?? 'all';
$status_filter = $_GET['status'] ?? 'all';
$where_clauses = ["m.status IN ('Selesai', 'Selesai (Terlambat)', 'Ditunda')"];
$params = [];
$types = '';
if ($location_filter !== 'all') {
    $where_clauses[] = "u.lokasi = ?";
    $params[] = $location_filter;
    $types .= 's';
}
if ($teknisi_filter !== 'all') {
    $where_clauses[] = "m.teknisi = ?";
    $params[] = $teknisi_filter;
    $types .= 's';
}
if ($status_filter !== 'all') {
    $where_clauses[] = "m.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}
$where_sql = "WHERE " . implode(' AND ', $where_clauses);
$total_query = "SELECT COUNT(*) as total FROM maintenance_ups m JOIN ups u ON m.ups_id = u.ups_id $where_sql";
$stmt_total = mysqli_prepare($conn, $total_query);
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
$list_query = "SELECT m.*, u.nama_ups, u.lokasi, u.merk, u.tipe_ups, u.ip_address, u.ukuran_kapasitas, u.jumlah_baterai, u.perusahaan_maintenance FROM maintenance_ups m JOIN ups u ON m.ups_id = u.ups_id $where_sql ORDER BY m.tanggal_pelaksanaan DESC LIMIT ? OFFSET ?";
$page_types = $types . 'ii';
$page_params = [...$params, $limit, $offset];
$stmt_list = mysqli_prepare($conn, $list_query);
if ($stmt_list) {
    mysqli_stmt_bind_param($stmt_list, $page_types, ...$page_params);
    mysqli_stmt_execute($stmt_list);
    $result = mysqli_stmt_get_result($stmt_list);
} else {
    $result = false;
}
$locations = mysqli_query($conn, "SELECT DISTINCT lokasi FROM ups ORDER BY lokasi");
$teknisi_list = mysqli_query($conn, "SELECT DISTINCT teknisi FROM maintenance_ups WHERE teknisi IS NOT NULL AND teknisi != '' ORDER BY teknisi");

$page_title = "UPS Maintenance - Riwayat";
include '../../templates/header.php';
?>

<main>
    <div class="page-header">
        <h1>Laporan Maintenance</h1>
        <div class="header-actions">
            <div class="search-container">
                <i class="fas fa-search"></i>
                <input type="text" id="historySearchInput" placeholder="Cari Hasil Laporan">
            </div>
            <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle" type="button" id="exportDropdownBtn">
                    <i class="fas fa-download"></i> Export
                </button>
                <div class="dropdown-menu" id="exportDropdownMenu">
                    <a href="#" class="dropdown-item" id="exportExcelBtn"><i class="fas fa-file-excel"></i> Export to Excel</a>
                    <a href="#" class="dropdown-item" id="exportPdfBtn"><i class="fas fa-file-pdf"></i> Export to PDF</a>
                </div>
            </div>
            <a href="../hasil/add" class="btn btn-primary"><i class="fas fa-plus"></i> Jadwalkan Tugas Baru</a>
        </div>
    </div>
    <div class="table-container">
        <table id="historyTable">
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
                <?php if ($result && mysqli_num_rows($result) > 0): while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td>RL-<?php echo str_pad($row['maintenance_id'], 4, '0', STR_PAD_LEFT); ?></td>
                            <td><?php echo htmlspecialchars($row['lokasi']); ?></td>
                            <td><?php echo htmlspecialchars($row['ukuran_kapasitas']); ?> KVA</td>
                            <td><?php echo htmlspecialchars($row['teknisi'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars(date('d M Y, H:i', strtotime($row['tanggal_pelaksanaan']))); ?></td>
                            <td>
                                <?php
                                $jenis_class_hasil = '';
                                if (strpos(strtolower($row['jenis']), 'preventive') !== false) {
                                    $jenis_class_hasil = 'status-preventive';
                                } elseif (strpos(strtolower($row['jenis']), 'corrective') !== false) {
                                    $jenis_class_hasil = 'status-corrective';
                                }

                                $jenis_text = htmlspecialchars(ucfirst($row['jenis']));
                                $main_type = $jenis_text;
                                $sub_type = '';

                                if (preg_match('/^(.*?)\s*(\(.*\))$/', $jenis_text, $matches)) {
                                    $main_type = trim($matches[1]);
                                    $sub_type = $matches[2];
                                }
                                ?>
                                <div class="maintenance-type-wrapper status-badge <?php echo $jenis_class_hasil; ?>">
                                    <span class="type-main"><?php echo $main_type; ?></span>
                                    <?php if (!empty($sub_type)): ?>
                                        <span class="type-sub"><?php echo $sub_type; ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <?php
                                $status_text = htmlspecialchars(ucfirst($row['status']));
                                $status_class = '';
                                $main_status = $status_text;
                                $sub_status = '';

                                if (preg_match('/^(.*?)\s*(\(.*\))$/', $status_text, $matches)) {
                                    $main_status = trim($matches[1]);
                                    $sub_status = $matches[2];
                                }

                                switch (strtolower($row['status'])) {
                                    case 'selesai':
                                        $status_class = 'status-completed';
                                        break;
                                    case 'selesai (terlambat)':
                                        $status_class = 'status-completed-late';
                                        break;
                                    case 'ditunda':
                                    default:
                                        $status_class = 'status-pending';
                                        break;
                                }
                                ?>
                                <div class="maintenance-type-wrapper status-badge <?php echo $status_class; ?>">
                                    <span class="type-main"><?php echo $main_status; ?></span>
                                    <?php if (!empty($sub_status)): ?>
                                        <span class="type-sub"><?php echo $sub_status; ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="table-actions">
                                <button class="btn-details open-details-btn" title="View Details" <?php foreach ($row as $key => $value) {
                                                                                                        if ($value !== null) {
                                                                                                            echo "data-{$key}='" . htmlspecialchars($value, ENT_QUOTES) . "' ";
                                                                                                        }
                                                                                                    } ?>><i class="fas fa-eye"></i></button>
                                <button class="btn-excel export-single-excel-btn" title="Export to Excel" <?php foreach ($row as $key => $value) {
                                                                                                                if ($value !== null) {
                                                                                                                    echo "data-{$key}='" . htmlspecialchars($value, ENT_QUOTES) . "' ";
                                                                                                                }
                                                                                                            } ?>><i class="fas fa-file-excel"></i></button>
                                <button class="btn-pdf export-single-pdf-btn" title="Export to PDF"
                                    <?php
                                    $temp_row = $row;
                                    foreach ($temp_row as $key => $value) {
                                        if ($key !== 'jenis' && $value !== null) {
                                            echo "data-{$key}='" . htmlspecialchars($value, ENT_QUOTES) . "' ";
                                        }
                                    }
                                    echo "data-jenis='" . htmlspecialchars($main_type, ENT_QUOTES) . "' ";
                                    echo "data-sub_jenis='" . htmlspecialchars($sub_type, ENT_QUOTES) . "' ";
                                    ?>><i class="fas fa-file-pdf"></i></button>
                                <button class="edit-report-btn" title="Edit"
                                    <?php
                                    foreach ($row as $key => $value) {
                                        if ($value !== null) {
                                            echo "data-{$key}='" . htmlspecialchars($value, ENT_QUOTES) . "' ";
                                        }
                                    }
                                    ?>><i class="fas fa-edit"></i></button>
                                <button class="delete-btn" title="Delete" data-detail="RL-<?php echo str_pad($row['maintenance_id'], 4, '0', STR_PAD_LEFT); ?>" data-href="delete?id=<?php echo $row['maintenance_id']; ?>"><i class="fas fa-trash-alt"></i></button>
                            </td>
                        </tr>
                    <?php endwhile;
                else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 20px;">No reports found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="pagination-footer">
            <p>Showing <?php echo $result ? mysqli_num_rows($result) : 0; ?> of <?php echo $total_rows; ?> results</p>
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php $query_params = "location=$location_filter&teknisi=$teknisi_filter&status=$status_filter"; ?>
                    <a href="?page=<?php echo max(1, $page - 1); ?>&<?php echo $query_params; ?>" class="<?php echo ($page <= 1) ? 'disabled' : ''; ?>">Previous</a>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?><a href="?page=<?php echo $i; ?>&<?php echo $query_params; ?>" class="<?php echo ($page == $i) ? 'active' : ''; ?>"><?php echo $i; ?></a><?php endfor; ?>
                    <a href="?page=<?php echo min($total_pages, $page + 1); ?>&<?php echo $query_params; ?>" class="<?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">Next</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<div id="detailsModal" class="modal">
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <h2 id="modal-title">Maintenance Report Details</h2>
            <button class="close-modal-btn">&times;</button>
        </div>
        <div class="modal-body">
            <div class="details-container">
                <div class="details-section">
                    <h2 class="section-title">UPS Information</h2>
                    <div class="details-grid">
                        <div><strong>Nama UPS:</strong> <span id="modal-nama_ups"></span></div>
                        <div><strong>Lokasi:</strong> <span id="modal-lokasi"></span></div>
                        <div><strong>Merk:</strong> <span id="modal-merk"></span></div>
                        <div><strong>Tipe:</strong> <span id="modal-tipe_ups"></span></div>
                        <div><strong>Kapasitas:</strong> <span id="modal-ukuran_kapasitas"></span></div>
                        <div><strong>Jumlah Baterai:</strong> <span id="modal-jumlah_baterai"></span></div>
                        <div><strong>IP Address:</strong> <span id="modal-ip_address"></span></div>
                        <div><strong>Company Service:</strong> <span id="modal-perusahaan_maintenance"></span></div>
                    </div>
                </div>
                <div class="details-section">
                    <h2 class="section-title">Maintenance Details</h2>
                    <div class="details-grid">
                        <div><strong>Status:</strong> <span id="modal-status"></span></div>
                        <div><strong>Jenis Maintenance:</strong> <span id="modal-jenis"></span></div>
                        <div><strong>Tanggal Jadwal:</strong> <span id="modal-tanggal_jadwal"></span></div>
                        <div><strong>Tanggal Pelaksanaan:</strong> <span id="modal-tanggal_pelaksanaan"></span></div>
                        <div><strong>Teknisi:</strong> <span id="modal-teknisi"></span></div>
                    </div>
                </div>
                <div class="details-section">
                    <h2 class="section-title">Hasil dan Tindakan</h2>
                    <div class="details-prose">
                        <h3>Hasil Pengecekan</h3>
                        <p id="modal-hasil_pengecekan"></p>
                    </div>
                    <div class="details-prose">
                        <h3>Pengubahan (Actions / Components Replaced)</h3>
                        <p id="modal-pengubahan"></p>
                    </div>
                </div>
                <div id="modal-attachment-section" class="details-section" style="display: none;">
                    <div class="section-header-action">
                        <h2 class="section-title">Attachment</h2>
                        <button type="button" class="btn btn-view-attachment view-attachment-btn"><i class="fas fa-eye"></i> View Attachment</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary close-modal-footer-btn">Close</button>
        </div>
    </div>
</div>

<?php include '../../templates/footer.php'; ?>