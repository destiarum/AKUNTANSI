<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
include "config/database.php";

// Handle Sorting (Default ASC = Terlama di atas)
$sort = isset($_GET['sort']) && $_GET['sort'] == 'desc' ? 'DESC' : 'ASC';
$nextSort = $sort == 'ASC' ? 'desc' : 'asc';
$sortIcon = $sort == 'ASC' ? 'fa-sort-amount-down-alt' : 'fa-sort-amount-up';
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jurnal Umum</title>
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
        }

        /* Table Styling */
        .table thead th {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 2px solid #e9ecef;
            background-color: #f8f9fa;
            color: #7b809a;
            padding: 16px;
            font-weight: 700;
        }

        .table tbody td {
            padding: 14px 16px;
            vertical-align: middle;
            border-bottom: 1px solid #f0f2f5;
            font-size: 0.9rem;
        }

        /* Indentation for Kredit */
        .account-kredit {
            padding-left: 40px !important;
            color: #d32f2f;
            /* Red accent for kredit */
        }

        .account-debit {
            color: #1976d2;
            /* Blue accent for debit */
            font-weight: 500;
        }

        .date-cell {
            font-weight: 600;
            color: #344767;
        }

        .total-row td {
            background-color: #f8f9fa;
            font-weight: 700;
            border-top: 2px solid #344767;
            font-size: 1rem;
        }
    </style>
</head>

<body>
    <?php include "sidebar.php"; ?>

    <div class="main">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="mb-1 fw-bold">Jurnal Umum</h3>
                <p class="text-muted mb-0">Rekap seluruh transaksi keuangan secara kronologis.</p>
            </div>
            <div>
                <a href="?sort=<?= $nextSort ?>" class="btn btn-outline-primary me-2 shadow-sm">
                    <i class="fas <?= $sortIcon ?> me-2"></i> Urutkan Tanggal
                </a>
                <button class="btn btn-light shadow-sm" onclick="window.print()">
                    <i class="fas fa-print me-2"></i> Cetak
                </button>
            </div>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover align-items-center mb-0" style="min-width: 800px;">
                    <thead>
                        <tr>
                            <th class="text-center" width="15%">Tanggal</th>
                            <th width="40%">Akun</th>
                            <th class="text-center" width="10%">Ref</th>
                            <th class="text-end" width="15%">Debit</th>
                            <th class="text-end" width="15%">Kredit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Query menggabungkan tabel jurnal, jurnal_detail, dan akun
                        $query = mysqli_query($koneksi, "
                            SELECT 
                                j.tanggal,
                                j.keterangan AS bukti_transaksi,
                                a.nama_akun,
                                a.kode_final,
                                jd.debit,
                                jd.kredit
                            FROM jurnal_detail jd
                            JOIN jurnal j ON jd.jurnal_id = j.id
                            JOIN akun a ON jd.akun_id = a.id
                            ORDER BY j.tanggal $sort, j.id $sort, jd.kredit ASC
                        ");

                        $prev_tanggal = '';
                        $total_debit = 0;
                        $total_kredit = 0;

                        while ($r = mysqli_fetch_assoc($query)):
                            // Hitung Total
                            $total_debit += $r['debit'];
                            $total_kredit += $r['kredit'];

                            // Style logic
                            $isKredit = $r['kredit'] > 0;
                            $accountClass = $isKredit ? 'account-kredit' : 'account-debit';
                            ?>
                            <tr>
                                <td class="text-center date-cell">
                                    <?= ($r['tanggal'] != $prev_tanggal) ? date('d M Y', strtotime($r['tanggal'])) : '' ?>
                                </td>

                                <td class="<?= $accountClass ?>">
                                    <?= $r['nama_akun'] ?>
                                </td>

                                <td class="text-center text-sm text-muted"><?= $r['kode_final'] ?></td>

                                <td class="text-end fw-bold text-dark">
                                    <?= ($r['debit'] > 0) ? number_format($r['debit'], 0, ',', '.') : '-' ?>
                                </td>
                                <td class="text-end fw-bold text-dark">
                                    <?= ($r['kredit'] > 0) ? number_format($r['kredit'], 0, ',', '.') : '-' ?>
                                </td>
                            </tr>
                            <?php
                            $prev_tanggal = $r['tanggal'];
                        endwhile;
                        ?>
                    </tbody>
                    <tfoot>
                        <tr class="total-row">
                            <td colspan="3" class="text-end text-uppercase">Total</td>
                            <td class="text-end text-primary">Rp <?= number_format($total_debit, 0, ',', '.') ?></td>
                            <td class="text-end text-danger">Rp <?= number_format($total_kredit, 0, ',', '.') ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- BALANCE STATUS FOOTER -->
            <div class="card-footer p-3 bg-light d-flex justify-content-between align-items-center">
                <span class="text-muted small">
                    <i class="fas fa-info-circle me-1"></i> Total Debit dan Kredit harus selalu sama.
                </span>
                <?php if ($total_debit == $total_kredit): ?>
                    <span class="badge bg-success rounded-pill px-3 py-2">
                        <i class="fas fa-check-circle me-1"></i> STATUS: SEIMBANG
                    </span>
                <?php else: ?>
                    <span class="badge bg-danger rounded-pill px-3 py-2">
                        <i class="fas fa-exclamation-triangle me-1"></i> STATUS: TIDAK SEIMBANG (Selisih:
                        <?= number_format(abs($total_debit - $total_kredit), 0, ',', '.') ?>)
                    </span>
                <?php endif; ?>
            </div>

        </div>
    </div>
</body>

</html>