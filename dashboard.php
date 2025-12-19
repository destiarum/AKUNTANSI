<?php
session_start();
if(!isset($_SESSION['username'])){
    header("Location: login.php");
    exit();
}
include "config/database.php";

$bulan = $_GET['bulan'] ?? '';
$tahun = $_GET['tahun'] ?? '';

function get_saldo($koneksi,$kode,$bulan='',$tahun=''){
    $w="";
    if($bulan!='') $w.=" AND MONTH(j.tanggal)='$bulan'";
    if($tahun!='') $w.=" AND YEAR(j.tanggal)='$tahun'";
    $q=mysqli_query($koneksi,"
        SELECT SUM(jd.debit)d,SUM(jd.kredit)k
        FROM jurnal_detail jd
        JOIN jurnal j ON jd.jurnal_id=j.id
        JOIN akun a ON jd.akun_id=a.id
        WHERE a.kode_akun='$kode' $w
    ");
    $r=mysqli_fetch_assoc($q);
    return ($r['d']??0)-($r['k']??0);
}

function total_by_tipe($koneksi,$tipe,$bulan,$tahun){
    $t=0;
    $q=mysqli_query($koneksi,"SELECT kode_akun FROM akun WHERE tipe_akun='$tipe'");
    while($r=mysqli_fetch_assoc($q)){
        $t+=get_saldo($koneksi,$r['kode_akun'],$bulan,$tahun);
    }
    return $t;
}

// Data utama
$pendapatan = total_by_tipe($koneksi,'Pendapatan',$bulan,$tahun);
$beban      = total_by_tipe($koneksi,'Beban',$bulan,$tahun);
$laba       = $pendapatan - $beban;
$aset       = total_by_tipe($koneksi,'Aset',$bulan,$tahun);
$liab       = total_by_tipe($koneksi,'Liabilitas',$bulan,$tahun);
$ekui       = total_by_tipe($koneksi,'Ekuitas',$bulan,$tahun);

// Arus kas (simulasi sederhana)
$arus_operasi   = $laba;
$arus_investasi = -($aset*0.15);
$arus_pendanaan = $ekui;

// Data garis untuk tren tiap bulan
$labels=[];
$pendapatan_bulan=[];
$beban_bulan=[];
$laba_bulan=[];
for($i=1;$i<=12;$i++){
    $labels[] = date('F',mktime(0,0,0,$i,1));
    $pendapatan_bulan[] = total_by_tipe($koneksi,'Pendapatan',$i,$tahun);
    $beban_bulan[]      = total_by_tipe($koneksi,'Beban',$i,$tahun);
    $laba_bulan[]       = $pendapatan_bulan[$i-1]-$beban_bulan[$i-1];
}
// total debit dan kredit
$q = mysqli_query($koneksi, "
    SELECT SUM(debit) AS total_debit, SUM(kredit) AS total_kredit
    FROM jurnal_detail
");
$r = mysqli_fetch_assoc($q);
$total_debit = $r['total_debit'] ?? 0;
$total_kredit = $r['total_kredit'] ?? 0;

// cek balance
$balance = $total_debit - $total_kredit;

?>
<!DOCTYPE html>
<html>
<head>
<title>Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body{background:#EEEDED;font-family:'Segoe UI'}
.sidebar{width:220px;height:100vh;background:#2c3e50;color:white;position:fixed;padding:20px}
.sidebar a{color:white;display:block;padding:10px;border-radius:6px;text-decoration:none}
.sidebar a:hover,.active{background:#083EA8}
.main{margin-left:240px;padding:20px}
.card{background:white;padding:20px;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,.1)}
</style>
</head>
<body>
<div class="sidebar">
<h4>AKUNTANSI</h4><hr>
<a class="active">Dashboard</a>
<a href="akun.php">Data Akun</a>
<a href="jurnal.php">Jurnal</a>
<a href="buku_besar.php">Buku Besar</a>
<a href="laporan.php">Laporan</a>
<hr><a href="logout.php">Logout</a>
</div>

<div class="main">
<h3>Dashboard Keuangan</h3>

<form class="row mb-4">
<div class="col-md-3">
<select name="bulan" class="form-select">
<option value="">Semua Bulan</option>
<?php for($i=1;$i<=12;$i++): ?>
<option value="<?=$i?>" <?=($bulan==$i?'selected':'')?>>
<?=date('F',mktime(0,0,0,$i,1))?>
</option>
<?php endfor; ?>
</select>
</div>

<div class="col-md-3">
<select name="tahun" class="form-select">
<option value="">Semua Tahun</option>
<?php for($i=date('Y');$i>=date('Y')-5;$i--): ?>
<option value="<?=$i?>" <?=($tahun==$i?'selected':'')?>>
<?=$i?>
</option>
<?php endfor; ?>
</select>
</div>

<div class="col-md-2">
<button class="btn btn-primary">Filter</button>
</div>

<div class="col-md-4 text-end">
<a href="export_dashboard_pdf.php?bulan=<?=$bulan?>&tahun=<?=$tahun?>" class="btn btn-danger">
📄 Export PDF
</a>
</div>
</form>

<div class="row mb-4">
  <div class="col-md-6">
    <div class="card text-center">
      <h6>Total Debit</h6>
      <h5 class="text-danger">Rp <?= number_format($total_debit,0,',','.') ?></h5>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card text-center">
      <h6>Total Kredit</h6>
      <h5 class="text-primary">Rp <?= number_format($total_kredit,0,',','.') ?></h5>
    </div>
  </div>
</div>

<div class="card mb-4 text-center">
  <h6>Status Balance</h6>
  <?php if($balance == 0): ?>
    <h5 class="text-success">Seimbang ✅</h5>
  <?php else: ?>
    <h5 class="text-danger">Tidak Seimbang ❌ (Selisih: Rp <?= number_format($balance,0,',','.') ?>)</h5>
  <?php endif; ?>
</div>

<div class="row mb-4">
<?php
$cards=[ 'Pendapatan'=>$pendapatan, 'Beban'=>$beban, 'Laba Bersih'=>$laba, 'Total Aset'=>$aset ];
foreach($cards as $k=>$v):
?>
<div class="col-md-3">
<div class="card">
<h6><?=$k?></h6>
<h5 style="color:<?=$k=='Laba Bersih'&&$v<0?'red':'black'?>">
Rp <?=number_format($v,0,',','.')?>
</h5>
</div>
</div>
<?php endforeach; ?>
</div>

<div class="row">
  <div class="col-md-6">
    <div class="card" style="height:320px">
      <h6 class="text-center">Laba, Pendapatan & Beban</h6>
      <canvas id="labaBar" style="height:250px"></canvas>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card" style="height:320px">
      <h6 class="text-center">Neraca</h6>
      <canvas id="neracaPie" style="height:250px"></canvas>
    </div>
  </div>
  <div class="col-md-12 mt-4">
    <div class="card" style="height:320px">
      <h6 class="text-center">Arus Kas</h6>
      <canvas id="arusBar" style="height:250px"></canvas>
    </div>
  </div>
  <div class="col-md-12 mt-4">
    <div class="card" style="height:320px">
      <h6 class="text-center">Tren Laba/Beban Tahun Ini</h6>
      <canvas id="trenLine" style="height:250px"></canvas>
    </div>
  </div>
</div>


<script>
new Chart(document.getElementById('labaBar'),{
type:'bar',
data:{labels:['Pendapatan','Beban','Laba'],datasets:[{label:'Rp',data:[<?=$pendapatan?>,<?=$beban?>,<?=$laba?>],backgroundColor:['#4caf50','#f44336','#2196f3']}]}
});

new Chart(document.getElementById('neracaPie'),{
type:'pie',
data:{labels:['Aset','Liabilitas','Ekuitas'],datasets:[{data:[<?=$aset?>,<?=$liab?>,<?=$ekui?>],backgroundColor:['#ff9800','#9c27b0','#00bcd4']}]}
});

new Chart(document.getElementById('arusBar'),{
type:'bar',
data:{labels:['Operasi','Investasi','Pendanaan'],datasets:[{label:'Rp',data:[<?=$arus_operasi?>,<?=$arus_investasi?>,<?=$arus_pendanaan?>],backgroundColor:['#3f51b5','#e91e63','#009688']}]}
});

// Diagram garis tren bulanan
new Chart(document.getElementById('trenLine'),{
type:'line',
data:{
labels:[<?=implode(',',array_map(fn($m)=>"'$m'",$labels))?>],
datasets:[
    {label:'Pendapatan',data:[<?=implode(',',$pendapatan_bulan)?>],borderColor:'#4caf50',fill:false},
    {label:'Beban',data:[<?=implode(',',$beban_bulan)?>],borderColor:'#f44336',fill:false},
    {label:'Laba',data:[<?=implode(',',$laba_bulan)?>],borderColor:'#2196f3',fill:false}
]
}
});
</script>
</body>
</html>
