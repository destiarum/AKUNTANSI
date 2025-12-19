<?php
error_reporting(E_ALL);
ini_set('display_errors',1);

require_once __DIR__ . '/fpdf/fpdf.php';
include "config/database.php";

if (!class_exists('FPDF')) {
    die('FPDF tidak ter-load. Cek folder /fpdf/fpdf.php');
}

/* ========================
   FILTER
======================== */
$bulan = $_GET['bulan'] ?? '';
$tahun = $_GET['tahun'] ?? '';

/* ========================
   FUNGSI SALDO
======================== */
function get_saldo($koneksi,$kode,$bulan,$tahun){
    $w="";
    if($bulan!='') $w.=" AND MONTH(j.tanggal)='$bulan'";
    if($tahun!='') $w.=" AND YEAR(j.tanggal)='$tahun'";

    $q=mysqli_query($koneksi,"
        SELECT SUM(jd.debit)d,SUM(jd.kredit)k
        FROM jurnal_detail jd
        JOIN jurnal j ON jd.jurnal_id=j.id
        JOIN akun a ON jd.akun_id=a.id
        WHERE a.kode_akun='$kode' $w
    ");
    $r=mysqli_fetch_assoc($q);
    return ($r['d']??0)-($r['k']??0);
}

function total_tipe($k,$tipe,$b,$t){
    $tot=0;
    $q=mysqli_query($k,"SELECT kode_akun FROM akun WHERE tipe_akun='$tipe'");
    while($r=mysqli_fetch_assoc($q)){
        $tot+=get_saldo($k,$r['kode_akun'],$b,$t);
    }
    return $tot;
}

/* ========================
   HITUNG
======================== */
$pendapatan = total_tipe($koneksi,'Pendapatan',$bulan,$tahun);
$beban      = total_tipe($koneksi,'Beban',$bulan,$tahun);
$laba       = $pendapatan-$beban;
$aset       = total_tipe($koneksi,'Aset',$bulan,$tahun);
$liab       = total_tipe($koneksi,'Liabilitas',$bulan,$tahun);
$ekui       = total_tipe($koneksi,'Ekuitas',$bulan,$tahun);

/* ========================
   PDF
======================== */
$pdf = new FPDF('P','mm','A4');
$pdf->AddPage();
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,'DASHBOARD KEUANGAN',0,1,'C');

$pdf->SetFont('Arial','',11);
$pdf->Ln(5);
$pdf->Cell(0,8,"Periode : ".($bulan?$bulan:'Semua')." / ".($tahun?$tahun:'Semua'),0,1);
$pdf->Ln(3);

$pdf->Cell(0,8,"Pendapatan : Rp ".number_format($pendapatan,0,',','.'),0,1);
$pdf->Cell(0,8,"Beban      : Rp ".number_format($beban,0,',','.'),0,1);
$pdf->Cell(0,8,"Laba Bersih: Rp ".number_format($laba,0,',','.'),0,1);

$pdf->Ln(5);
$pdf->Cell(0,8,"Aset        : Rp ".number_format($aset,0,',','.'),0,1);
$pdf->Cell(0,8,"Liabilitas  : Rp ".number_format($liab,0,',','.'),0,1);
$pdf->Cell(0,8,"Ekuitas     : Rp ".number_format($ekui,0,',','.'),0,1);

$pdf->Output('I','Dashboard-Keuangan.pdf');

