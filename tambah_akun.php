<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Tambah Akun</title>
<style>
body{font-family:'Segoe UI';background:#f4f6f9;display:flex;justify-content:center;align-items:center;height:100vh}
.card{width:450px;background:white;padding:25px;border-radius:10px;box-shadow:0 4px 12px rgba(0,0,0,.1)}
label{font-weight:600}
input,select,textarea{width:100%;padding:10px;margin-bottom:15px;border:1px solid #ccc;border-radius:6px}
button{width:100%;background:#083EA8;color:white;padding:12px;border:none;border-radius:6px;font-weight:bold}
</style>
</head>

<body>
<div class="card">
<h3 style="text-align:center">Tambah Akun</h3>

<form action="proses_tambah_akun.php" method="POST">

<label>Kode Induk</label>
<select name="kode_induk" required>
<option value="">-- Pilih --</option>
<option value="1000">1000 - Aset</option>
<option value="2000">2000 - Liabilitas</option>
<option value="3000">3000 - Ekuitas</option>
<option value="4000">4000 - Pendapatan</option>
<option value="5000">5000 - Beban</option>
</select>

<label>Kode Sub</label>
<select name="kode_sub" required>
<option value="">-- Pilih --</option>
<option value="">-- ASET --</option>
<option value="100">100 - Aset Lancar</option>
<option value="200">200 - Aset Tidak Lancar</option>
<option value="">-- LIABILITAS  --</option>
<option value="100">100 - Liabilitas Jangka Pendek</option>
<option value="200">200 - Liabilitas Jangka Panjang </option>
<option value="">-- EKUITAS --</option>
<option value="100">100 - Modal</option>
<option value="200">200 - Pendapatan Ditahan</option>
<option value="">-- PENDAPATAN --</option>
<option value="100">100 - Pendapatan Operasional</option>
<option value="200">200 - Pendapatan Non Operasional</option>   
<option value="">-- BEBAN --</option>
<option value="100">100 - Harga Pokok Penjualan</option>
<option value="200">200 - Beban Operasional</option>
<option value="300">300 - Pendapatan Non Operasional</option>   
</select>

<label>Nama Akun</label>
<input type="text" name="nama_akun" required>

<label>Tipe Akun</label>
<select name="tipe_akun" required>
<option>Aset</option>
<option>Liabilitas</option>
<option>Ekuitas</option>
<option>Pendapatan</option>
<option>Beban</option>
</select>

<label>Saldo Normal</label>
<select name="saldo_normal" required>
<option>Debit</option>
<option>Kredit</option>
</select>

<!-- /<label>Deskripsi</label>
<textarea name="deskripsi"></textarea>/* -->
<label>Saldo Awal</label>
<div style="display:flex;align-items:center;gap:5px">
    <span>Rp</span>
    <input type="text" id="rupiah" autocomplete="off">
    <input type="hidden" name="nominal" id="nominal">
</div>
<script>
const rupiah = document.getElementById('rupiah');
const nominal = document.getElementById('nominal');

rupiah.addEventListener('input', function () {
    let angka = this.value.replace(/[^0-9]/g, '');
    nominal.value = angka;
    this.value = new Intl.NumberFormat('id-ID').format(angka);
});
</script>



<button type="submit">Simpan Akun</button>
</form>
</div>
</body>
</html>
