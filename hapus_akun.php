<?php
session_start();
include "config/database.php";

if (!isset($_GET['id'])) {
    header("Location: akun.php");
    exit;
}

$id = intval($_GET['id']);

/* ======================
   CEK JURNAL
====================== */
$cek = mysqli_query($koneksi, "
    SELECT 1 FROM jurnal_detail WHERE akun_id = $id
");

if (mysqli_num_rows($cek) > 0) {
    echo "<script>
        alert('Akun ini masih digunakan di jurnal, tidak bisa dihapus');
        window.location='akun.php';
    </script>";
    exit;
}

/* ======================
   HAPUS AKUN
====================== */
mysqli_query($koneksi, "DELETE FROM akun WHERE id = $id");

header("Location: akun.php?delete=success");
exit;
