<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
include "config/database.php";

/* =========================
   FUNGSI HITUNG SALDO
========================= */
function get_saldo($koneksi, $akun_id)
{
    // 1. Ambil Total Transaksi
    $q = mysqli_query($koneksi, "
        SELECT SUM(debit) AS debit, SUM(kredit) AS kredit
        FROM jurnal_detail
        WHERE akun_id = $akun_id
    ");
    $r = mysqli_fetch_assoc($q);
    $trx_debit = $r['debit'] ?? 0;
    $trx_kredit = $r['kredit'] ?? 0;

    // 2. Ambil Saldo Awal (Nominal) dari Master Akun
    $qAkun = mysqli_query($koneksi, "SELECT nominal, saldo_normal, tipe_akun FROM akun WHERE id = $akun_id");
    $rAkun = mysqli_fetch_assoc($qAkun);
    $nominal = $rAkun['nominal'] ?? 0;
    $saldo_normal = $rAkun['saldo_normal'] ?? 'Debit';
    $tipe = $rAkun['tipe_akun'];

    // 3. Gabungkan (Khusus Akun Neraca: Aset, Liabilitas, Ekuitas)
    // Akun Laba Rugi (Pendapatan/Beban) tidak punya saldo awal di laporan ini (kecuali ada filter tahun, tapi defaultnya no).
    $final_debit = $trx_debit;
    $final_kredit = $trx_kredit;

    if (in_array($tipe, ['Aset', 'Liabilitas', 'Ekuitas'])) {
        if ($saldo_normal == 'Debit') {
            $final_debit += $nominal;
        } else {
            $final_kredit += $nominal;
        }
    }

    // Return Net Debit balance
    return $final_debit - $final_kredit;
}

function total_saldo_by_name($koneksi, $nama_akun)
{
    // Menggunakan LIKE agar lebih fleksibel jika ada spasi/case berbeda, 
    // tapi sebaiknya exact match jika nama akun konsisten.
    $nama_akun_safe = mysqli_real_escape_string($koneksi, $nama_akun);
    $query = mysqli_query($koneksi, "SELECT id FROM akun WHERE nama_akun = '$nama_akun_safe'");
    $total = 0;
    while ($r = mysqli_fetch_assoc($query)) {
        $total += get_saldo($koneksi, $r['id']);
    }
    return $total;
}

function get_accounts_by_type($koneksi, $tipe_akun, $exclude_names = [])
{
    $query = mysqli_query($koneksi, "SELECT id, nama_akun FROM akun WHERE tipe_akun='$tipe_akun'");
    $total = 0;
    $akun_list = [];
    while ($r = mysqli_fetch_assoc($query)) {
        if (in_array($r['nama_akun'], $exclude_names))
            continue;

        $saldo = get_saldo($koneksi, $r['id']);
        if ($saldo != 0) {
            $akun_list[] = ['nama' => $r['nama_akun'], 'saldo' => $saldo];
            $total += $saldo;
        }
    }
    return ['total' => $total, 'akun' => $akun_list];
}

/* =========================
   1. HITUNG KPrPJ (MANUFACTURING)
========================= */
// A. BAHAN BAKU
$bb_awal = total_saldo_by_name($koneksi, "Persediaan bahan baku awal");
$bb_beli = total_saldo_by_name($koneksi, "Pembelian bahan baku langsung");
$bb_akhir = total_saldo_by_name($koneksi, "Persediaan bahan baku akhir"); // Biasanya kredit di jurnal penyesuaian, tapi saldo akun aset tetap debit. Kita asumsikan input manual/adjust men-set saldo ini. 

// Jika BB Akhir adalah saldo aset, maka pengurangannya logika akuntansi: BTUD - Akhir = Pemakaian.
// Di sistem sederhana ini, kita ambil saldo buku besar. 

$pemakaian_bb = ($bb_awal + $bb_beli) - $bb_akhir;

// B. TENAGA KERJA LANGSUNG
$tkl = total_saldo_by_name($koneksi, "Kos tenaga kerja langsung");

// C. OVERHEAD PABRIK (BOP) list
$bop_accounts = [
    "Kos tenaga kerja tidak langsung pabrik",
    "Kos bahan baku tidak langsung pabrik",
    "Kos utilitas pabrik",
    "Kos penyusutan peralatan pabrik",
    "Kos pemeliharaan perbaikan pabrik",
    "Kos penyusutan mesin pabrik"
];

$total_bop = 0;
$bop_details = [];
foreach ($bop_accounts as $name) {
    $val = total_saldo_by_name($koneksi, $name);
    $total_bop += $val;
    $bop_details[] = ['nama' => $name, 'saldo' => $val];
}

// TOTAL BIAYA PRODUKSI
$total_biaya_produksi = $pemakaian_bb + $tkl + $total_bop;

// D. PDP (Persediaan Dalam Proses)
$pdp_awal = total_saldo_by_name($koneksi, "PDP awal");
$pdp_akhir = total_saldo_by_name($koneksi, "PDP akhir");

// HASIL KPrPJ
$kprpj = $total_biaya_produksi + $pdp_awal - $pdp_akhir;


/* =========================
   2. HITUNG LABA RUGI
========================= */
// A. PENJUALAN
$penjualan = total_saldo_by_name($koneksi, "Penjualan"); // Saldo normal Kredit (negatif di get_saldo), kita perlu absolutkan atau balik logic.
// Logic get_saldo = Debit - Kredit. Untuk Pendapatan (Saldo Kredit), hasilnya minus.
// Agar mudah, kita balik tanda untuk Pendapatan & Ekuitas jika hasilnya minus.
$penjualan = -1 * total_saldo_by_name($koneksi, "Penjualan");
$pot_penjualan = total_saldo_by_name($koneksi, "Potongan penjualan"); // Saldo normal Debit
$penjualan_bersih = $penjualan - $pot_penjualan;

// B. HPP (COGS)
$produk_jadi_awal = total_saldo_by_name($koneksi, "Persediaan produk jadi awal");
$produk_jadi_akhir = total_saldo_by_name($koneksi, "Persediaan produk jadi akhir");

$barang_siap_jual = $produk_jadi_awal + $kprpj;
$hpp = $barang_siap_jual - $produk_jadi_akhir;

// C. LABA BRUTO
$laba_bruto = $penjualan_bersih - $hpp;

// D. BEBAN OPERASIONAL
// Ambil semua beban KECUALI yang sudah masuk BOP/TKL/Pembelian
$exclude_beban = array_merge($bop_accounts, [
    "Kos tenaga kerja langsung",
    "Pembelian bahan baku langsung",
    "Potongan penjualan",
    "Pajak penghasilan" // Kita pisah pajaknya
]);

$beban_ops_data = get_accounts_by_type($koneksi, "Beban", $exclude_beban);
$total_beban_ops = $beban_ops_data['total'];

// E. LABA BERSIH OPERASI (EBIT)
$laba_bersih_sebelum_pajak = $laba_bruto - $total_beban_ops;

// F. PAJAK
// User minta 15% manual atau akun. Kita coba cari akun dulu, kalau 0 hitung manual?
// Sesuai gambar: "Pajak penghasilan (15% dari ...)"
// Kita hitung manual saja sesuai request user image.
$pajak = 0.15 * $laba_bersih_sebelum_pajak;
if ($pajak < 0)
    $pajak = 0; // Jika rugi, pajak 0 (asumsi simplified)

$laba_bersih_setelah_pajak = $laba_bersih_sebelum_pajak - $pajak;
$laba_rugi = $laba_bersih_setelah_pajak; // Untuk link ke Ekuitas


/* =========================
   3. DATA LAIN (EKUITAS, NERACA, DLL)
========================= */
// PERUBAHAN EKUITAS
// Note: Saldo Ekuitas normal Kredit, get_saldo return Debit-Kredit (Minus). Kita -1 kan.
// FIX: Saldo Awal Modal Saham ambil dari 'nominal' di tabel akun, bukan transaksi.
$qModal = mysqli_query($koneksi, "SELECT nominal FROM akun WHERE nama_akun LIKE 'Modal Saham%' LIMIT 1");
$rModal = mysqli_fetch_assoc($qModal);
$modal_awal = $rModal['nominal'] ?? 0;

$setoran_modal = -1 * total_saldo_by_name($koneksi, "Setoran Modal");
// Jika akun tidak ditemukan (0), logic aman.
$modal_akhir = $modal_awal + $setoran_modal;

$saldo_laba_awal = 0; // User request: No opening balance for retained earnings.
$dividen = total_saldo_by_name($koneksi, "Dividen"); // Debit
$saldo_laba_akhir = $saldo_laba_awal + $laba_rugi - $dividen;
$total_ekuitas = $modal_akhir + $saldo_laba_akhir;

// NERACA
$aktiva = get_accounts_by_type($koneksi, "Aset"); // Logic Debit normal (+)
$liabilitas_data = get_accounts_by_type($koneksi, "Liabilitas");
$liabilitas = -1 * $liabilitas_data['total']; // Logic Kredit normal (-), jadi dikali -1 biar positif tampilnya
$total_pasiva = $liabilitas + $total_ekuitas;

// ARUS KAS
$saldo_kas_awal = total_saldo_by_name($koneksi, "Kas Awal Periode");

// ARUS KAS (METODE TIDAK LANGSUNG / INDIRECT)
// 1. Aktivitas Operasi
// Start from Net Income
$arus_op_laba = $laba_rugi;

// Add back Non-Cash Expenses (Depreciation)
// Cari semua akun 'Beban Penyusutan'
$qDep = mysqli_query($koneksi, "SELECT id FROM akun WHERE nama_akun LIKE 'Beban Penyusutan%'");
$arus_op_depresiasi = 0;
while ($rD = mysqli_fetch_assoc($qDep)) {
    // Beban is normally Debit. We add it back.
    // get_saldo returns (Debit - Kredit).
    // Note: get_saldo now includes Nominal (Opening Balance), but Expense usually has 0 opening unless 2024 special case.
    // Ideally we want PURE transaction movement for the period.
    // Let's make a quick helper for Pure Transaction Movement here.
    $qTrx = mysqli_query($koneksi, "SELECT SUM(debit) d, SUM(kredit) k FROM jurnal_detail WHERE akun_id={$rD['id']}");
    $rTrx = mysqli_fetch_assoc($qTrx);
    $arus_op_depresiasi += ($rTrx['d'] - $rTrx['k']);
}

// Changes in Working Capital (Current Assets & Liabilities)
// Aset Lancar (Non-Kas): Piutang, Persediaan, Perlengkapan
// Kenaikan Aset = Arus Kas Keluar (-)
// Penurunan Aset = Arus Kas Masuk (+)
function get_trx_net_change($koneksi, $keyword)
{
    // Return (Debit - Kredit) movement
    $q = mysqli_query($koneksi, "SELECT id FROM akun WHERE nama_akun LIKE '%$keyword%'");
    $net = 0;
    while ($r = mysqli_fetch_assoc($q)) {
        $qTrx = mysqli_query($koneksi, "SELECT SUM(debit) d, SUM(kredit) k FROM jurnal_detail WHERE akun_id={$r['id']}");
        $row = mysqli_fetch_assoc($qTrx);
        $net += ($row['d'] - $row['k']);
    }
    return $net;
}

$chg_piutang = get_trx_net_change($koneksi, "Piutang");
$chg_persediaan = get_trx_net_change($koneksi, "Persediaan");
// Perlengkapan, Sewa Dibayar Dimuka, dll? Asumsikan Piutang & Persediaan major components.

// Liabilitas Lancar: Utang Usaha, Utang Gaji, Utang Pajak
// Kenaikan Liabilitas = Arus Kas Masuk (+)
// Penurunan Liabilitas = Arus Kas Keluar (-)
// Liabilitas normal Kredit. get_trx_net_change returns Debit-Kredit.
// So if Debit > Kredit (run down debt), net is Positive. Convert to Outflow (-).
// If Debit < Kredit (more debt), net is Negative. Convert to Inflow (+).
// Basically: -1 * (Debit - Kredit) = Kredit - Debit.
$chg_utang = get_trx_net_change($koneksi, "Utang Usaha");

// Total Operasi
// Rumus: Laba + Depresiasi - KenaikanAset + KenaikanLiabilitas
$arus_kas_operasi_total = $arus_op_laba + $arus_op_depresiasi - $chg_piutang - $chg_persediaan + (-1 * $chg_utang);


// 2. Aktivitas Investasi
// Pembelian/Penjualan Aset Tetap (Peralatan, Mesin, Gedung, Tanah, Kendaraan)
// Belanja Modal (Capex) = Debit movement (Purchase).
// Sale = Kredit movement.
// Net Debit = Outflow.
$invest_keywords = ['Peralatan', 'Mesin', 'Gedung', 'Tanah', 'Kendaraan', 'Inventaris'];
$arus_invest_net = 0;
foreach ($invest_keywords as $kw) {
    $arus_invest_net += get_trx_net_change($koneksi, $kw);
}
// Jika Net Debit (+), berarti keluar uang. Jadi dikali -1.
$arus_kas_investasi_total = -1 * $arus_invest_net;


// 3. Aktivitas Pendanaan
// Utang Bank, Modal Saham, Dividen
$chg_utang_bank = get_trx_net_change($koneksi, "Utang Bank"); // Liab: Kredit - Debit is inflow
$chg_modal = get_trx_net_change($koneksi, "Modal"); // Equity: Kredit - Debit is inflow
$chg_dividen = get_trx_net_change($koneksi, "Dividen"); // Equity withdraw: Debit is outflow

// Inflows
$flow_utang = -1 * $chg_utang_bank;
$flow_modal = -1 * $chg_modal;
// Outflows
// Dividen normally Debit (+), so outflow is minus.
$flow_dividen = -1 * $chg_dividen;

$arus_kas_pendanaan_total = $flow_utang + $flow_modal + $flow_dividen;

// Final
$saldo_kas_akhir = $saldo_kas_awal + $arus_kas_operasi_total + $arus_kas_investasi_total + $arus_kas_pendanaan_total;

?>
<!DOCTYPE html>
<html>

<head>
    <title>Laporan Keuangan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            margin-bottom: 30px;
        }

        .text-end {
            text-align: right
        }

        .table {
            margin-bottom: 0;
        }

        .table td {
            padding: 10px 15px;
            vertical-align: middle;
            border-bottom: 1px solid #f0f2f5;
            color: #555;
            font-size: 0.95rem;
        }

        .table-secondary td {
            background-color: #f8f9fa;
            color: #2c3e50;
            font-size: 1rem;
            border-top: 2px solid #344767;
            font-weight: 700;
        }

        .sub-header {
            background-color: #e8eaf6;
            color: #1a237e;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        /* Nav Pills Custom */
        .nav-pills .nav-link {
            color: #344767;
            background: white;
            margin-right: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            font-weight: 600;
            padding: 10px 20px;
            border: 1px solid #f0f2f5;
            transition: all 0.3s;
        }

        .nav-pills .nav-link:hover {
            transform: translateY(-2px);
            background: #f8f9fa;
        }

        .nav-pills .nav-link.active {
            background: linear-gradient(135deg, #1a237e 0%, #283593 100%);
            color: white;
            box-shadow: 0 4px 10px rgba(26, 35, 126, 0.3);
            border: none;
        }

        .tab-content {
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <?php include "sidebar.php"; ?>

    <div class="main">
        <div class="mb-4">
            <h3 class="mb-2 fw-bold">Laporan Keuangan</h3>
            <p class="text-muted mb-4">Ringkasan performa keuangan manufaktur secara real-time.</p>

            <!-- NAV PILLS -->
            <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="pills-laba-tab" data-bs-toggle="pill"
                        data-bs-target="#pills-laba"><i class="fas fa-file-invoice-dollar me-2"></i>Laba Rugi</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="pills-ekuitas-tab" data-bs-toggle="pill"
                        data-bs-target="#pills-ekuitas"><i class="fas fa-chart-line me-2"></i>Perubahan Ekuitas</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="pills-neraca-tab" data-bs-toggle="pill"
                        data-bs-target="#pills-neraca"><i class="fas fa-balance-scale me-2"></i>Neraca</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="pills-kas-tab" data-bs-toggle="pill" data-bs-target="#pills-kas"><i
                            class="fas fa-money-bill-wave me-2"></i>Arus Kas</button>
                </li>
            </ul>
        </div>

        <div class="tab-content" id="pills-tabContent">

            <!-- TAB 1: LABA RUGI (MANUFACTURING) -->
            <div class="tab-pane fade show active" id="pills-laba">
                <div class="card">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                            <h5 class="fw-bold text-dark mb-0">Laporan Laba Rugi</h5>
                            <button class="btn btn-primary btn-sm rounded-pill px-3" data-bs-toggle="modal"
                                data-bs-target="#modalKprpj">
                                <i class="fas fa-eye me-2"></i>Lihat Detail KPrPJ
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <!-- PENDAPATAN -->
                                <tr>
                                    <td>Penjualan</td>
                                    <td class="text-end"><?= number_format($penjualan, 0, ',', '.') ?></td>
                                </tr>
                                <tr>
                                    <td>(-) Potongan Penjualan</td>
                                    <td class="text-end text-danger">(<?= number_format($pot_penjualan, 0, ',', '.') ?>)
                                    </td>
                                </tr>
                                <tr class="fw-bold bg-light">
                                    <td>Penjualan Bersih</td>
                                    <td class="text-end"><?= number_format($penjualan_bersih, 0, ',', '.') ?></td>
                                </tr>

                                <!-- HPP SECTION -->
                                <tr>
                                    <td colspan="2" class="py-3"></td>
                                </tr>
                                <tr class="sub-header">
                                    <td colspan="2">HARGA POKOK PENJUALAN</td>
                                </tr>

                                <tr>
                                    <td>Persediaan Produk Jadi Awal</td>
                                    <td class="text-end"><?= number_format($produk_jadi_awal, 0, ',', '.') ?></td>
                                </tr>
                                <tr>
                                    <td>(+) KPrPJ (Kos Produksi Produk Jadi)</td>
                                    <td class="text-end"><?= number_format($kprpj, 0, ',', '.') ?></td>
                                </tr>
                                <tr class="fw-bold">
                                    <td>(=) Barang Siap Jual</td>
                                    <td class="text-end"><?= number_format($barang_siap_jual, 0, ',', '.') ?></td>
                                </tr>
                                <tr>
                                    <td>(-) Persediaan Produk Jadi Akhir</td>
                                    <td class="text-end text-danger">
                                        (<?= number_format($produk_jadi_akhir, 0, ',', '.') ?>)</td>
                                </tr>
                                <tr class="fw-bold text-danger">
                                    <td>(-) Beban Pokok Penjualan (HPP)</td>
                                    <td class="text-end">(<?= number_format($hpp, 0, ',', '.') ?>)</td>
                                </tr>

                                <!-- LABA BRUTO -->
                                <tr class="table-secondary">
                                    <td>LABA BRUTO</td>
                                    <td class="text-end"><?= number_format($laba_bruto, 0, ',', '.') ?></td>
                                </tr>

                                <!-- BEBAN OPERASIONAL -->
                                <tr>
                                    <td colspan="2" class="py-3"></td>
                                </tr>
                                <tr class="sub-header">
                                    <td colspan="2">BEBAN OPERASIONAL</td>
                                </tr>

                                <?php foreach ($beban_ops_data['akun'] as $b): ?>
                                    <tr>
                                        <td class="ps-4"><?= $b['nama'] ?></td>
                                        <td class="text-end"><?= number_format($b['saldo'], 0, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach; ?>

                                <tr class="fw-bold text-danger">
                                    <td>(-) Total Beban Operasional</td>
                                    <td class="text-end">(<?= number_format($total_beban_ops, 0, ',', '.') ?>)</td>
                                </tr>

                                <tr class="fw-bold">
                                    <td>Laba Bersih Sebelum Pajak</td>
                                    <td class="text-end"><?= number_format($laba_bersih_sebelum_pajak, 0, ',', '.') ?>
                                    </td>
                                </tr>

                                <tr>
                                    <td>(-) Pajak Penghasilan (15%)</td>
                                    <td class="text-end text-danger">(<?= number_format($pajak, 0, ',', '.') ?>)</td>
                                </tr>

                                <tr class="table-secondary" style="border-top: 3px double #1a237e;">
                                    <td>LABA BERSIH SETELAH PAJAK</td>
                                    <td class="text-end"><?= number_format($laba_bersih_setelah_pajak, 0, ',', '.') ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: EKUITAS -->
            <div class="tab-pane fade" id="pills-ekuitas">
                <div class="card">
                    <div class="card-body p-4">
                        <h5 class="mb-4 fw-bold text-dark border-bottom pb-3">Laporan Perubahan Ekuitas</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <tr class="sub-header">
                                    <td colspan="2">MODAL SAHAM</td>
                                </tr>
                                <tr>
                                    <td>Saldo Awal</td>
                                    <td class="text-end"><?= number_format($modal_awal, 0, ',', '.') ?></td>
                                </tr>
                                <tr>
                                    <td>Setoran Modal</td>
                                    <td class="text-end"><?= number_format($setoran_modal, 0, ',', '.') ?></td>
                                </tr>
                                <tr class="fw-bold">
                                    <td>Saldo Akhir Modal</td>
                                    <td class="text-end"><?= number_format($modal_akhir, 0, ',', '.') ?></td>
                                </tr>

                                <tr class="sub-header">
                                    <td colspan="2">SALDO LABA</td>
                                </tr>
                                <!-- Removed Saldo Awal row as requested -->
                                <tr>
                                    <td>Laba Bersih Tahun Ini</td>
                                    <td class="text-end"><?= number_format($laba_rugi, 0, ',', '.') ?></td>
                                </tr>
                                <tr>
                                    <td>Dividen</td>
                                    <td class="text-end text-danger">(<?= number_format($dividen, 0, ',', '.') ?>)</td>
                                </tr>
                                <tr class="fw-bold">
                                    <td>Saldo Akhir Laba</td>
                                    <td class="text-end"><?= number_format($saldo_laba_akhir, 0, ',', '.') ?></td>
                                </tr>

                                <tr class="table-secondary">
                                    <td>TOTAL EKUITAS</td>
                                    <td class="text-end"><?= number_format($total_ekuitas, 0, ',', '.') ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 3: NERACA -->
            <div class="tab-pane fade" id="pills-neraca">
                <div class="card">
                    <div class="card-body p-4">
                        <h5 class="mb-4 fw-bold text-dark border-bottom pb-3">Laporan Neraca</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <tr class="sub-header">
                                    <td colspan="2">ASET</td>
                                </tr>
                                <?php foreach ($aktiva['akun'] as $a): ?>
                                    <tr>
                                        <td><?= $a['nama'] ?></td>
                                        <td class="text-end"><?= number_format($a['saldo'], 0, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr class="table-secondary">
                                    <td>Total Aset</td>
                                    <td class="text-end"><?= number_format($aktiva['total'], 0, ',', '.') ?></td>
                                </tr>

                                <tr class="sub-header">
                                    <td colspan="2">LIABILITAS & EKUITAS</td>
                                </tr>
                                <?php foreach ($liabilitas_data['akun'] as $l): ?>
                                    <tr>
                                        <td><?= $l['nama'] ?></td>
                                        <td class="text-end"><?= number_format(-$l['saldo'], 0, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr>
                                    <td>Total Ekuitas</td>
                                    <td class="text-end"><?= number_format($total_ekuitas, 0, ',', '.') ?></td>
                                </tr>
                                <tr class="table-secondary">
                                    <td>Total Liabilitas + Ekuitas</td>
                                    <td class="text-end">
                                        <?= number_format((-1 * $liabilitas_data['total']) + $total_ekuitas, 0, ',', '.') ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 4: ARUS KAS -->
            <div class="tab-pane fade" id="pills-kas">
                <div class="card">
                    <div class="card-body p-4">
                        <h5 class="mb-4 fw-bold text-dark border-bottom pb-3">Laporan Arus Kas</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <!-- ACTIVITY 1: OPERATING -->
                                <tr class="sub-header">
                                    <td colspan="2">AKTIVITAS OPERASI</td>
                                </tr>
                                <tr>
                                    <td>Laba Bersih</td>
                                    <td class="text-end fw-bold"><?= number_format($arus_op_laba, 0, ',', '.') ?></td>
                                </tr>
                                <tr>
                                    <td>(+) Penyusutan</td>
                                    <td class="text-end"><?= number_format($arus_op_depresiasi, 0, ',', '.') ?></td>
                                </tr>
                                
                                <tr class="text-muted text-xs bg-light">
                                    <td colspan="2"><small><i>Perubahan Modal Kerja:</i></small></td>
                                </tr>
                                <tr>
                                    <td>(Kenaikan)/Penurunan Piutang</td>
                                    <td class="text-end"><?= number_format(-$chg_piutang, 0, ',', '.') ?></td>
                                </tr>
                                <tr>
                                    <td>(Kenaikan)/Penurunan Persediaan</td>
                                    <td class="text-end"><?= number_format(-$chg_persediaan, 0, ',', '.') ?></td>
                                </tr>
                                <tr>
                                    <td>Kenaikan/(Penurunan) Utang Usaha</td>
                                    <td class="text-end"><?= number_format(-$chg_utang, 0, ',', '.') ?></td>
                                </tr>

                                <tr class="fw-bold table-success">
                                    <td>Arus Kas Bersih dari Aktivitas Operasi</td>
                                    <td class="text-end"><?= number_format($arus_kas_operasi_total, 0, ',', '.') ?></td>
                                </tr>

                                <!-- ACTIVITY 2: INVESTING -->
                                <tr><td colspan="2"></td></tr>
                                <tr class="sub-header">
                                    <td colspan="2">AKTIVITAS INVESTASI</td>
                                </tr>
                                <tr>
                                    <td>Perolehan Aset Tetap (Capex)</td>
                                    <td class="text-end"><?= number_format($arus_kas_investasi_total, 0, ',', '.') ?></td>
                                </tr>
                                <tr class="fw-bold table-warning">
                                    <td>Arus Kas Bersih dari Aktivitas Investasi</td>
                                    <td class="text-end"><?= number_format($arus_kas_investasi_total, 0, ',', '.') ?></td>
                                </tr>

                                <!-- ACTIVITY 3: FINANCING -->
                                <tr><td colspan="2"></td></tr>
                                <tr class="sub-header">
                                    <td colspan="2">AKTIVITAS PENDANAAN</td>
                                </tr>
                                <tr>
                                    <td>Penerimaan Utang Bank</td>
                                    <td class="text-end"><?= number_format($flow_utang, 0, ',', '.') ?></td>
                                </tr>
                                <tr>
                                    <td>Setoran Modal</td>
                                    <td class="text-end"><?= number_format($flow_modal, 0, ',', '.') ?></td>
                                </tr>
                                <tr>
                                    <td>Pembayaran Dividen</td>
                                    <td class="text-end"><?= number_format($flow_dividen, 0, ',', '.') ?></td>
                                </tr>
                                <tr class="fw-bold table-info">
                                    <td>Arus Kas Bersih dari Aktivitas Pendanaan</td>
                                    <td class="text-end"><?= number_format($arus_kas_pendanaan_total, 0, ',', '.') ?></td>
                                </tr>

                                <!-- SUMMARY -->
                                <tr><td colspan="2"></td></tr>
                                <tr class="bg-light">
                                    <td>Saldo Kas Awal Periode</td>
                                    <td class="text-end"><?= number_format($saldo_kas_awal, 0, ',', '.') ?></td>
                                </tr>
                                <tr class="table-secondary" style="border-top: 2px solid #344767;">
                                    <td>SALDO KAS AKHIR</td>
                                    <td class="text-end fw-bolder fs-5"><?= number_format($saldo_kas_akhir, 0, ',', '.') ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- MODAL KPrPJ -->
    <div class="modal fade" id="modalKprpj" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold">Detail KPrPJ (Kos Produksi Produk Jadi)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <table class="table table-sm table-borderless">
                        <tr class="fw-bold text-primary">
                            <td colspan="2">Bahan Baku</td>
                        </tr>
                        <tr>
                            <td>Persediaan Bahan Baku Awal</td>
                            <td class="text-end"><?= number_format($bb_awal, 0, ',', '.') ?></td>
                        </tr>
                        <tr>
                            <td>(+) Pembelian Bahan Baku</td>
                            <td class="text-end"><?= number_format($bb_beli, 0, ',', '.') ?></td>
                        </tr>
                        <tr>
                            <td>(-) Persediaan Bahan Baku Akhir</td>
                            <td class="text-end text-danger">(<?= number_format($bb_akhir, 0, ',', '.') ?>)</td>
                        </tr>
                        <tr class="fw-bold border-top border-dark">
                            <td class="ps-4">Total Pemakaian Bahan Baku</td>
                            <td class="text-end"><?= number_format($pemakaian_bb, 0, ',', '.') ?></td>
                        </tr>

                        <!-- TKL -->
                        <tr>
                            <td colspan="2" class="py-2"></td>
                        </tr>
                        <tr class="fw-bold text-primary">
                            <td colspan="2">Tenaga Kerja Langsung</td>
                        </tr>
                        <tr>
                            <td>Kos Tenaga Kerja Langsung</td>
                            <td class="text-end"><?= number_format($tkl, 0, ',', '.') ?></td>
                        </tr>

                        <!-- BOP -->
                        <tr>
                            <td colspan="2" class="py-2"></td>
                        </tr>
                        <tr class="fw-bold text-primary">
                            <td colspan="2">Overhead Pabrik (BOP)</td>
                        </tr>
                        <?php foreach ($bop_details as $bop): ?>
                            <tr>
                                <td><?= $bop['nama'] ?></td>
                                <td class="text-end"><?= number_format($bop['saldo'], 0, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>

                        <tr class="fw-bold border-top border-dark">
                            <td class="ps-4">Total Biaya Produksi</td>
                            <td class="text-end"><?= number_format($total_biaya_produksi, 0, ',', '.') ?></td>
                        </tr>

                        <!-- PDP -->
                        <tr>
                            <td colspan="2" class="py-2"></td>
                        </tr>
                        <tr>
                            <td>(+) PDP Awal</td>
                            <td class="text-end"><?= number_format($pdp_awal, 0, ',', '.') ?></td>
                        </tr>
                        <tr>
                            <td>(-) PDP Akhir</td>
                            <td class="text-end text-danger">(<?= number_format($pdp_akhir, 0, ',', '.') ?>)</td>
                        </tr>

                        <tr class="table-primary fw-bold border-top border-dark">
                            <td>TOTAL KPrPJ</td>
                            <td class="text-end"><?= number_format($kprpj, 0, ',', '.') ?></td>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle (Required for Tabs & Modal) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>