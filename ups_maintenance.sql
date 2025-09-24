-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 24, 2025 at 12:54 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ups_maintenance`
--

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_ups`
--

CREATE TABLE `maintenance_ups` (
  `maintenance_id` int(11) NOT NULL,
  `ups_id` int(11) NOT NULL,
  `tanggal_jadwal` datetime DEFAULT NULL,
  `jenis` enum('Preventive (Rutin)','Corrective (Perbaikan)') NOT NULL,
  `status` enum('Terjadwal','Selesai','Selesai (Terlambat)','Ditunda') DEFAULT 'Terjadwal',
  `tanggal_pelaksanaan` datetime DEFAULT NULL,
  `teknisi` varchar(100) DEFAULT NULL,
  `hasil_pengecekan` text DEFAULT NULL,
  `pengubahan` text DEFAULT NULL,
  `attachment_path` text NOT NULL,
  `catatan` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `maintenance_ups`
--

INSERT INTO `maintenance_ups` (`maintenance_id`, `ups_id`, `tanggal_jadwal`, `jenis`, `status`, `tanggal_pelaksanaan`, `teknisi`, `hasil_pengecekan`, `pengubahan`, `attachment_path`, `catatan`, `created_by`, `created_at`) VALUES
(1, 33, '2025-09-24 15:06:38', 'Preventive (Rutin)', 'Selesai (Terlambat)', '2025-09-24 15:07:00', 'Noir', 'Noir', 'Noir', '', NULL, 1, '2025-09-24 15:07:17'),
(2, 19, '2025-09-24 15:08:00', 'Preventive (Rutin)', 'Selesai', '2025-09-24 15:08:00', 'Rama', 'Untuknya oh tuan', 'Rana Kematian\r\n', '', NULL, 1, '2025-09-24 15:08:08');

-- --------------------------------------------------------

--
-- Table structure for table `penanggung_jawab`
--

CREATE TABLE `penanggung_jawab` (
  `pj_id` int(11) NOT NULL,
  `ups_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `departemen` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ups`
--

CREATE TABLE `ups` (
  `ups_id` int(11) NOT NULL,
  `nama_ups` varchar(255) NOT NULL,
  `lokasi` varchar(100) DEFAULT NULL,
  `merk` varchar(50) DEFAULT NULL,
  `tipe_ups` varchar(50) DEFAULT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `ukuran_kapasitas` varchar(100) DEFAULT NULL,
  `jumlah_baterai` int(11) NOT NULL,
  `perusahaan_maintenance` varchar(100) NOT NULL,
  `uploads` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ups`
--

INSERT INTO `ups` (`ups_id`, `nama_ups`, `lokasi`, `merk`, `tipe_ups`, `ip_address`, `ukuran_kapasitas`, `jumlah_baterai`, `perusahaan_maintenance`, `uploads`) VALUES
(2, 'UPS-GUBA', 'Kantor Utama', 'EATON', 'EATON 93E 200', '10.1.6.200', '200', 4, 'Vendor PT.SKB', ''),
(6, 'UPS-B', 'Data Center B', 'EATON', '93E HV 80000', '10.1.6.221', '80', 4, 'Vendor DATA', ''),
(8, 'UPS-K.Bola', 'Kamar Bola', 'EATON', 'EATON 9SX 6KI', '10.4.2.222', '6', 2, 'Vendor PT.SKB', ''),
(9, 'UPS-MCC', 'Mining Control Center', 'EATON', '9155-10-S-0', '10.3.2.246', '10', 2, 'Vendor PT.RAFF', ''),
(10, 'UPS-Proyek-P3', 'Proyek P3', 'EATON', 'PXGX UPS + Proyek-P2i', '10.8.1.232', '10', 2, 'Vendor PT.SKB', ''),
(11, 'UPS-PATB', 'PATB', 'EATON', 'PXGX UPS + EATON 9155-PATB', '10.8.1.238', '10', 2, 'Vendor PT.SKB', ''),
(12, 'UPS-Prokes-PTBA', 'Prokes PTBA', 'EATON', 'EATON 9SX 6KI', '10.8.1.244', '6', 2, 'Vendor PT.SKB', ''),
(14, 'UPS-Explorasi-PTBA', 'Explorasi PTBA', 'EATON', 'EATON 9SX 11000i', '10.3.6.236', '10', 2, 'Vendor PT.SKB', ''),
(15, 'UPS-Pool-Umum', 'Pool Umum', 'EATON', 'PXGX UPS + POOL UMUM', '10.4.1.241', '10', 2, 'Vendor PT.SKB', ''),
(16, 'UPS-Bengkel-Utama', 'Bengkel Utama', 'EATON', 'EATON 9SX 6Ki', '10.3.1.243', '6', 2, 'Vendor PT.SKB', ''),
(17, 'UPS-MTBU', 'MTBU', 'EATON', 'EATON 9SX 11000i', '10.3.6.243', '10', 2, 'Vendor PT.SKB', ''),
(18, 'UPS-Pusdiklat', 'Diklat', 'EATON', '9355 30I-N-0', '10.4.2.202', '20', 2, 'Vendor PT.RAFF', ''),
(19, 'UPS-Balitas', 'Balitas', 'EATON', 'EATON 9SX 6Ki', '10.4.1.203', '6', 2, 'Vendor PT.RAFF', ''),
(20, 'UPS-Pentam-PLPT-Klawas', 'Pentam', 'EATON', 'EATON 9SX 6Ki', '10.3.8.213', '10', 2, 'Vendor PT.RAFF', ''),
(21, 'UPS-K3L-PTBA', 'K3L PTBA', 'EATON', 'EATON 9SX 6Ki', '10.3.8.221', '6', 2, 'Vendor PT.SKB', ''),
(23, 'UPS-Bpk-Banko', 'Bpk Banko', 'EATON', 'EATON 9SX 6Ki', '10.3.6.204', '10', 2, 'Vendor PT.RAFF', ''),
(26, 'UPS-A', 'Data Center A', 'EATON', '93E HV 80000', '10.1.6.222', '80', 80, 'Vendor PROTEC', ''),
(31, 'UPS-Bricket-Banko', 'Bricket Banko', 'EATON', 'PXGX UPS + Bricket-Banko', '10.3.6.202', '10', 0, 'Vendor PT.SKB', ''),
(32, 'UPS-Gudang', 'Gudang', 'EATON', 'PXGX UPS + Gudang-Tambang', '10.3.1.245', '10', 0, 'Vendor PT.RAFF', ''),
(33, 'UPS-Iws-Lama', 'Iws Lama', 'EATON', 'Eaton 9130 6000', '10.3.1.249', '6', 0, 'Vendor PT.SKB', ''),
(34, 'UPS-Renhar', 'Renhar', 'EATON', 'EATON 9SX 6Ki', '10.3.8.224', '6', 0, 'Vendor PT.RAFF', ''),
(35, 'UPS-Smp-Mr', 'Smp Mr', 'EATON', 'Eaton 9SX 6Ki', '10.8.1.218', '6', 0, 'Vendor PT.RAFF', ''),
(36, 'UPS-PAB/TLS-1', 'PAB/TLS-1', 'EATON', '9155 10-S-0', '10.3.2.251', '6', 0, 'Vendor PT.SKB', ''),
(37, 'UPS-Penambangan-Klawas', 'Tambang Klawas', 'EATON', 'Eaton 9SX 6Ki', '10.3.8.225', '6', 0, 'Vendor PT.RAFF', ''),
(38, 'UPS-Iws-Baru', 'Iws Baru', 'EATON', 'PXGX UPS + Eaton 9355', '10.3.1.228', '20', 0, 'Vendor PT.RAFF', ''),
(39, 'UPS-MSF/Bak', 'MSF/Bak', 'EATON', '-', '10.4.3.205', '10', 0, 'Vendor PT.SKB', ''),
(40, 'UPS-Security', 'Security', 'EATON', '-', '10.8.1.206', '6', 0, 'Vendor PT.RAFF', ''),
(41, 'UPS-Keu.Upte', 'Keuangan Upte', 'EATON', '-', '10.8.1.247', '6', 0, 'Vendor PT.RAFF', '');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('admin') DEFAULT 'admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `nama`, `email`, `no_hp`, `password`, `role`) VALUES
(1, 'Noir', 'admin@ptba.co.id', '08123456789', '12', 'admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `maintenance_ups`
--
ALTER TABLE `maintenance_ups`
  ADD PRIMARY KEY (`maintenance_id`),
  ADD KEY `fk_ups` (`ups_id`),
  ADD KEY `fk_user` (`created_by`);

--
-- Indexes for table `penanggung_jawab`
--
ALTER TABLE `penanggung_jawab`
  ADD PRIMARY KEY (`pj_id`),
  ADD KEY `ups_id` (`ups_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `ups`
--
ALTER TABLE `ups`
  ADD PRIMARY KEY (`ups_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `maintenance_ups`
--
ALTER TABLE `maintenance_ups`
  MODIFY `maintenance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `penanggung_jawab`
--
ALTER TABLE `penanggung_jawab`
  MODIFY `pj_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ups`
--
ALTER TABLE `ups`
  MODIFY `ups_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `maintenance_ups`
--
ALTER TABLE `maintenance_ups`
  ADD CONSTRAINT `fk_maintenance_ups` FOREIGN KEY (`ups_id`) REFERENCES `ups` (`ups_id`),
  ADD CONSTRAINT `fk_maintenance_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `penanggung_jawab`
--
ALTER TABLE `penanggung_jawab`
  ADD CONSTRAINT `penanggung_jawab_ibfk_1` FOREIGN KEY (`ups_id`) REFERENCES `ups` (`ups_id`),
  ADD CONSTRAINT `penanggung_jawab_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
