<?php
session_start();
include "config/database.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php?error=Akses+tidak+sah");
    exit;
}

$username = mysqli_real_escape_string($koneksi, $_POST['username']);
$password = $_POST['password']; // jangan mysqli_real_escape untuk password karena kita bandingkan

// ambil user
$res = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username'");
$user = mysqli_fetch_assoc($res);

if (!$user) {
    header("Location: login.php?error=Username+tidak+ditemukan");
    exit;
}

$stored = $user['password'];

// 1) Jika stored terlihat seperti password_hash (bcrypt)
if (strpos($stored, '$2y$') === 0 || strpos($stored, '$argon2') === 0) {
    if (password_verify($password, $stored)) {
        // berhasil
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        header("Location: dashboard.php");
        exit;
    }
    header("Location: login.php?error=Password+salah");
    exit;
}

// 2) Jika stored 32 chars → kemungkinan MD5
if (strlen($stored) === 32 && ctype_xdigit($stored)) {
    if (md5($password) === $stored) {
        // migrasi: ubah ke password_hash agar lebih aman
        $newhash = password_hash($password, PASSWORD_DEFAULT);
        mysqli_query($koneksi, "UPDATE users SET password='".mysqli_real_escape_string($koneksi,$newhash)."' WHERE username='$username'");

        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        header("Location: dashboard.php");
        exit;
    }
    header("Location: login.php?error=Password+salah");
    exit;
}

// 3) Kalau tersimpan plain text (tidak disarankan)
if ($password === $stored) {
    // migrasi ke password_hash
    $newhash = password_hash($password, PASSWORD_DEFAULT);
    mysqli_query($koneksi, "UPDATE users SET password='".mysqli_real_escape_string($koneksi,$newhash)."' WHERE username='$username'");

    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    header("Location: dashboard.php");
    exit;
}

// default: gagal
header("Location: login.php?error=Password+salah");
exit;
?>
