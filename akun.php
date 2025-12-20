<?php
session_start();
include "config/database.php";

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

/*
  LOGIKA:
  kode_final = [1][10][1] → 1101
  kode_induk = digit ke-1 × 1000
  kode_sub   = digit ke-2 & 3
*/
$akun = mysqli_query($koneksi, "
    SELECT *,
        (LEFT(kode_final,1) * 1000) AS kode_induk_tampil,
        SUBSTRING(kode_final,2,2)   AS kode_sub_tampil
    FROM akun
    ORDER BY kode_final ASC
");
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun</title>
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

        /* Card & Table Styles */
        .card {
            background: white;
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }

        .table thead th {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 1px solid #e9ecef;
            background-color: #f8f9fa;
            color: #7b809a;
            padding: 12px 16px;
        }

        .table tbody td {
            padding: 16px;
            vertical-align: middle;
            font-size: 0.875rem;
            color: #344767;
            border-bottom: 1px solid #f0f2f5;
        }

        .table-hover tbody tr:hover {
            background-color: #fafbfc;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, #083EA8 0%, #3a7bd5 100%);
            color: white;
            border: none;
            box-shadow: 0 4px 6px rgba(58, 123, 213, 0.3);
        }

        .btn-primary-custom:hover {
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 6px 8px rgba(58, 123, 213, 0.4);
        }

        /* Badge Styles */
        .badge-custom {
            padding: 6px 10px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.75rem;
        }

        .badge-aset {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .badge-liabilitas {
            background: #ffebee;
            color: #c62828;
        }

        .badge-ekuitas {
            background: #e3f2fd;
            color: #1565c0;
        }

        .badge-pendapatan {
            background: #fff3e0;
            color: #ef6c00;
        }

        .badge-beban {
            background: #f3e5f5;
            color: #7b1fa2;
        }

        .btn-action {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            margin: 0 2px;
        }
    </style>
</head>

<body>
    <?php include "sidebar.php"; ?>

    <div class="main">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="mb-1 fw-bold">Daftar Akun</h3>
                <p class="text-muted mb-0">Kelola Chart of Accounts (COA) Anda.</p>
                <small class="text-danger fw-bold">* Angka disajikan dalam Jutaan Rupiah</small>
            </div>
            <button type="button" class="btn btn-primary-custom px-4 py-2 rounded-3 fw-bold" data-bs-toggle="modal"
                data-bs-target="#tambahAkunModal">
                <i class="fas fa-plus me-2"></i> Tambah Akun
            </button>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover align-items-center mb-0">
                    <thead>
                        <tr>
                            <th>Kode Induk</th>
                            <th>Kode Sub</th>
                            <th>Urutan</th>
                            <th>Kode Final</th>
                            <th>Nama Akun</th>
                            <th>Tipe</th>
                            <th>Saldo Normal</th>
                            <th class="text-end">Saldo Awal</th>
                            <th class="text-center" width="120">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        while ($r = mysqli_fetch_assoc($akun)) {
                            // Determine badge class
                            $badgeClass = 'badge-secondary';
                            switch ($r['tipe_akun']) {
                                case 'Aset':
                                    $badgeClass = 'badge-aset';
                                    break;
                                case 'Liabilitas':
                                    $badgeClass = 'badge-liabilitas';
                                    break;
                                case 'Ekuitas':
                                    $badgeClass = 'badge-ekuitas';
                                    break;
                                case 'Pendapatan':
                                    $badgeClass = 'badge-pendapatan';
                                    break;
                                case 'Beban':
                                    $badgeClass = 'badge-beban';
                                    break;
                            }
                            ?>
                            <tr>
                                <td class="ps-4 fw-bold text-secondary"><?= $r['kode_induk_tampil']; ?></td>
                                <td class="text-secondary"><?= $r['kode_sub_tampil']; ?></td>
                                <td class="text-secondary"><?= $r['kode_akun']; ?></td>
                                <td><span class="fw-bold text-dark"><?= $r['kode_final']; ?></span></td>
                                <td class="fw-semibold"><?= $r['nama_akun']; ?></td>
                                <td><span class="badge-custom <?= $badgeClass ?>"><?= $r['tipe_akun']; ?></span></td>
                                <td class="text-sm"><?= $r['saldo_normal']; ?></td>
                                <td class="text-end fw-bold text-dark">Rp <?= number_format($r['nominal'], 0, ',', '.') ?>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-action btn-light text-warning shadow-sm"
                                        data-bs-toggle="modal" data-bs-target="#editAkunModal" data-id="<?= $r['id'] ?>"
                                        data-kode="<?= $r['kode_final'] ?>" data-nama="<?= $r['nama_akun'] ?>"
                                        data-tipe="<?= $r['tipe_akun'] ?>" data-saldo="<?= $r['saldo_normal'] ?>"
                                        data-nominal="<?= $r['nominal'] ?>"
                                        data-deskripsi="<?= htmlspecialchars($r['deskripsi']) ?>" data-bs-toggle="tooltip"
                                        title="Edit">
                                        <i class="fas fa-pen fa-xs"></i>
                                    </button>
                                    <button type="button" class="btn btn-action btn-light text-danger shadow-sm"
                                        data-bs-toggle="modal" data-bs-target="#hapusAkunModal" data-id="<?= $r['id'] ?>"
                                        data-bs-toggle="tooltip" title="Hapus">
                                        <i class="fas fa-trash fa-xs"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MODAL TAMBAH AKUN -->
    <div class="modal fade" id="tambahAkunModal" tabindex="-1" aria-labelledby="tambahAkunLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header border-bottom-0 p-4 pb-0">
                    <h5 class="modal-title fw-bold" id="tambahAkunLabel">Tambah Akun Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form action="proses_tambah_akun.php" method="POST">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Kode Induk</label>
                                <select name="kode_induk" class="form-select" required>
                                    <option value="">-- Pilih --</option>
                                    <option value="1000">1000 - Aset</option>
                                    <option value="2000">2000 - Liabilitas</option>
                                    <option value="3000">3000 - Ekuitas</option>
                                    <option value="4000">4000 - Pendapatan</option>
                                    <option value="5000">5000 - Beban</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Kode Sub</label>
                                <select name="kode_sub" class="form-select" required>
                                    <option value="">-- Pilih --</option>
                                    <optgroup label="ASET">
                                        <option value="100">100 - Aset Lancar</option>
                                        <option value="200">200 - Aset Tidak Lancar</option>
                                    </optgroup>
                                    <optgroup label="LIABILITAS">
                                        <option value="100">100 - Liabilitas Jangka Pendek</option>
                                        <option value="200">200 - Liabilitas Jangka Panjang</option>
                                    </optgroup>
                                    <optgroup label="EKUITAS">
                                        <option value="100">100 - Modal</option>
                                        <option value="200">200 - Pendapatan Ditahan</option>
                                    </optgroup>
                                    <optgroup label="PENDAPATAN">
                                        <option value="100">100 - Pendapatan Operasional</option>
                                        <option value="200">200 - Pendapatan Non Operasional</option>
                                    </optgroup>
                                    <optgroup label="BEBAN">
                                        <option value="100">100 - Harga Pokok Penjualan</option>
                                        <option value="200">200 - Beban Operasional</option>
                                        <option value="300">300 - Pendapatan Non Operasional</option>
                                    </optgroup>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted">Nama Akun</label>
                                <input type="text" name="nama_akun" class="form-control" placeholder="Contoh: Kas Kecil"
                                    required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Tipe Akun</label>
                                <select name="tipe_akun" class="form-select" required>
                                    <option>Aset</option>
                                    <option>Liabilitas</option>
                                    <option>Ekuitas</option>
                                    <option>Pendapatan</option>
                                    <option>Beban</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Saldo Normal</label>
                                <select name="saldo_normal" class="form-select" required>
                                    <option>Debit</option>
                                    <option>Kredit</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted">Saldo Awal</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light fw-bold">Rp</span>
                                    <input type="text" id="rupiah" class="form-control" autocomplete="off"
                                        placeholder="0">
                                    <input type="hidden" name="nominal" id="nominal">
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary-custom w-100 py-2 rounded-3 fw-bold">Simpan
                                Akun</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL EDIT AKUN -->
    <div class="modal fade" id="editAkunModal" tabindex="-1" aria-labelledby="editAkunLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header border-bottom-0 p-4 pb-0">
                    <h5 class="modal-title fw-bold" id="editAkunLabel">Edit Akun</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form action="proses_edit_akun.php" method="POST">
                        <input type="hidden" name="id" id="edit_id">

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Kode Akun (Read-Only)</label>
                            <input type="text" id="edit_kode" class="form-control bg-light" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Nama Akun</label>
                            <input type="text" name="nama_akun" id="edit_nama" class="form-control" required>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Tipe Akun</label>
                                <select name="tipe_akun" id="edit_tipe" class="form-select">
                                    <option>Aset</option>
                                    <option>Liabilitas</option>
                                    <option>Ekuitas</option>
                                    <option>Pendapatan</option>
                                    <option>Beban</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Saldo Normal</label>
                                <select name="saldo_normal" id="edit_saldo" class="form-select">
                                    <option>Debit</option>
                                    <option>Kredit</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Saldo Awal</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold">Rp</span>
                                <input type="number" name="nominal" id="edit_nominal" class="form-control">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Deskripsi</label>
                            <textarea name="deskripsi" id="edit_deskripsi" class="form-control" rows="2"></textarea>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary-custom w-100 py-2 rounded-3 fw-bold">Update
                                Akun</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL HAPUS AKUN -->
    <div class="modal fade" id="hapusAkunModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content rounded-4 border-0 shadow-lg text-center p-4">
                <div class="mb-3 text-danger">
                    <i class="fas fa-exclamation-circle fa-3x"></i>
                </div>
                <h5 class="fw-bold mb-2">Hapus Akun?</h5>
                <p class="text-muted small mb-4">Tindakan ini tidak dapat dibatalkan. Menghapus akun dapat mempengaruhi
                    laporan keuangan.</p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light w-50 fw-bold" data-bs-dismiss="modal">Batal</button>
                    <a href="#" id="link_hapus" class="btn btn-danger w-50 fw-bold">Hapus</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Input Rupiah Formatter
        const rupiah = document.getElementById('rupiah');
        const nominal = document.getElementById('nominal');
        if (rupiah) {
            rupiah.addEventListener('input', function () {
                let angka = this.value.replace(/[^0-9]/g, '');
                nominal.value = angka;
                this.value = new Intl.NumberFormat('id-ID').format(angka);
            });
        }

        // Edit Modal Handler
        const editModal = document.getElementById('editAkunModal');
        if (editModal) {
            editModal.addEventListener('show.bs.modal', event => {
                const button = event.relatedTarget;

                // Extract info from data-* attributes
                const id = button.getAttribute('data-id');
                const kode = button.getAttribute('data-kode');
                const nama = button.getAttribute('data-nama');
                const tipe = button.getAttribute('data-tipe');
                const saldo = button.getAttribute('data-saldo');
                const nominalVal = button.getAttribute('data-nominal');
                const deskripsi = button.getAttribute('data-deskripsi');

                // Update the modal's content
                editModal.querySelector('#edit_id').value = id;
                editModal.querySelector('#edit_kode').value = kode;
                editModal.querySelector('#edit_nama').value = nama;
                editModal.querySelector('#edit_tipe').value = tipe;
                editModal.querySelector('#edit_saldo').value = saldo;
                editModal.querySelector('#edit_nominal').value = nominalVal;
                editModal.querySelector('#edit_deskripsi').value = deskripsi;
            });
        }

        // Delete Modal Handler
        const hapusModal = document.getElementById('hapusAkunModal');
        if (hapusModal) {
            hapusModal.addEventListener('show.bs.modal', event => {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-id');
                const link = hapusModal.querySelector('#link_hapus');
                link.href = 'hapus_akun.php?id=' + id;
            });
        }

        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    </script>
</body>

</html>