<?php
include "config/database.php";

echo "<h2>Content of Jurnal Detail (Transactions)</h2>";
$q = mysqli_query($koneksi, "SELECT jd.*, a.nama_akun, j.tanggal FROM jurnal_detail jd JOIN akun a ON jd.akun_id=a.id JOIN jurnal j ON jd.jurnal_id=j.id");
if (mysqli_num_rows($q) > 0) {
    echo "<table border=1><tr><th>ID</th><th>Date</th><th>Account</th><th>Debit</th><th>Credit</th></tr>";
    while ($r = mysqli_fetch_assoc($q)) {
        echo "<tr>";
        echo "<td>{$r['id']}</td>";
        echo "<td>{$r['tanggal']}</td>";
        echo "<td>{$r['nama_akun']}</td>";
        echo "<td>{$r['debit']}</td>";
        echo "<td>{$r['kredit']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No transactions found.";
}

echo "<h2>Content of Akun (Opening Balances > 0)</h2>";
$q2 = mysqli_query($koneksi, "SELECT * FROM akun WHERE nominal > 0");
if (mysqli_num_rows($q2) > 0) {
    echo "<table border=1><tr><th>ID</th><th>Kode</th><th>Nama</th><th>Nominal</th></tr>";
    while ($r = mysqli_fetch_assoc($q2)) {
        echo "<tr>";
        echo "<td>{$r['id']}</td>";
        echo "<td>{$r['kode_final']}</td>";
        echo "<td>{$r['nama_akun']}</td>";
        echo "<td>{$r['nominal']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No opening balances found.";
}
?>