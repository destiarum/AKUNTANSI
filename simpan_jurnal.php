<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include 'config/database.php';

// Ambil data form
$tgl = $_POST['tgl'];

if ($tgl < '2025-01-01') {
    die("<script>alert('Gagal: Tanggal transaksi minimal 1 Januari 2025. Periode sebelumnya adalah Saldo Awal.'); history.back();</script>");
}

$deskripsi = $_POST['deskripsi'];
$akun_debit = $_POST['akun_debit'];
$desc_debit = $_POST['desc_debit'];
$nominal_debit = $_POST['nominal_debit'];
$akun_kredit = $_POST['akun_kredit'];
$desc_kredit = $_POST['desc_kredit'];
$nominal_kredit = $_POST['nominal_kredit'];

// Hitung total
$totalDebit = array_sum($nominal_debit);
$totalKredit = array_sum($nominal_kredit);

if ($totalDebit != $totalKredit) {
    die("<script>alert('Total Debit dan Kredit harus sama!'); history.back();</script>");
}

// Simpan header jurnal
$kode_bukti = 'JRN' . date('YmdHis');
mysqli_query($koneksi, "INSERT INTO jurnal (tanggal,kode_bukti,keterangan) VALUES ('$tgl','$kode_bukti','$deskripsi')");
$jurnal_id = mysqli_insert_id($koneksi);

// Simpan detail debit
for ($i = 0; $i < count($akun_debit); $i++) {
    $akun_id = $akun_debit[$i];
    $nominal = $nominal_debit[$i];
    mysqli_query($koneksi, "INSERT INTO jurnal_detail (jurnal_id,akun_id,debit,kredit) VALUES ($jurnal_id,$akun_id,$nominal,0)");
}

// Simpan detail kredit
for ($i = 0; $i < count($akun_kredit); $i++) {
    $akun_id = $akun_kredit[$i];
    $nominal = $nominal_kredit[$i];
    mysqli_query($koneksi, "INSERT INTO jurnal_detail (jurnal_id,akun_id,debit,kredit) VALUES ($jurnal_id,$akun_id,0,$nominal)");
}

// Alert sukses dan redirect kembali ke form kosong
echo "<!DOCTYPE html>
<html lang='id'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Processing...</title>
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8f9fa; }
    </style>
</head>
<body>
    <script>
        Swal.fire({
            title: 'Berhasil!',
            text: 'Transaksi berhasil disimpan.',
            icon: 'success',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'OK'
        }).then((result) => {
            window.location.href = 'jurnal_umum.php';
        });
    </script>
</body>
</html>";
exit();
