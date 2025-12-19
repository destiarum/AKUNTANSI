<?php
session_start();
include "config/database.php";

$id = $_GET['id'];
$data = mysqli_fetch_assoc(mysqli_query(
    $koneksi,
    "SELECT * FROM jurnal_detail WHERE id=$id"
));

if (isset($_POST['simpan'])) {
    $debit = $_POST['debit'];
    $kredit = $_POST['kredit'];

    mysqli_query($koneksi, "
        UPDATE jurnal_detail 
        SET debit='$debit', kredit='$kredit'
        WHERE id=$id
    ");

    header("Location: buku_besar.php");
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Jurnal Detail</title>
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
    </style>
</head>

<body>
    <?php include "sidebar.php"; ?>

    <div class="main">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="mb-0 fw-bold">Edit Jurnal Detail</h3>
                    <a href="buku_besar.php" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Batal
                    </a>
                </div>

                <div class="card p-4">
                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Nominal Debit</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">Rp</span>
                                <input type="number" name="debit" value="<?= $data['debit'] ?>" class="form-control"
                                    required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold small text-muted">Nominal Kredit</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">Rp</span>
                                <input type="number" name="kredit" value="<?= $data['kredit'] ?>" class="form-control"
                                    required>
                            </div>
                        </div>

                        <button name="simpan" class="btn btn-primary w-100 fw-bold py-2">
                            <i class="fas fa-save me-2"></i> Simpan Perubahan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>