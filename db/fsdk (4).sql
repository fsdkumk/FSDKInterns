-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 13, 2025 at 03:10 AM
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
-- Database: `fsdk`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `message`, `date`, `created_at`) VALUES
(2, 'Industry Training Briefing on', 'Attendance is compulsory', '2025-05-23', '2025-05-05 08:41:04'),
(3, 'Online briefing', 'Must attend', '2025-05-23', '2025-05-05 08:48:10'),
(4, 'Online Meeting', 'Must attend', '2025-05-24', '2025-05-07 07:36:37');

-- --------------------------------------------------------

--
-- Table structure for table `before_li`
--

CREATE TABLE `before_li` (
  `id` int(11) NOT NULL,
  `form_name` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `due_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `button_name` varchar(255) NOT NULL,
  `redirect_url` varchar(255) DEFAULT NULL,
  `require_upload` enum('Yes','No') DEFAULT 'No'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `before_li`
--

INSERT INTO `before_li` (`id`, `form_name`, `file_path`, `due_date`, `created_at`, `button_name`, `redirect_url`, `require_upload`) VALUES
(167, '4. LI_BUKU LOG HARIAN (Submit setiap Bulan).doc', 'form_67a7f903d16572.80318104.doc', '2025-02-21 08:38:00', '2025-02-08 17:38:27', '', NULL, 'No'),
(223, '3. LI_BORANG PEMBERITAHUAN (Hari Pertama Latihan Industri)', 'uploads/form_67fca8bdb0aec_3. LI_BORANG PEMBERITAHUAN (Hari Pertama Latihan Industri).doc', '2025-04-24 14:18:00', '2025-04-14 06:18:37', '', '', 'No'),
(224, '2. LI_BORANG PERAKUAN LEPAS TANGGUNG OLEH PELAJAR KEPADA FAKULTI (Sebelum Latihan Industri)', 'uploads/form_67fca8e6d3f36_2. LI_BORANG PERAKUAN LEPAS TANGGUNG OLEH PELAJAR KEPADA FAKULTI (Sebelum Latihan Industri).doc', '2025-04-19 14:19:00', '2025-04-14 06:19:18', '', '', 'No'),
(225, '1. LI_BORANG MAKLUM BALAS INDUSTRI NEW (Sebelum Latihan Industri)', 'uploads/form_67fca90cad18e_1. LI_BORANG MAKLUM BALAS INDUSTRI NEW (Sebelum Latihan Industri).doc', '2025-04-25 14:19:00', '2025-04-14 06:19:56', 'fill in', 'http://10.3.244.152/LI/form.php', 'Yes');

-- --------------------------------------------------------

--
-- Table structure for table `form`
--

CREATE TABLE `form` (
  `id` int(11) NOT NULL,
  `student_name` varchar(255) NOT NULL,
  `programme` varchar(255) NOT NULL,
  `duration` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `internship_end_date` date DEFAULT NULL,
  `reporting_date` date NOT NULL,
  `reporting_time` time NOT NULL,
  `industry_name` varchar(255) NOT NULL,
  `industry_address` text NOT NULL,
  `state` varchar(255) DEFAULT NULL,
  `supervisor_name` varchar(255) NOT NULL,
  `supervisor_phone` varchar(20) NOT NULL,
  `supervisor_email` varchar(255) NOT NULL,
  `remarks` text DEFAULT NULL,
  `allowance_amount` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `which_form` varchar(255) DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `form`
--

INSERT INTO `form` (`id`, `student_name`, `programme`, `duration`, `email`, `phone`, `internship_end_date`, `reporting_date`, `reporting_time`, `industry_name`, `industry_address`, `state`, `supervisor_name`, `supervisor_phone`, `supervisor_email`, `remarks`, `allowance_amount`, `created_at`, `which_form`, `attachment`) VALUES
(12, 'student2', 'IT', '27/06/2003-27/12/2004', '34DDT22F1004@example.com', '01140660755', NULL, '0000-00-00', '00:00:00', '', 'Lot 3301 Kg Landak, Pengkalan Chepa', 'Kelantan', '', '', '', '', '', '2025-03-04 01:15:17', '1. LI_BORANG MAKLUM BALAS INDUSTRI NEW (Sebelum Latihan Industri)', NULL),
(13, 'TINI1', 'IT', '27/06/2003-27/12/2004', 'student1@gmail.com', '01140660755', NULL, '0000-00-00', '00:00:00', '', 'Lot 3301 Kg Landak, Pengkalan Chepa', 'Kelantan', '', '', '', '', '', '2025-03-04 01:35:00', '1. LI_BORANG MAKLUM BALAS INDUSTRI NEW (Sebelum Latihan Industri)', NULL),
(14, 'Alice', '', '', 'alice@example.com', '', NULL, '0000-00-00', '00:00:00', '', '', NULL, '', '', '', NULL, NULL, '2025-03-05 00:21:20', '1. LI_BORANG MAKLUM BALAS INDUSTRI NEW (Sebelum Latihan Industri)', NULL),
(15, 'FAT', 'qqqq', '27/06/2003-27/12/2004', 'fat@gmail.com', '01140660755', NULL, '0000-00-00', '00:00:00', '', 'Lot 3301 Kg Landak, Pengkalan Chepa', 'Kelantan', '', '', '', '', '', '2025-03-19 00:43:08', '1. LI_BORANG MAKLUM BALAS INDUSTRI NEW (Sebelum Latihan Industri)', NULL),
(18, 'TINI', 'IT', '27/06/2003-27/12/2004', 'tini@gmail.com', '01140660755', NULL, '0000-00-00', '00:00:00', '', 'Lot 3301 Kg Landak, Pengkalan Chepa', 'Kelantan', '', '', '', '', '', '2025-03-25 02:33:57', '1. LI_BORANG MAKLUM BALAS INDUSTRI NEW (Sebelum Latihan Industri)', NULL),
(19, 'T', 'IT', '27/06/2003-27/12/2004', 't@gmail.com', '01140660755', '2025-04-16', '0000-00-00', '00:00:00', '', 'Lot 3301 Kg Landak, Pengkalan Chepa', 'Kelantan', '', '', '', NULL, NULL, '2025-04-06 01:32:05', '1. LI_BORANG MAKLUM BALAS INDUSTRI NEW (Sebelum Latihan Industri)', NULL),
(21, 'TINI', 'IT', '27/06/2003-27/12/2004', 'fatini@gmail.com', '01140660755', NULL, '0000-00-00', '00:00:00', '', 'Lot 3301 Kg Landak, Pengkalan Chepa', 'Kelantan', '', '', '', NULL, NULL, '2025-04-06 07:01:13', '1. LI_BORANG MAKLUM BALAS INDUSTRI NEW (Sebelum Latihan Industri)', NULL),
(22, 'qqqqq', 'IT', '27/06/2003-27/12/2004', '34DDT22F1002@example.com', '01140660755', NULL, '0000-00-00', '00:00:00', '', 'Lot 3301 Kg Landak, Pengkalan Chepa', 'Kelantan', '', '', '', '', '', '2025-04-07 02:57:43', '1. LI_BORANG MAKLUM BALAS INDUSTRI NEW (Sebelum Latihan Industri)', 'uploads/1743994663_1740965506_2. LI_BORANG PERAKUAN LEPAS TANGGUNG OLEH PELAJAR KEPADA FAKULTI (Sebelum Latihan Industri) (1).doc'),
(23, 'diana', 'IT', '27/06/2003-27/12/2004', 'diana@example.com', '01140660755', NULL, '0000-00-00', '00:00:00', '', 'Lot 3301 Kg Landak, Pengkalan Chepa', 'Kelantan', '', '', '', '', '', '2025-04-16 03:37:07', '1. LI_BORANG MAKLUM BALAS INDUSTRI NEW (Sebelum Latihan Industri)', ''),
(24, 'student3', 'IT', '27/06/2003-27/12/2004', '34DDT22F1003@example.com', '01140660755', NULL, '0000-00-00', '00:00:00', '', 'Lot 3301 Kg Landak, Pengkalan Chepa', 'Kelantan', '', '', '', '', '', '2025-04-27 02:11:19', '1. LI_BORANG MAKLUM BALAS INDUSTRI NEW (Sebelum Latihan Industri)', ''),
(25, 'student5', 'IT', '27/06/2003-27/12/2004', '34DDT22F1005@example.com', '01140660755', NULL, '0000-00-00', '00:00:00', '', 'Lot 3301 Kg Landak, Pengkalan Chepa', 'Kelantan', '', '', '', '', '', '2025-04-27 02:36:24', '1. LI_BORANG MAKLUM BALAS INDUSTRI NEW (Sebelum Latihan Industri)', ''),
(26, 'bob', 'qqqq', '27/06/2003-27/12/2004', 'bob@example.com', '01140660755', NULL, '0000-00-00', '00:00:00', '', 'Lot 3301 Kg Landak, Pengkalan Chepa', 'Kelantan', '', '', '', '', '', '2025-04-27 02:37:11', '1. LI_BORANG MAKLUM BALAS INDUSTRI NEW (Sebelum Latihan Industri)', ''),
(27, 'charlie', 'IT', '27/06/2003-27/12/2004', 'charlie@example.com', '01140660755', NULL, '0000-00-00', '00:00:00', '', 'Lot 3301 Kg Landak, Pengkalan Chepa', 'Kelantan', '', '', '', '', '', '2025-04-27 02:37:49', '1. LI_BORANG MAKLUM BALAS INDUSTRI NEW (Sebelum Latihan Industri)', ''),
(28, 'edward', 'IT', '27/06/2003-27/12/2004', 'edward@example.com', '01140660755', NULL, '0000-00-00', '00:00:00', '', 'Lot 3301 Kg Landak, Pengkalan Chepa', 'Kelantan', '', '', '', '', '', '2025-04-27 02:38:11', '1. LI_BORANG MAKLUM BALAS INDUSTRI NEW (Sebelum Latihan Industri)', ''),
(30, 'TINI', 'IT', '27/06/2003-27/12/2004', 'tinieynadlan@gmail.com', '01140660755', NULL, '0000-00-00', '00:00:00', '', 'Lot 3301 Kg Landak, Pengkalan Chepa', 'Kelantan', '', '', '', '', '', '2025-05-05 07:07:04', '1. LI_BORANG MAKLUM BALAS INDUSTRI NEW (Sebelum Latihan Industri)', '');

-- --------------------------------------------------------

--
-- Table structure for table `letters`
--

CREATE TABLE `letters` (
  `id` int(11) NOT NULL,
  `letter_name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `letters`
--

INSERT INTO `letters` (`id`, `letter_name`, `description`, `file_path`) VALUES
(7, 'dean list umk.docx', 'student dean list', 'uploads/dean list umk.docx'),
(8, 'BORANG CUTI REHAT.doc', 'cuti', 'uploads/BORANG CUTI REHAT.doc'),
(11, 'dean list umk.docx', 'gerg', 'uploads/dean list umk.docx'),
(13, 'BORANG CUTI REHAT.doc', 'vf', 'uploads/BORANG CUTI REHAT.doc'),
(15, 'BORANG BAYARAN ELAUN PELAJAR PRAKTIKAL.docx', 'ff', 'uploads/BORANG BAYARAN ELAUN PELAJAR PRAKTIKAL.docx');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(10) NOT NULL,
  `name` varchar(255) NOT NULL,
  `matrix` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `lecturer` varchar(255) DEFAULT NULL,
  `semester` varchar(50) DEFAULT NULL,
  `status` enum('Haven''t got any LI yet','At least get 1 LI','Already confirmed / decided') NOT NULL DEFAULT 'Haven''t got any LI yet'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `name`, `matrix`, `email`, `lecturer`, `semester`, `status`) VALUES
(75, 'NUR FATINI BT NADLAN', '34DDT22F1002', 'tinieynadlan@gmail.com', 'Dr. John Doe', 'FEB 24/25', 'At least get 1 LI'),
(103, 'Alice Wonderland', '34DDT22F1006', 'alice@example.com', 'Dr. John Doe', 'FEB 24/25', 'Already confirmed / decided'),
(104, 'Bob Builder', '34DDT22F1007', 'bob@example.com', 'Dr. John Doe', 'FEB 24/25', 'Haven\'t got any LI yet'),
(105, 'Charlie Brown', '34DDT22F1008', 'charlie@example.com', 'Dr. Jane Smith', 'FEB 24/25', 'Haven\'t got any LI yet'),
(106, 'Diana Prince', '34DDT22F1009', 'diana@example.com', 'Dr. Jane Smith', 'SEP 25/26', 'At least get 1 LI'),
(107, 'Edward Elric', '34DDT22F1010', 'edward@example.com', 'Dr. Emily Johnson', 'SEP 25/26', 'Haven\'t got any LI yet');

-- --------------------------------------------------------

--
-- Table structure for table `student_uploads`
--

CREATE TABLE `student_uploads` (
  `id` int(11) NOT NULL,
  `student_name` varchar(255) NOT NULL,
  `student_email` varchar(255) NOT NULL,
  `form_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_uploads`
--

INSERT INTO `student_uploads` (`id`, `student_name`, `student_email`, `form_name`, `file_path`, `upload_date`) VALUES
(3, 'Student2', '34DDT22F1002@example.com', '2. LI_BORANG PERAKUAN LEPAS TANGGUNG OLEH PELAJAR KEPADA FAKULTI (Sebelum Latihan Industri)', 'uploads/1740965506_2. LI_BORANG PERAKUAN LEPAS TANGGUNG OLEH PELAJAR KEPADA FAKULTI (Sebelum Latihan Industri).doc', '2025-03-03 01:31:46'),
(4, 'Student2', '34DDT22F1002@example.com', '4. LI_BUKU LOG HARIAN (Submit setiap Bulan).doc', 'uploads/1740965535_1. LI_BORANG MAKLUM BALAS INDUSTRI NEW (Sebelum Latihan Industri).doc', '2025-03-03 01:32:15'),
(5, 'Student3', '34DDT22F1003@example.com', '4. LI_BUKU LOG HARIAN (Submit setiap Bulan).doc', 'uploads/1740973633_1. LI_BORANG MAKLUM BALAS INDUSTRI NEW (Sebelum Latihan Industri).doc', '2025-03-03 03:47:13'),
(8, 'Student3', '34DDT22F1003@example.com', '2. LI_BORANG PERAKUAN LEPAS TANGGUNG OLEH PELAJAR KEPADA FAKULTI (Sebelum Latihan Industri)', 'uploads/1744773177_BORANG KAJI SELIDIK PELAJAR TAHUN AKHIR EXIT SURVEY FORM .csv', '2025-04-16 03:12:57'),
(9, 'Charlie Brown', 'charlie@example.com', '3. LI_BORANG PEMBERITAHUAN (Hari Pertama Latihan Industri)', 'uploads/1744773398_BORANG KAJI SELIDIK PELAJAR TAHUN AKHIR EXIT SURVEY FORM .csv', '2025-04-16 03:16:38'),
(10, 'Student3', '34DDT22F1003@example.com', '4. LI_BUKU LOG HARIAN (Submit setiap Bulan).doc', 'uploads/1744774147_BORANG KAJI SELIDIK PELAJAR TAHUN AKHIR EXIT SURVEY FORM .csv', '2025-04-16 03:29:07'),
(11, 'Charlie Brown', 'charlie@example.com', '4. LI_BUKU LOG HARIAN (Submit setiap Bulan).doc', 'uploads/1744774187_BORANG KAJI SELIDIK PELAJAR TAHUN AKHIR EXIT SURVEY FORM .csv', '2025-04-16 03:29:47'),
(12, 'Diana Prince', 'diana@example.com', '4. LI_BUKU LOG HARIAN (Submit setiap Bulan).doc', 'uploads/1744774208_BORANG KAJI SELIDIK PELAJAR TAHUN AKHIR EXIT SURVEY FORM .csv', '2025-04-16 03:30:08'),
(13, 'Diana Prince', 'diana@example.com', '4. LI_BUKU LOG HARIAN (Submit setiap Bulan).doc', 'uploads/1745974929_dean list umk.docx', '2025-04-30 01:02:09'),
(14, 'NUR FATINI BT NADLAN', 'tinieynadlan@gmail.com', '4. LI_BUKU LOG HARIAN (Submit setiap Bulan).doc', 'uploads/1746418196_dean list umk.docx', '2025-05-05 04:09:56');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','lecturer','admin') NOT NULL DEFAULT 'student'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `role`) VALUES
(10, '', 'admin@gmail.com', NULL, 'AdminLI25%#', 'admin'),
(52, 'tini', 'tinieynadlan@gmail.com', '01340660755', 'dummy1234', 'student'),
(53, 'Bob Builder', 'bob@example.com', '01140760755', 'bob123', 'student'),
(54, 'Charlie Brown', 'charlie@example.com', '01150660755', '123456', 'student'),
(55, 'Diana Prince', 'diana@example.com', '01140660855', 'diana123', 'student'),
(56, 'Edward Elric', 'edward@example.com', '01440660755', 'edward123', 'student'),
(64, 'Dr. John Doe', 'john.doe@example.com', NULL, 'john123', 'lecturer'),
(65, 'Dr. Jane Smith', 'jane.smith@example.com', NULL, 'jane123', 'lecturer'),
(66, 'Dr. Emily Johnson', 'emily.johnson@example.com', NULL, 'lecturer123', 'lecturer');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `before_li`
--
ALTER TABLE `before_li`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `form`
--
ALTER TABLE `form`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `letters`
--
ALTER TABLE `letters`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `student_uploads`
--
ALTER TABLE `student_uploads`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `before_li`
--
ALTER TABLE `before_li`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=229;

--
-- AUTO_INCREMENT for table `form`
--
ALTER TABLE `form`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `letters`
--
ALTER TABLE `letters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=109;

--
-- AUTO_INCREMENT for table `student_uploads`
--
ALTER TABLE `student_uploads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
