<?php
require_once '../../config/init.php';
checkLogin();

// --- VALIDATION ---
$year = filter_input(INPUT_GET, 'year', FILTER_VALIDATE_INT);
$month = filter_input(INPUT_GET, 'month', FILTER_VALIDATE_INT);

if (!$year || !$month || $month < 1 || $month > 12) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid year or month provided.']);
    exit;
}

// --- DATABASE QUERY ---
// REVISED: Fetched maintenance_id and attachment_path
$query = "
    SELECT 
        m.maintenance_id, m.tanggal_jadwal, m.jenis, m.status, m.teknisi, m.attachment_path,
        u.nama_ups, u.lokasi, u.merk, u.tipe_ups, u.ukuran_kapasitas, 
        u.jumlah_baterai, u.perusahaan_maintenance, u.ip_address
    FROM maintenance_ups m
    JOIN ups u ON m.ups_id = u.ups_id
    WHERE 
        YEAR(m.tanggal_jadwal) = ? AND 
        MONTH(m.tanggal_jadwal) = ?
";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $year, $month);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// --- PROCESS DATA ---
$events = [];
while ($row = mysqli_fetch_assoc($result)) {
    $event_class = 'event-terjadwal'; // Default
    if (strpos(strtolower($row['jenis']), 'corrective') !== false) {
        $event_class = 'status-corrective';
    } elseif (strpos(strtolower($row['jenis']), 'preventive') !== false) {
        $event_class = 'event-preventive';
    }

    $date = date('Y-m-d', strtotime($row['tanggal_jadwal']));

    $report_id = 'RL-' . str_pad($row['maintenance_id'], 4, '0', STR_PAD_LEFT);
    $event_title = $report_id . ' - ' . $row['lokasi'];

    $events[] = [
        'date' => $date,
        'title' => $event_title,
        'class' => $event_class,
        'details' => [
            'nama_ups' => $row['nama_ups'],
            'lokasi' => $row['lokasi'],
            'merk' => $row['merk'],
            'tipe_ups' => $row['tipe_ups'],
            'ukuran_kapasitas' => $row['ukuran_kapasitas'],
            'jumlah_baterai' => $row['jumlah_baterai'],
            'perusahaan_maintenance' => $row['perusahaan_maintenance'],
            'ip_address' => $row['ip_address'],
            'status' => $row['status'],
            'jenis' => $row['jenis'],
            'tanggal_jadwal' => date('d M Y, H:i', strtotime($row['tanggal_jadwal'])),
            'teknisi' => $row['teknisi'] ?? 'N/A',
            'attachment_path' => $row['attachment_path'] // REVISED: Added attachment_path
        ]
    ];
}

mysqli_stmt_close($stmt);

// --- OUTPUT JSON ---
header('Content-Type: application/json');
echo json_encode($events);
exit;
