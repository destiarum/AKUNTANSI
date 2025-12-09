<?php
session_start();
if(!isset($_SESSION['username'])){
    header("Location: login.php");
    exit();
}

include 'config/database.php';

// Ambil daftar akun
$akun = mysqli_query($koneksi, "SELECT * FROM akun ORDER BY kode_akun ASC");
if(!$akun) die("Query akun gagal: ".mysqli_error($koneksi));

// Buat array JS dari akun
$akunArray = [];
mysqli_data_seek($akun,0);
while($row = mysqli_fetch_assoc($akun)){
    $akunArray[] = ['id'=>$row['id'],'kode'=>$row['kode_akun'],'nama'=>$row['nama_akun']];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Input Jurnal Umum</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
/* BODY & LAYOUT */
body { font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color:#EEEDED; margin:0; padding:0; }
.wrapper { max-width:1000px; margin:auto; padding:20px; margin-left:250px; }

/* SIDEBAR */
.sidebar {
    width: 220px;
    height: 100vh;
    background: #2c3e50;
    color: white;
    position: fixed;
    left: 0; top: 0;
    padding: 20px;
}
.sidebar h2 { font-size:22px; margin-top:0; font-weight:700; }
.sidebar hr { border:1px solid rgba(255,255,255,0.2); margin:15px 0; }
.sidebar ul { list-style:none; padding:0; margin-top:20px; }
.sidebar li { margin-bottom:10px; }
.sidebar a {
    color:white; text-decoration:none; display:block; padding:8px 12px; border-radius:6px;
    transition:0.3s;
}
.sidebar a:hover, .sidebar a.active { background-color:#D11716; color:white; }

/* JUDUL */
h2 { text-align:center; color:#083EA8; margin-bottom:30px; font-size:28px; font-weight:700; }

/* LABEL & INPUT */
label { font-weight:600; display:block; margin-bottom:6px; color:#333; }
input[type="date"], textarea { width:100%; padding:10px; border-radius:6px; border:1px solid #ccc; margin-bottom:15px; transition:0.3s; font-size:15px;}
input[type="date"]:focus, textarea:focus { border-color:#083EA8; outline:none; box-shadow:0 0 8px rgba(8,62,168,0.3); }

/* TABEL CONTAINER */
.container { display:flex; gap:20px; flex-wrap:wrap; justify-content:space-between; margin-top:20px; }
table { border-collapse: collapse; width:100%; background-color:white; border-radius:10px; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.1);}
th { padding:12px; color:white; font-size:16px; }
#table_debit th { background-color:#D11716; }
#table_kredit th { background-color:#083EA8; }
td { padding:8px; text-align:center; font-size:14px; border-bottom:1px solid #eee; vertical-align:middle;}
input[type="number"], select, input[type="text"] { width:90%; padding:6px; border-radius:5px; border:1px solid #ccc; font-size:14px; transition:0.3s;}
input[type="number"]:focus, select:focus, input[type="text"]:focus { border-color:#083EA8; outline:none; box-shadow:0 0 6px rgba(8,62,168,0.3);}
.total { font-weight:bold; background-color:#f9f9f9; font-size:15px; }

/* Warna Nominal */
.debit { color:red; font-weight:bold; }
.kredit { color:blue; font-weight:bold; }

/* BUTTONS */
button.add, button.delete { margin-top:10px; padding:6px 12px; border:none; border-radius:6px; cursor:pointer; font-weight:600; color:white; transition:0.3s; font-size:14px;}
button.add.debit { background-color:#D11716;}
button.add.kredit { background-color:#083EA8;}
button.add.debit:hover { background-color:#b01012;}
button.add.kredit:hover { background-color:#062f80;}
button.delete { background-color:#888888; margin-left:5px;}
button.delete:hover { background-color:#555555;}
button.submit { margin-top:30px; padding:12px 30px; border:none; border-radius:8px; background-color:#083EA8; color:white; font-size:16px; cursor:pointer; transition:0.3s;}
button.submit:hover { background-color:#062f80;}
</style>
<script>
// simpan akun ke array JS
const akunOptions = <?php echo json_encode($akunArray); ?>;

// HITUNG TOTAL
function hitungTotal() {
    let debitTotal = 0;
    let kreditTotal = 0;
    document.querySelectorAll('.debit').forEach(input => debitTotal += parseFloat(input.value) || 0);
    document.querySelectorAll('.kredit').forEach(input => kreditTotal += parseFloat(input.value) || 0);
    document.getElementById('total_debit').innerText = debitTotal.toLocaleString();
    document.getElementById('total_kredit').innerText = kreditTotal.toLocaleString();
}

// VALIDASI FORM
function validateForm() {
    let debit = 0;
    let kredit = 0;
    document.querySelectorAll('.debit').forEach(input => debit += parseFloat(input.value) || 0);
    document.querySelectorAll('.kredit').forEach(input => kredit += parseFloat(input.value) || 0);

    if(debit !== kredit){
        alert('Total Debit dan Kredit harus sama!');
        return false;
    }
    return true;
}

// TAMBAH BARIS
function addRow(tableId,type) {
    let table = document.getElementById(tableId);
    let row = table.insertRow(table.rows.length - 1);
    let cell1 = row.insertCell(0);
    let cell2 = row.insertCell(1);
    let cell3 = row.insertCell(2);

    let options = '<option value="">-- Pilih Akun --</option>';
    akunOptions.forEach(a => {
        options += `<option value="${a.id}">${a.kode} - ${a.nama}</option>`;
    });

    cell1.innerHTML = `<select name="akun_${type}[]" required>${options}</select>`;
    cell2.innerHTML = `<input type="text" name="desc_${type}[]">`;
    cell3.innerHTML = `<div style="display:flex; justify-content:center;"><input type="number" name="nominal_${type}[]" class="${type}" value="0" oninput="hitungTotal()" required> <button type="button" class="delete" onclick="deleteRow(this)">Hapus</button></div>`;
}

// HAPUS BARIS
function deleteRow(btn){
    let row = btn.closest('tr');
    row.parentNode.removeChild(row);
    hitungTotal();
}

// SIDEBAR ACTIVE
document.addEventListener("DOMContentLoaded", () => {
    const links = document.querySelectorAll('.sidebar a');
    links.forEach(link => {
        if(link.getAttribute('href') === "jurnal.php") link.classList.add('active');
    });
});
</script>
</head>
<body>
<div class="sidebar">
    <h2>AKUNTANSI</h2>
    <hr>
    <ul>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="akun.php">Data Akun</a></li>
        <li><a href="jurnal.php" class="active">Jurnal</a></li>
        <li><a href="buku_besar.php">Buku Besar</a></li>
        <li><a href="laporan.php">Laporan</a></li>
        <li style="margin-top:40px;"><a href="logout.php">Logout</a></li>
    </ul>
</div>

<div class="wrapper">
<h2>Input Jurnal Umum</h2>

<form action="simpan_jurnal.php" method="POST" onsubmit="return validateForm()">
<label>Tanggal:</label>
<input type="date" name="tgl" required>
<label>Deskripsi:</label>
<textarea name="deskripsi" rows="2" required></textarea>

<div class="container">
    <!-- Debit -->
    <div style="flex:1">
        <table id="table_debit">
            <tr><th colspan="3">DEBIT</th></tr>
            <tr><th>Akun</th><th>Deskripsi</th><th>Nominal</th></tr>
            <tr>
                <td>
                   <select name="akun_debit[]" required>
                        <option value="">-- Pilih Akun --</option>
                        <?php mysqli_data_seek($akun,0); while($row = mysqli_fetch_assoc($akun)){ ?>
                            <option value="<?= $row['id'] ?>"><?= $row['kode_akun'] ?> - <?= $row['nama_akun'] ?></option>
                        <?php } ?>
                    </select>
                </td>
                <td><input type="text" name="desc_debit[]"></td>
                <td><input type="number" name="nominal_debit[]" class="debit" value="0" oninput="hitungTotal()" required></td>
            </tr>
            <tr>
                <td colspan="2" class="total">TOTAL DEBIT</td>
                <td class="total" id="total_debit">0</td>
            </tr>
        </table>
        <button type="button" class="add debit" onclick="addRow('table_debit','debit')">Tambah Baris Debit</button>
    </div>

    <!-- Kredit -->
    <div style="flex:1">
        <table id="table_kredit">
            <tr><th colspan="3">KREDIT</th></tr>
            <tr><th>Akun</th><th>Deskripsi</th><th>Nominal</th></tr>
            <tr>
                <td>
                   <select name="akun_kredit[]" required>
                        <option value="">-- Pilih Akun --</option>
                        <?php mysqli_data_seek($akun,0); while($row = mysqli_fetch_assoc($akun)){ ?>
                            <option value="<?= $row['id'] ?>"><?= $row['kode_akun'] ?> - <?= $row['nama_akun'] ?></option>
                        <?php } ?>
                    </select>
                </td>
                <td><input type="text" name="desc_kredit[]"></td>
                <td><input type="number" name="nominal_kredit[]" class="kredit" value="0" oninput="hitungTotal()" required></td>
            </tr>
            <tr>
                <td colspan="2" class="total">TOTAL KREDIT</td>
                <td class="total" id="total_kredit">0</td>
            </tr>
        </table>
        <button type="button" class="add kredit" onclick="addRow('table_kredit','kredit')">Tambah Baris Kredit</button>
    </div>
</div>

<button type="submit" class="submit">SIMPAN JURNAL</button>
</form>
</div>
</body>
</html>
