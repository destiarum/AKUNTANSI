<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
include "config/database.php";

// Ambil filter tipe jika ada
$tipe = $_GET['tipe'] ?? '';
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Besar</title>
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
            overflow: hidden;
            margin-bottom: 20px;
        }

        .card-header-custom {
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .badge-custom {
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.75rem;
        }

        .bg-aset {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .bg-liabilitas {
            background: #ffebee;
            color: #c62828;
        }

        .bg-ekuitas {
            background: #e3f2fd;
            color: #1565c0;
        }

        .bg-pendapatan {
            background: #fff3e0;
            color: #ef6c00;
        }

        .bg-beban {
            background: #f3e5f5;
            color: #7b1fa2;
        }

        .btn-filter {
            border-radius: 20px;
            padding: 6px 16px;
            font-weight: 600;
            font-size: 0.9rem;
            margin-right: 5px;
            margin-bottom: 5px;
            border: 1px solid #e9ecef;
            color: #7b809a;
            background: white;
            transition: all 0.2s;
        }

        .btn-filter:hover,
        .btn-filter.active {
            background: #344767;
            color: white;
            border-color: #344767;
            box-shadow: 0 4px 6px rgba(52, 71, 103, 0.2);
        }

        .table th {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: #fff;
            color: #7b809a;
            padding: 12px;
            border-bottom: 1px solid #e9ecef;
        }

        .table td {
            font-size: 0.875rem;
            vertical-align: middle;
            padding: 12px;
            border-bottom: 1px solid #f0f2f5;
        }

        .debit-val {
            color: #2e7d32;
            font-weight: 600;
        }

        .kredit-val {
            color: #c62828;
            font-weight: 600;
        }

        .saldo-val {
            color: #344767;
            font-weight: 700;
        }

        .saldo-row {
            background: #f8f9fa;
        }
    </style>
</head>

<body>
    <?php include "sidebar.php"; ?>

    <div class="main">
        <div class="mb-4">
            <h3 class="mb-1 fw-bold">Buku Besar</h3>
            <p class="text-muted mb-3">Rincian riwayat transaksi dikelompokkan per akun.</p>

            <div class="d-flex flex-wrap">
                <a href="buku_besar.php" class="btn-filter <?= ($tipe == '') ? 'active' : '' ?>">Semua</a>
                <a href="buku_besar.php?tipe=Aset" class="btn-filter <?= ($tipe == 'Aset') ? 'active' : '' ?>">
                    <i class="fas fa-landmark me-1"></i> Aset
                </a>
                <a href="buku_besar.php?tipe=Liabilitas"
                    class="btn-filter <?= ($tipe == 'Liabilitas') ? 'active' : '' ?>">
                    <i class="fas fa-file-invoice-dollar me-1"></i> Liabilitas
                </a>
                <a href="buku_besar.php?tipe=Ekuitas" class="btn-filter <?= ($tipe == 'Ekuitas') ? 'active' : '' ?>">
                    <i class="fas fa-chart-pie me-1"></i> Ekuitas
                </a>
                <a href="buku_besar.php?tipe=Pendapatan"
                    class="btn-filter <?= ($tipe == 'Pendapatan') ? 'active' : '' ?>">
                    <i class="fas fa-hand-holding-usd me-1"></i> Pendapatan
                </a>
                <a href="buku_besar.php?tipe=Beban" class="btn-filter <?= ($tipe == 'Beban') ? 'active' : '' ?>">
                    <i class="fas fa-money-bill-wave me-1"></i> Beban
                </a>
            </div>
        </div>

        <?php
        // 1. QUERY UNTUK MENGAMBIL DAFTAR AKUN
        if ($tipe != '') {
            $queryAkun = mysqli_query($koneksi, "SELECT * FROM akun WHERE tipe_akun = '$tipe' ORDER BY kode_final ASC");
        } else {
            $queryAkun = mysqli_query($koneksi, "SELECT * FROM akun ORDER BY kode_final ASC");
        }

        // Loop setiap Akun
        while ($akun = mysqli_fetch_assoc($queryAkun)):
            $id_akun = $akun['id'];
            $tipe_akun = $akun['tipe_akun'];

            // Tentukan Posisi Saldo Normal
            $saldoNormal = 'debit';
            if (in_array($tipe_akun, ['Liabilitas', 'Ekuitas', 'Pendapatan'])) {
                $saldoNormal = 'kredit';
            }

            // Badge style
            $badgeColor = 'bg-secondary';
            if ($tipe_akun == 'Aset')
                $badgeColor = 'bg-aset';
            if ($tipe_akun == 'Liabilitas')
                $badgeColor = 'bg-liabilitas';
            if ($tipe_akun == 'Ekuitas')
                $badgeColor = 'bg-ekuitas';
            if ($tipe_akun == 'Pendapatan')
                $badgeColor = 'bg-pendapatan';
            if ($tipe_akun == 'Beban')
                $badgeColor = 'bg-beban';

            // Hitung Saldo Awal
            // Standard: Ambil dari Nominal
            $saldoBerjalan = $akun['nominal'] ?? 0;

            // EXCEPTION: Pendapatan & Beban tidak punya Saldo Awal di Buku Besar
            // (Murni akumulasi transaksi per periode)
            if (in_array($tipe_akun, ['Pendapatan', 'Beban'])) {
                $saldoBerjalan = 0;
            }
            ?>

            <div class="card">
                <div class="card-header-custom">
                    <div class="d-flex align-items-center">
                        <div class="icon-shape bg-light text-primary rounded-circle p-2 me-3 shadow-sm"
                            style="width:40px;height:40px;display:flex;justify-content:center;align-items:center;">
                            <i class="fas fa-book"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold text-dark"><?= $akun['nama_akun'] ?></h6>
                            <span class="text-xs text-muted">Kode: <?= $akun['kode_final'] ?></span>
                        </div>
                    </div>
                    <span class="badge-custom <?= $badgeColor ?>"><?= $tipe_akun ?></span>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th width="15%">Tanggal</th>
                                    <th width="40%">Keterangan</th>
                                    <th class="text-end" width="15%">Debit</th>
                                    <th class="text-end" width="15%">Kredit</th>
                                    <th class="text-end" width="15%">Saldo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="text-muted fst-italic bg-light">
                                    <td colspan="2"><small>Saldo Awal</small></td>
                                    <td class="text-end">-</td>
                                    <td class="text-end">-</td>
                                    <td class="text-end fw-bold"><?= number_format($saldoBerjalan, 0, ',', '.') ?></td>
                                </tr>

                                <?php
                                $qTrans = mysqli_query($koneksi, "
                                    SELECT 
                                        jd.id AS id_detail,
                                        j.tanggal,
                                        j.keterangan,
                                        jd.debit,
                                        jd.kredit
                                    FROM jurnal_detail jd
                                    JOIN jurnal j ON jd.jurnal_id = j.id
                                    WHERE jd.akun_id = '$id_akun'
                                    ORDER BY j.tanggal ASC, j.id ASC
                                ");

                                if (mysqli_num_rows($qTrans) > 0) {
                                    while ($r = mysqli_fetch_assoc($qTrans)) {
                                        // Logic Saldo
                                        if ($saldoNormal == 'debit') {
                                            $saldoBerjalan += ($r['debit'] - $r['kredit']);
                                        } else {
                                            $saldoBerjalan += ($r['kredit'] - $r['debit']);
                                        }
                                        ?>
                                        <tr>
                                            <td><?= date('d M Y', strtotime($r['tanggal'])) ?></td>
                                            <td><?= $r['keterangan'] ?></td>
                                            <td class="text-end debit-val">
                                                <?= ($r['debit'] > 0) ? number_format($r['debit'], 0, ',', '.') : '-' ?>
                                            </td>
                                            <td class="text-end kredit-val">
                                                <?= ($r['kredit'] > 0) ? number_format($r['kredit'], 0, ',', '.') : '-' ?>
                                            </td>
                                            <td class="text-end saldo-val"><?= number_format($saldoBerjalan, 0, ',', '.') ?></td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    // If no transactions, just show current balance (which is opening balance)
                                    // But we already showed opening balance above.
                                    // Maybe just show a row saying "No recent transactions"
                                    echo "<tr><td colspan='5' class='text-center py-4 text-muted small'>Belum ada transaksi periode ini.</td></tr>";
                                }
                                ?>
                            </tbody>
                            <tfoot class="saldo-row">
                                <tr>
                                    <td colspan="4" class="text-end text-sm fw-bold text-uppercase text-secondary">Saldo
                                        Akhir</td>
                                    <td class="text-end fw-bolder text-dark" style="font-size:1.1rem;">
                                        <?= number_format($saldoBerjalan, 0, ',', '.') ?>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

        <?php endwhile; ?>

        <?php if (mysqli_num_rows($queryAkun) == 0): ?>
            <div class="alert alert-light border shadow-sm text-center p-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Tidak ada data akun ditemukan.</h5>
            </div>
        <?php endif; ?>

    </div>
</body>

</html>