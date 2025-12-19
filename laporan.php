<?php
session_start();
if(!isset($_SESSION['username'])){
    header("Location: login.php");
    exit();
}
include "config/database.php";

/* =========================
   FUNGSI HITUNG SALDO
========================= */
function get_saldo($koneksi, $akun_id){
    $q = mysqli_query($koneksi, "
        SELECT SUM(debit) AS debit, SUM(kredit) AS kredit
        FROM jurnal_detail
        WHERE akun_id = $akun_id
    ");
    $r = mysqli_fetch_assoc($q);
    return ($r['debit'] ?? 0) - ($r['kredit'] ?? 0);
}

function total_saldo_by_type($koneksi, $tipe_akun){
    $query = mysqli_query($koneksi, "SELECT id, nama_akun FROM akun WHERE tipe_akun='$tipe_akun'");
    $total = 0;
    $akun_list = [];
    while($r = mysqli_fetch_assoc($query)){
        $saldo = get_saldo($koneksi, $r['id']);
        $akun_list[] = ['nama'=>$r['nama_akun'], 'saldo'=>$saldo];
        $total += $saldo;
    }
    return ['total'=>$total, 'akun'=>$akun_list];
}

function total_saldo_by_name($koneksi, $nama_akun){
    $query = mysqli_query($koneksi, "SELECT id FROM akun WHERE nama_akun='$nama_akun'");
    $total = 0;
    while($r = mysqli_fetch_assoc($query)){
        $total += get_saldo($koneksi, $r['id']);
    }
    return $total;
}

/* =========================
   HITUNG LAPORAN
========================= */
// LABA RUGI
$pendapatan = total_saldo_by_type($koneksi,"Pendapatan Neto");
$beban = total_saldo_by_type($koneksi,"Beban");
$laba_rugi = $pendapatan['total'] - $beban['total'];

// PERUBAHAN EKUITAS
$modal_awal = total_saldo_by_name($koneksi,"Modal Saham Awal");
$setoran_modal = total_saldo_by_name($koneksi,"Setoran Modal");
$modal_akhir = $modal_awal + $setoran_modal;

$saldo_laba_awal = total_saldo_by_name($koneksi,"Saldo Laba Awal");
$dividen = total_saldo_by_name($koneksi,"Dividen");
$saldo_laba_akhir = $saldo_laba_awal + $laba_rugi - $dividen;

$total_ekuitas = $modal_akhir + $saldo_laba_akhir;

// NERACA
$aktiva = total_saldo_by_type($koneksi,"Aset");
$liabilitas = total_saldo_by_type($koneksi,"Liabilitas");
$total_pasiva = $liabilitas['total'] + $total_ekuitas;

// SALDO KAS AWAL
$saldo_kas_awal = total_saldo_by_name($koneksi,"Kas Awal Periode");

// ARUS KAS PER AKUN
function kas_by_type($koneksi, $tipe){
    $query = mysqli_query($koneksi, "SELECT id, nama_akun FROM akun WHERE tipe_akun LIKE '%$tipe%'");
    $akun_list = [];
    $total = 0;
    while($r = mysqli_fetch_assoc($query)){
        $saldo = get_saldo($koneksi, $r['id']);
        if($saldo != 0){
            $akun_list[] = ['nama'=>$r['nama_akun'], 'saldo'=>$saldo];
            $total += $saldo;
        }
    }
    return ['total'=>$total, 'akun'=>$akun_list];
}

$kas_operasi = kas_by_type($koneksi,"Kas Operasi");
$kas_investasi = kas_by_type($koneksi,"Kas Investasi");
$kas_pendanaan = kas_by_type($koneksi,"Kas Pendanaan");

$saldo_kas_akhir = $saldo_kas_awal + $kas_operasi['total'] + $kas_investasi['total'] + $kas_pendanaan['total'];
?>
<!DOCTYPE html>
<html>
<head>
<title>Laporan Keuangan Sinkron</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{background:#EEEDED;font-family:'Segoe UI'}
.sidebar{width:220px;height:100vh;background:#2c3e50;color:white;position:fixed;padding:20px}
.sidebar a{color:white;text-decoration:none;display:block;padding:10px;border-radius:6px}
.sidebar a:hover,.active{background:#D11716}
.main{margin-left:240px;padding:20px}
.card{background:white;padding:25px;border-radius:12px;margin-bottom:30px;box-shadow:0 4px 12px rgba(0,0,0,.1)}
.text-end{text-align:right}
</style>
</head>
<body>
<div class="sidebar">
<h4>AKUNTANSI</h4><hr>
<a href="dashboard.php">Dashboard</a>
<a href="akun.php">Data Akun</a>
<a href="jurnal.php">Jurnal</a>
<a href="buku_besar.php">Buku Besar</a>
<a href="laporan.php" class="active">Laporan</a>
<hr><a href="logout.php">Logout</a>
</div>

<div class="main">

<!-- LAPORAN LABA RUGI -->
<div class="card">
<h4>Laporan Laba Rugi</h4>
<table class="table table-bordered">
<?php foreach($pendapatan['akun'] as $a): ?>
<tr><td><?=$a['nama']?></td><td class="text-end"><?=number_format($a['saldo'],0,',','.')?></td></tr>
<?php endforeach; ?>
<?php foreach($beban['akun'] as $b): ?>
<tr><td><?=$b['nama']?></td><td class="text-end"><?=number_format($b['saldo'],0,',','.')?></td></tr>
<?php endforeach; ?>
<tr class="table-secondary fw-bold"><td>Laba/Rugi Tahun Berjalan</td><td class="text-end"><?=number_format($laba_rugi,0,',','.')?></td></tr>
</table>
</div>

<!-- PERUBAHAN EKUITAS -->
<div class="card">
<h4>Laporan Perubahan Ekuitas</h4>
<table class="table table-bordered">
<tr><td colspan="2" class="fw-bold">MODAL SAHAM</td></tr>
<tr><td>Saldo Awal, 1 Jan</td><td class="text-end"><?=number_format($modal_awal,0,',','.')?></td></tr>
<tr><td>Setoran Modal</td><td class="text-end"><?=number_format($setoran_modal,0,',','.')?></td></tr>
<tr><td>Saldo Akhir Modal Saham</td><td class="text-end"><?=number_format($modal_akhir,0,',','.')?></td></tr>

<tr><td colspan="2" class="fw-bold">SALDO LABA</td></tr>
<tr><td>Saldo Awal, 1 Jan</td><td class="text-end"><?=number_format($saldo_laba_awal,0,',','.')?></td></tr>
<tr><td>Laba Bersih Tahun Berjalan</td><td class="text-end"><?=number_format($laba_rugi,0,',','.')?></td></tr>
<tr><td>Dividen</td><td class="text-end"><?=number_format($dividen,0,',','.')?></td></tr>
<tr><td>Saldo Akhir Saldo Laba</td><td class="text-end"><?=number_format($saldo_laba_akhir,0,',','.')?></td></tr>

<tr class="table-secondary fw-bold"><td>TOTAL EKUITAS (31 Jan)</td><td class="text-end"><?=number_format($total_ekuitas,0,',','.')?></td></tr>
</table>
</div>

<!-- NERACA -->
<div class="card">
<h4>Laporan Neraca</h4>
<table class="table table-bordered">
<tr><td colspan="2" class="fw-bold">AKTIVA</td></tr>
<?php foreach($aktiva['akun'] as $a): ?>
<tr><td><?=$a['nama']?></td><td class="text-end"><?=number_format($a['saldo'],0,',','.')?></td></tr>
<?php endforeach; ?>
<tr class="table-secondary fw-bold"><td>Total Aktiva</td><td class="text-end"><?=number_format($aktiva['total'],0,',','.')?></td></tr>

<tr><td colspan="2" class="fw-bold">PASIVA</td></tr>
<?php foreach($liabilitas['akun'] as $l): ?>
<tr><td><?=$l['nama']?></td><td class="text-end"><?=number_format($l['saldo'],0,',','.')?></td></tr>
<?php endforeach; ?>
<tr class="table-secondary fw-bold"><td>Total Pasiva + Ekuitas</td><td class="text-end"><?=number_format($total_pasiva,0,',','.')?></td></tr>
</table>
</div>

<!-- ARUS KAS -->
<!-- LAPORAN ARUS KAS -->
<div class="card">
<h4>Laporan Arus Kas</h4>
<table class="table table-bordered">

<tr><td colspan="2" class="fw-bold">AKTIVITAS OPERASI</td></tr>
<?php foreach($kas_operasi['akun'] as $k): ?>
<tr><td><?=$k['nama']?></td><td class="text-end"><?=number_format($k['saldo'],0,',','.')?></td></tr>
<?php endforeach; ?>
<tr class="fw-bold"><td>Subtotal Operasi</td><td class="text-end"><?=number_format($kas_operasi['total'],0,',','.')?></td></tr>

<tr><td colspan="2" class="fw-bold">AKTIVITAS INVESTASI</td></tr>
<?php foreach($kas_investasi['akun'] as $k): ?>
<tr><td><?=$k['nama']?></td><td class="text-end"><?=number_format($k['saldo'],0,',','.')?></td></tr>
<?php endforeach; ?>
<tr class="fw-bold"><td>Subtotal Investasi</td><td class="text-end"><?=number_format($kas_investasi['total'],0,',','.')?></td></tr>

<tr><td colspan="2" class="fw-bold">AKTIVITAS PENDANAAN</td></tr>
<?php foreach($kas_pendanaan['akun'] as $k): ?>
<tr><td><?=$k['nama']?></td><td class="text-end"><?=number_format($k['saldo'],0,',','.')?></td></tr>
<?php endforeach; ?>
<tr class="fw-bold"><td>Subtotal Pendanaan</td><td class="text-end"><?=number_format($kas_pendanaan['total'],0,',','.')?></td></tr>

<tr class="table-secondary fw-bold"><td>Saldo Kas Akhir</td><td class="text-end"><?=number_format($saldo_kas_akhir,0,',','.')?></td></tr>

</table>
</div>

</div>
</body>
</html>
