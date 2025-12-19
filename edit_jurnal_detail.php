<?php
session_start();
include "config/database.php";

$id = $_GET['id'];
$data = mysqli_fetch_assoc(mysqli_query(
    $koneksi, "SELECT * FROM jurnal_detail WHERE id=$id"
));

if (isset($_POST['simpan'])) {
    $debit  = $_POST['debit'];
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
<title>Edit Jurnal</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">
<h4>Edit Jurnal Detail</h4>

<form method="post">
    <label>Debit</label>
    <input type="number" name="debit" value="<?= $data['debit'] ?>" class="form-control">

    <label>Kredit</label>
    <input type="number" name="kredit" value="<?= $data['kredit'] ?>" class="form-control">

    <button name="simpan" class="btn btn-primary mt-3">Simpan</button>
    <a href="buku_besar.php" class="btn btn-secondary mt-3">Batal</a>
</form>
</body>
</html>
