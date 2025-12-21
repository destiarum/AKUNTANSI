<!-- SIDEBAR COMPONENT -->
<style>
    .sidebar {
        width: 260px;
        height: 100vh;
        background: linear-gradient(180deg, #b71c1c 0%, #1a237e 40%, #283593 100%);
        color: white;
        position: fixed;
        left: 0;
        top: 0;
        padding: 24px 16px;
        z-index: 1000;
        font-family: 'Outfit', sans-serif;
        box-shadow: 4px 0 10px rgba(0, 0, 0, 0.05);
        transition: transform 0.3s ease-in-out;
    }

    .sidebar-header {
        text-align: center;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
    }

    .sidebar-logo {
        width: 80px;
        height: auto;
        display: block;
        margin-bottom: 5px;
    }

    .sidebar h2 {
        margin: 0;
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 0.5px;
        color: white !important;
        text-align: center;
        line-height: 1.4;
        max-width: 200px;
    }

    .sidebar ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .sidebar li {
        margin-bottom: 8px;
    }

    .sidebar a {
        color: rgba(255, 255, 255, 0.8) !important;
        text-decoration: none;
        display: flex;
        align-items: center;
        padding: 12px 16px;
        border-radius: 12px;
        transition: all 0.3s ease;
        font-size: 15px;
        font-weight: 500;
    }

    .sidebar a i {
        width: 24px;
        font-size: 18px;
        margin-right: 12px;
        text-align: center;
        transition: transform 0.3s;
    }

    .sidebar a:hover {
        background: rgba(255, 255, 255, 0.1);
        color: white !important;
        transform: translateX(5px);
    }

    .sidebar a:hover i {
        transform: scale(1.1);
    }

    .sidebar a.active {
        background: white;
        color: #1a237e !important;
        font-weight: 700;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .sidebar-footer {
        position: absolute;
        bottom: 24px;
        left: 16px;
        right: 16px;
    }

    /* Main Content Adjustment Helper */
    .main {
        margin-left: 260px;
        padding: 30px;
        min-height: 100vh;
        transition: margin-left 0.3s ease-in-out;
    }

    /* Mobile Toggle Button */
    .mobile-toggle {
        display: none;
        position: fixed;
        top: 20px;
        left: 20px;
        z-index: 1001;
        background: #1a237e;
        color: white;
        border: none;
        border-radius: 8px;
        padding: 10px 15px;
        font-size: 1.2rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        cursor: pointer;
    }

    /* Overlay for mobile */
    .sidebar-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 999;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    @media (max-width: 768px) {
        .mobile-toggle {
            display: block;
        }

        .sidebar {
            transform: translateX(-100%);
        }

        .sidebar.active {
            transform: translateX(0);
        }

        .main {
            margin-left: 0 !important;
            padding-top: 80px !important;
            /* Space for toggle button */
            padding-left: 15px !important;
            padding-right: 15px !important;
        }

        .sidebar-overlay.active {
            display: block;
            opacity: 1;
        }
    }
</style>

<?php
// Dapatkan nama file saat ini untuk penanda aktif
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Mobile Toggle & Overlay -->
<button class="mobile-toggle" onclick="toggleSidebar()">
    <i class="fas fa-bars"></i>
</button>
<div class="sidebar-overlay" onclick="toggleSidebar()"></div>

<div class="sidebar">
    <div class="sidebar-header">
        <img src="assets/img/logo_indocement.png" alt="PT Indocement" class="sidebar-logo">
        <h2>PT INDOCEMENT TUNGGAL PRAKARSA Tbk</h2>
    </div>

    <ul>
        <li>
            <a href="dashboard.php" class="<?= ($current_page == 'dashboard.php') ? 'active' : '' ?>">
                <i class="fas fa-th-large"></i> Dashboard
            </a>
        </li>

        <li>
            <a href="akun.php" class="<?= ($current_page == 'akun.php') ? 'active' : '' ?>">
                <i class="fas fa-book"></i> Data Akun
            </a>
        </li>

        <li>
            <a href="input.php" class="<?= ($current_page == 'input.php') ? 'active' : '' ?>">
                <i class="fas fa-edit"></i> Input Transaksi
            </a>
        </li>

        <li>
            <a href="jurnal_umum.php" class="<?= ($current_page == 'jurnal_umum.php') ? 'active' : '' ?>">
                <i class="fas fa-list-alt"></i> Jurnal Umum
            </a>
        </li>

        <li>
            <a href="buku_besar.php" class="<?= ($current_page == 'buku_besar.php') ? 'active' : '' ?>">
                <i class="fas fa-book-open"></i> Buku Besar
            </a>
        </li>

        <li>
            <a href="laporan.php" class="<?= ($current_page == 'laporan.php') ? 'active' : '' ?>">
                <i class="fas fa-chart-bar"></i> Laporan
            </a>
        </li>
    </ul>

    <div class="sidebar-footer">
        <a href="logout.php" class="text-danger fw-bold"
            style="background: rgba(255,0,0,0.1); color: #ff8a80 !important">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>

<script>
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
    }
</script>