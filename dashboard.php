<?php
session_start();
if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit();
}
include "config/database.php";

$bulan = $_GET['bulan'] ?? '';
$tahun = $_GET['tahun'] ?? '';

function get_saldo($koneksi, $kode, $bulan = '', $tahun = '', $tipe_akun = '')
{
  $w = "";
  if ($bulan != '')
    $w .= " AND MONTH(j.tanggal)='$bulan'";
  if ($tahun != '')
    $w .= " AND YEAR(j.tanggal)='$tahun'";

  // 1. Get Transaction Sums
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

  // 2. Get Opening Balance (Nominal) from Akun table
  $qAkun = mysqli_query($koneksi, "SELECT nominal FROM akun WHERE kode_final='$kode'");
  $rAkun = mysqli_fetch_assoc($qAkun);
  $nominal = $rAkun['nominal'] ?? 0;

  $saldoAwal = 0;

  // Logic Saldo Awal:
  // 1. Tahun = kosong (Semua) atau 2024: Include Nominal
  // 2. TAPI, hanya untuk akun Neraca (Aset, Liabilitas, Ekuitas).
  //    Akun Laba Rugi (Pendapatan, Beban) selalu 0 saldo awalnya (murni transaksi berjalan).
  if (($tahun == '' || $tahun == '2024') && in_array($tipe_akun, ['Aset', 'Liabilitas', 'Ekuitas'])) {
    $saldoAwal = $nominal;
  }

  // 3. Normal Balance Logic
  // Aset & Beban: Debit Balance
  // Liabilitas, Ekuitas, Pendapatan: Credit Balance
  if (in_array($tipe_akun, ['Aset', 'Beban'])) {
    return ($saldoAwal + $debit) - $kredit;
  } else {
    return ($saldoAwal + $kredit) - $debit;
  }
}

function total_by_tipe($koneksi, $tipe, $bulan, $tahun)
{
  $t = 0;
  // Pastikan ambil tipe_akun yang sesuai untuk filtering strict
  $q = mysqli_query($koneksi, "SELECT kode_final, tipe_akun FROM akun WHERE tipe_akun='$tipe'");
  while ($r = mysqli_fetch_assoc($q)) {
    $t += get_saldo($koneksi, $r['kode_final'], $bulan, $tahun, $r['tipe_akun']);
  }
  return $t;
}

// Data utama
$pendapatan = total_by_tipe($koneksi, 'Pendapatan', $bulan, $tahun);
$beban = total_by_tipe($koneksi, 'Beban', $bulan, $tahun);
$laba = $pendapatan - $beban;
$aset = total_by_tipe($koneksi, 'Aset', $bulan, $tahun);
$liab = total_by_tipe($koneksi, 'Liabilitas', $bulan, $tahun);
$ekui = total_by_tipe($koneksi, 'Ekuitas', $bulan, $tahun);

// Arus kas (simulasi sederhana)
$arus_operasi = $laba;
$arus_investasi = -($aset * 0.15);
$arus_pendanaan = $ekui;

// Data garis untuk tren tiap bulan
$labels = [];
$pendapatan_bulan = [];
$beban_bulan = [];
$laba_bulan = [];
for ($i = 1; $i <= 12; $i++) {
  $labels[] = date('F', mktime(0, 0, 0, $i, 1));
  $pendapatan_bulan[] = total_by_tipe($koneksi, 'Pendapatan', $i, $tahun);
  $beban_bulan[] = total_by_tipe($koneksi, 'Beban', $i, $tahun);
  $laba_bulan[] = $pendapatan_bulan[$i - 1] - $beban_bulan[$i - 1];
}
// total debit dan kredit
$q = mysqli_query($koneksi, "
    SELECT SUM(debit) AS total_debit, SUM(kredit) AS total_kredit
    FROM jurnal_detail
");
$r = mysqli_fetch_assoc($q);
$total_debit = $r['total_debit'] ?? 0;
$total_kredit = $r['total_kredit'] ?? 0;

// cek balance
$balance = $total_debit - $total_kredit;

?>
<!DOCTYPE html>
<html>

<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <!-- FontAwesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <style>
    body {
      background: #f8f9fa;
      font-family: 'Outfit', sans-serif;
      color: #344767;
    }

    .main {
      padding: 30px;
    }

    /* Card Styles */
    .card {
      background: white;
      border: none;
      border-radius: 16px;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
      transition: transform 0.2s;
    }

    .card:hover {
      transform: translateY(-2px);
    }

    .card-header-custom {
      background: transparent;
      border-bottom: 1px solid #f0f2f5;
      padding: 20px 24px;
    }

    .card-body {
      padding: 24px;
    }

    /* Stats Cards */
    .icon-box {
      width: 48px;
      height: 48px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      color: white;
    }

    .bg-gradient-primary {
      background: linear-gradient(135deg, #083EA8 0%, #3a7bd5 100%);
    }

    .bg-gradient-success {
      background: linear-gradient(135deg, #4caf50 0%, #81c784 100%);
    }

    .bg-gradient-danger {
      background: linear-gradient(135deg, #f44336 0%, #e57373 100%);
    }

    .bg-gradient-info {
      background: linear-gradient(135deg, #00bcd4 0%, #4dd0e1 100%);
    }

    h6 {
      color: #7b809a;
      font-weight: 600;
      font-size: 0.875rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    h3,
    h5 {
      font-weight: 700;
      color: #344767;
    }

    /* Layout Helpers */
    .chart-container {
      position: relative;
      height: 300px;
      width: 100%;
      margin: auto;
    }

    @media (max-width: 768px) {
      .chart-container {
        height: 250px;
      }

      .main {
        padding: 15px;
      }
    }
  </style>
</head>

<body>
  <?php include "sidebar.php"; ?>

  <div class="main">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-5">
      <div>
        <h3 class="mb-1">Dashboard Overview</h3>
        <p class="text-muted mb-0">Ringkasan kesehatan keuangan Anda hari ini.</p>
      </div>
      <div class="d-flex gap-2">
        <div class="bg-white px-3 py-2 rounded-pill shadow-sm text-sm fw-bold border">
          <i class="far fa-calendar-alt me-2 text-primary"></i> <?= date('d F Y') ?>
        </div>
      </div>
    </div>

    <!-- FILTERS -->
    <div class="card mb-5">
      <div class="card-body p-4">
        <form class="row g-3 align-items-end">
          <div class="col-md-3">
            <label class="form-label fw-bold small">Bulan</label>
            <select name="bulan" class="form-select border-light bg-light">
              <option value="">Semua Bulan</option>
              <?php for ($i = 1; $i <= 12; $i++): ?>
                <option value="<?= $i ?>" <?= ($bulan == $i ? 'selected' : '') ?>>
                  <?= date('F', mktime(0, 0, 0, $i, 1)) ?>
                </option>
              <?php endfor; ?>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label fw-bold small">Tahun</label>
            <select name="tahun" class="form-select border-light bg-light">
              <option value="">Semua Tahun</option>
              <?php for ($i = date('Y'); $i >= date('Y') - 1; $i--): ?>
                <option value="<?= $i ?>" <?= ($tahun == $i ? 'selected' : '') ?>>
                  <?= $i ?>
                </option>
              <?php endfor; ?>
            </select>
          </div>

          <div class="col-md-2">
            <button class="btn btn-primary w-100 fw-bold"><i class="fas fa-filter me-1"></i> Filter</button>
          </div>

          <div class="col-md-4 text-end ms-auto">
            <a href="export_dashboard_pdf.php?bulan=<?= $bulan ?>&tahun=<?= $tahun ?>"
              class="btn btn-danger fw-bold shadow-sm">
              <i class="fas fa-file-pdf me-2"></i> Export PDF
            </a>
          </div>
        </form>
      </div>
    </div>

    <!-- SUMMARY CARDS ROW 1 (Debit/Kredit/Balance) -->
    <div class="row g-4 mb-4">
      <div class="col-xl-4 col-md-6">
        <div class="card h-100">
          <div class="card-body d-flex align-items-center justify-content-between">
            <div>
              <h6 class="mb-1">Total Debit</h6>
              <h4 class="mb-0 text-success">Rp <?= number_format($total_debit, 0, ',', '.') ?></h4>
            </div>
            <div class="icon-box bg-gradient-success shadow">
              <i class="fas fa-arrow-up"></i>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-4 col-md-6">
        <div class="card h-100">
          <div class="card-body d-flex align-items-center justify-content-between">
            <div>
              <h6 class="mb-1">Total Kredit</h6>
              <h4 class="mb-0 text-danger">Rp <?= number_format($total_kredit, 0, ',', '.') ?></h4>
            </div>
            <div class="icon-box bg-gradient-danger shadow">
              <i class="fas fa-arrow-down"></i>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-4 col-md-12">
        <div class="card h-100">
          <div class="card-body d-flex align-items-center justify-content-between">
            <div>
              <h6 class="mb-1">Status Balance</h6>
              <?php if ($balance == 0): ?>
                <h4 class="text-success mb-0">Seimbang <i class="fas fa-check-circle ms-2"></i></h4>
              <?php else: ?>
                <h5 class="text-danger mb-0">Selisih: Rp <?= number_format($balance, 0, ',', '.') ?></h5>
                <small class="text-muted">Tidak Seimbang</small>
              <?php endif; ?>
            </div>
            <div class="icon-box bg-gradient-primary shadow">
              <i class="fas fa-balance-scale"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- KEY METRICS ROW -->
    <div class="row g-4 mb-5">
      <?php
      // Map for custom icons and backgrounds
      $metrics_config = [
        'Pendapatan' => ['bg' => 'bg-gradient-success', 'icon' => 'fa-hand-holding-usd'],
        'Beban' => ['bg' => 'bg-gradient-danger', 'icon' => 'fa-file-invoice-dollar'],
        'Laba Bersih' => ['bg' => 'bg-gradient-primary', 'icon' => 'fa-chart-line'],
        'Total Aset' => ['bg' => 'bg-gradient-info', 'icon' => 'fa-building']
      ];

      $cards = ['Pendapatan' => $pendapatan, 'Beban' => $beban, 'Laba Bersih' => $laba, 'Total Aset' => $aset];
      foreach ($cards as $k => $v):
        $conf = $metrics_config[$k];
        $valColor = ($k == 'Laba Bersih' && $v < 0) ? 'text-danger' : 'text-dark';
        ?>
        <div class="col-xl-3 col-md-6">
          <div class="card">
            <div class="card-body p-3">
              <div class="row">
                <div class="col-8">
                  <div class="numbers">
                    <p class="text-sm mb-0 text-uppercase font-weight-bold text-muted"><?= $k ?></p>
                    <h5 class="font-weight-bolder <?= $valColor ?> mb-0">
                      Rp <?= number_format($v, 0, ',', '.') ?>
                    </h5>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon-box <?= $conf['bg'] ?> shadow text-center ms-auto">
                    <i class="fas <?= $conf['icon'] ?>"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- CHARTS ROW 1 -->
    <div class="row g-4 mb-4">
      <div class="col-lg-7">
        <div class="card h-100">
          <div class="card-header-custom pb-0">
            <h6>Pendapatan, Beban & Laba/rugi</h6>
          </div>
          <div class="card-body">
            <div class="chart-container">
              <canvas id="labaBar"></canvas>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-5">
        <div class="card h-100">
          <div class="card-header-custom pb-0">
            <h6>Komposisi Neraca</h6>
          </div>
          <div class="card-body">
            <div class="chart-container" style="height: 250px;"> <!-- Pie chart smaller height -->
              <canvas id="neracaPie"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- CHARTS ROW 2 -->
    <div class="row g-4">
      <div class="col-lg-6">
        <div class="card h-100">
          <div class="card-header-custom pb-0">
            <h6>Arus Kas Aktivitas</h6>
          </div>
          <div class="card-body">
            <div class="chart-container">
              <canvas id="arusBar"></canvas>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="card h-100">
          <div class="card-header-custom pb-0">
            <h6>Tren Keuangan Tahun Ini</h6>
          </div>
          <div class="card-body">
            <div class="chart-container">
              <canvas id="trenLine"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>


    <script>
      // Common Options for better visuals
      const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              usePointStyle: true,
              padding: 20,
              font: { family: 'Outfit', size: 12 }
            }
          }
        }
      };

      // 1. Laba Bar Chart
      new Chart(document.getElementById('labaBar'), {
        type: 'bar',
        data: {
          labels: ['Pendapatan', 'Beban', 'Laba/Rugi'],
          datasets: [{
            label: 'Nominal (Rp)',
            data: [<?= $pendapatan ?>, <?= $beban ?>, <?= $laba ?>],
            backgroundColor: ['#4caf50', '#f44336', '#2196f3'],
            borderRadius: 6,
            barThickness: 40
          }]
        },
        options: {
          ...commonOptions,
          scales: {
            y: { grid: { borderDash: [2, 2] } },
            x: { grid: { display: false } }
          }
        }
      });

      // 2. Neraca Pie Chart
      new Chart(document.getElementById('neracaPie'), {
        type: 'doughnut', // Changed to doughnut for modern look
        data: {
          labels: ['Aset', 'Liabilitas', 'Ekuitas'],
          datasets: [{
            data: [<?= $aset ?>, <?= $liab ?>, <?= $ekui ?>],
            backgroundColor: ['#ff9800', '#9c27b0', '#00bcd4'],
            borderWidth: 0,
            hoverOffset: 4
          }]
        },
        options: {
          ...commonOptions,
          cutout: '60%'
        }
      });

      // 3. Arus Kas Bar Chart
      new Chart(document.getElementById('arusBar'), {
        type: 'bar',
        data: {
          labels: ['Operasi', 'Investasi', 'Pendanaan'],
          datasets: [{
            label: 'Arus Kas (Rp)',
            data: [<?= $arus_operasi ?>, <?= $arus_investasi ?>, <?= $arus_pendanaan ?>],
            backgroundColor: ['#3f51b5', '#e91e63', '#009688'],
            borderRadius: 6,
            barThickness: 50
          }]
        },
        options: {
          ...commonOptions,
          indexAxis: 'y', // Horizontal bar for variety
          scales: {
            x: { grid: { borderDash: [2, 2] } },
            y: { grid: { display: false } }
          }
        }
      });

      // 4. Tren Line Chart
      new Chart(document.getElementById('trenLine'), {
        type: 'line',
        data: {
          labels: [<?= implode(',', array_map(fn($m) => "'$m'", $labels)) ?>],
          datasets: [
            {
              label: 'Pendapatan',
              data: [<?= implode(',', $pendapatan_bulan) ?>],
              borderColor: '#4caf50',
              backgroundColor: 'rgba(76, 175, 80, 0.1)',
              tension: 0.4,
              fill: true
            },
            {
              label: 'Beban',
              data: [<?= implode(',', $beban_bulan) ?>],
              borderColor: '#f44336',
              backgroundColor: 'rgba(244, 67, 54, 0.1)',
              tension: 0.4,
              fill: true
            },
            {
              label: 'Laba',
              data: [<?= implode(',', $laba_bulan) ?>],
              borderColor: '#2196f3',
              backgroundColor: 'rgba(33, 150, 243, 0.1)',
              tension: 0.4,
              fill: true
            }
          ]
        },
        options: {
          ...commonOptions,
          scales: {
            y: { grid: { borderDash: [2, 2] } },
            x: { grid: { display: false } }
          },
          interaction: {
            mode: 'index',
            intersect: false,
          },
        }
      });
    </script>
</body>

</html>