-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 27, 2026 at 02:53 PM
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
-- Database: `habit_tracker`
--

-- --------------------------------------------------------

--
-- Table structure for table `dnevniki`
--

CREATE TABLE `dnevniki` (
  `id_dnevnika` int(11) NOT NULL,
  `id_navade` int(11) NOT NULL,
  `datum` datetime NOT NULL,
  `opravljeno` tinyint(1) NOT NULL,
  `komentar` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dnevniki`
--

INSERT INTO `dnevniki` (`id_dnevnika`, `id_navade`, `datum`, `opravljeno`, `komentar`) VALUES
(1, 1, '2026-03-12 00:00:00', 1, ''),
(3, 1, '2026-03-13 00:00:00', 1, ''),
(4, 3, '2026-03-13 00:00:00', 1, ''),
(5, 3, '2026-03-12 00:00:00', 1, ''),
(6, 3, '2026-03-06 00:00:00', 1, ''),
(7, 3, '2026-03-04 00:00:00', 0, ''),
(8, 3, '2026-03-02 00:00:00', 1, ''),
(9, 3, '2026-03-01 00:00:00', 1, ''),
(10, 3, '2026-03-10 00:00:00', 1, ''),
(11, 3, '2026-03-11 00:00:00', 0, ''),
(12, 3, '2026-03-07 00:00:00', 0, ''),
(13, 3, '2026-03-08 00:00:00', 1, ''),
(14, 3, '2026-03-03 00:00:00', 0, ''),
(15, 3, '2026-03-05 00:00:00', 1, ''),
(16, 3, '2026-03-09 00:00:00', 1, ''),
(17, 1, '2026-03-11 00:00:00', 1, ''),
(18, 1, '2026-03-10 00:00:00', 1, ''),
(19, 5, '2026-03-13 00:00:00', 1, ''),
(20, 5, '2026-03-12 00:00:00', 1, ''),
(21, 5, '2026-03-11 00:00:00', 1, ''),
(22, 5, '2026-03-10 00:00:00', 1, ''),
(23, 5, '2026-03-09 00:00:00', 1, ''),
(24, 5, '2026-03-07 00:00:00', 1, ''),
(25, 5, '2026-03-06 00:00:00', 1, ''),
(26, 5, '2026-03-05 00:00:00', 1, ''),
(27, 5, '2026-03-03 00:00:00', 1, ''),
(28, 5, '2026-03-02 00:00:00', 1, ''),
(29, 5, '2026-03-04 00:00:00', 1, ''),
(30, 3, '2026-02-28 00:00:00', 1, ''),
(31, 3, '2026-02-27 00:00:00', 1, ''),
(32, 3, '2026-02-26 00:00:00', 1, ''),
(33, 3, '2026-02-25 00:00:00', 1, ''),
(34, 1, '2026-03-05 00:00:00', 1, ''),
(35, 1, '2026-03-07 00:00:00', 1, ''),
(36, 1, '2026-03-09 00:00:00', 0, ''),
(37, 3, '2026-03-14 00:00:00', 0, ''),
(38, 3, '2026-02-11 00:00:00', 0, ''),
(39, 3, '2026-02-19 00:00:00', 0, ''),
(40, 3, '2026-02-04 00:00:00', 0, ''),
(41, 1, '2026-03-14 00:00:00', 1, ''),
(42, 6, '2026-03-14 00:00:00', 1, ''),
(43, 6, '2026-03-13 00:00:00', 1, ''),
(44, 7, '2026-03-14 00:00:00', 1, ''),
(45, 7, '2026-03-13 00:00:00', 1, ''),
(46, 7, '2026-03-12 00:00:00', 1, ''),
(47, 7, '2026-03-04 00:00:00', 1, ''),
(48, 7, '2026-03-03 00:00:00', 1, ''),
(49, 7, '2026-03-02 00:00:00', 1, ''),
(50, 7, '2026-03-07 00:00:00', 1, ''),
(51, 7, '2026-03-01 00:00:00', 1, ''),
(52, 8, '2026-03-14 00:00:00', 1, ''),
(53, 8, '2026-03-13 00:00:00', 1, ''),
(54, 8, '2026-03-12 00:00:00', 1, ''),
(55, 9, '2026-03-19 00:00:00', 1, ''),
(56, 9, '2026-03-18 00:00:00', 1, ''),
(57, 9, '2026-03-17 00:00:00', 1, ''),
(58, 9, '2026-03-15 00:00:00', 1, ''),
(59, 9, '2026-03-14 00:00:00', 1, ''),
(60, 1, '2026-03-19 00:00:00', 1, ''),
(61, 9, '2026-03-12 00:00:00', 1, ''),
(62, 9, '2026-03-13 00:00:00', 1, ''),
(63, 10, '2026-03-19 00:00:00', 1, ''),
(64, 10, '2026-03-18 00:00:00', 1, ''),
(65, 10, '2026-03-17 00:00:00', 1, ''),
(66, 10, '2026-03-14 00:00:00', 1, ''),
(67, 10, '2026-03-15 00:00:00', 1, ''),
(68, 10, '2026-03-12 00:00:00', 1, ''),
(69, 10, '2026-03-16 00:00:00', 1, ''),
(70, 10, '2026-03-13 00:00:00', 1, ''),
(71, 10, '2026-03-11 00:00:00', 1, ''),
(72, 10, '2026-03-10 00:00:00', 1, ''),
(73, 10, '2026-03-09 00:00:00', 1, ''),
(74, 10, '2026-03-02 00:00:00', 1, ''),
(75, 10, '2026-03-03 00:00:00', 1, ''),
(76, 10, '2026-03-04 00:00:00', 1, ''),
(77, 10, '2026-03-05 00:00:00', 1, ''),
(78, 10, '2026-03-06 00:00:00', 1, ''),
(79, 10, '2026-03-07 00:00:00', 1, ''),
(80, 10, '2026-03-08 00:00:00', 1, ''),
(81, 10, '2026-03-01 00:00:00', 1, ''),
(82, 10, '2026-02-28 00:00:00', 1, ''),
(83, 10, '2026-02-27 00:00:00', 1, ''),
(84, 10, '2026-02-20 00:00:00', 1, ''),
(85, 10, '2026-02-26 00:00:00', 1, ''),
(86, 10, '2026-02-25 00:00:00', 1, ''),
(87, 10, '2026-02-24 00:00:00', 1, ''),
(88, 10, '2026-02-23 00:00:00', 1, ''),
(89, 10, '2026-02-22 00:00:00', 1, ''),
(90, 10, '2026-02-21 00:00:00', 1, ''),
(91, 10, '2026-02-19 00:00:00', 1, ''),
(92, 10, '2026-02-18 00:00:00', 1, ''),
(93, 10, '2026-02-17 00:00:00', 1, ''),
(94, 9, '2026-03-05 00:00:00', 1, ''),
(95, 9, '2026-03-11 00:00:00', 1, ''),
(96, 9, '2026-03-10 00:00:00', 1, ''),
(97, 9, '2026-03-09 00:00:00', 1, ''),
(98, 9, '2026-03-16 00:00:00', 1, ''),
(99, 9, '2026-03-06 00:00:00', 1, ''),
(100, 9, '2026-03-07 00:00:00', 1, ''),
(101, 9, '2026-03-08 00:00:00', 1, ''),
(102, 9, '2026-03-04 00:00:00', 1, ''),
(103, 9, '2026-03-03 00:00:00', 1, ''),
(104, 9, '2026-03-02 00:00:00', 1, ''),
(105, 9, '2026-03-01 00:00:00', 0, ''),
(106, 9, '2026-02-28 00:00:00', 1, ''),
(107, 9, '2026-02-21 00:00:00', 1, ''),
(108, 9, '2026-02-27 00:00:00', 1, ''),
(109, 9, '2026-02-20 00:00:00', 1, ''),
(110, 9, '2026-02-26 00:00:00', 1, ''),
(111, 9, '2026-02-19 00:00:00', 1, ''),
(112, 9, '2026-02-25 00:00:00', 1, ''),
(113, 9, '2026-02-18 00:00:00', 1, ''),
(114, 9, '2026-02-24 00:00:00', 1, ''),
(115, 9, '2026-02-17 00:00:00', 1, ''),
(116, 9, '2026-02-23 00:00:00', 1, ''),
(117, 9, '2026-02-16 00:00:00', 1, ''),
(118, 9, '2026-02-22 00:00:00', 1, ''),
(119, 9, '2026-02-15 00:00:00', 1, ''),
(120, 9, '2026-02-14 00:00:00', 1, ''),
(121, 9, '2026-02-13 00:00:00', 1, ''),
(122, 9, '2026-02-12 00:00:00', 1, ''),
(123, 9, '2026-02-11 00:00:00', 1, ''),
(124, 9, '2026-02-10 00:00:00', 1, ''),
(125, 9, '2026-02-09 00:00:00', 1, ''),
(126, 9, '2026-02-07 00:00:00', 1, ''),
(127, 9, '2026-02-08 00:00:00', 1, ''),
(128, 9, '2026-02-06 00:00:00', 1, ''),
(129, 9, '2026-02-03 00:00:00', 1, ''),
(130, 9, '2026-02-05 00:00:00', 1, ''),
(131, 9, '2026-02-04 00:00:00', 1, ''),
(132, 9, '2026-02-02 00:00:00', 1, ''),
(133, 8, '2026-03-19 00:00:00', 1, ''),
(134, 7, '2026-03-19 00:00:00', 1, ''),
(135, 6, '2026-03-19 00:00:00', 1, ''),
(136, 3, '2026-03-19 00:00:00', 0, ''),
(137, 11, '2026-03-19 00:00:00', 1, ''),
(138, 12, '2026-03-20 00:00:00', 1, ''),
(139, 12, '2026-03-19 00:00:00', 1, ''),
(140, 12, '2026-03-09 00:00:00', 1, ''),
(141, 12, '2026-03-11 00:00:00', 1, ''),
(142, 12, '2026-03-12 00:00:00', 0, ''),
(143, 12, '2026-03-18 00:00:00', 1, ''),
(144, 12, '2026-03-17 00:00:00', 1, ''),
(145, 12, '2026-03-16 00:00:00', 1, ''),
(146, 12, '2026-03-14 00:00:00', 1, ''),
(147, 12, '2026-03-15 00:00:00', 1, ''),
(148, 12, '2026-03-10 00:00:00', 1, ''),
(149, 12, '2026-03-13 00:00:00', 1, ''),
(150, 8, '2026-03-20 00:00:00', 1, ''),
(151, 8, '2026-03-17 00:00:00', 1, ''),
(152, 8, '2026-03-16 00:00:00', 1, ''),
(153, 8, '2026-03-18 00:00:00', 1, ''),
(154, 12, '2026-03-27 00:00:00', 1, ''),
(155, 10, '2026-03-27 00:00:00', 1, ''),
(156, 12, '2026-03-01 00:00:00', 1, ''),
(157, 12, '2026-03-26 00:00:00', 0, ''),
(158, 12, '2026-03-25 00:00:00', 1, ''),
(159, 12, '2026-03-24 00:00:00', 1, ''),
(160, 10, '2026-03-26 00:00:00', 1, ''),
(161, 10, '2026-03-25 00:00:00', 1, ''),
(162, 10, '2026-03-24 00:00:00', 1, ''),
(163, 10, '2026-03-23 00:00:00', 1, ''),
(164, 10, '2026-03-22 00:00:00', 1, ''),
(165, 10, '2026-03-21 00:00:00', 1, ''),
(166, 10, '2026-03-20 00:00:00', 1, ''),
(167, 9, '2026-03-27 00:00:00', 1, ''),
(168, 8, '2026-03-27 00:00:00', 1, '');

-- --------------------------------------------------------

--
-- Table structure for table `kategorije`
--

CREATE TABLE `kategorije` (
  `id_kategorije` int(11) NOT NULL,
  `id_uporabnika` int(11) NOT NULL,
  `barva` varchar(50) NOT NULL,
  `ime` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kategorije`
--

INSERT INTO `kategorije` (`id_kategorije`, `id_uporabnika`, `barva`, `ime`) VALUES
(1, 1, '#4a9d6f', 'zdravje'),
(2, 1, '#c47c9f', 'osebno'),
(3, 2, '#4a9d6f', 'zdravje'),
(5, 1, '#49819c', 'Izobrazba'),
(6, 1, '#4a90e2', 'delo'),
(7, 1, '#b845ba', 'Spiritualno'),
(8, 3, '#4a9d6f', 'zdravje');

-- --------------------------------------------------------

--
-- Table structure for table `navade`
--

CREATE TABLE `navade` (
  `id_navade` int(100) NOT NULL,
  `id_uporabnika` int(100) NOT NULL,
  `id_kategorije` int(100) NOT NULL,
  `ime_navade` varchar(255) NOT NULL,
  `ponavljanje` enum('dnevno','tedensko','mesečno') NOT NULL,
  `izbrani_dnevi` varchar(255) NOT NULL,
  `del_dneva` varchar(50) NOT NULL,
  `cilj_kolicina` int(50) NOT NULL DEFAULT 1,
  `cilj_enota` varchar(50) NOT NULL,
  `cilj_obdobje` varchar(50) NOT NULL,
  `cilj_dni` int(50) NOT NULL,
  `opis` text DEFAULT NULL,
  `emoji` varchar(200) NOT NULL,
  `streak` int(255) NOT NULL DEFAULT 0,
  `ustvarjeno` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `navade`
--

INSERT INTO `navade` (`id_navade`, `id_uporabnika`, `id_kategorije`, `ime_navade`, `ponavljanje`, `izbrani_dnevi`, `del_dneva`, `cilj_kolicina`, `cilj_enota`, `cilj_obdobje`, `cilj_dni`, `opis`, `emoji`, `streak`, `ustvarjeno`) VALUES
(1, 1, 2, 'boks', 'dnevno', 'ponedeljek,torek,sreda,cetrtek,petek,sobota,nedelja', 'popoldne', 3, '0', '0', 0, '', '', 1, '2026-03-12 15:35:31'),
(3, 1, 2, 'izak bot tih ne dobis nalog', 'dnevno', 'ponedeljek,torek,sreda,cetrtek,petek,sobota,nedelja', 'zjutraj', 1, '0', '0', 0, 'bot tih !', '', 0, '2026-03-13 07:02:50'),
(5, 2, 3, 'tek', 'tedensko', 'ponedeljek,torek,sreda,cetrtek,petek,sobota', 'popoldne', 1, '0', '0', 0, 'Tekam ker hocem bit tako David Goggings', '', 5, '2026-03-13 08:53:26'),
(6, 1, 5, 'Ucenje', 'dnevno', 'ponedeljek,torek,sreda,cetrtek,petek,sobota,nedelja', 'zjutraj,popoldne,zvecer', 2, 'krat', 'na_dan', 0, '', '', 1, '2026-03-14 17:22:50'),
(7, 1, 1, 'meditacija', 'tedensko', 'ponedeljek,torek,sreda,cetrtek,petek,sobota,nedelja', 'zjutraj,popoldne,zvecer', 3, 'krat', 'na_dan', 0, '', '', 1, '2026-03-14 17:24:09'),
(8, 1, 2, 'branje', 'tedensko', 'ponedeljek,torek,sreda,cetrtek', 'zjutraj,popoldne,zvecer', 10, '0', '0', 0, '', '', 1, '2026-03-14 17:26:34'),
(9, 1, 2, 'meditacija', 'dnevno', 'ponedeljek,torek,sreda,cetrtek,petek,sobota,nedelja', 'zjutraj,popoldne,zvecer', 1, 'krat', 'na_dan', 50, '5min meditacije', '', 1, '2026-03-19 16:22:52'),
(10, 1, 6, 'asd', 'dnevno', 'ponedeljek,torek,sreda,cetrtek,petek,sobota,nedelja', 'zjutraj,popoldne,zvecer', 1, 'krat', 'na_dan', 40, '', '🧠', 39, '2026-03-19 16:31:26'),
(11, 3, 8, 'aa', 'dnevno', 'ponedeljek,torek,sreda,cetrtek,petek,sobota,nedelja', 'zjutraj,popoldne,zvecer', 1, 'krat', 'na_dan', 1, '', '', 1, '2026-03-19 18:43:57'),
(12, 1, 2, 'Gnater je slab', 'dnevno', 'ponedeljek,torek,sreda,cetrtek,petek,sobota,nedelja', 'zjutraj,popoldne,zvecer', 1, 'krat', 'na_dan', 10, 'peder', '🏃', 1, '2026-03-20 06:59:30');

-- --------------------------------------------------------

--
-- Table structure for table `uporabniki`
--

CREATE TABLE `uporabniki` (
  `id_uporabnika` int(11) NOT NULL,
  `uporabnisko_ime` varchar(20) NOT NULL,
  `email` varchar(30) NOT NULL,
  `hash_gesla` text NOT NULL,
  `profilna_slika` varchar(255) NOT NULL,
  `ustvarjeno` timestamp NOT NULL DEFAULT current_timestamp(),
  `vloga` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `uporabniki`
--

INSERT INTO `uporabniki` (`id_uporabnika`, `uporabnisko_ime`, `email`, `hash_gesla`, `profilna_slika`, `ustvarjeno`, `vloga`) VALUES
(1, 'Luka420', 'gorenjak.luka11@gmail.com', '$2y$10$/IolVTFNjIJWOzJwm3NXouF0tPT14LEOU0cP9JzDDcozYeS2Llq6i', 'ostalo/slike/profil/1_1773509121.png', '2026-03-09 15:46:18', 'uporabnik'),
(2, 'JanJurharLulek', 'janjurhar1@gmail.com', '$2y$10$mdiRK.wJ1dGfERDszVGUWuWtQ6/2ZyZEIjq9LZj1Nc3/kLW3RzCku', 'ostalo/slike/profil/2_1773945992.png', '2026-03-13 08:51:46', 'uporabnik'),
(3, 'Admin69', 'admin@gmail.com', '$2y$10$hO2KEkpMDsiM5HdWUbeFnefPBT7uIo4EJYyBNQ.wzGKEGhLQPCFBO', 'ostalo/slike/profil/3_1773945825.png', '2026-03-14 11:30:23', 'admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dnevniki`
--
ALTER TABLE `dnevniki`
  ADD PRIMARY KEY (`id_dnevnika`),
  ADD KEY `FOREIGN_NAVADE` (`id_navade`);

--
-- Indexes for table `kategorije`
--
ALTER TABLE `kategorije`
  ADD PRIMARY KEY (`id_kategorije`),
  ADD KEY `FOREIGN_UPORABIKA` (`id_uporabnika`);

--
-- Indexes for table `navade`
--
ALTER TABLE `navade`
  ADD PRIMARY KEY (`id_navade`),
  ADD KEY `FOREIGN` (`id_uporabnika`);

--
-- Indexes for table `uporabniki`
--
ALTER TABLE `uporabniki`
  ADD PRIMARY KEY (`id_uporabnika`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `dnevniki`
--
ALTER TABLE `dnevniki`
  MODIFY `id_dnevnika` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=169;

--
-- AUTO_INCREMENT for table `kategorije`
--
ALTER TABLE `kategorije`
  MODIFY `id_kategorije` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `navade`
--
ALTER TABLE `navade`
  MODIFY `id_navade` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `uporabniki`
--
ALTER TABLE `uporabniki`
  MODIFY `id_uporabnika` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
