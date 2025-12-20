-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 20, 2025 at 11:06 AM
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
(70, '', '200', 2, '5202', 'Beban Administratif', 'Beban', 'Debit', '', 740446.00),
(74, '', '100', 3, '2103', 'Utang beban', 'Liabilitas', 'Kredit', '', 1266838.00),
(75, '', '100', 4, '2104', 'Pendapatan diterima dimuka', 'Liabilitas', 'Kredit', '', 93820.00),
(76, '', '100', 6, '1106', 'KPrPT', 'Aset', 'Debit', '', 0.00),
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
(94, '', '100', 2, '5102', 'KOP', 'Beban', 'Debit', '', 0.00),
(95, '', '200', 5, '5205', 'Beban penyusutan', 'Beban', 'Debit', '', 0.00),
(96, '', '200', 6, '5206', 'Beban utilitas', 'Beban', 'Debit', '', 0.00);

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
(61, '2025-11-03', 'JRN20251220102452', 'Membeli bahan baku'),
(62, '2025-11-03', 'JRN20251220102609', 'Pemisahan antar bahan baku langsung dan tidak langsung'),
(63, '2025-11-07', 'JRN20251220102747', 'Membayar gaji karyawan'),
(64, '2025-11-07', 'JRN20251220102847', 'Pemisahan antar gaji TKL dan TKTL'),
(65, '2025-11-13', 'JRN20251220105051', 'Pembayaran beban dan penyusutan'),
(66, '2025-11-17', 'JRN20251220105659', 'Memasukkan beban ke dalam KOP'),
(67, '2025-11-18', 'JRN20251220105826', 'Memindahkan KOP ke PDP'),
(68, '2025-11-20', 'JRN20251220105923', 'Hasil produk jadi'),
(69, '2025-11-23', 'JRN20251220110100', 'Pendapatan penjualan '),
(70, '2025-11-23', 'JRN20251220110138', 'Kos penjualan');

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
(133, 61, 48, 6700.00, 0.00),
(134, 61, 54, 0.00, 6700.00),
(135, 62, 49, 5400.00, 0.00),
(136, 62, 94, 600.00, 0.00),
(137, 62, 48, 0.00, 6000.00),
(138, 63, 70, 5500.00, 0.00),
(139, 63, 42, 0.00, 5500.00),
(140, 64, 49, 4200.00, 0.00),
(141, 64, 94, 1300.00, 0.00),
(142, 64, 70, 0.00, 5500.00),
(143, 65, 96, 800.00, 0.00),
(144, 65, 95, 1800.00, 0.00),
(145, 65, 42, 0.00, 800.00),
(146, 65, 85, 0.00, 1800.00),
(147, 66, 94, 2600.00, 0.00),
(148, 66, 96, 0.00, 800.00),
(149, 66, 95, 0.00, 1800.00),
(150, 67, 49, 2550.00, 0.00),
(151, 67, 94, 0.00, 2550.00),
(152, 68, 50, 11000.00, 0.00),
(153, 68, 49, 0.00, 11000.00),
(154, 69, 44, 12000.00, 0.00),
(155, 69, 64, 0.00, 12000.00),
(156, 70, 76, 10000.00, 0.00),
(157, 70, 50, 0.00, 10000.00);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT for table `jurnal`
--
ALTER TABLE `jurnal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `jurnal_detail`
--
ALTER TABLE `jurnal_detail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=158;

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
