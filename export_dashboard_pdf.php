<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/fpdf/fpdf.php/fpdf184/fpdf.php';
include "config/database.php";

if (!class_exists('FPDF')) {
    die('FPDF tidak ter-load. Cek folder /fpdf/fpdf.php');
}

/* ========================
   FILTER
======================== */
$bulan = $_GET['bulan'] ?? '';
$tahun = $_GET['tahun'] ?? date('Y');

/* ========================
   FUNGSI SALDO
======================== */
function get_saldo($koneksi, $kode, $bulan, $tahun, $tipe_akun = '')
{
    $w = "";
    if ($bulan != '')
        $w .= " AND MONTH(j.tanggal)='$bulan'";
    if ($tahun != '')
        $w .= " AND YEAR(j.tanggal)='$tahun'";

    $q = mysqli_query($koneksi, "
        SELECT SUM(jd.debit)d,SUM(jd.kredit)k
        FROM jurnal_detail jd
        JOIN jurnal j ON jd.jurnal_id=j.id
        JOIN akun a ON jd.akun_id=a.id
        WHERE a.kode_final='$kode' $w
    ");
    $r = mysqli_fetch_assoc($q);
    $debit = $r['d'] ?? 0;
    $kredit = $r['k'] ?? 0;

    // Handling Khusus 2024: Hanya ambil Saldo Awal (abaikan transaksi)
    if ($tahun == '2024') {
        $debit = 0;
        $kredit = 0;
    }

    // 2. Opening Balance & Normal
    $qAkun = mysqli_query($koneksi, "SELECT nominal, saldo_normal FROM akun WHERE kode_final='$kode'");
    $rAkun = mysqli_fetch_assoc($qAkun);
    $nominal = $rAkun['nominal'] ?? 0;
    $saldo_normal_akun = $rAkun['saldo_normal'] ?? 'Debit';

    $saldoAwal = 0;
    if ($tahun == '2024') {
        $saldoAwal = $nominal;
    } elseif (in_array($tipe_akun, ['Aset', 'Liabilitas', 'Ekuitas'])) {
        $saldoAwal = $nominal;
    }

    // 3. Calculate Balance Magnitude
    if ($saldo_normal_akun == 'Debit') {
        $balance_magnitude = ($saldoAwal + $debit) - $kredit;
    } else {
        $balance_magnitude = ($saldoAwal + $kredit) - $debit;
    }

    // 4. Determine Sign
    $group_normal = in_array($tipe_akun, ['Aset', 'Beban']) ? 'Debit' : 'Kredit';

    if ($saldo_normal_akun == $group_normal) {
        return $balance_magnitude;
    } else {
        return -$balance_magnitude;
    }
}

function total_tipe($k, $tipe, $b, $t)
{
    $tot = 0;
    $q = mysqli_query($k, "SELECT kode_final, tipe_akun FROM akun WHERE tipe_akun='$tipe'");
    while ($r = mysqli_fetch_assoc($q)) {
        $tot += get_saldo($k, $r['kode_final'], $b, $t, $r['tipe_akun']);
    }
    return $tot;
}

/* ========================
   HITUNG
======================== */
$pendapatan = total_tipe($koneksi, 'Pendapatan', $bulan, $tahun);
$beban = total_tipe($koneksi, 'Beban', $bulan, $tahun);
$laba = $pendapatan - $beban;
$aset = total_tipe($koneksi, 'Aset', $bulan, $tahun);
$liab = total_tipe($koneksi, 'Liabilitas', $bulan, $tahun);
$ekui = total_tipe($koneksi, 'Ekuitas', $bulan, $tahun);

/* ========================
   PDF
======================== */
$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'DASHBOARD KEUANGAN', 0, 1, 'C');

$pdf->SetFont('Arial', '', 11);
$pdf->Ln(5);
$pdf->Cell(0, 8, "Periode : " . ($bulan ? $bulan : 'Semua') . " / " . ($tahun ? $tahun : 'Semua'), 0, 1);
$pdf->Ln(3);

$pdf->Cell(0, 8, "Pendapatan : Rp " . number_format($pendapatan, 0, ',', '.'), 0, 1);
$pdf->Cell(0, 8, "Beban      : Rp " . number_format($beban, 0, ',', '.'), 0, 1);
$pdf->Cell(0, 8, "Laba Bersih: Rp " . number_format($laba, 0, ',', '.'), 0, 1);

$pdf->Ln(5);
$pdf->Cell(0, 8, "Aset        : Rp " . number_format($aset, 0, ',', '.'), 0, 1);
$pdf->Cell(0, 8, "Liabilitas  : Rp " . number_format($liab, 0, ',', '.'), 0, 1);
$pdf->Cell(0, 8, "Ekuitas     : Rp " . number_format($ekui, 0, ',', '.'), 0, 1);

$pdf->Output('I', 'Dashboard-Keuangan.pdf');

