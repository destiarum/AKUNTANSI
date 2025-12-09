<?php
session_start();
if(!isset($_SESSION['username'])){
    header("Location: login.php");
    exit();
}

include "config/database.php";

// Ambil semua akun
$akunQuery = mysqli_query($koneksi, "SELECT * FROM akun ORDER BY kode_akun ASC");
$akunList = [];
while($row = mysqli_fetch_assoc($akunQuery)){
    $akunList[$row['id']] = [
        'kode' => $row['kode_akun'],
        'nama' => $row['nama_akun'],
        'tipe' => $row['tipe_akun'],
        'saldo_normal' => $row['saldo_normal']
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Buku Besar</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background:#EEEDED; margin:0; padding:0; }
.sidebar {
    width:220px; height:100vh; background:#2c3e50; color:white; position:fixed; left:0; top:0; padding:20px;
}
.sidebar h2 { font-size:22px; margin-top:0; }
.sidebar a { color:white; text-decoration:none; display:block; padding:8px 0; transition:0.2s; }
.sidebar a:hover, .sidebar a.active { background:#D11716; border-radius:5px; }

.main { margin-left:240px; padding:20px; }

table { width:100%; border-collapse: collapse; margin-bottom:40px; }
th, td { padding:8px; border:1px solid #ccc; text-align:center; }
th { background:#083EA8; color:white; }
.debit { color:red; font-weight:bold; }
.kredit { color:blue; font-weight:bold; }
.total-row { font-weight:bold; background:#f0f0f0; }
</style>
</head>
<body>

<div class="sidebar">
    <h2>AKUNTANSI</h2>
    <hr style="border-color: #fff3;">
    <a href="dashboard.php">Dashboard</a>
    <a href="akun.php">Data Akun</a>
    <a href="jurnal.php">Jurnal</a>
    <a href="buku_besar.php" class="active">Buku Besar</a>
    <a href="laporan.php">Laporan</a>
    <hr style="border-color: #fff3; margin-top:40px;">
    <a href="logout.php">Logout</a>
</div>

<div class="main">
<h3>Buku Besar</h3>

<?php foreach($akunList as $akunId => $akun): ?>
    <h4><?= $akun['kode'] ?> - <?= $akun['nama'] ?></h4>

    <?php
    // Ambil transaksi untuk akun ini
    $jurnalQuery = mysqli_query($koneksi, "
        SELECT j.tanggal, j.keterangan, jd.debit, jd.kredit
        FROM jurnal_detail jd
        JOIN jurnal j ON jd.jurnal_id = j.id
        WHERE jd.akun_id = $akunId
        ORDER BY j.tanggal, jd.id
    ");

    $saldo = 0;
    ?>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Keterangan</th>
                <th>Posisi</th>
                <th>Nominal</th>
                <th>Saldo</th>
            </tr>
        </thead>
        <tbody>
        <?php while($row = mysqli_fetch_assoc($jurnalQuery)):
            if($row['debit'] > 0){
                $posisi = 'Debit';
                $nominal = $row['debit'];
                $saldo += $nominal;
                $class = 'debit';
            } else {
                $posisi = 'Kredit';
                $nominal = $row['kredit'];
                $saldo -= $nominal;
                $class = 'kredit';
            }
        ?>
            <tr>
                <td><?= $row['tanggal'] ?></td>
                <td><?= $row['keterangan'] ?></td>
                <td class="<?= $class ?>"><?= $posisi ?></td>
                <td class="<?= $class ?>"><?= number_format($nominal,0,',','.') ?></td>
                <td><?= number_format($saldo,0,',','.') ?></td>
            </tr>
        <?php endwhile; ?>
        <tr class="total-row">
            <td colspan="3">Total Saldo</td>
            <td colspan="2"><?= number_format($saldo,0,',','.') ?></td>
        </tr>
        </tbody>
    </table>
<?php endforeach; ?>

</div>
</body>
</html>
