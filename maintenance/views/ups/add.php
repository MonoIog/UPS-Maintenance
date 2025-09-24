<?php
include '../../functions/auth.php';
include '../../config/db.php';
checkLogin();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve data from the form, matching the database schema
    $nama_ups = $_POST['nama_ups'];
    $lokasi = $_POST['lokasi'];
    $merk = $_POST['merk'];
    $tipe_ups = $_POST['tipe_ups'];
    $ip_address = $_POST['ip_address'];
    $ukuran_kapasitas = $_POST['ukuran_kapasitas'];
    $jumlah_baterai = $_POST['jumlah_baterai'];
    $perusahaan_maintenance = $_POST['perusahaan_maintenance'];

    // Prepare and execute the SQL statement to insert data
    $query = "INSERT INTO ups (nama_ups, lokasi, merk, tipe_ups, ip_address, ukuran_kapasitas, jumlah_baterai, perusahaan_maintenance) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        mysqli_stmt_bind_param(
            $stmt,
            "ssssssis",
            $nama_ups,
            $lokasi,
            $merk,
            $tipe_ups,
            $ip_address,
            $ukuran_kapasitas,
            $jumlah_baterai,
            $perusahaan_maintenance
        );

        if (mysqli_stmt_execute($stmt)) {
            // Redirect to the UPS list page on success
            header("Location: list");
            exit;
        } else {
            $error_message = "Error: Failed to add UPS. " . mysqli_stmt_error($stmt);
        }
        mysqli_stmt_close($stmt);
    } else {
        $error_message = "Error: Could not prepare the statement. " . mysqli_error($conn);
    }
}

$page_title = "Add New UPS";
include '../../templates/header.php';
?>

<main>
    <div class="page-header">
        <div>
            <h1>Add New UPS</h1>
            <p>Fill in the details below to add a new UPS unit to the inventory.</p>
        </div>
    </div>

    <?php if (isset($error_message)): ?>
        <div class="alert-danger" style="margin-bottom: 20px;"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <div class="form-container" style="background-color: #fff; padding: 30px; border-radius: 8px; border: 1px solid #e0e0e0;">
        <form method="POST" action="add" class="modal-form">
            <div class="form-grid">
                <div class="form-group">
                    <label for="nama_ups">Nama UPS</label>
                    <input type="text" id="nama_ups" name="nama_ups" placeholder="e.g., UPS Data Center Lt. 1" required>
                </div>
                <div class="form-group">
                    <label for="lokasi">Lokasi</label>
                    <input type="text" id="lokasi" name="lokasi" placeholder="e.g., Ruang Server A" required>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="merk">Merk</label>
                    <input type="text" id="merk" name="merk" placeholder="e.g., APC, Eaton" required>
                </div>
                <div class="form-group">
                    <label for="tipe_ups">Tipe UPS</label>
                    <input type="text" id="tipe_ups" name="tipe_ups" placeholder="e.g., Smart-UPS 3000" required>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="ukuran_kapasitas">Ukuran/Kapasitas</label>
                    <input type="text" id="ukuran_kapasitas" name="ukuran_kapasitas" placeholder="e.g., 3000VA / 2700W" required>
                </div>
                <div class="form-group">
                    <label for="jumlah_baterai">Jumlah Baterai</label>
                    <input type="number" id="jumlah_baterai" name="jumlah_baterai" placeholder="e.g., 8" required>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="ip_address">IP Address (optional)</label>
                    <input type="text" id="ip_address" name="ip_address" placeholder="e.g., 192.168.1.100">
                </div>
                <div class="form-group">
                    <label for="perusahaan_maintenance">Perusahaan Maintenance</label>
                    <input type="text" id="perusahaan_maintenance" name="perusahaan_maintenance" placeholder="e.g., PT. Solusi Daya" required>
                </div>
            </div>

            <div class="modal-footer" style="padding-right: 0; padding-bottom: 0;">
                <a href="list" class="btn-secondary" style="text-decoration: none;">Cancel</a>
                <button type="submit" class="btn-add-ups">Save UPS</button>
            </div>
        </form>
    </div>
</main>

<?php include '../../templates/footer.php'; ?>
