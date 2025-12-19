<?php
session_start();
include "config/database.php";

$id = $_GET['id'];
mysqli_query($koneksi, "DELETE FROM jurnal_detail WHERE id=$id");

header("Location: buku_besar.php");
exit;
