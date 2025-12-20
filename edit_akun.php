<?php
include 'config/database.php';

if (!isset($_GET['id'])) {
    header("Location: akun.php");
    exit;
}

$id = intval($_GET['id']);
$q = mysqli_query($koneksi, "SELECT * FROM akun WHERE id=$id");
$data = mysqli_fetch_assoc($q);

if (!$data) {
    header("Location: akun.php");
    exit;
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Akun</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        body {
            background: #f8f9fa;
            font-family: 'Outfit', sans-serif;
            color: #344767;
        }

        .main {
            padding: 30px;
        }

        .card {
            background: white;
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .btn-success-custom {
            background: linear-gradient(135deg, #4caf50 0%, #81c784 100%);
            border: none;
            color: white;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(76, 175, 80, 0.3);
            transition: transform 0.2s;
        }

        .btn-success-custom:hover {
            transform: translateY(-2px);
            color: white;
            box-shadow: 0 6px 8px rgba(76, 175, 80, 0.4);
        }
    </style>
</head>

<body>

    <?php include "sidebar.php"; ?>

    <div class="main">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="mb-1 fw-bold">Edit Akun</h3>
                <p class="text-muted mb-0">Perbarui informasi akun.</p>
            </div>
            <a href="akun.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i> Kembali
            </a>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card p-4">
                    <form action="proses_edit_akun.php" method="POST">

                        <input type="hidden" name="id" value="<?= $data['id']; ?>">

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Kode Akun (Final)</label>
                            <input type="text" class="form-control bg-light" value="<?= $data['kode_final']; ?>"
                                readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Nama Akun</label>
                            <input type="text" name="nama_akun" class="form-control" value="<?= $data['nama_akun']; ?>"
                                required>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">Tipe Akun</label>
                                <select name="tipe_akun" class="form-select">
                                    <option <?= $data['tipe_akun'] == "Aset" ? "selected" : "" ?>>Aset</option>
                                    <option <?= $data['tipe_akun'] == "Liabilitas" ? "selected" : "" ?>>Liabilitas</option>
                                    <option <?= $data['tipe_akun'] == "Ekuitas" ? "selected" : "" ?>>Ekuitas</option>
                                    <option <?= $data['tipe_akun'] == "Pendapatan" ? "selected" : "" ?>>Pendapatan</option>
                                    <option <?= $data['tipe_akun'] == "Beban" ? "selected" : "" ?>>Beban</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">Saldo Normal</label>
                                <select name="saldo_normal" class="form-select">
                                    <option <?= $data['saldo_normal'] == "Debit" ? "selected" : "" ?>>Debit</option>
                                    <option <?= $data['saldo_normal'] == "Kredit" ? "selected" : "" ?>>Kredit</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Saldo Awal</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">Rp</span>
                                <input type="number" name="nominal" class="form-control"
                                    value="<?= $data['nominal']; ?>">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold small text-muted">Deskripsi</label>
                            <textarea name="deskripsi" class="form-control"
                                rows="3"><?= $data['deskripsi']; ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-success-custom w-100">
                            <i class="fas fa-save me-2"></i> Update Akun
                        </button>

                    </form>
                </div>
            </div>
        </div>
    </div>

</body>

</html>