<?php
include 'config/database.php';

if (!isset($_GET['id'])) {
    header("Location: akun.php");
    exit;
}

$id = intval($_GET['id']);
$q  = mysqli_query($koneksi, "SELECT * FROM akun WHERE id=$id");
$data = mysqli_fetch_assoc($q);

if (!$data) {
    header("Location: akun.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Akun</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body { background:#f6f8fa; }
        .sidebar {
            width:230px;
            height:100vh;
            background:#233446;
            color:white;
            position:fixed;
            padding:25px;
        }
        .sidebar a {
            color:#e5e5e5;
            text-decoration:none;
            display:block;
            margin:8px 0;
        }
        .content {
            margin-left:250px;
            padding:40px;
        }
        .form-box {
            max-width:550px;
            background:white;
            padding:30px;
            border-radius:10px;
        }
    </style>
</head>

<body>

<div class="sidebar">
    <h4>AKUNTANSI</h4>
    <hr>
    <a href="dashboard.php">Dashboard</a>
    <a href="akun.php">Data Akun</a>
    <a href="jurnal.php">Jurnal</a>
    <a href="buku_besar.php">Buku Besar</a>
    <a href="laporan.php">Laporan</a>
</div>

<div class="content">
    <h3>Edit Akun</h3>

    <div class="form-box">
        <form action="proses_edit_akun.php" method="POST">

            <input type="hidden" name="id" value="<?= $data['id']; ?>">

            <label class="fw-bold">Kode Akun</label>
            <input type="text" class="form-control mb-3"
                   value="<?= $data['kode_final']; ?>" readonly>

            <label class="fw-bold">Nama Akun</label>
            <input type="text" name="nama_akun" class="form-control mb-3"
                   value="<?= $data['nama_akun']; ?>" required>

            <label class="fw-bold">Tipe Akun</label>
            <select name="tipe_akun" class="form-select mb-3">
                <option <?= $data['tipe_akun']=="Aset"?"selected":"" ?>>Aset</option>
                <option <?= $data['tipe_akun']=="Liabilitas"?"selected":"" ?>>Liabilitas</option>
                <option <?= $data['tipe_akun']=="Ekuitas"?"selected":"" ?>>Ekuitas</option>
                <option <?= $data['tipe_akun']=="Pendapatan"?"selected":"" ?>>Pendapatan</option>
                <option <?= $data['tipe_akun']=="Beban"?"selected":"" ?>>Beban</option>
            </select>

            <label class="fw-bold">Saldo Normal</label>
            <select name="saldo_normal" class="form-select mb-3">
                <option <?= $data['saldo_normal']=="Debit"?"selected":"" ?>>Debit</option>
                <option <?= $data['saldo_normal']=="Kredit"?"selected":"" ?>>Kredit</option>
            </select>

            <label class="fw-bold">Saldo Awal</label>
            <input type="number" name="nominal" class="form-control mb-3"
                   value="<?= $data['nominal']; ?>">

            <label class="fw-bold">Deskripsi</label>
            <textarea name="deskripsi" class="form-control mb-3"
            rows="3"><?= $data['deskripsi']; ?></textarea>

            <button type="submit" class="btn btn-success w-100">
                Update
            </button>

        </form>
    </div>
</div>

</body>
</html>
