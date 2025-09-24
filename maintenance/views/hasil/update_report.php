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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect data from the submitted form, including hidden fields
    $id = $_POST['maintenance_id'];

    // Use submitted values for editable fields
    $tanggal_pelaksanaan = !empty($_POST['tanggal_pelaksanaan']) ? $_POST['tanggal_pelaksanaan'] : null;
    $status = $_POST['status'];
    $hasil_pengecekan = $_POST['hasil_pengecekan'];
    $pengubahan = $_POST['pengubahan'];
    $catatan = $_POST['catatan'];
    $old_attachment_path = $_POST['old_attachment_path'];
    $attachment_path = $old_attachment_path;

    // REVISED: The form doesn't submit these as they are read-only displays.
    // They should already be in the database and don't need to be updated.
    // We only need the schedule date to calculate late status if applicable.
    $tanggal_jadwal = $_POST['hidden_tanggal_jadwal'];

    // Auto-determine status based on execution date vs schedule date
    $final_status = $status;
    if (strtolower($status) === 'selesai' && !empty($tanggal_pelaksanaan) && !empty($tanggal_jadwal)) {
        try {
            $pelaksanaan_dt = new DateTime($tanggal_pelaksanaan);
            $jadwal_dt = new DateTime($tanggal_jadwal);

            if ($pelaksanaan_dt > $jadwal_dt) {
                $final_status = 'Selesai (Terlambat)';
            }
        } catch (Exception $e) {
            // If date parsing fails, just keep the original 'Selesai' status
            $final_status = 'Selesai';
        }
    }


    // Check if a new file was uploaded
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = realpath(__DIR__ . '/../../uploads/report_attachments/') . '/';
        if (!is_dir($upload_dir)) {
            @mkdir($upload_dir, 0775, true);
        }
        if (is_writable($upload_dir)) {
            $report_id_padded = "DLAR-" . str_pad($id, 4, '0', STR_PAD_LEFT);
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
                } else {
                    set_notification("Gagal memindahkan file yang diunggah.", "error");
                    header("Location: list");
                    exit;
                }
            } else {
                set_notification("Jenis file tidak valid atau ukuran file terlalu besar.", "error");
                header("Location: list");
                exit;
            }
        } else {
            set_notification("Direktori unggahan tidak dapat ditulis.", "error");
            header("Location: list");
            exit;
        }
    }


    // The SQL query to update the report.
    // NOTE: We do not update `tanggal_jadwal`, `jenis`, or `teknisi`
    // as those are static fields from the original scheduled task.
    $query = "UPDATE maintenance_ups SET tanggal_pelaksanaan=?, status=?, hasil_pengecekan=?, pengubahan=?, catatan=?, attachment_path=? WHERE maintenance_id=?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ssssssi", $tanggal_pelaksanaan, $final_status, $hasil_pengecekan, $pengubahan, $catatan, $attachment_path, $id);

    if (mysqli_stmt_execute($stmt)) {
        set_notification("Laporan Berhasil Diperbarui!", "success");
        header("Location: list");
        exit;
    } else {
        set_notification("Error: " . mysqli_stmt_error($stmt), "error");
        header("Location: list");
        exit;
    }
} else {
    // Redirect if accessed directly without POST method
    header("Location: ../dashboard");
    exit;
}
