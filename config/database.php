<?php
$host = "localhost";     // Server database
$user = "root";          // Username phpMyAdmin
$pass = "";              // Password phpMyAdmin (kosong kalau XAMPP)
$db   = "akuntansi";  // Nama database yang kamu buat

// Koneksi
$koneksi = mysqli_connect($host, $user, $pass, $db);

// Cek koneksi
if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
