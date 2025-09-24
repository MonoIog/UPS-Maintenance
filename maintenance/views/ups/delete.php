<?php
// Use the centralized init file for database connection and functions
require_once '../../config/init.php';
checkLogin();

// 1. VALIDATE THE INCOMING REQUEST
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    set_notification('Error: Invalid or missing UPS ID provided.', 'error');
    header("Location: list");
    exit;
}

$id = (int)$_GET['id'];

// Start a database transaction to ensure all or no deletions happen
mysqli_begin_transaction($conn);

try {
    // 2. DELETE DEPENDENT RECORDS FROM 'maintenance_ups'
    $query_maintenance = "DELETE FROM maintenance_ups WHERE ups_id = ?";
    $stmt_maintenance = mysqli_prepare($conn, $query_maintenance);
    mysqli_stmt_bind_param($stmt_maintenance, "i", $id);
    mysqli_stmt_execute($stmt_maintenance);
    mysqli_stmt_close($stmt_maintenance);

    $query_pj = "DELETE FROM penanggung_jawab WHERE ups_id = ?";
    $stmt_pj = mysqli_prepare($conn, $query_pj);
    mysqli_stmt_bind_param($stmt_pj, "i", $id);
    mysqli_stmt_execute($stmt_pj);
    mysqli_stmt_close($stmt_pj);

    // 4. DELETE THE MAIN UPS RECORD
    $query_ups = "DELETE FROM ups WHERE ups_id = ?";
    $stmt_ups = mysqli_prepare($conn, $query_ups);
    mysqli_stmt_bind_param($stmt_ups, "i", $id);

    if (mysqli_stmt_execute($stmt_ups)) {
        // If all deletions were successful, commit the transaction
        mysqli_commit($conn);
        set_notification('Perangkat UPS Berhasil Dihapus.', 'success');
    } else {
        // If the main deletion fails, roll back
        mysqli_rollback($conn);
        set_notification('Gagal Menghapus Perangkat UPS. ' . mysqli_stmt_error($stmt_ups), 'error');
    }
    mysqli_stmt_close($stmt_ups);
} catch (mysqli_sql_exception $exception) {
    // If any query fails, roll back the transaction and show the error
    mysqli_rollback($conn);
    set_notification('Database error: ' . $exception->getMessage(), 'error');
}

// 5. REDIRECT BACK TO THE LIST
header("Location: list");
exit;
