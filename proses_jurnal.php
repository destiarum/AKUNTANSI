<?php
session_start();
include "config/database.php";

$tanggal = $_POST['tanggal'];
$jenis_transaksi = $_POST['jenis_transaksi'];
$deskripsi = $_POST['deskripsi'];

$akun1 = $_POST['akun1'];
$akun2 = $_POST['akun2'];

$debit1 = $_POST['debit1'];
$kredit1 = $_POST['kredit1'];

$debit2 = $_POST['debit2'];
$kredit2 = $_POST['kredit2'];

$totalDebit = $debit1 + $debit2;
$totalKredit = $kredit1 + $kredit2;

if ($totalDebit != $totalKredit) {
    die("<script>alert('Total Debit dan Kredit HARUS sama!'); history.back();</script>");
}

mysqli_query($koneksi, "INSERT INTO jurnal (tanggal, jenis_transaksi, deskripsi) 
VALUES ('$tanggal','$jenis_transaksi','$deskripsi')");

$id_jurnal = mysqli_insert_id($koneksi); 

mysqli_query($koneksi, "INSERT INTO jurnal_detail (id_jurnal, kode_akun, debit, kredit) 
VALUES ('$id_jurnal','$akun1','$debit1','$kredit1')");

mysqli_query($koneksi, "INSERT INTO jurnal_detail (id_jurnal, kode_akun, debit, kredit) 
VALUES ('$id_jurnal','$akun2','$debit2','$kredit2')");

header("Location: jurnal.php?msg=success");
?>
