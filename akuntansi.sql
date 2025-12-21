-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 20, 2025 at 05:12 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `akuntansi`
--

-- --------------------------------------------------------

--
-- Table structure for table `akun`
--

CREATE TABLE `akun` (
  `id` int(11) NOT NULL,
  `kode_induk` enum('1000','2000','3000','4000','5000') NOT NULL,
  `kode_sub` enum('100','200','300','400') NOT NULL,
  `kode_akun` tinyint(4) NOT NULL,
  `kode_final` varchar(10) NOT NULL,
  `nama_akun` varchar(200) NOT NULL,
  `tipe_akun` varchar(50) DEFAULT '',
  `saldo_normal` varchar(10) DEFAULT '',
  `deskripsi` text DEFAULT NULL,
  `nominal` decimal(15,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `akun`
--

INSERT INTO `akun` (`id`, `kode_induk`, `kode_sub`, `kode_akun`, `kode_final`, `nama_akun`, `tipe_akun`, `saldo_normal`, `deskripsi`, `nominal`) VALUES
(42, '', '100', 1, '1101', 'Kas', 'Aset', 'Debit', '', 4496547.00),
(44, '', '100', 2, '1102', 'Piutang', 'Aset', 'Debit', '', 3012292.00),
(48, '', '100', 3, '1103', 'PBB (Persediaan Bahan Baku)', 'Aset', 'Debit', '', 2103296.00),
(49, '', '100', 4, '1104', 'PDP (Produk Dalam Proses)', 'Aset', 'Debit', '', 270934.00),
(50, '', '100', 5, '1105', 'KPrPJ', 'Aset', 'Debit', '', 218831.00),
(54, '', '100', 1, '2101', 'Utang Usaha', 'Liabilitas', 'Kredit', '', 1790625.00),
(55, '', '100', 2, '2102', 'Utang bank', 'Liabilitas', 'Kredit', '', 2000000.00),
(62, '', '100', 1, '3101', 'Modal Saham', 'Ekuitas', 'Kredit', '', 22114350.00),
(64, '', '100', 1, '4101', 'Penjualan', 'Pendapatan', 'Kredit', '', 18548734.00),
(68, '', '100', 1, '5101', 'Beban Pokok Pendapatan', 'Beban', 'Debit', '', 12487779.00),
(69, '', '200', 1, '5201', 'Beban Penjualan', 'Beban', 'Debit', '', 2984660.00),
(70, '', '200', 2, '5202', 'Beban gaji', 'Beban', 'Debit', '', 740446.00),
(74, '', '100', 3, '2103', 'Utang beban', 'Liabilitas', 'Kredit', '', 1266838.00),
(75, '', '100', 4, '2104', 'Pendapatan diterima dimuka', 'Liabilitas', 'Kredit', '', 93820.00),
(77, '', '200', 1, '1201', 'Tanah', 'Aset', 'Debit', '', 1606032.00),
(78, '', '200', 2, '1202', 'Bangunan', 'Aset', 'Debit', '', 10236781.00),
(79, '', '200', 3, '1203', 'Mesin', 'Aset', 'Debit', '', 18831893.00),
(80, '', '200', 4, '1204', 'Peralatan', 'Aset', 'Debit', '', 2134334.00),
(81, '', '200', 5, '1205', 'Kendaraan', 'Aset', 'Debit', '', 1576937.00),
(82, '', '200', 6, '1206', 'Aset lainnya', 'Aset', 'Debit', '', 5734099.00),
(85, '', '200', 7, '1207', 'Akumulasi penyusutan', 'Aset', 'Kredit', '', 20088904.00),
(86, '', '100', 5, '2105', 'Utang lainnya', 'Liabilitas', 'Kredit', '', 2023846.00),
(87, '', '200', 1, '2201', 'Utang jangka panjang', 'Liabilitas', 'Kredit', '', 1130527.00),
(88, '', '100', 2, '4102', 'Penjualan lainnya', 'Pendapatan', 'Kredit', '', 336242.00),
(89, '', '200', 3, '5203', 'Beban bunga', 'Beban', 'Debit', '', 209073.00),
(90, '', '200', 4, '5204', 'Beban pajak', 'Beban', 'Debit', '', 455071.00),
(92, '', '200', 1, '3201', 'Dividen', 'Ekuitas', 'Debit', '', 0.00),
(93, '', '100', 7, '1107', 'Beban dibayar dimuka', 'Aset', 'Debit', '', 286934.00),
(94, '', '100', 2, '5102', 'Kos BBTL', 'Beban', 'Debit', '', 0.00),
(95, '', '200', 5, '5205', 'Beban penyusutan', 'Beban', 'Debit', '', 0.00),
(96, '', '200', 6, '5206', 'Beban utilitas', 'Beban', 'Debit', '', 0.00),
(97, '', '100', 3, '5103', 'Kos TKL', 'Beban', 'Debit', '', 0.00),
(98, '', '100', 4, '5104', 'Kos TKTL', 'Beban', 'Debit', '', 0.00),
(99, '', '100', 5, '5105', 'KOP', 'Beban', 'Debit', '', 0.00),
(101, '', '100', 3, '4103', 'Potongan penjualan', 'Pendapatan', 'Debit', '', 0.00),
(102, '', '100', 6, '5106', 'KPrPT', 'Beban', 'Debit', '', 0.00);

--
-- Triggers `akun`
--
DELIMITER $$
CREATE TRIGGER `tg_kode_induk_auto` BEFORE INSERT ON `akun` FOR EACH ROW BEGIN
    DECLARE last_kode INT;

    SELECT MAX(kode_induk) INTO last_kode FROM akun;

    IF last_kode IS NULL THEN
        SET NEW.kode_induk = 1000;
    ELSE
        SET NEW.kode_induk = last_kode + 1000;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `jurnal`
--

CREATE TABLE `jurnal` (
  `id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `kode_bukti` varchar(50) DEFAULT NULL,
  `keterangan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jurnal`
--

INSERT INTO `jurnal` (`id`, `tanggal`, `kode_bukti`, `keterangan`) VALUES
(98, '2025-01-06', 'JRN20251220155428', 'Peminjaman modal dari bank'),
(99, '2025-01-07', 'JRN20251220155548', 'Membeli mesin dan peralatan baru'),
(100, '2025-01-08', 'JRN20251220155712', 'Membeli bahan baku'),
(101, '2025-01-08', 'JRN20251220155814', 'Pemisahan antar bahan baku langsung dan tidak langsung'),
(102, '2025-01-13', 'JRN20251220155906', 'Pembayaran gaji'),
(103, '2025-01-13', 'JRN20251220160118', 'Pemisahan antar gaji TKL dan TKTL'),
(104, '2025-01-15', 'JRN20251220160253', 'Pembayaran beban dan penyusutan'),
(106, '2025-01-17', 'JRN20251220160834', 'Pemindahan beban tidak langsung ke KOP'),
(107, '2025-01-17', 'JRN20251220161738', 'Pemindahan kos TKL dan KOP ke PDP'),
(108, '2025-01-20', 'JRN20251220161925', 'Hasil produk jadi'),
(109, '2025-01-24', 'JRN20251220162036', 'Penjualan barang'),
(111, '2025-01-24', 'JRN20251220163546', 'Kos penjualan'),
(112, '2025-01-31', 'JRN20251220164147', 'Penjualan barang bekas');

-- --------------------------------------------------------

--
-- Table structure for table `jurnal_detail`
--

CREATE TABLE `jurnal_detail` (
  `id` int(11) NOT NULL,
  `jurnal_id` int(11) NOT NULL,
  `akun_id` int(11) NOT NULL,
  `debit` decimal(15,2) DEFAULT 0.00,
  `kredit` decimal(15,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jurnal_detail`
--

INSERT INTO `jurnal_detail` (`id`, `jurnal_id`, `akun_id`, `debit`, `kredit`) VALUES
(222, 98, 42, 500000.00, 0.00),
(223, 98, 55, 0.00, 500000.00),
(224, 99, 79, 120000.00, 0.00),
(225, 99, 80, 30000.00, 0.00),
(226, 99, 42, 0.00, 150000.00),
(227, 100, 48, 67000.00, 0.00),
(228, 100, 54, 0.00, 67000.00),
(229, 101, 49, 54000.00, 0.00),
(230, 101, 94, 6000.00, 0.00),
(231, 101, 48, 0.00, 60000.00),
(232, 102, 70, 55000.00, 0.00),
(233, 102, 42, 0.00, 55000.00),
(234, 103, 97, 42000.00, 0.00),
(235, 103, 98, 13000.00, 0.00),
(236, 103, 70, 0.00, 55000.00),
(237, 104, 96, 8000.00, 0.00),
(238, 104, 95, 18000.00, 0.00),
(239, 104, 42, 0.00, 8000.00),
(240, 104, 85, 0.00, 18000.00),
(246, 106, 99, 45000.00, 0.00),
(247, 106, 94, 0.00, 6000.00),
(248, 106, 98, 0.00, 13000.00),
(249, 106, 95, 0.00, 18000.00),
(250, 106, 96, 0.00, 8000.00),
(251, 107, 49, 87000.00, 0.00),
(252, 107, 97, 0.00, 42000.00),
(253, 107, 99, 0.00, 45000.00),
(254, 108, 50, 110000.00, 0.00),
(255, 108, 49, 0.00, 110000.00),
(256, 109, 44, 150000.00, 0.00),
(257, 109, 64, 0.00, 150000.00),
(260, 111, 102, 100000.00, 0.00),
(261, 111, 50, 0.00, 100000.00),
(262, 112, 42, 100000.00, 0.00),
(263, 112, 101, 10000.00, 0.00),
(264, 112, 88, 0.00, 110000.00);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(1, 'admin', '$2y$10$L5fNKcVU60aC5jehKn.hSO5MUa4vV2AFp.r0CUbm5VwO8pJrM9zp2', 'admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `akun`
--
ALTER TABLE `akun`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_final` (`kode_final`);

--
-- Indexes for table `jurnal`
--
ALTER TABLE `jurnal`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `jurnal_detail`
--
ALTER TABLE `jurnal_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jurnal_id` (`jurnal_id`),
  ADD KEY `akun_id` (`akun_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `akun`
--
ALTER TABLE `akun`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT for table `jurnal`
--
ALTER TABLE `jurnal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;

--
-- AUTO_INCREMENT for table `jurnal_detail`
--
ALTER TABLE `jurnal_detail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=265;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `jurnal_detail`
--
ALTER TABLE `jurnal_detail`
  ADD CONSTRAINT `jurnal_detail_ibfk_1` FOREIGN KEY (`jurnal_id`) REFERENCES `jurnal` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `jurnal_detail_ibfk_2` FOREIGN KEY (`akun_id`) REFERENCES `akun` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
