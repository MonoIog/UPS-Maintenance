<?php
require_once '../../config/init.php';
checkLogin();

// --- Get filters from the query string ---
$location_filter = $_GET['location'] ?? 'all';
$teknisi_filter = $_GET['teknisi'] ?? 'all';
$status_filter = $_GET['status'] ?? 'all';

// --- Build the WHERE clause dynamically ---
$where_clauses = [];
$params = [];
$types = '';

// Handle status filter first
if ($status_filter !== 'all') {
    $where_clauses[] = "m.status = ?";
    $params[] = $status_filter;
    $types .= 's';
} else {
    // Default to all relevant statuses if no specific one is chosen
    $where_clauses[] = "m.status IN ('Selesai', 'Selesai (Terlambat)', 'Ditunda')";
}

// Add other filters
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

$where_sql = "WHERE " . implode(' AND ', $where_clauses);

// --- Build the main query to fetch all details ---
$query = "
    SELECT 
        m.maintenance_id, m.tanggal_jadwal, m.tanggal_pelaksanaan, m.jenis, m.status,
        m.teknisi, m.hasil_pengecekan, m.pengubahan, m.catatan,
        u.nama_ups, u.lokasi, u.merk, u.tipe_ups, u.ip_address,
        u.ukuran_kapasitas, u.jumlah_baterai, u.perusahaan_maintenance
    FROM maintenance_ups m
    JOIN ups u ON m.ups_id = u.ups_id
    $where_sql
    ORDER BY m.tanggal_pelaksanaan DESC
";

$stmt = mysqli_prepare($conn, $query);

if ($stmt && !empty($types)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    // EDITED: Split 'jenis' into main type and sub-type
    $jenis_text = $row['jenis'];
    $main_type = $jenis_text;
    $sub_type = '';

    if (preg_match('/^(.*?)\s*(\(.*\))$/', $jenis_text, $matches)) {
        $main_type = trim($matches[1]);
        $sub_type = $matches[2];
    }
    
    $row['jenis'] = $main_type;
    $row['sub_jenis'] = $sub_type;
    
    $data[] = $row;
}

mysqli_stmt_close($stmt);

// --- Return the data as JSON ---
header('Content-Type: application/json');
echo json_encode($data);
exit;

