<?php
session_start();
include "config/database.php";

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

/*
  LOGIKA:
  kode_final = [1][10][1] → 1101
  kode_induk = digit ke-1 × 1000
  kode_sub   = digit ke-2 & 3
*/
$akun = mysqli_query($koneksi, "
    SELECT *,
        (LEFT(kode_final,1) * 1000) AS kode_induk_tampil,
        SUBSTRING(kode_final,2,2)   AS kode_sub_tampil
    FROM akun
    ORDER BY kode_final ASC
");
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Daftar Akun</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{background:#EEEDED;font-family:'Segoe UI'}
.sidebar{width:220px;height:100vh;background:#2c3e50;color:white;position:fixed;padding:20px}
.sidebar a{color:white;text-decoration:none;display:block;padding:10px;border-radius:6px}
.sidebar a:hover,.active{background:#083EA8}
.main{margin-left:240px;padding:20px}
</style>
</head>

<body>
<div class="sidebar">
<h4>AKUNTANSI</h4><hr>
<a href="dashboard.php">Dashboard</a>
<a href="akun.php" class="active">Data Akun</a>
<a href="jurnal.php">Jurnal</a>
<a href="buku_besar.php">Buku Besar</a>
<a href="laporan.php">Laporan</a>
<hr><a href="logout.php">Logout</a>
</div>

<div class="main">
<h3>Daftar Akun</h3>
<a href="tambah_akun.php" class="btn btn-primary btn-sm mb-3">+ Tambah Akun</a>

<table class="table table-bordered table-striped bg-white">
<thead class="table-dark">
<tr>
<th>Kode Induk</th>
<th>Kode Sub</th>
<th>Kode Akun</th>
<th>Kode Final</th>
<th>Nama Akun</th>
<th>Tipe</th>
<th>Saldo Normal</th>
<th>Saldo Awal</th>
<th width="130">Aksi</th>
</tr>
</thead>

<tbody>
<?php while($r = mysqli_fetch_assoc($akun)) { ?>
<tr>
<td><?= $r['kode_induk_tampil']; ?></td>
<td><?= $r['kode_sub_tampil']; ?></td>
<td><?= $r['kode_akun']; ?></td>
<td><b><?= $r['kode_final']; ?></b></td>
<td><?= $r['nama_akun']; ?></td>
<td><?= $r['tipe_akun']; ?></td>
<td><?= $r['saldo_normal']; ?></td>
<td>Rp <?= number_format($r['nominal'],0,',','.') ?></td>
<td>
<a href="edit_akun.php?id=<?= $r['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
<a href="hapus_akun.php?id=<?= $r['id'] ?>" class="btn btn-danger btn-sm"
onclick="return confirm('Hapus akun ini?')">Hapus</a>
</td>
</tr>
<?php } ?>
</tbody>
</table>
</div>
</body>
</html>
