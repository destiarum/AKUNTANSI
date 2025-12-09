<?php
session_start();
include "config/database.php";

// Proteksi halaman
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Total Debit
$qDebit = mysqli_query($koneksi, "SELECT IFNULL(SUM(debit),0) AS total_debit FROM jurnal_detail");
$debit = mysqli_fetch_assoc($qDebit)['total_debit'];

// Total Kredit
$qKredit = mysqli_query($koneksi, "SELECT IFNULL(SUM(kredit),0) AS total_kredit FROM jurnal_detail");
$kredit = mysqli_fetch_assoc($qKredit)['total_kredit'];


// Balance
$balance = $debit - $kredit;

?>
<!DOCTYPE html>
<html>

<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial;
        }

        .sidebar {
            width: 220px;
            height: 100vh;
            background: #2c3e50;
            color: white;
            position: fixed;
            left: 0;
            top: 0;
            padding: 20px;
        }

        .content {
            margin-left: 240px;
            padding: 20px;
        }

        .card-box {
            padding: 20px;
            border-radius: 10px;
            color: white;
        }

        .bg-green { background: #27ae60; }
        .bg-red { background: #c0392b; }
        .bg-blue { background: #2980b9; }
    </style>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar navbar-dark bg-primary">
    <div class="container-fluid">
        <span class="navbar-brand">Sistem Akuntansi</span>
        <a href="logout.php" class="btn btn-light btn-sm">Logout</a>
    </div>
</nav>

<!-- SIDEBAR -->
<div class="sidebar">
    <h2 style="font-size: 22px;">AKUNTANSI</h2>
    <hr>

    <ul style="list-style: none; padding-left: 0;">
        <li><a href="dashboard.php" style="color:white; text-decoration:none;">Dashboard</a></li><br>
        <li><a href="akun.php" style="color:white; text-decoration:none;">Data Akun</a></li><br>
        <li><a href="jurnal.php" style="color:white; text-decoration:none;">Jurnal</a></li><br>
        <li><a href="buku_besar.php" style="color:white; text-decoration:none;">Buku Besar</a></li><br>
        <li><a href="laporan.php" style="color:white; text-decoration:none;">Laporan</a></li><br>
    </ul>
</div>

<!-- CONTENT -->
<div class="content">

    <h3>Selamat datang, <?= $_SESSION['username']; ?>!</h3>
    <p>Berikut ringkasan data transaksi.</p>

    <div class="row">
        <div class="col-md-4">
            <div class="card-box bg-green">
                <h4>Total Kas Masuk</h4>
                <h3>Rp <?= number_format($debit, 0, ',', '.'); ?></h3>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card-box bg-red">
                <h4>Total Kas Keluar</h4>
                <h3>Rp <?= number_format($kredit, 0, ',', '.'); ?></h3>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card-box bg-blue">
                <h4>Balance</h4>
                <h3>Rp <?= number_format($balance, 0, ',', '.'); ?></h3>
            </div>
        </div>
    </div>

    <br>

    <div class="card">
        <div class="card-header">
            Grafik Arus Kas
        </div>
        <div class="card-body">
            <canvas id="chartKas"></canvas>
        </div>
    </div>

</div>

<script>
const ctx = document.getElementById('chartKas');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Kas Masuk', 'Kas Keluar'],
        datasets: [{
            label: 'Nominal (Rp)',
            data: [<?= $debit ?>, <?= $kredit ?>],
        }]
    }
});
</script>

</body>
</html>
