<?php
require_once '../../config/init.php';
checkLogin();

// --- Handle Form Submissions (Add/Update) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_ups'])) {
        $query = "INSERT INTO ups (nama_ups, lokasi, merk, tipe_ups, ip_address, ukuran_kapasitas, jumlah_baterai, perusahaan_maintenance) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssssssis", $_POST['nama_ups'], $_POST['lokasi'], $_POST['merk'], $_POST['tipe_ups'], $_POST['ip_address'], $_POST['ukuran_kapasitas'], $_POST['jumlah_baterai'], $_POST['perusahaan_maintenance']);
            if (mysqli_stmt_execute($stmt)) set_notification('UPS baru berhasil ditambahkan!', 'success');
            else set_notification("Error: " . mysqli_stmt_error($stmt), 'error');
            mysqli_stmt_close($stmt);
        }
    } elseif (isset($_POST['update_ups'])) {
        $query = "UPDATE ups SET nama_ups=?, lokasi=?, merk=?, tipe_ups=?, ip_address=?, ukuran_kapasitas=?, jumlah_baterai=?, perusahaan_maintenance=? WHERE ups_id=?";
        $stmt = mysqli_prepare($conn, $query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssssssisi", $_POST['nama_ups'], $_POST['lokasi'], $_POST['merk'], $_POST['tipe_ups'], $_POST['ip_address'], $_POST['ukuran_kapasitas'], $_POST['jumlah_baterai'], $_POST['perusahaan_maintenance'], $_POST['ups_id']);
            if (mysqli_stmt_execute($stmt)) set_notification('Detail UPS berhasil diperbarui!', 'success');
            else set_notification("Error: " . mysqli_stmt_error($stmt), 'error');
            mysqli_stmt_close($stmt);
        }
    }
    header("Location: list");
    exit;
}

// --- Data Fetching & Filtering ---
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$tipe_filter = $_GET['tipe'] ?? 'all';
$capacity_filter = $_GET['capacity'] ?? 'all';
$company_filter = $_GET['company'] ?? 'all';

$where_clauses = [];
$params = [];
$types = '';

if ($tipe_filter !== 'all') {
    $where_clauses[] = "tipe_ups = ?";
    $params[] = $tipe_filter;
    $types .= 's';
}
if ($capacity_filter !== 'all') {
    $where_clauses[] = "ukuran_kapasitas = ?";
    $params[] = $capacity_filter;
    $types .= 's';
}
if ($company_filter !== 'all') {
    $where_clauses[] = "perusahaan_maintenance = ?";
    $params[] = $company_filter;
    $types .= 's';
}


$where_sql = "";
if (!empty($where_clauses)) {
    $where_sql = "WHERE " . implode(' AND ', $where_clauses);
}

// Get total rows for pagination
$total_query = "SELECT COUNT(*) as total FROM ups $where_sql";
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

// Get paginated data with specific columns
$data_query = "SELECT ups_id, nama_ups, lokasi, merk, tipe_ups, ip_address, ukuran_kapasitas, jumlah_baterai, perusahaan_maintenance FROM ups $where_sql ORDER BY nama_ups ASC LIMIT ? OFFSET ?";
$page_types = $types . 'ii';
$page_params = [...$params, $limit, $offset];
$stmt_data = mysqli_prepare($conn, $data_query);
mysqli_stmt_bind_param($stmt_data, $page_types, ...$page_params);
mysqli_stmt_execute($stmt_data);
$dataUPS = mysqli_stmt_get_result($stmt_data);

// Data for filter dropdowns
$tipes = mysqli_query($conn, "SELECT DISTINCT tipe_ups FROM ups ORDER BY tipe_ups");
$capacities = mysqli_query($conn, "SELECT DISTINCT ukuran_kapasitas FROM ups ORDER BY ukuran_kapasitas");
$companies = mysqli_query($conn, "SELECT DISTINCT perusahaan_maintenance FROM ups ORDER BY perusahaan_maintenance");

// --- AJAX Check ---
if (isset($_GET['ajax_ups_list'])) {
    // REVISED: Wrap the partial content in the div that JS expects
    echo '<div id="ups-list-content">';
    include 'ups_list_partial.php';
    echo '</div>';
    exit;
}

$page_title = "UPS Maintenance - Inventory";
include '../../templates/header.php';
?>

<main>
    <div class="page-header">
        <h1>Manajemen Perangkat UPS</h1>
        <div class="header-actions">
            <button id="addUpsBtn" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah UPS
            </button>
        </div>
    </div>

    <div id="ups-list-container" class="ajax-loading">
        <div class="loading-overlay">
            <div class="spinner"></div>
        </div>
        <div id="ups-list-content">
            <?php include 'ups_list_partial.php'; ?>
        </div>
    </div>
</main>

<!-- Add UPS Modal -->
<div id="addUpsModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Tambah UPS</h2>
            <button type="button" class="close-modal-btn">&times;</button>
        </div>
        <form method="POST" action="list" class="modal-form" enctype="multipart/form-data">
            <div class="modal-body">
                <input type="hidden" name="save_ups" value="1">
                <div class="form-grid">
                    <div class="form-group"><label for="add_nama_ups">Nama UPS</label><input type="text" id="add_nama_ups" name="nama_ups" required></div>
                    <div class="form-group"><label for="add_lokasi">Lokasi</label><input type="text" id="add_lokasi" name="lokasi" required></div>
                </div>
                <div class="form-grid">
                    <div class="form-group"><label for="add_merk">Merk</label><input type="text" id="add_merk" name="merk"></div>
                    <div class="form-group"><label for="add_tipe_ups">Tipe UPS</label><input type="text" id="add_tipe_ups" name="tipe_ups"></div>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="add_ukuran_kapasitas">Ukuran/Kapasitas</label>
                        <div class="input-with-suffix">
                            <input type="number" id="add_ukuran_kapasitas" name="ukuran_kapasitas" placeholder="Contoh; 10" required>
                            <span>KVA</span>
                        </div>
                    </div>
                    <div class="form-group"><label for="add_jumlah_baterai">Jumlah Baterai</label><input type="number" id="add_jumlah_baterai" name="jumlah_baterai"></div>
                </div>
                <div class="form-grid">
                    <div class="form-group"><label for="add_ip_address">IP Address</label><input type="text" id="add_ip_address" name="ip_address"></div>
                    <div class="form-group"><label for="add_perusahaan_maintenance">Perusahaan Maintenance</label><input type="text" id="add_perusahaan_maintenance" name="perusahaan_maintenance"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary close-modal-footer-btn">Batal</button>
                <button type="submit" class="btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit UPS Modal -->
<div id="editUpsModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Edit Detail UPS</h2>
            <button type="button" class="close-modal-btn">&times;</button>
        </div>
        <form method="POST" action="list" class="modal-form" enctype="multipart/form-data">
            <div class="modal-body">
                <input type="hidden" name="update_ups" value="1">
                <input type="hidden" id="edit_ups_id" name="ups_id">
                <div class="form-grid">
                    <div class="form-group"><label for="edit_nama_ups">Nama UPS</label><input type="text" id="edit_nama_ups" name="nama_ups" required></div>
                    <div class="form-group"><label for="edit_lokasi">Lokasi</label><input type="text" id="edit_lokasi" name="lokasi" required></div>
                </div>
                <div class="form-grid">
                    <div class="form-group"><label for="edit_merk">Merk</label><input type="text" id="edit_merk" name="merk" required></div>
                    <div class="form-group"><label for="edit_tipe_ups">Tipe UPS</label><input type="text" id="edit_tipe_ups" name="tipe_ups" required></div>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="edit_ukuran_kapasitas">Ukuran/Kapasitas</label>
                        <div class="input-with-suffix">
                            <input type="number" id="edit_ukuran_kapasitas" name="ukuran_kapasitas" placeholder="Contoh; 10" required><span>KVA</span>

                        </div>
                    </div>
                    <div class="form-group"><label for="edit_jumlah_baterai">Jumlah Baterai</label><input type="number" id="edit_jumlah_baterai" name="jumlah_baterai" required></div>
                </div>
                <div class="form-grid">
                    <div class="form-group"><label for="edit_ip_address">IP Address</label><input type="text" id="edit_ip_address" name="ip_address"></div>
                    <div class="form-group"><label for="edit_perusahaan_maintenance">Perusahaan Maintenance</label><input type="text" id="edit_perusahaan_maintenance" name="perusahaan_maintenance" required></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary close-modal-footer-btn">Batal</button>
                <button type="submit" class="btn-primary">Simpan Details</button>
            </div>
        </form>
    </div>
</div>

<!-- UPS Details Modal with History Tab -->
<div id="upsDetailsModal" class="modal">
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <h2 id="ups-modal-title">Detail UPS</h2>
            <button type="button" class="close-modal-btn">&times;</button>
        </div>

        <div class="modal-tabs">
            <button class="modal-tab active" data-tab="details">Details</button>
            <button class="modal-tab" data-tab="history">History</button>
            <button class="modal-tab" data-tab="report" id="report-detail-tab-button" style="display: none;">Report Details</button>
        </div>

        <div class="modal-body">
            <!-- UPS Details Content -->
            <div id="ups-details-tab" class="modal-tab-content active">
                <div class="details-container">
                    <div class="details-section">
                        <h3 class="section-title">Informasi Unit</h3>
                        <div class="details-grid">
                            <div><strong>Nama UPS:</strong> <span id="modal-ups-nama_ups"></span></div>
                            <div><strong>Lokasi:</strong> <span id="modal-ups-lokasi"></span></div>
                            <div><strong>Merk:</strong> <span id="modal-ups-merk"></span></div>
                            <div><strong>Tipe:</strong> <span id="modal-ups-tipe_ups"></span></div>
                        </div>
                    </div>
                    <div class="details-section">
                        <h3 class="section-title">Spesifikasi</h3>
                        <div class="details-grid">
                            <div><strong>Kapasitas:</strong> <span id="modal-ups-ukuran_kapasitas"></span></div>
                            <div><strong>Jumlah Baterai:</strong> <span id="modal-ups-jumlah_baterai"></span></div>
                            <div><strong>IP Address:</strong> <span id="modal-ups-ip_address"></span></div>
                        </div>
                    </div>
                    <div class="details-section">
                        <h3 class="section-title">Detail Pemeliharaan</h3>
                        <div class="details-grid">
                            <div><strong>Company Service:</strong> <span id="modal-ups-perusahaan_maintenance"></span></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- UPS History Content -->
            <div id="ups-history-tab" class="modal-tab-content">
                <div id="ups-history-content" class="table-container" style="box-shadow: none; border: none; padding: 0;">
                    <!-- History will be loaded here by JavaScript -->
                </div>
            </div>

            <!-- Detailed report view -->
            <div id="ups-report-detail-tab" class="modal-tab-content">
                <div class="details-container">
                    <div class="details-section">
                        <h2 class="section-title">UPS Information</h2>
                        <div class="details-grid">
                            <div><strong>Nama UPS:</strong> <span class="report-nama_ups"></span></div>
                            <div><strong>Lokasi:</strong> <span class="report-lokasi"></span></div>
                            <div><strong>Merk:</strong> <span class="report-merk"></span></div>
                            <div><strong>Tipe:</strong> <span class="report-tipe_ups"></span></div>
                            <div><strong>Kapasitas:</strong> <span class="report-ukuran_kapasitas"></span></div>
                            <div><strong>Jumlah Baterai:</strong> <span class="report-jumlah_baterai"></span></div>
                            <div><strong>IP Address:</strong> <span class="report-ip_address"></span></div>
                            <div><strong>Company Service:</strong> <span class="report-perusahaan_maintenance"></span></div>
                        </div>
                    </div>
                    <div class="details-section">
                        <h2 class="section-title">Maintenance Details</h2>
                        <div class="details-grid">
                            <div><strong>Status:</strong> <span class="report-status"></span></div>
                            <div><strong>Jenis Maintenance:</strong> <span class="report-jenis"></span></div>
                            <div><strong>Tanggal Jadwal:</strong> <span class="report-tanggal_jadwal"></span></div>
                            <div><strong>Tanggal Pelaksanaan:</strong> <span class="report-tanggal_pelaksanaan"></span></div>
                            <div><strong>Teknisi:</strong> <span class="report-teknisi"></span></div>
                        </div>
                    </div>
                    <div class="details-section">
                        <h2 class="section-title">Findings & Actions</h2>
                        <div class="details-prose">
                            <h3>Hasil Pengecekan (Findings)</h3>
                            <p class="report-hasil_pengecekan"></p>
                        </div>
                        <div class="details-prose">
                            <h3>Pengubahan (Actions / Components Replaced)</h3>
                            <p class="report-pengubahan"></p>
                        </div>
                    </div>
                    <div class="report-attachment-section details-section" style="display: none;">
                        <div class="section-header-action">
                            <h2 class="section-title">Attachment</h2>
                            <button type="button" class="btn btn-view-attachment view-attachment-btn"><i class="fas fa-eye"></i> View Attachment</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary close-modal-footer-btn">Close</button>
        </div>
    </div>
</div>


<div id="deleteConfirmModal" class="modal">
    <div class="modal-content" style="max-width: 450px;">
        <div class="modal-body" style="padding: 30px;">
            <div style="display: flex; gap: 20px; align-items: flex-start;">
                <div class="modal-icon-container">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div>
                    <h2 style="margin-top:0; margin-bottom: 10px; font-size: 1.25rem;">Hapus Konfirmasi</h2>
                    <p id="deleteModalDetail" style="margin-top: 0; color: #666;">Are you sure you want to delete this item? This action cannot be undone.</p>
                </div>
            </div>
        </div>
        <div class="modal-footer-confirm">
            <button type="button" class="btn-secondary close-modal-footer-btn" id="cancelDeleteBtn">Batal</button>
            <a href="#" id="confirmDeleteBtn" class="btn-danger" style="text-decoration:none;">Hapus</a>
        </div>
    </div>
</div>

<?php
if ($dataUPS) mysqli_stmt_close($stmt_data);
include '../../templates/footer.php';
?>