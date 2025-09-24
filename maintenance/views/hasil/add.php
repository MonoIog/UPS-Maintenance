<?php
require_once '../../config/init.php';
checkLogin();

// --- IMAGE RESIZING FUNCTION (for attachments) ---
function resize_image($file, $max_pixels = 25000000)
{
    if (!function_exists('imagecreatefromjpeg')) return false;
    $source_info = getimagesize($file);
    if (!$source_info) return false;
    list($width, $height, $type) = $source_info;
    $mime = $source_info['mime'];
    if ($width * $height <= $max_pixels) return true;
    $ratio = sqrt($max_pixels / ($width * $height));
    $new_width = (int)($width * $ratio);
    $new_height = (int)($height * $ratio);
    $source_image = null;
    switch ($mime) {
        case 'image/jpeg':
            $source_image = @imagecreatefromjpeg($file);
            break;
        case 'image/png':
            $source_image = @imagecreatefrompng($file);
            break;
        case 'image/gif':
            $source_image = @imagecreatefromgif($file);
            break;
        case 'image/webp':
            $source_image = @imagecreatefromwebp($file);
            break;
        case 'image/bmp':
            $source_image = @imagecreatefrombmp($file);
            break;
        default:
            return false;
    }
    if (!$source_image) return false;
    $resized_image = imagecreatetruecolor($new_width, $new_height);
    if (in_array($mime, ['image/png', 'image/gif'])) {
        imagealphablending($resized_image, false);
        imagesavealpha($resized_image, true);
        imagefilledrectangle($resized_image, 0, 0, $new_width, $new_height, imagecolorallocatealpha($resized_image, 255, 255, 255, 127));
    }
    imagecopyresampled($resized_image, $source_image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
    $success = false;
    switch ($mime) {
        case 'image/jpeg':
            $success = imagejpeg($resized_image, $file, 90);
            break;
        case 'image/png':
            $success = imagepng($resized_image, $file, 9);
            break;
        case 'image/gif':
            $success = imagegif($resized_image, $file);
            break;
        case 'image/webp':
            $success = imagewebp($resized_image, $file);
            break;
        case 'image/bmp':
            $success = imagebmp($resized_image, $file);
            break;
    }
    imagedestroy($source_image);
    imagedestroy($resized_image);
    return $success;
}

// --- HANDLE BOTH SCHEDULING AND REPORTING FROM THIS PAGE ---

// 1. HANDLE "SAVE SCHEDULE" FORM SUBMISSION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_schedule'])) {
    $ups_id = $_POST['ups_id'];
    $tanggal_jadwal = $_POST['tanggal_jadwal'];
    $maintenance_type = $_POST['maintenance_type'];
    $teknisi = $_POST['teknisi'];
    $status = 'Terjadwal';
    $created_by = 1;
    $perusahaan_maintenance = $_POST['perusahaan_maintenance'];


    $query = "INSERT INTO maintenance_ups (ups_id, tanggal_jadwal, jenis, status, teknisi, created_by) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "issssi", $ups_id, $tanggal_jadwal, $maintenance_type, $status, $teknisi, $created_by);
        if (mysqli_stmt_execute($stmt)) {
            set_notification("Jadwal Maintenance Berhasil Ditambahkan!", "success");
        } else {
            set_notification("Error: " . mysqli_stmt_error($stmt), "error");
        }
        mysqli_stmt_close($stmt);
    } else {
        set_notification("Error: " . mysqli_error($conn), "error");
    }
    header("Location: add");
    exit;
}

// 2. HANDLE "SAVE REPORT" MODAL SUBMISSION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_report'])) {
    $maintenance_id = $_POST['maintenance_id'];
    $tanggal_jadwal = $_POST['tanggal_jadwal'];
    $tanggal_pelaksanaan = $_POST['tanggal_pelaksanaan'];
    $hasil_pengecekan = $_POST['hasil_pengecekan'];
    $pengubahan = $_POST['pengubahan'];
    $status_from_form = $_POST['status'];
    $attachment_path = null;
    $old_attachment_path = $_POST['old_attachment_path'];
    $final_status = $status_from_form;

    if (strtolower($status_from_form) === 'selesai') {
        try {
            $pelaksanaan_dt = new DateTime($tanggal_pelaksanaan);
            $jadwal_dt = new DateTime($tanggal_jadwal);

            if ($pelaksanaan_dt > $jadwal_dt) {
                $final_status = 'Selesai (Terlambat)';
            } else {
                $final_status = 'Selesai';
            }
        } catch (Exception $e) {
            $final_status = 'Selesai';
        }
    }

    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = realpath(__DIR__ . '/../../uploads/report_attachments/') . '/';
        if (!is_dir($upload_dir)) {
            @mkdir($upload_dir, 0775, true);
        }
        if (is_writable($upload_dir)) {
            $report_id_padded = "RL-" . str_pad($maintenance_id, 4, '0', STR_PAD_LEFT);
            $file_extension = strtolower(pathinfo($_FILES["attachment"]["name"], PATHINFO_EXTENSION));
            $new_file_name = "" . $report_id_padded . "." . $file_extension;
            $target_file = $upload_dir . $new_file_name;
            $allowed_types = ['pdf', 'png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp'];
            if (in_array($file_extension, $allowed_types) && $_FILES["attachment"]["size"] <= 50000000) {
                if (move_uploaded_file($_FILES["attachment"]["tmp_name"], $target_file)) {
                    if ($file_extension !== 'pdf') {
                        resize_image($target_file);
                    }
                    $attachment_path = 'uploads/report_attachments/' . $new_file_name;
                    if ($old_attachment_path && $old_attachment_path !== $attachment_path && file_exists(realpath(__DIR__ . '/../../') . '/' . $old_attachment_path)) {
                        @unlink(realpath(__DIR__ . '/../../') . '/' . $old_attachment_path);
                    }
                }
            }
        }
    } else {
        $attachment_path = $old_attachment_path;
    }

    $query_update = "UPDATE maintenance_ups SET tanggal_pelaksanaan = ?, hasil_pengecekan = ?, pengubahan = ?, status = ?, attachment_path = ? WHERE maintenance_id = ?";
    $stmt_update = mysqli_prepare($conn, $query_update);
    mysqli_stmt_bind_param($stmt_update, "sssssi", $tanggal_pelaksanaan, $hasil_pengecekan, $pengubahan, $final_status, $attachment_path, $maintenance_id);
    if (mysqli_stmt_execute($stmt_update)) {
        set_notification("Laporan Berhasil Disimpan!", "success");
    } else {
        set_notification("Error: " . mysqli_stmt_error($stmt_update), "error");
    }
    mysqli_stmt_close($stmt_update);
    header("Location: add");
    exit;
}

// --- DATA FETCHING FOR THE PAGE ---
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$total_rows_query = "SELECT COUNT(*) as total FROM maintenance_ups WHERE status = 'Terjadwal'";
$total_result = mysqli_query($conn, $total_rows_query);
$total_rows = mysqli_fetch_assoc($total_result)['total'] ?? 0;
$total_pages = ceil($total_rows / $limit);
$list_query = "SELECT m.*, u.nama_ups, u.lokasi 
               FROM maintenance_ups m JOIN ups u ON m.ups_id = u.ups_id
               WHERE m.status = 'Terjadwal' ORDER BY m.tanggal_jadwal ASC LIMIT ? OFFSET ?";
$stmt_list = mysqli_prepare($conn, $list_query);
mysqli_stmt_bind_param($stmt_list, "ii", $limit, $offset);
mysqli_stmt_execute($stmt_list);
$scheduled_tasks = mysqli_stmt_get_result($stmt_list);
$ups_list_result = mysqli_query($conn, "SELECT ups_id, nama_ups, lokasi FROM ups ORDER BY nama_ups ASC");

$page_title = "UPS Maintenance - Check in & Out";
include '../../templates/header.php';
?>

<main>
    <div class="page-header">
        <div>
            <h1>Maintenance Tasks</h1>
            <p>Schedule new tasks and check out upcoming maintenance.</p>
        </div>
        <div class="header-actions">
            <a href="list" class="btn btn-secondary btn-sm">
                <i class="fas fa-history"></i> View All Reports
            </a>
        </div>
    </div>

    <div class="schedule-layout">
        <div class="schedule-form-container">
            <div class="table-header">
                <h2>Schedule a New Task</h2>
            </div>
            <form id="scheduleForm" method="POST" action="add" class="modal-form">
                <input type="hidden" name="save_schedule" value="1">
                <div class="form-group">
                    <label for="ups_id">Nama UPS</label>
                    <div class="custom-select-wrapper">
                        <div class="custom-select-trigger"><span class="placeholder">-- Pilih UPS --</span><i class="fas fa-chevron-down"></i></div>
                        <div class="custom-options"><input type="text" class="custom-select-search" placeholder="Cari UPS...">
                            <div class="options-list"></div>
                        </div>
                        <select id="ups_id" name="ups_id" required style="display: none;">
                            <option value="">-- Pilih UPS --</option>
                            <?php if ($ups_list_result && mysqli_num_rows($ups_list_result) > 0) mysqli_data_seek($ups_list_result, 0); ?>
                            <?php while ($ups = mysqli_fetch_assoc($ups_list_result)) : ?>
                                <option value="<?php echo $ups['ups_id']; ?>"><?php echo htmlspecialchars($ups['nama_ups'] . ' || ' . $ups['lokasi']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="tanggal_jadwal_display">Tanggal & Waktu Check-In</label>
                    <input type="text" id="tanggal_jadwal_display" value="<?php echo date('d/m/Y H:i'); ?>" readonly style="background-color: #e9ecef; cursor: not-allowed; padding: 12px; border: 1px solid #ccc; border-radius: 6px;">
                    <input type="hidden" name="tanggal_jadwal" value="<?php echo date('Y-m-d H:i:s'); ?>">
                </div>
                <div class="form-group">
                    <label for="teknisi">Teknisi</label>
                    <input type="text" id="teknisi" name="teknisi" placeholder="Masukkan Nama..." required>
                </div>
                <div class="form-group">
                    <label for="maintenance_type">Tipe Maintenance</label>
                    <div class="custom-select-wrapper">
                        <div class="custom-select-trigger"><span class="placeholder">-- Pilih Tipe --</span><i class="fas fa-chevron-down"></i></div>
                        <div class="custom-options">
                            <div class="options-list"></div>
                        </div>
                        <select id="maintenance_type" name="maintenance_type" required style="display: none;">
                            <option value="">-- Pilih Tipe --</option>
                            <option value="Preventive (Rutin)">Preventive (Rutin)</option>
                            <option value="Corrective (Perbaikan)">Corrective (Perbaikan)</option>
                        </select>
                    </div>
                </div>
                <div class="form-footer"><button type="submit" class="btn btn-primary">Save Schedule</button></div>
            </form>
        </div>
        <div class="schedule-list-container">
            <div class="table-header">
                <h2>Upcoming Tasks</h2>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama UPS</th>
                            <th>Lokasi</th>
                            <th>Jenis</th>
                            <th>Tanggal</th>
                            <th>Teknisi</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($scheduled_tasks && mysqli_num_rows($scheduled_tasks) > 0): ?>
                            <?php while ($task = mysqli_fetch_assoc($scheduled_tasks)):
                                $is_overdue = strtotime($task['tanggal_jadwal']) < (time() - (36 * 60 * 60));
                            ?>
                                <tr class="<?php echo $is_overdue ? 'task-overdue' : ''; ?>">
                                    <td>SCH-<?php echo str_pad($task['maintenance_id'], 4, '0', STR_PAD_LEFT); ?></td>
                                    <td><?php echo htmlspecialchars($task['nama_ups']); ?></td>
                                    <td><?php echo htmlspecialchars($task['lokasi']); ?></td>
                                    <td>
                                        <?php
                                        $jenis_class_task = '';
                                        if (strpos(strtolower($task['jenis']), 'preventive') !== false) {
                                            $jenis_class_task = 'status-preventive';
                                        } elseif (strpos(strtolower($task['jenis']), 'corrective') !== false) {
                                            $jenis_class_task = 'status-corrective';
                                        }
                                        ?>
                                        <span class="status-badge <?php echo $jenis_class_task; ?>"><?php echo htmlspecialchars($task['jenis']); ?></span>
                                    </td>
                                    <td>
                                        <div class="date-status-container">
                                            <span><?php echo htmlspecialchars(date('d M Y, H:i', strtotime($task['tanggal_jadwal']))); ?></span>
                                            <?php if ($is_overdue): ?><span class="status-badge status-overdue">Overdue</span><?php endif; ?>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($task['teknisi']); ?></td>
                                    <td class="table-actions">
                                        <button class="btn btn-checkout btn-icon open-report-modal-btn" title="Check Out / Complete Task"
                                            data-maintenance-id="<?php echo $task['maintenance_id']; ?>"
                                            data-nama-ups="<?php echo htmlspecialchars($task['nama_ups']); ?>"
                                            data-lokasi="<?php echo htmlspecialchars($task['lokasi']); ?>"
                                            data-jenis="<?php echo htmlspecialchars($task['jenis']); ?>"
                                            data-tanggal-jadwal="<?php echo htmlspecialchars($task['tanggal_jadwal']); ?>"
                                            data-teknisi="<?php echo htmlspecialchars($task['teknisi']); ?>"
                                            data-attachment-path="<?php echo htmlspecialchars($task['attachment_path']); ?>">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 20px;">No upcoming maintenance tasks.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="pagination-footer">
                <p>Showing <?php echo $scheduled_tasks ? mysqli_num_rows($scheduled_tasks) : 0; ?> of <?php echo $total_rows; ?> results</p>
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <a href="?page=<?php echo max(1, $page - 1); ?>" class="<?php echo ($page <= 1) ? 'disabled' : ''; ?>">Previous</a>
                        <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                            <a href="?page=<?php echo $i; ?>" class="<?php echo ($page == $i) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                        <?php endfor; ?>
                        <a href="?page=<?php echo min($total_pages, $page + 1); ?>" class="<?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">Next</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<!-- MODAL FOR COMPLETING REPORTS -->
<div id="addReportModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Complete Maintenance Report</h2>
            <button type="button" class="close-modal-btn">&times;</button>
        </div>
        <form id="reportForm" method="POST" action="add" enctype="multipart/form-data" class="modal-form">
            <div class="modal-body">
                <input type="hidden" name="save_report" value="1">
                <input type="hidden" name="maintenance_id" id="report_maintenance_id">
                <input type="hidden" name="tanggal_jadwal" id="report_tanggal_jadwal">
                <input type="hidden" name="old_attachment_path" id="report_old_attachment_path">

                <div class="report-info">
                    <div><strong>Nama UPS:</strong> <span id="report_nama_ups"></span></div>
                    <div><strong>Lokasi:</strong> <span id="report_lokasi"></span></div>
                    <div><strong>Jadwal:</strong> <span id="report_jadwal_display"></span></div>
                    <div><strong>Jenis:</strong> <span id="report_jenis"></span></div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="report_tanggal_pelaksanaan">Tanggal & Waktu Check-Out</label>
                        <input type="datetime-local" id="report_tanggal_pelaksanaan" name="tanggal_pelaksanaan" readonly required style="background-color: #e9ecef; cursor: not-allowed;">
                    </div>
                    <div class="form-group">
                        <label for="report_teknisi">Teknisi</label>
                        <input type="text" id="report_teknisi" name="teknisi" readonly required style="background-color: #e9ecef; cursor: not-allowed;">
                    </div>
                </div>

                <div class="form-group">
                    <label for="report_status">Status</label>
                    <div class="custom-select-wrapper">
                        <div class="custom-select-trigger"><span>Selesai</span><i class="fas fa-chevron-down"></i></div>
                        <div class="custom-options">
                            <div class="options-list"></div>
                        </div>
                        <select id="report_status" name="status" required style="display:none;">
                            <option value="Selesai" selected>Selesai</option>
                            <option value="Ditunda">Ditunda</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="hasil_pengecekan">Hasil Pengecekan</label>
                    <textarea id="hasil_pengecekan" name="hasil_pengecekan" placeholder="Jelaskan temuan..."></textarea>
                </div>

                <div class="form-group">
                    <label for="pengubahan">Tindakan yang diambil</label>
                    <textarea id="pengubahan" name="pengubahan" placeholder="Jelaskan tindakan yang diambil..."></textarea>
                </div>

                <div class="form-group">
                    <label>Attachment (Hardcopy)</label>
                    <label for="attachment" class="attachment-box" data-default-text="Upload file or drag & drop">
                        <i class="fas fa-file-upload" style="color: #4299e1;"></i>
                        <p class="attachment-text">Upload file or drag & drop</p>
                        <span>PDF, Images up to 50MB</span>
                    </label>
                    <input type="file" id="attachment" name="attachment" accept=".pdf,.png,.jpg,.jpeg,.gif,.webp,.bmp" style="display: none;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary close-modal-footer-btn">Cancel</button>
                <button type="submit" class="btn-primary">Save Report</button>
            </div>
        </form>
    </div>
</div>

<?php include '../../templates/footer.php'; ?>
