<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
include "config/database.php";
$tipe = $_GET['tipe'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Buku Besar</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{background:#EEEDED;font-family:'Segoe UI'}
.sidebar{width:220px;height:100vh;background:#2c3e50;color:white;position:fixed;padding:20px}
.sidebar a{color:white;text-decoration:none;display:block;padding:10px;border-radius:6px}
.sidebar a:hover,.active{background:#083EA8}
.main{margin-left:240px;padding:20px}
.debit{color:red;font-weight:bold}
.kredit{color:blue;font-weight:bold}
</style>
</head>

<body>
<div class="sidebar">
<h4>AKUNTANSI</h4><hr>
<a href="dashboard.php">Dashboard</a>
<a href="akun.php">Data Akun</a>
<a href="jurnal.php">Jurnal</a>
<a href="buku_besar.php" class="active">Buku Besar</a>
<a href="laporan.php">Laporan</a>
<hr><a href="logout.php">Logout</a>
</div>

<div class="main">
<h3>Buku Besar</h3>

<div class="mb-3">
    <a href="buku_besar.php?tipe=Aset" class="btn btn-primary btn-sm">🏦 Aset</a>
    <a href="buku_besar.php?tipe=Liabilitas" class="btn btn-secondary btn-sm">📄 Liabilitas</a>
    <a href="buku_besar.php?tipe=Ekuitas" class="btn btn-success btn-sm">📊 Ekuitas</a>
    <a href="buku_besar.php?tipe=Pendapatan" class="btn btn-warning btn-sm">💰 Pendapatan</a>
    <a href="buku_besar.php?tipe=Beban" class="btn btn-danger btn-sm">📉 Beban</a>
    <a href="buku_besar.php" class="btn btn-dark btn-sm">📘 Semua</a>
</div>

<?php
/* =========================
   MODE SEMUA AKUN
========================= */
if ($tipe == '') {

$q = mysqli_query($koneksi, "
    SELECT 
        jd.id AS id_detail,
        j.tanggal,
        j.keterangan,
        a.kode_final,
        a.nama_akun,
        jd.debit,
        jd.kredit
    FROM jurnal_detail jd
    JOIN jurnal j ON jd.jurnal_id = j.id
    JOIN akun a ON jd.akun_id = a.id
    ORDER BY j.tanggal, j.id
");

$totalDebit = 0;
$totalKredit = 0;
?>

<table class="table table-bordered bg-white">
<thead class="table-dark">
<tr>
    <th>Tanggal</th>
    <th>Keterangan</th>
    <th>Kode Akun</th>
    <th>Nama Akun</th>
    <th>Debit</th>
    <th>Kredit</th>
    <th>Aksi</th>
</tr>
</thead>
<tbody>

<?php while($r = mysqli_fetch_assoc($q)): ?>
<?php
$totalDebit  += $r['debit'];
$totalKredit += $r['kredit'];
?>
<tr>
    <td><?= $r['tanggal'] ?></td>
    <td><?= $r['keterangan'] ?></td>
    <td><?= $r['kode_final'] ?></td>
    <td><?= $r['nama_akun'] ?></td>
    <td class="debit"><?= number_format($r['debit'],0,',','.') ?></td>
    <td class="kredit"><?= number_format($r['kredit'],0,',','.') ?></td>
    <td class="text-center">
        <a href="edit_jurnal_detail.php?id=<?= $r['id_detail'] ?>" class="btn btn-warning btn-sm">✏️</a>
        <a href="hapus_jurnal_detail.php?id=<?= $r['id_detail'] ?>"
           onclick="return confirm('Yakin hapus data ini?')"
           class="btn btn-danger btn-sm">🗑️</a>
    </td>
</tr>
<?php endwhile; ?>

<tr class="fw-bold bg-light">
    <td colspan="4">TOTAL</td>
    <td class="debit"><?= number_format($totalDebit,0,',','.') ?></td>
    <td class="kredit"><?= number_format($totalKredit,0,',','.') ?></td>
    <td></td>
</tr>

</tbody>
</table>

<?php
/* =========================
   MODE PER TIPE AKUN
========================= */
} else {

$akunQuery = mysqli_query($koneksi, "
    SELECT * FROM akun
    WHERE tipe_akun = '$tipe'
    ORDER BY kode_final ASC
");

while ($akun = mysqli_fetch_assoc($akunQuery)) :
?>

<h5><?= $akun['kode_final'] ?> - <?= $akun['nama_akun'] ?></h5>

<?php
$q = mysqli_query($koneksi, "
    SELECT 
        jd.id AS id_detail,
        j.tanggal,
        j.keterangan,
        jd.debit,
        jd.kredit
    FROM jurnal_detail jd
    JOIN jurnal j ON jd.jurnal_id = j.id
    WHERE jd.akun_id = {$akun['id']}
    ORDER BY j.tanggal
");

$saldo = 0;
?>

<table class="table table-bordered bg-white mb-4">
<thead class="table-dark">
<tr>
    <th>Tanggal</th>
    <th>Keterangan</th>
    <th>Debit</th>
    <th>Kredit</th>
    <th>Saldo</th>
    <th>Aksi</th>
</tr>
</thead>
<tbody>

<?php while ($r = mysqli_fetch_assoc($q)) : ?>
<?php
$saldo += $r['debit'];
$saldo -= $r['kredit'];
?>
<tr>
    <td><?= $r['tanggal'] ?></td>
    <td><?= $r['keterangan'] ?></td>
    <td class="debit"><?= number_format($r['debit'],0,',','.') ?></td>
    <td class="kredit"><?= number_format($r['kredit'],0,',','.') ?></td>
    <td><?= number_format($saldo,0,',','.') ?></td>
    <td class="text-center">
        <a href="edit_jurnal_detail.php?id=<?= $r['id_detail'] ?>" class="btn btn-warning btn-sm">✏️</a>
        <a href="hapus_jurnal_detail.php?id=<?= $r['id_detail'] ?>"
           onclick="return confirm('Yakin hapus data ini?')"
           class="btn btn-danger btn-sm">🗑️</a>
    </td>
</tr>
<?php endwhile; ?>

<tr class="fw-bold bg-light">
    <td colspan="4">Saldo Akhir</td>
    <td><?= number_format($saldo,0,',','.') ?></td>
    <td></td>
</tr>

</tbody>
</table>

<?php endwhile; } ?>

</div>
</body>
</html>
