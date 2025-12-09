<?php
include "config/database.php";

if (!isset($_GET['kode_akun'])) {
    header("Location: akun.php");
    exit;
}

$kode_akun = $_GET['kode_akun'];

// Ambil id akun
$result = mysqli_query($koneksi, "SELECT id FROM akun WHERE kode_akun='$kode_akun'");
$row = mysqli_fetch_assoc($result);
$akun_id = $row['id'];

// Cek apakah ada jurnal_detail
$cek = mysqli_query($koneksi, "SELECT * FROM jurnal_detail WHERE akun_id=$akun_id");
if(mysqli_num_rows($cek) > 0){
    echo "<script>alert('Akun ini masih digunakan di jurnal, tidak bisa dihapus'); window.location='akun.php';</script>";
    exit;
}

// Hapus akun
mysqli_query($koneksi, "DELETE FROM akun WHERE id=$akun_id");

header("Location: akun.php");
exit;
?>
