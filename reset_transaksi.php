<?php
include "config/database.php";

// Disable foreign key checks to allow truncation
mysqli_query($koneksi, "SET FOREIGN_KEY_CHECKS = 0");

$t1 = mysqli_query($koneksi, "TRUNCATE TABLE jurnal_detail");
$t2 = mysqli_query($koneksi, "TRUNCATE TABLE jurnal");

mysqli_query($koneksi, "SET FOREIGN_KEY_CHECKS = 1");

if ($t1 && $t2) {
    echo "<h1>✅ Sukses!</h1>";
    echo "<p>Semua data transaksi (Jurnal & Detail) telah dihapus bersih.</p>";
    echo "<p>Sekarang Dashboard Anda seharusnya bersih.</p>";
    echo "<a href='dashboard.php'>Kembali ke Dashboard</a>";
} else {
    echo "<h1>❌ Gagal!</h1>";
    echo "<p>" . mysqli_error($koneksi) . "</p>";
}
?>