<?php
session_start();
if(!isset($_SESSION['username'])){
    header("Location: login.php");
    exit();
}

include 'config/database.php';

// Fungsi ambil saldo akun dari jurnal_detail
function get_saldo($koneksi, $kode_akun){
    $q = mysqli_query($koneksi, "
        SELECT SUM(jd.debit) as total_debit, SUM(jd.kredit) as total_kredit
        FROM jurnal_detail jd
        JOIN akun a ON jd.akun_id = a.id
        WHERE a.kode_akun='$kode_akun'
    ");
    $row = mysqli_fetch_assoc($q);
    $saldo = ($row['total_debit'] ?? 0) - ($row['total_kredit'] ?? 0);
    return $saldo;
}

// Ambil akun berdasarkan tipe
$akun_pdptu = mysqli_query($koneksi, "SELECT * FROM akun WHERE tipe_akun='Pendapatan' ORDER BY kode_akun ASC");
$akun_kprpj = mysqli_query($koneksi, "SELECT * FROM akun WHERE tipe_akun='Beban' ORDER BY kode_akun ASC");
$akun_ekuitas = mysqli_query($koneksi, "SELECT * FROM akun WHERE tipe_akun='Ekuitas' ORDER BY kode_akun ASC");
$akun_aset = mysqli_query($koneksi, "SELECT * FROM akun WHERE tipe_akun='Aset' ORDER BY kode_akun ASC");
$akun_liabilitas = mysqli_query($koneksi, "SELECT * FROM akun WHERE tipe_akun='Liabilitas' ORDER BY kode_akun ASC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Laporan Keuangan</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background:#EEEDED; margin:0; padding:0;}
.sidebar {
    width: 220px;
    height: 100vh;
    background: #2c3e50;
    color: white;
    position: fixed;
    left: 0; top: 0;
    padding: 20px;
}
.sidebar h2 { font-size:22px; margin-top:0; font-weight:700; }
.sidebar hr { border:1px solid rgba(255,255,255,0.2); margin:15px 0; }
.sidebar ul { list-style:none; padding:0; margin-top:20px; }
.sidebar li { margin-bottom:10px; }
.sidebar a {
    color:white; text-decoration:none; display:block; padding:8px 12px; border-radius:6px;
    transition:0.3s;
}
.sidebar a:hover, .sidebar a.active { background-color:#D11716; color:white; }

/* CONTENT */
.wrapper { margin-left:250px; padding:20px;}
.card { padding:20px; background:white; border-radius:10px; margin-bottom:30px; box-shadow:0 4px 12px rgba(0,0,0,0.1);}
h3 { color:#083EA8; margin-bottom:15px;}
.total { font-weight:bold; background:#f9f9f9; text-align:right; }
</style>
</head>
<body>

<div class="sidebar">
    <h2>AKUNTANSI</h2>
    <hr>
    <ul>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="akun.php">Data Akun</a></li>
        <li><a href="jurnal.php">Jurnal</a></li>
        <li><a href="buku_besar.php">Buku Besar</a></li>
        <li><a href="laporan.php" class="active">Laporan</a></li>
        <li style="margin-top:40px;"><a href="logout.php">Logout</a></li>
    </ul>
</div>

<div class="wrapper">

<div class="card">
<h3>Laporan Laba Rugi (PDPTU / KPRPJ)</h3>
<table class="table table-bordered">
<thead class="table-dark">
<tr><th>Kode Akun</th><th>Nama Akun</th><th>Debit</th><th>Kredit</th><th>Saldo</th><th>Jenis</th></tr>
</thead>
<tbody>
<?php 
$total_pdptu=0; $total_kprpj=0;

// PDPTU (Pendapatan)
while($row = mysqli_fetch_assoc($akun_pdptu)){
    $saldo = get_saldo($koneksi, $row['kode_akun']);
    echo "<tr>
        <td>{$row['kode_akun']}</td>
        <td>{$row['nama_akun']}</td>
        <td>".($saldo>0 ? number_format($saldo,0,',','.') : '')."</td>
        <td>".($saldo<0 ? number_format(abs($saldo),0,',','.') : '')."</td>
        <td>".number_format($saldo,0,',','.')."</td>
        <td>PDPTU</td>
    </tr>";
    $total_pdptu += $saldo;
}

// KPRPJ (Beban)
while($row = mysqli_fetch_assoc($akun_kprpj)){
    $saldo = get_saldo($koneksi, $row['kode_akun']);
    echo "<tr>
        <td>{$row['kode_akun']}</td>
        <td>{$row['nama_akun']}</td>
        <td>".($saldo>0 ? number_format($saldo,0,',','.') : '')."</td>
        <td>".($saldo<0 ? number_format(abs($saldo),0,',','.') : '')."</td>
        <td>".number_format($saldo,0,',','.')."</td>
        <td>KPRPJ</td>
    </tr>";
    $total_kprpj += $saldo;
}

$laba_bersih = $total_pdptu - $total_kprpj;
?>
<tr class="total"><td colspan="4">Total PDPTU</td><td><?=number_format($total_pdptu,0,',','.')?></td><td></td></tr>
<tr class="total"><td colspan="4">Total KPRPJ</td><td><?=number_format($total_kprpj,0,',','.')?></td><td></td></tr>
<tr class="total"><td colspan="4">Laba Bersih</td><td><?=number_format($laba_bersih,0,',','.')?></td><td></td></tr>
</tbody>
</table>
</div>

<div class="card">
<h3>Laporan Perubahan Ekuitas</h3>
<table class="table table-bordered">
<thead class="table-dark"><tr><th>Kode Akun</th><th>Nama Akun</th><th>Saldo Awal</th><th>Laba/Rugi</th><th>Dividen</th><th>Saldo Akhir</th></tr></thead>
<tbody>
<?php 
while($row = mysqli_fetch_assoc($akun_ekuitas)){
    $saldo_awal = get_saldo($koneksi, $row['kode_akun']); 
    $dividen = 0;
    $saldo_akhir = $saldo_awal + $laba_bersih - $dividen;
    echo "<tr>
        <td>{$row['kode_akun']}</td>
        <td>{$row['nama_akun']}</td>
        <td>".number_format($saldo_awal,0,',','.')."</td>
        <td>".number_format($laba_bersih,0,',','.')."</td>
        <td>".number_format($dividen,0,',','.')."</td>
        <td>".number_format($saldo_akhir,0,',','.')."</td>
    </tr>";
}
?>
</tbody>
</table>
</div>

<div class="card">
<h3>Neraca</h3>
<table class="table table-bordered">
<thead class="table-dark"><tr><th>Kode Akun</th><th>Nama Akun</th><th>Debit</th><th>Kredit</th><th>Saldo</th></tr></thead>
<tbody>
<?php
$total_aset=0; $total_liab=0;
while($row=mysqli_fetch_assoc($akun_aset)){
    $saldo=get_saldo($koneksi,$row['kode_akun']); $total_aset+=$saldo;
    echo "<tr><td>{$row['kode_akun']}</td><td>{$row['nama_akun']}</td><td>".number_format($saldo,0,',','.')."</td><td></td><td>".number_format($saldo,0,',','.')."</td></tr>";
}
while($row=mysqli_fetch_assoc($akun_liabilitas)){
    $saldo=get_saldo($koneksi,$row['kode_akun']); $total_liab+=$saldo;
    echo "<tr><td>{$row['kode_akun']}</td><td>{$row['nama_akun']}</td><td></td><td>".number_format($saldo,0,',','.')."</td><td>".number_format($saldo,0,',','.')."</td></tr>";
}
$total_ekuitas=$laba_bersih;
?>
<tr class="total"><td colspan="4">Total Aset</td><td><?=number_format($total_aset,0,',','.')?></td></tr>
<tr class="total"><td colspan="4">Total Liabilitas + Ekuitas</td><td><?=number_format($total_liab+$total_ekuitas,0,',','.')?></td></tr>
</tbody>
</table>
</div>

</div>
</body>
</html>
