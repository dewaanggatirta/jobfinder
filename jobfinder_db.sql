-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 23 Nov 2025 pada 13.14
-- Versi server: 8.0.30
-- Versi PHP: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Basis data: `jobfinder_db`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `admin`
--

CREATE TABLE `admin` (
  `id` int NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`) VALUES
(5, 'admin', '$2y$10$HLden/Tpw.taSAPeAH5jRuimCdW3Dc4QVKuGzD.AoBdQ99XvoWxG2');

-- --------------------------------------------------------

--
-- Struktur dari tabel `applications`
--

CREATE TABLE `applications` (
  `id` int NOT NULL,
  `job_id` int NOT NULL,
  `user_id` int NOT NULL,
  `cv_file` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `applied_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(20) COLLATE utf8mb4_general_ci DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `applications`
--

INSERT INTO `applications` (`id`, `job_id`, `user_id`, `cv_file`, `applied_at`, `created_at`, `status`) VALUES
(5, 13, 3, '1763619002_aaaaaaaaaaa.pdf', '2025-11-20 06:10:02', '2025-11-20 14:10:02', 'pending'),
(6, 4, 3, '1763896867_aaaaaaaaaaa.pdf', '2025-11-23 11:21:07', '2025-11-23 19:21:07', 'pending'),
(7, 14, 3, '1763899277_aaaaaaaaaaa.pdf', '2025-11-23 12:01:17', '2025-11-23 20:01:17', 'pending'),
(8, 15, 3, '1763901751_aaaaaaaaaaa.pdf', '2025-11-23 12:42:31', '2025-11-23 20:42:31', 'pending'),
(9, 15, 3, '1763903261_aaaaaaaaaaa.pdf', '2025-11-23 13:07:41', '2025-11-23 21:07:41', 'pending');

-- --------------------------------------------------------

--
-- Struktur dari tabel `companies`
--

CREATE TABLE `companies` (
  `id` int NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` text,
  `logo` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `companies`
--

INSERT INTO `companies` (`id`, `company_name`, `email`, `password`, `contact_person`, `phone`, `address`, `logo`, `website`, `created_at`) VALUES
(1, 'pt.jaya makmur', 'angga@gmail.com', '$2y$10$X69uaDFBiUj/CnOYomjWZek0bbmR/zIKirmo3HVm4UUuVqtr8XQhy', NULL, NULL, NULL, NULL, NULL, '2025-11-23 20:29:20');

-- --------------------------------------------------------

--
-- Struktur dari tabel `jobs`
--

CREATE TABLE `jobs` (
  `id` int NOT NULL,
  `title` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `company` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `location` varchar(120) COLLATE utf8mb4_general_ci NOT NULL,
  `category` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'Full-time',
  `salary` varchar(80) COLLATE utf8mb4_general_ci DEFAULT '',
  `description` text COLLATE utf8mb4_general_ci,
  `logo` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `posted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(20) COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `owner_id` int DEFAULT NULL,
  `company_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `jobs`
--

INSERT INTO `jobs` (`id`, `title`, `company`, `location`, `category`, `type`, `salary`, `description`, `logo`, `posted_at`, `status`, `owner_id`, `company_id`) VALUES
(2, 'Akuntan Junior', 'Solusi Keuangan', 'Bandung', 'IT & Software', 'Freelance', 'IDR 5.000.000 - 8.000.000', 'Menangani pembukuan dan pelaporan pajak.', NULL, '2025-10-29 11:11:24', 'approved', NULL, NULL),
(3, 'Marketing Officer', 'Pemasaran Nusantara', 'Bali', 'Marketing', 'Contract', 'IDR 4.000.000 - 7.000.000', 'Menyusun strategi pemasaran digital.', NULL, '2025-10-29 11:11:24', 'rejected', NULL, NULL),
(4, 'UI/UX Designer', 'Studio Desain', 'Yogyakarta', 'Desain', 'Part-time', 'IDR 6.000.000 - 10.000.000', 'Mendesain antarmuka aplikasi mobile.', NULL, '2025-10-29 11:11:24', 'rejected', NULL, NULL),
(5, 'Admin Office', 'Perusahaan ABC', 'Surabaya', 'Administrasi', 'Full-time', 'IDR 3.500.000 - 5.000.000', 'Mengelola administrasi kantor dan dokumen.', NULL, '2025-10-29 11:11:24', 'rejected', NULL, NULL),
(10, 'cs', 'pt mencari cinta sejati', 'jakarta', 'IT & Software', 'Part Time', '2.000.000', 'aaaaaaa', NULL, '2025-11-19 15:10:13', 'active', 4, NULL),
(11, 'aaa', 'aaa', 'aa', 'Marketing', 'Full Time', '2.000.000', 'aaa', 'uploads/logos/691de5703503b.png', '2025-11-19 15:42:40', 'active', NULL, NULL),
(12, 'eee', 'eee', 'ee', 'IT & Software', 'Full Time', 'e', 'eee', NULL, '2025-11-19 15:54:13', 'active', 5, NULL),
(13, 'aaa', 'aa', 'aa', 'Human Resources', 'Full Time', 'IDR 5.000.000 - 8.000.000', 'aa', 'uploads/logos/691eaf0f0d1ce.png', '2025-11-20 06:02:55', 'active', NULL, NULL),
(14, 'rrr', 'rr', 'rr', 'Design', 'Part Time', 'IDR 5.000.000 - 8.000.000', 'rrr', NULL, '2025-11-23 11:44:14', 'active', NULL, NULL),
(15, 'vvv', 'pt.jaya makmur', 'vvv', 'Marketing', 'Full Time', '2.000.000', 'vvv', NULL, '2025-11-23 12:40:18', 'active', NULL, 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `cv_file` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `role` varchar(20) COLLATE utf8mb4_general_ci DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `cv_file`, `password`, `created_at`, `role`) VALUES
(1, 'Djarot', 'wahyuneyy86@gmail.com', NULL, '$2y$10$LtS6cRdXhGR5P2QyTF3bqOWdENyvMdPsihXIIEpA1DSGqbSfDAId.', '2025-10-29 12:12:45', 'user'),
(2, 'angga', 'anggatirta2350@gmail.com', NULL, '$2y$10$UGOtHtWGvB9cIr0I6HAvkuML37P8QamN1JRM4TZHcJri8Hg4TyXUy', '2025-11-10 01:24:56', 'user'),
(3, 'angga', 'angga@gmail.com', NULL, '$2y$10$J/TML3TEWtayRuWfQR0UleUYS5ielWYnXP5KF2oS.Gto6r3G692BO', '2025-11-17 01:18:58', 'user'),
(4, 'alexniboss', 'anggat@gmail.com', NULL, '$2y$10$HAhRQvo4jUEHCKROfZi7UOUh2YOuFLa68NMcEvExtFUid3gtJx0Ey', '2025-11-19 15:06:15', 'perusahaan'),
(5, 'perusahaan', 'perusahaan@gmail.com', NULL, '$2y$10$EgUMBq92oqOwx27avqKp5ODJnUeGe8Kg1aHqx.vFQsq4qysPD8OC.', '2025-11-19 15:50:59', 'perusahaan');

--
-- Indeks untuk tabel yang dibuang
--

--
-- Indeks untuk tabel `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indeks untuk tabel `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `job_id` (`job_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeks untuk tabel `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_jobs_company` (`company_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `applications`
--
ALTER TABLE `applications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `companies`
--
ALTER TABLE `companies`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
