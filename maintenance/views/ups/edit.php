<?php
include '../../functions/auth.php';
include '../../functions/ups.php';
checkLogin();

$id = $_GET['id'];
$result = getUPSById($id);
$row = mysqli_fetch_assoc($result);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lokasi = $_POST['lokasi'];
    $kapasitas = $_POST['kapasitas'];
    $merek = $_POST['merek'];
    $model = $_POST['model'];
    $nomor_seri = $_POST['nomor_seri'];
    $tahun_pembelian = $_POST['tahun_pembelian'];
    $status = $_POST['status'];

    if (updateUPS($id, $lokasi, $kapasitas, $merek, $model, $nomor_seri, $tahun_pembelian, $status)) {
        header("Location: list.php");
        exit;
    } else {
        echo "Gagal mengupdate data UPS.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit UPS</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
    <div class="container">
        <h2>Edit UPS</h2>
        <form method="POST">
            <label>Lokasi:</label><br>
            <input type="text" name="lokasi" value="<?php echo $row['lokasi']; ?>" required><br>

            <label>Kapasitas:</label><br>
            <input type="text" name="kapasitas" value="<?php echo $row['kapasitas']; ?>" required><br>

            <label>Merek:</label><br>
            <input type="text" name="merek" value="<?php echo $row['merek']; ?>" required><br>

            <label>Model:</label><br>
            <input type="text" name="model" value="<?php echo $row['model']; ?>" required><br>

            <label>Nomor Seri:</label><br>
            <input type="text" name="nomor_seri" value="<?php echo $row['nomor_seri']; ?>" required><br>

            <label>Tahun Pembelian:</label><br>
            <input type="number" name="tahun_pembelian" value="<?php echo $row['tahun_pembelian']; ?>" required><br>

            <label>Status:</label><br>
            <select name="status">
                <option value="aktif" <?php if($row['status']=="aktif") echo "selected"; ?>>Aktif</option>
                <option value="standby" <?php if($row['status']=="standby") echo "selected"; ?>>Standby</option>
                <option value="rusak" <?php if($row['status']=="rusak") echo "selected"; ?>>Rusak</option>
            </select><br><br>

            <button type="submit" class="btn btn-success">Update</button>
            <a href="list.php" class="btn">Batal</a>
        </form>
    </div>
</body>
</html>
