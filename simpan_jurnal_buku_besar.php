<?php
session_start();
if(!isset($_SESSION['username'])){
    header("Location: login.php");
    exit();
}

include 'config/database.php';

// Ambil data form
$tgl = $_POST['tgl'];
$deskripsi = $_POST['deskripsi'];

// Debit
$akun_debit = $_POST['akun_debit'];         // array
$desc_debit = $_POST['desc_debit'];
$nominal_debit = $_POST['nominal_debit'];

// Kredit
$akun_kredit = $_POST['akun_kredit'];       // array
$desc_kredit = $_POST['desc_kredit'];
$nominal_kredit = $_POST['nominal_kredit'];

// Hitung total debit & kredit
$totalDebit = array_sum($nominal_debit);
$totalKredit = array_sum($nominal_kredit);

if($totalDebit != $totalKredit){
    die("<script>alert('Total Debit dan Kredit harus sama!'); history.back();</script>");
}

// Buat kode bukti otomatis
$kode_bukti = 'JRN'.date('YmdHis');

// Simpan header jurnal
mysqli_query($koneksi, "INSERT INTO jurnal (tanggal,kode_bukti,keterangan) VALUES ('$tgl','$kode_bukti','$deskripsi')");
$jurnal_id = mysqli_insert_id($koneksi);

// Simpan detail debit
for($i=0;$i<count($akun_debit);$i++){
    $akun_id = $akun_debit[$i];
    $nominal = $nominal_debit[$i];

    mysqli_query($koneksi, "INSERT INTO jurnal_detail (jurnal_id,akun_id,debit,kredit) VALUES ($jurnal_id,$akun_id,$nominal,0)");
}

// Simpan detail kredit
for($i=0;$i<count($akun_kredit);$i++){
    $akun_id = $akun_kredit[$i];
    $nominal = $nominal_kredit[$i];

    mysqli_query($koneksi, "INSERT INTO jurnal_detail (jurnal_id,akun_id,debit,kredit) VALUES ($jurnal_id,$akun_id,0,$nominal)");
}

header("Location:jurnal_buku_besar_addrow.php?success=Transaksi berhasil disimpan");
exit(
);;
?>  