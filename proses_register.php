<?php
session_start();
include "config/database.php";

$username = $_POST['username'];
$password = md5($_POST['password']);
$role     = $_POST['role'];

// Cek apakah username sudah terpakai
$cek = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username'");

if (mysqli_num_rows($cek) > 0) {
    header("Location: register.php?msg=Username sudah digunakan!");
    exit;
}

// Jika aman → simpan user baru
$query = mysqli_query($koneksi, 
    "INSERT INTO users (username, password, role) 
     VALUES ('$username', '$password', '$role')"
);

if ($query) {
    header("Location: register.php?msg=User berhasil dibuat!");
} else {
    header("Location: register.php?msg=Gagal membuat user!");
}
?>
