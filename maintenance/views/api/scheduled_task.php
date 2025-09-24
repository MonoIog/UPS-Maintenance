<?php
require_once '../../config/init.php';
// In a real-world API, you would implement token-based authentication.
// For now, we continue to use the session-based check.
checkLogin();

// --- VALIDATION ---
// In a real app, you might get the technician from the auth token.
// For this example, we'll allow it as a GET parameter.
$teknisi_filter = $_GET['teknisi'] ?? null;

if (empty($teknisi_filter)) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Technician name is required.']);
    exit;
}

// --- DATABASE QUERY ---
$query = "
    SELECT 
        m.maintenance_id,
        m.tanggal_jadwal, 
        m.jenis, 
        m.status,
        u.nama_ups, 
        u.lokasi
    FROM maintenance_ups m
    JOIN ups u ON m.ups_id = u.ups_id
    WHERE 
        m.teknisi = ? AND
        m.status = 'Terjadwal'
    ORDER BY m.tanggal_jadwal ASC
";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $teknisi_filter);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// --- PROCESS & RETURN DATA ---
$tasks = [];
while ($row = mysqli_fetch_assoc($result)) {
    $tasks[] = [
        'taskId' => 'SCH-' . str_pad($row['maintenance_id'], 4, '0', STR_PAD_LEFT),
        'scheduledAt' => date('c', strtotime($row['tanggal_jadwal'])), // ISO 8601 format
        'type' => $row['jenis'],
        'upsName' => $row['nama_ups'],
        'location' => $row['lokasi'],
    ];
}

mysqli_stmt_close($stmt);

header('Content-Type: application/json');
echo json_encode($tasks);
exit;
?>
