<?php
session_start();
include "config/database.php";

// Proteksi login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Ambil semua akun
$akun = mysqli_query($koneksi, "SELECT * FROM akun ORDER BY kode_akun ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Daftar Akun</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #EEEDED;
    margin: 0;
    padding: 0;
}

/* Sidebar */
.sidebar {
    width: 220px;          /* sama dengan jurnal.php */
    height: 100vh;
    background: #2c3e50;
    color: white;
    position: fixed;
    left: 0;
    top: 0;
    padding: 20px;
    font-family: 'Segoe UI', sans-serif;
}
.sidebar h2 {
    font-size: 22px;
    margin-top: 0;
}
.sidebar a {
    color: white;
    text-decoration: none;
    display: block;
    padding: 10px 12px;  /* sama jaraknya dengan jurnal.php */
    border-radius: 6px;   /* sama border-radius */
    margin-bottom: 8px;
    transition: 0.2s;
}
.sidebar a:hover, .sidebar a.active {
    background: #083EA8;
}

/* Main content */
.main {
    margin-left: 240px;   /* sama dengan jurnal.php */
    padding: 20px;        /* padding seragam */
}
.table th, .table td {
    vertical-align: middle;
}
.alert-success {
    margin-top: 15px;
}
</style>


<body>

<div class="sidebar">
    <h2>AKUNTANSI</h2>
    <hr style="border-color: #fff3;">
    <a href="dashboard.php">Dashboard</a>
    <a href="akun.php" class="active">Data Akun</a>
    <a href="jurnal.php">Jurnal</a>
    <a href="buku_besar.php">Buku Besar</a>
    <a href="laporan.php">Laporan</a>
    <hr style="border-color: #fff3; margin-top: 40px;">
    <a href="logout.php">Logout</a>
</div>

<div class="main">
    <h3>Daftar Akun</h3>

    <?php if(isset($_GET['msg']) && $_GET['msg'] === 'added'): ?>
        <div class="alert alert-success">Akun berhasil ditambahkan!</div>
    <?php endif; ?>

    <a href="tambah_akun.php" class="btn btn-primary btn-sm mb-3">+ Tambah Akun</a>

    <table class="table table-bordered table-striped table-hover bg-white">
        <thead class="table-dark">
            <tr>
                <th>Kode Akun</th>
                <th>Nama Akun</th>
                <th>Tipe Akun</th>
                <th>Saldo Normal</th>
                <th>Deskripsi</th>
                <th>Nominal</th>
                <th width="150px">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($akun)) { ?>
            <tr>
                <td><?= htmlspecialchars($row['kode_akun']); ?></td>
                <td><?= htmlspecialchars($row['nama_akun']); ?></td>
                <td><?= htmlspecialchars($row['tipe_akun']); ?></td>
                <td><?= htmlspecialchars($row['saldo_normal']); ?></td>
                <td><?= htmlspecialchars($row['deskripsi']); ?></td>
                <td><?= number_format($row['nominal'],0,',','.'); ?></td>
                <td>
                    <a href="edit_akun.php?kode_akun=<?= $row['kode_akun']; ?>" class="btn btn-warning btn-sm">Edit</a>
                    <a href="hapus_akun.php?kode_akun=<?= $row['kode_akun']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus akun ini?')">Hapus</a>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

</body>
</html>
