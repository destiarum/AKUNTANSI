<?php
session_start();
include "config/database.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id           = $_POST['id'];
    $nama_akun    = $_POST['nama_akun'];
    $tipe_akun    = $_POST['tipe_akun'];
    $saldo_normal = $_POST['saldo_normal'];
    $nominal      = $_POST['nominal'];
    $deskripsi    = $_POST['deskripsi'];

    $sql = "UPDATE akun SET
        nama_akun='$nama_akun',
        tipe_akun='$tipe_akun',
        saldo_normal='$saldo_normal',
        nominal='$nominal',
        deskripsi='$deskripsi'
        WHERE id='$id'
    ";

    mysqli_query($koneksi, $sql);
    header("Location: akun.php?update=success");
    exit;
}
