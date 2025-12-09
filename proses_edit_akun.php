<?php
session_start();
include "config/database.php";

// CEK LOGIN
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// HANYA BOLEH POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Akses ditolak!");
}

// AMBIL DATA
$id         = $_POST['id'] ?? '';
$kode_akun  = $_POST['kode_akun'] ?? '';
$nama_akun  = $_POST['nama_akun'] ?? '';
$jenis      = $_POST['jenis'] ?? '';

// VALIDASI — CEGAH DATA KEHAPUS
if (
    trim($id) == '' ||
    trim($kode_akun) == '' ||
    trim($nama_akun) == '' ||
    trim($jenis) == ''
) {
    die("Data tidak lengkap! Pastikan semua form terisi.");
}

// QUERY UPDATE — SUDAH BENAR
$query = mysqli_query($koneksi, "
    UPDATE akun SET
        kode_akun = '$kode_akun',
        nama_akun = '$nama_akun',
        jenis     = '$jenis'
    WHERE id = '$id'
");

// CEK BERHASIL / GAGAL
if ($query) {
    header("Location: akun.php?msg=updated");
    exit;
} else {
    echo "Gagal update akun: " . mysqli_error($koneksi);
}
?>
