<?php
include "config/database.php";

// Helper function to get raw sums
function get_raw_sums($koneksi)
{
    echo "<h3>Raw Account Balances</h3>";
    echo "<table border='1' cellpadding='5' style='border-collapse:collapse; width:100%'>";
    echo "<thead><tr style='background:#ccc'>
            <th>ID</th>
            <th>Kode</th>
            <th>Nama Akun</th>
            <th>Tipe</th>
            <th>Total Debit</th>
            <th>Total Kredit</th>
            <th>Net (D-K)</th>
          </tr></thead><tbody>";

    $q = mysqli_query($koneksi, "
        SELECT 
            a.id, a.kode_akun, a.nama_akun, a.tipe_akun,
            COALESCE(SUM(jd.debit), 0) as total_debit,
            COALESCE(SUM(jd.kredit), 0) as total_kredit
        FROM akun a
        LEFT JOIN jurnal_detail jd ON a.id = jd.akun_id
        GROUP BY a.id, a.kode_akun, a.nama_akun, a.tipe_akun
        ORDER BY a.kode_akun ASC
    ");

    while ($row = mysqli_fetch_assoc($q)) {
        $net = $row['total_debit'] - $row['total_kredit'];
        $style = ($net < 0) ? "color:red" : "color:green";
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['kode_akun']}</td>";
        echo "<td>{$row['nama_akun']}</td>";
        echo "<td>{$row['tipe_akun']}</td>";
        echo "<td align='right'>" . number_format($row['total_debit'], 0) . "</td>";
        echo "<td align='right'>" . number_format($row['total_kredit'], 0) . "</td>";
        echo "<td align='right' style='$style'>" . number_format($net, 0) . "</td>";
        echo "</tr>";
    }
    echo "</tbody></table>";
}

?>
<!DOCTYPE html>
<html>

<head>
    <title>Debug Akuntansi</title>
    <style>
        body {
            font-family: sans-serif;
            padding: 20px;
        }
    </style>
</head>

<body>
    <h2>Debug Dashboard Data</h2>
    <p>This page shows raw sums from the database to diagnose negative values.</p>
    <a href="dashboard.php">Back to Dashboard</a>
    <hr>
    <?php get_raw_sums($koneksi); ?>
</body>

</html>