<?php
session_start();
include "config/database.php";

// Proteksi akses
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: tambah_akun.php?error=Akses tidak diizinkan");
    exit;
}

// Ambil data dari form
$kode_akun    = $_POST['kode_akun'];
$nama_akun    = $_POST['nama_akun'];
$tipe_akun    = $_POST['tipe_akun'];
$saldo_normal = $_POST['saldo_normal'];
$deskripsi    = $_POST['deskripsi'];
$nominal      = $_POST['nominal'] ?? 0;

// Validasi: kode akun tidak boleh duplikat
$cek = mysqli_query($koneksi, "SELECT kode_akun FROM akun WHERE kode_akun='$kode_akun'");
if (!$cek) {
    die("Query gagal: " . mysqli_error($koneksi));
}
if (mysqli_num_rows($cek) > 0) {
    header("Location: tambah_akun.php?error=Kode akun sudah ada");
    exit;
}

// Insert ke database
$query = mysqli_query($koneksi, "
    INSERT INTO akun (kode_akun, nama_akun, tipe_akun, saldo_normal, deskripsi, nominal)
    VALUES ('$kode_akun', '$nama_akun', '$tipe_akun', '$saldo_normal', '$deskripsi', '$nominal')
");

if ($query) {
    header("Location: akun.php?msg=added");
    exit;
} else {
    die("Gagal menambah akun: " . mysqli_error($koneksi));
}
