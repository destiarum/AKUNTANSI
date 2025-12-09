<?php
session_start();
include "config/database.php";

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    die("ID tidak ditemukan");
}

$id = $_GET["id"];

// QUERY DATA
$result = mysqli_query($koneksi, "SELECT * FROM akun WHERE id='$id'");

// CEK DATA
if (mysqli_num_rows($result) == 0) {
    die("Akun tidak ditemukan!");
}

// AMBIL SATU BARIS
$data = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Akun</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>

<body>

<?php include "sidebar.php"; ?>

<div class="container mt-4">
    <h3>Edit Akun</h3>

    <form method="POST" action="proses_edit_akun.php">

        <input type="hidden" name="id" value="<?= $data['id']; ?>">

        <div class="mb-3">
            <label>Kode Akun</label>
            <input type="text" name="kode_akun" class="form-control" value="<?= $data['kode_akun']; ?>" required>
        </div>

        <div class="mb-3">
            <label>Nama Akun</label>
            <input type="text" name="nama_akun" class="form-control" value="<?= $data['nama_akun']; ?>" required>
        </div>

        <div class="mb-3">
            <label>Jenis Akun</label>
            <select name="jenis" class="form-control" required>
                <option value="Aset" <?= $data['jenis']=="Aset"?"selected":""; ?>>Aset</option>
                <option value="Liabilitas" <?= $data['jenis']=="Liabilitas"?"selected":""; ?>>Liabilitas</option>
                <option value="Modal" <?= $data['jenis']=="Modal"?"selected":""; ?>>Modal</option>
                <option value="Pendapatan" <?= $data['jenis']=="Pendapatan"?"selected":""; ?>>Pendapatan</option>
                <option value="Beban" <?= $data['jenis']=="Beban"?"selected":""; ?>>Beban</option>
            </select>
        </div>

        <button class="btn btn-success">Update</button>
        <a href="akun.php" class="btn btn-secondary">Kembali</a>

    </form>
</div>

</body>
</html>
