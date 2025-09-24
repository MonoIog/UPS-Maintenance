<?php
require_once '../../config/init.php';
checkLogin();

// 1. Validate the incoming request
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    set_notification('Error: Invalid or missing report ID provided.', 'error');
    header("Location: list.php");
    exit;
}
$id = (int)$_GET['id'];

// 2. Fetch the attachment path before deleting the record from the database
$query_select = "SELECT attachment_path FROM maintenance_ups WHERE maintenance_id = ?";
$stmt_select = mysqli_prepare($conn, $query_select);
mysqli_stmt_bind_param($stmt_select, "i", $id);
mysqli_stmt_execute($stmt_select);
$result_select = mysqli_stmt_get_result($stmt_select);
$attachment_path = null;
if ($row = mysqli_fetch_assoc($result_select)) {
    $attachment_path = $row['attachment_path'];
}
mysqli_stmt_close($stmt_select);

// 3. Delete the database record
$query_delete = "DELETE FROM maintenance_ups WHERE maintenance_id = ?";
$stmt_delete = mysqli_prepare($conn, $query_delete);
mysqli_stmt_bind_param($stmt_delete, "i", $id);

if (mysqli_stmt_execute($stmt_delete)) {
    // 4. If the database deletion was successful, delete the physical file
    if ($attachment_path) {
        $full_path = realpath(__DIR__ . '/../../') . '/' . $attachment_path;
        if (file_exists($full_path)) {
            @unlink($full_path); // Use @ to suppress errors if the file is already gone
        }
    }
    set_notification('Laporan berhasil dihapus.', 'success');
} else {
    set_notification('Error: Could not delete the report from the database. ' . mysqli_stmt_error($stmt_delete), 'error');
}
mysqli_stmt_close($stmt_delete);

// 5. Redirect back to the list
header("Location: list");
exit;
