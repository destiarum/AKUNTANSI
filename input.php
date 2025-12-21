<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include 'config/database.php';

// Ambil daftar akun
// Ambil daftar akun
$akun = mysqli_query($koneksi, "SELECT * FROM akun ORDER BY kode_final ASC");
if (!$akun)
    die("Query akun gagal: " . mysqli_error($koneksi));

// Buat array JS dari akun
$akunArray = [];
mysqli_data_seek($akun, 0);
while ($row = mysqli_fetch_assoc($akun)) {
    $akunArray[] = ['id' => $row['id'], 'kode' => $row['kode_final'], 'nama' => $row['nama_akun']];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Transaksi</title>
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

        .form-label {
            font-weight: 600;
            font-size: 0.875rem;
            color: #7b809a;
            margin-bottom: 8px;
        }

        .form-control,
        .form-select {
            border: 1px solid #e9ecef;
            padding: 10px 15px;
            font-size: 0.9rem;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #083EA8;
            box-shadow: 0 0 0 3px rgba(8, 62, 168, 0.1);
        }

        .section-title {
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f2f5;
            display: flex;
            align-items: center;
        }

        .section-debit {
            color: #d32f2f;
            border-color: #ffebee;
        }

        .section-kredit {
            color: #1976d2;
            border-color: #e3f2fd;
        }

        .table thead th {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background-color: #f8f9fa;
            color: #7b809a;
            padding: 12px;
            border-bottom: 1px solid #e9ecef;
        }

        .table tbody td {
            padding: 10px;
            vertical-align: middle;
            border-bottom: 1px solid #f0f2f5;
        }

        .btn-add-row {
            font-size: 0.8rem;
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 6px;
        }

        .total-box {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            text-align: right;
            font-weight: 700;
        }

        .btn-submit-custom {
            background: linear-gradient(135deg, #083EA8 0%, #3a7bd5 100%);
            color: white;
            border: none;
            padding: 12px 40px;
            border-radius: 10px;
            font-weight: 600;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 6px rgba(58, 123, 213, 0.3);
            transition: transform 0.2s;
        }

        .btn-submit-custom:hover {
            transform: translateY(-2px);
            color: white;
            box-shadow: 0 6px 10px rgba(58, 123, 213, 0.4);
        }

        .btn-delete {
            color: #dc3545;
            background: #fff0f0;
            border: none;
            border-radius: 6px;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.2s;
        }

        .btn-delete:hover {
            background: #dc3545;
            color: white;
        }
    </style>

    <script>
        // simpan akun ke array JS
        const akunOptions = <?php echo json_encode($akunArray); ?>;

        // HITUNG TOTAL
        function hitungTotal() {
            let debitTotal = 0;
            let kreditTotal = 0;
            document.querySelectorAll('.debit').forEach(input => debitTotal += parseFloat(input.value) || 0);
            document.querySelectorAll('.kredit').forEach(input => kreditTotal += parseFloat(input.value) || 0);
            document.getElementById('total_debit').innerText = 'Rp ' + debitTotal.toLocaleString();
            document.getElementById('total_kredit').innerText = 'Rp ' + kreditTotal.toLocaleString();

            // Visual feedback for balance
            const balanceBadge = document.getElementById('balance_status');
            if (debitTotal === kreditTotal && debitTotal > 0) {
                balanceBadge.innerHTML = '<span class="badge bg-success"><i class="fas fa-check me-1"></i> Seimbang</span>';
            } else {
                balanceBadge.innerHTML = '<span class="badge bg-danger"><i class="fas fa-times me-1"></i> Tidak Seimbang</span>';
            }
        }

        // VALIDASI FORM
        function validateForm() {
            let debit = 0;
            let kredit = 0;
            document.querySelectorAll('.debit').forEach(input => debit += parseFloat(input.value) || 0);
            document.querySelectorAll('.kredit').forEach(input => kredit += parseFloat(input.value) || 0);

            if (debit !== kredit) {
                alert('Total Debit dan Kredit harus sama!');
                return false;
            }
            if (debit === 0) {
                alert('Mohon isi nominal transaksi.');
                return false;
            }
            return true;
        }

        // TAMBAH BARIS
        function addRow(tableId, type) {
            let table = document.getElementById(tableId).getElementsByTagName('tbody')[0];
            let row = table.insertRow();
            let cell1 = row.insertCell(0);
            let cell2 = row.insertCell(1);
            let cell3 = row.insertCell(2);
            let cell4 = row.insertCell(3);

            let options = '<option value="">-- Pilih Akun --</option>';
            akunOptions.forEach(a => {
                options += `<option value="${a.id}">${a.kode} - ${a.nama}</option>`;
            });

            cell1.innerHTML = `<select name="akun_${type}[]" class="form-select" required>${options}</select>`;
            cell2.innerHTML = `<input type="text" name="desc_${type}[]" class="form-control" placeholder="Keterangan opsional">`;
            cell3.innerHTML = `<div class="input-group"><span class="input-group-text bg-light border-end-0">Rp</span><input type="number" name="nominal_${type}[]" class="form-control border-start-0 ps-0 ${type}" value="0" oninput="hitungTotal()" required></div>`;
            cell4.innerHTML = `<div class="text-center"><button type="button" class="btn-delete mx-auto" onclick="deleteRow(this)"><i class="fas fa-trash-alt"></i></button></div>`;
        }

        // HAPUS BARIS
        function deleteRow(btn) {
            let row = btn.closest('tr');
            // Ensure at least one row remains is usually good UX, but basic delete is requested
            if (row.parentNode.rows.length > 1) {
                row.parentNode.removeChild(row);
            } else {
                // Clear values instead of deleting last row
                row.querySelectorAll('input').forEach(i => i.value = (i.type === 'number' ? '0' : ''));
                row.querySelector('select').value = '';
            }
            hitungTotal();
        }
    </script>
</head>

<body>
    <?php include "sidebar.php"; ?>

    <div class="main">
        <div class="row justify-content-center">
            <div class="col-xl-10">

                <form action="simpan_jurnal.php" method="POST" onsubmit="return validateForm()">
                    <div class="card p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                            <div>
                                <h4 class="mb-1 fw-bold text-primary"><i class="fas fa-edit me-2"></i>Input Transaksi
                                </h4>
                                <p class="text-muted mb-0 small">Catat transaksi keuangan harian jurnal umum.</p>
                            </div>
                            <div id="balance_status">
                                <span class="badge bg-secondary">Menunggu Input</span>
                            </div>
                        </div>

                        <!-- Top Section: Date & Description -->
                        <div class="row g-4 mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Tanggal Transaksi</label>
                                <input type="date" name="tgl" class="form-control" required min="2025-01-01"
                                    max="2025-12-31" value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Deskripsi Umum</label>
                                <input type="text" name="deskripsi" class="form-control"
                                    placeholder="Contoh: Pembayaran listrik bulan ini" required>
                            </div>
                        </div>

                        <!-- Debit Section -->
                        <div class="mb-4">
                            <div class="section-title section-debit">
                                <i class="fas fa-arrow-down me-2"></i> DEBIT (Masuk/Beban)
                                <button type="button" class="btn btn-sm btn-outline-danger ms-auto btn-add-row"
                                    onclick="addRow('table_debit','debit')">
                                    <i class="fas fa-plus me-1"></i> Tambah Baris
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-borderless" id="table_debit" style="min-width: 600px;">
                                    <thead style="border-bottom: 2px solid #ffebee;">
                                        <tr>
                                            <th style="width: 35%;">Akun</th>
                                            <th style="width: 35%;">Keterangan (Opsional)</th>
                                            <th style="width: 25%;">Nominal</th>
                                            <th style="width: 5%;"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <select name="akun_debit[]" class="form-select" required>
                                                    <option value="">-- Pilih Akun --</option>
                                                    <?php mysqli_data_seek($akun, 0);
                                                    while ($row = mysqli_fetch_assoc($akun)) { ?>
                                                        <option value="<?= $row['id'] ?>"><?= $row['kode_final'] ?> -
                                                            <?= $row['nama_akun'] ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </td>
                                            <td><input type="text" name="desc_debit[]" class="form-control"
                                                    placeholder="Keterangan opsional"></td>
                                            <td>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-end-0">Rp</span>
                                                    <input type="number" name="nominal_debit[]"
                                                        class="form-control border-start-0 ps-0 debit" value="0"
                                                        oninput="hitungTotal()" required>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn-delete mx-auto"
                                                    onclick="deleteRow(this)">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="total-box text-danger">
                                Total Debit: <span id="total_debit">Rp 0</span>
                            </div>
                        </div>

                        <!-- Kredit Section -->
                        <div class="mb-5">
                            <div class="section-title section-kredit">
                                <i class="fas fa-arrow-up me-2"></i> KREDIT (Keluar/Pendapatan)
                                <button type="button" class="btn btn-sm btn-outline-primary ms-auto btn-add-row"
                                    onclick="addRow('table_kredit','kredit')">
                                    <i class="fas fa-plus me-1"></i> Tambah Baris
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-borderless" id="table_kredit" style="min-width: 600px;">
                                    <thead style="border-bottom: 2px solid #e3f2fd;">
                                        <tr>
                                            <th style="width: 35%;">Akun</th>
                                            <th style="width: 35%;">Keterangan (Opsional)</th>
                                            <th style="width: 25%;">Nominal</th>
                                            <th style="width: 5%;"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <select name="akun_kredit[]" class="form-select" required>
                                                    <option value="">-- Pilih Akun --</option>
                                                    <?php mysqli_data_seek($akun, 0);
                                                    while ($row = mysqli_fetch_assoc($akun)) { ?>
                                                        <option value="<?= $row['id'] ?>"><?= $row['kode_final'] ?> -
                                                            <?= $row['nama_akun'] ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </td>
                                            <td><input type="text" name="desc_kredit[]" class="form-control"
                                                    placeholder="Keterangan opsional"></td>
                                            <td>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-end-0">Rp</span>
                                                    <input type="number" name="nominal_kredit[]"
                                                        class="form-control border-start-0 ps-0 kredit" value="0"
                                                        oninput="hitungTotal()" required>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn-delete mx-auto"
                                                    onclick="deleteRow(this)">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="total-box text-primary">
                                Total Kredit: <span id="total_kredit">Rp 0</span>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="text-center">
                            <button type="submit" class="btn btn-submit-custom">
                                <i class="fas fa-save me-2"></i> SIMPAN TRANSAKSI
                            </button>
                        </div>

                    </div>
                </form>

            </div>
        </div>
    </div>
</body>

</html>