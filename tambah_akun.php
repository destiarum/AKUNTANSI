<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Tambah Akun</title>
<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f4f6f9;
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
}

/* CARD FORM */
.card {
    width: 480px;
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

h2.card-title {
    text-align: center;
    color: #083EA8;
    margin-bottom: 25px;
    font-size: 24px;
    font-weight: 700;
}

label {
    font-weight: 600;
    margin-top: 10px;
    display: block;
}

input, select, textarea {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 14px;
    transition: 0.3s;
}

input:focus, select:focus, textarea:focus {
    border-color: #083EA8;
    outline: none;
    box-shadow: 0 0 6px rgba(8,62,168,0.2);
}

.currency {
    display: flex;
    align-items: center;
    border: 1px solid #ccc;
    border-radius: 5px;
}

.currency span {
    padding: 10px;
    background: #e8e8e8;
    border-right: 1px solid #ccc;
    font-weight: bold;
}

.currency input {
    border: none;
    width: 100%;
    padding-left: 10px;
}

.submit-btn {
    width: 100%;
    padding: 12px;
    background: #083EA8;
    color: white;
    border: none;
    font-size: 16px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
    transition: 0.3s;
}

.submit-btn:hover {
    background: #062f80;
}

.error {
    color: red;
    text-align: center;
    margin-bottom: 15px;
}
</style>
</head>
<body>

<div class="card">
    <h2 class="card-title">Tambah Akun Baru</h2>

    <?php if (isset($_GET['error'])): ?>
        <div class="error"><?= htmlspecialchars($_GET['error']); ?></div>
    <?php endif; ?>

    <form action="proses_tambah_akun.php" method="POST">
        <label>Kode Akun (Pilih Akun Besar)</label>
        <select name="kode_akun" required>
            <option value="">-- Pilih Kode Akun --</option>
            <option value="1000">1000 - Aset</option>
            <option value="2000">2000 - Liabilitas</option>
            <option value="3000">3000 - Ekuitas</option>
            <option value="4000">4000 - Pendapatan</option>
            <option value="5000">5000 - Beban</option>
            <option value="6000">6000 - Lain-lain</option>
        </select>

        <label>Nama Akun</label>
        <input type="text" name="nama_akun" required>

        <label>Tipe Akun</label>
        <select name="tipe_akun" required>
            <option value="Aset">Aset</option>
            <option value="Liabilitas">Liabilitas</option>
            <option value="Ekuitas">Ekuitas</option>
            <option value="Pendapatan">Pendapatan</option>
            <option value="Beban">Beban</option>
        </select>

        <label>Saldo Normal</label>
        <select name="saldo_normal" required>
            <option value="Debit">Debit</option>
            <option value="Kredit">Kredit</option>
        </select>

        <label>Deskripsi</label>
        <textarea name="deskripsi" rows="2"></textarea>

        <label>Saldo Awal (Opsional)</label>
        <div class="currency">
            <span>Rp</span>
            <input type="number" name="nominal" value="0" min="0">
        </div>

        <button type="submit" class="submit-btn">Simpan Akun</button>
    </form>
</div>

</body>
</html>
