<?php
session_start();
include "config/database.php";

/* =======================
   MAPPING KODE
======================= */
$map_induk = [
    '1000' => '1', // Aset
    '2000' => '2', // Liabilitas
    '3000' => '3', // Ekuitas
    '4000' => '4', // Pendapatan
    '5000' => '5'  // Beban
];

$map_sub = [
    '100' => '10',
    '200' => '20',
    '300' => '30',
    '400' => '40'
];

/* =======================
   INPUT
======================= */
$kode_induk   = $_POST['kode_induk'];
$kode_sub     = $_POST['kode_sub'];
$nama_akun    = $_POST['nama_akun'];
$tipe_akun    = $_POST['tipe_akun'];
$saldo_normal = $_POST['saldo_normal'];
$deskripsi    = $_POST['deskripsi'];
$nominal      = $_POST['nominal'];

/* =======================
   PREFIX (PER KATEGORI)
======================= */
$prefix =
    $map_induk[$kode_induk] .
    $map_sub[$kode_sub];   // contoh: 110, 120, 210

/* =======================
   AUTO INCREMENT PER PREFIX
======================= */
$q = mysqli_query($koneksi, "
    SELECT MAX(RIGHT(kode_final,1)) AS last
    FROM akun
    WHERE kode_final LIKE '$prefix%'
");

$d = mysqli_fetch_assoc($q);
$urut = ($d['last'] ?? 0) + 1;

if ($urut > 9) {
    die("❌ Maksimal akun untuk kategori ini sudah penuh");
}

/* =======================
   FINAL KODE
======================= */
$kode_akun  = $urut;
$kode_final = $prefix . $urut;

/* =======================
   INSERT
======================= */
$sql = "INSERT INTO akun
(kode_induk, kode_sub, kode_akun, kode_final,
 nama_akun, tipe_akun, saldo_normal, nominal, deskripsi)
VALUES (
 '$kode_induk', '$kode_sub', '$kode_akun', '$kode_final',
 '$nama_akun', '$tipe_akun', '$saldo_normal', '$nominal', '$deskripsi'
)";

mysqli_query($koneksi, $sql);
header("Location: akun.php");
exit;
