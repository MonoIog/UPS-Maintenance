<?php
include '../../functions/auth.php';
include '../../config/db.php';
checkLogin();

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // Redirect or show an error
    header("Location: ../dashboard.php");
    exit;
}
$maintenance_id = (int)$_GET['id'];

// Fetch detailed report data
$query = "
    SELECT
        m.maintenance_id, m.tanggal_jadwal, m.tanggal_pelaksanaan, m.jenis, m.status,
        m.teknisi, m.hasil_pengecekan, m.pengubahan, m.catatan,
        u.nama_ups, u.lokasi, u.merk, u.tipe_ups, u.ip_address,
        u.ukuran_kapasitas, u.jumlah_baterai, u.perusahaan_maintenance
    FROM maintenance_ups m
    JOIN ups u ON m.ups_id = u.ups_id
    WHERE m.maintenance_id = ?
";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $maintenance_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$report = mysqli_fetch_assoc($result);

if (!$report) {
    // Handle report not found
    $page_title = "Error";
    include '../../templates/header.php';
    echo "<main><div class='page-header'><h1>Report Not Found</h1></div> <div class='table-container'><p>The requested maintenance report does not exist. <a href='../dashboard.php'>Return to Dashboard</a>.</p></div></main>";
    include '../../templates/footer.php';
    exit;
}

$page_title = "Maintenance Report Details";
include '../../templates/header.php';
?>

<main>
    <div class="page-header">
        <div>
            <h1>Maintenance Report Details</h1>
            <p>Report ID: RPT-<?php echo str_pad($report['maintenance_id'], 4, '0', STR_PAD_LEFT); ?></p>
        </div>
        <a href="../dashboard.php" class="btn-secondary" style="text-decoration: none;">Back to Dashboard</a>
    </div>

    <div class="details-container">
        <!-- UPS Information Section -->
        <div class="details-section">
            <h2 class="section-title">UPS Information</h2>
            <div class="details-grid">
                <div><strong>Nama UPS:</strong> <?php echo htmlspecialchars($report['nama_ups']); ?></div>
                <div><strong>Lokasi:</strong> <?php echo htmlspecialchars($report['lokasi']); ?></div>
                <div><strong>Merk:</strong> <?php echo htmlspecialchars($report['merk']); ?></div>
                <div><strong>Tipe:</strong> <?php echo htmlspecialchars($report['tipe_ups']); ?></div>
                <div><strong>Kapasitas:</strong> <?php echo htmlspecialchars($report['ukuran_kapasitas']); ?></div>
                <div><strong>Jumlah Baterai:</strong> <?php echo htmlspecialchars($report['jumlah_baterai']); ?></div>
                <div><strong>IP Address:</strong> <?php echo htmlspecialchars($report['ip_address'] ?? 'N/A'); ?></div>
                <div><strong>Company Service:</strong> <?php echo htmlspecialchars($report['perusahaan_maintenance']); ?></div>
            </div>
        </div>

        <!-- Maintenance Details Section -->
        <div class="details-section">
            <h2 class="section-title">Maintenance Details</h2>
            <div class="details-grid">
                <div><strong>Status:</strong> <span class="status-badge <?php echo (strtolower($report['status']) == 'selesai') ? 'status-completed' : 'status-pending'; ?>"><?php echo htmlspecialchars(ucfirst($report['status'])); ?></span></div>
                <div><strong>Jenis Maintenance:</strong> <?php echo htmlspecialchars(ucfirst($report['jenis'])); ?></div>
                <div><strong>Tanggal Jadwal:</strong> <?php echo htmlspecialchars(date('d M Y', strtotime($report['tanggal_jadwal']))); ?></div>
                <div><strong>Tanggal Pelaksanaan:</strong> <?php echo htmlspecialchars(date('d M Y', strtotime($report['tanggal_pelaksanaan']))); ?></div>
                <div><strong>Teknisi:</strong> <?php echo htmlspecialchars($report['teknisi']); ?></div>
            </div>
        </div>
        
        <!-- Findings and Actions Section -->
        <div class="details-section">
            <h2 class="section-title">Findings & Actions</h2>
            <div class="details-prose">
                <h3>Hasil Pengecekan (Findings)</h3>
                <p><?php echo nl2br(htmlspecialchars($report['hasil_pengecekan'] ?: 'No findings reported.')); ?></p>
            </div>
             <div class="details-prose">
                <h3>Pengubahan (Actions / Components Replaced)</h3>
                <p><?php echo nl2br(htmlspecialchars($report['pengubahan'] ?: 'No actions reported.')); ?></p>
            </div>
             <div class="details-prose">
                <h3>Catatan Awal (Initial Notes)</h3>
                <p><?php echo nl2br(htmlspecialchars($report['catatan'] ?: 'No initial notes.')); ?></p>
            </div>
        </div>
    </div>
</main>

<?php
mysqli_stmt_close($stmt);
include '../../templates/footer.php';
?>
