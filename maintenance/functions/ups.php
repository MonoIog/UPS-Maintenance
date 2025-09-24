<?php
include_once __DIR__ . '/../config/db.php';

// Ambil semua data UPS
function getAllUPS() {
    global $conn;
    $query = "SELECT * FROM ups ORDER BY ups_id DESC";
    $result = mysqli_query($conn, $query);
    return $result;
}

// Ambil UPS by ID
function getUPSById($id) {
    global $conn;
    $query = "SELECT * FROM ups WHERE ups_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

// Tambah UPS
function addUPS($lokasi, $kapasitas, $merek, $model, $nomor_seri, $tahun_pembelian, $status) {
    global $conn;
    $query = "INSERT INTO ups (lokasi, kapasitas, merek, model, nomor_seri, tahun_pembelian, status) 
              VALUES (?,?,?,?,?,?,?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sssssis", $lokasi, $kapasitas, $merek, $model, $nomor_seri, $tahun_pembelian, $status);
    return mysqli_stmt_execute($stmt);
}

// Update UPS
function updateUPS($id, $lokasi, $kapasitas, $merek, $model, $nomor_seri, $tahun_pembelian, $status) {
    global $conn;
    $query = "UPDATE ups SET lokasi=?, kapasitas=?, merek=?, model=?, nomor_seri=?, tahun_pembelian=?, status=? 
              WHERE ups_id=?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ssssssii", $lokasi, $kapasitas, $merek, $model, $nomor_seri, $tahun_pembelian, $status, $id);
    return mysqli_stmt_execute($stmt);
}

// Hapus UPS
function deleteUPS($id) {
    global $conn;
    $query = "DELETE FROM ups WHERE ups_id=?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    return mysqli_stmt_execute($stmt);
}
?>
