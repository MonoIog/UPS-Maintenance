<?php
require_once '../../config/init.php';
checkLogin();

// 1. Validate Input
if (!isset($_GET['ups_id']) || !is_numeric($_GET['ups_id'])) {
    header('Content-Type: application/json');
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Invalid or missing UPS ID.']);
    exit;
}
$ups_id = (int)$_GET['ups_id'];

// 2. Prepare and execute the query
$query = "
    SELECT 
        m.maintenance_id, m.tanggal_jadwal, m.tanggal_pelaksanaan, m.jenis, m.status,
        m.teknisi, m.hasil_pengecekan, m.pengubahan, m.catatan, m.attachment_path,
        u.nama_ups, u.lokasi, u.merk, u.tipe_ups, u.ip_address,
        u.ukuran_kapasitas, u.jumlah_baterai, u.perusahaan_maintenance
    FROM maintenance_ups m
    JOIN ups u ON m.ups_id = u.ups_id
    WHERE m.ups_id = ? 
      AND m.status IN ('Selesai', 'Selesai (Terlambat)', 'Ditunda')
    ORDER BY m.tanggal_pelaksanaan DESC
";

$stmt = mysqli_prepare($conn, $query);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $ups_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $history = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $history[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    
    // 3. Return data as JSON
    header('Content-Type: application/json');
    echo json_encode($history);

} else {
    header('Content-Type: application/json');
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Database query failed.']);
}
exit;
?>

