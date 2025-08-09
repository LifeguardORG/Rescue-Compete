-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: db
-- Generation Time: May 10, 2025 at 06:24 PM
-- Server version: 10.11.11-MariaDB-ubu2204
-- PHP Version: 8.2.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `webappdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `Answer`
--

CREATE TABLE `Answer` (
  `ID` int(11) NOT NULL,
  `Question_ID` int(11) NOT NULL,
  `Text` text NOT NULL,
  `IsCorrect` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Answer`
--

INSERT INTO `Answer` (`ID`, `Question_ID`, `Text`, `IsCorrect`) VALUES
(20, 16, '110', 0),
(21, 16, '112', 1),
(22, 16, '911', 0),
(23, 16, '115', 0),
(36, 21, 'Rettungsring', 0),
(37, 21, 'Rettungsboje', 0),
(38, 21, 'Rettungsbrett', 1),
(39, 21, 'Rettungsleine', 0),
(44, 31, 'a', 0),
(45, 31, 'B', 1),
(46, 31, 'C', 0),
(47, 31, 'D', 0),
(48, 32, 'a', 0),
(49, 32, 's', 0),
(50, 32, 'd', 0),
(51, 32, 'f', 1),
(52, 33, 'fgd', 0),
(53, 33, 'd', 0),
(54, 33, 'dsf', 1),
(55, 33, 'dsf', 0),
(56, 34, 'dsg', 0),
(57, 34, 'fads', 0),
(58, 34, 'dfa', 1),
(59, 34, 'dsf', 0),
(60, 35, 'a', 0),
(61, 35, 'a', 0),
(62, 35, 'a', 1),
(63, 35, 'a', 0),
(64, 36, 'rt', 1),
(65, 36, 'fgfg', 0),
(66, 36, 'fgfg', 0),
(67, 36, 'vvb', 0),
(68, 37, 'asdf', 0),
(69, 37, 'asdf', 0),
(70, 37, 'sdf', 1),
(71, 37, 'edf', 0),
(72, 38, 'tzfghd', 1),
(73, 38, 'fgdgh', 0),
(74, 38, 'dfg fgh', 0),
(75, 38, 'fdgghj', 0),
(76, 39, 'fsdsdfg', 1),
(77, 39, 'bvcvx', 0),
(78, 39, 'bvcx', 0),
(79, 39, 'ydf', 0),
(80, 40, 'uz', 1),
(81, 40, 'hj', 0),
(82, 40, 'hj', 0),
(83, 40, 'kj', 0);

-- --------------------------------------------------------

--
-- Table structure for table `FormQuestion`
--

CREATE TABLE `FormQuestion` (
  `form_ID` int(11) NOT NULL,
  `question_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `FormQuestion`
--

INSERT INTO `FormQuestion` (`form_ID`, `question_ID`) VALUES
(46, 32),
(46, 37),
(46, 39),
(46, 40),
(47, 31),
(47, 33),
(47, 35),
(48, 34),
(48, 36),
(48, 38),
(49, 34),
(49, 36),
(49, 39),
(49, 40),
(50, 33),
(50, 37),
(50, 38),
(51, 31),
(51, 32),
(51, 35),
(52, 33),
(52, 35),
(52, 38),
(52, 39),
(53, 31),
(53, 36),
(53, 37),
(54, 32),
(54, 34),
(54, 40),
(55, 34),
(55, 36),
(55, 38),
(55, 40),
(56, 33),
(56, 35),
(56, 37),
(57, 31),
(57, 32),
(57, 39),
(58, 33),
(58, 34),
(58, 36),
(58, 38),
(59, 31),
(59, 39),
(59, 40),
(60, 32),
(60, 35),
(60, 37),
(61, 33),
(61, 34),
(61, 37),
(61, 40),
(62, 32),
(62, 36),
(62, 39),
(63, 31),
(63, 35),
(63, 38),
(64, 33),
(64, 37),
(64, 38),
(64, 39),
(65, 32),
(65, 35),
(65, 40),
(66, 31),
(66, 34),
(66, 36),
(67, 32),
(67, 34),
(67, 36),
(67, 39),
(68, 31),
(68, 33),
(68, 35),
(69, 37),
(69, 38),
(69, 40),
(70, 33),
(70, 34),
(70, 38),
(70, 39),
(71, 31),
(71, 36),
(71, 40),
(72, 32),
(72, 35),
(72, 37),
(73, 33),
(73, 36),
(73, 39),
(73, 40),
(74, 31),
(74, 32),
(74, 37),
(75, 34),
(75, 35),
(75, 38),
(76, 33),
(76, 38),
(76, 39),
(76, 40),
(77, 31),
(77, 32),
(77, 37),
(78, 34),
(78, 35),
(78, 36),
(79, 31),
(79, 33),
(79, 36),
(79, 39),
(80, 32),
(80, 35),
(80, 40),
(81, 34),
(81, 37),
(81, 38),
(82, 34),
(82, 36),
(82, 37),
(82, 40),
(83, 32),
(83, 33),
(83, 35),
(84, 31),
(84, 38),
(84, 39),
(85, 33),
(85, 35),
(85, 39),
(85, 40),
(86, 32),
(86, 34),
(86, 37),
(87, 31),
(87, 36),
(87, 38),
(88, 32),
(88, 33),
(88, 37),
(88, 38),
(89, 34),
(89, 35),
(89, 36),
(90, 31),
(90, 39),
(90, 40);

-- --------------------------------------------------------

--
-- Table structure for table `Mannschaft`
--

CREATE TABLE `Mannschaft` (
  `ID` int(11) NOT NULL,
  `Teamname` varchar(32) NOT NULL,
  `Kreisverband` varchar(32) NOT NULL,
  `Landesverband` varchar(32) NOT NULL,
  `Gesamtpunkte` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Mannschaft`
--

INSERT INTO `Mannschaft` (`ID`, `Teamname`, `Kreisverband`, `Landesverband`, `Gesamtpunkte`) VALUES
(1, 'Erfurt-Damen', 'Erfurt', 'Thüringen', NULL),
(2, 'Erfurt-Herren', 'Erfurt', 'Thüringen', NULL),
(3, 'Sömmerda-Damen', 'Sömmerda', 'Thüringen', NULL),
(4, 'Sömmerda-Gemischt', 'Sömmerda', 'Thüringen', NULL),
(5, 'Sömmerda-Herren', 'Sömmerda', 'Thüringen', NULL),
(6, 'Greiz-Gemischt', 'Greiz', 'Thüringen', NULL),
(7, 'Ilmenau-Gemischt', 'Ilmenau', 'Thüringen', NULL),
(8, 'Ilmenau-Damen', 'Ilmenau', 'Thüringen', NULL),
(9, 'Leipzig-Damen', 'Leipzig', 'Sachsen', NULL),
(10, 'Leipzig-Herren', 'Leipzig', 'Sachsen', NULL),
(11, 'Leipzig-Gemischt', 'Leipzig', 'Sachsen', NULL),
(12, 'Dresden-Gemischt', 'Dresden', 'Sachsen', NULL),
(13, 'Dresden-Herren', 'Dresden', 'Sachsen', NULL),
(14, 'Chemnitz-Herren', 'Chemnitz', 'Sachsen', NULL),
(15, 'Chemnitz-Damen', 'Chemnitz', 'Sachsen', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `MannschaftProtokoll`
--

CREATE TABLE `MannschaftProtokoll` (
  `mannschaft_ID` int(11) NOT NULL,
  `protokoll_Nr` int(11) NOT NULL,
  `erreichte_Punkte` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `MannschaftProtokoll`
--

INSERT INTO `MannschaftProtokoll` (`mannschaft_ID`, `protokoll_Nr`, `erreichte_Punkte`) VALUES
(1, 1, 110),
(1, 2, 110),
(1, 3, 110),
(1, 4, 110),
(1, 36, 135),
(1, 37, 90),
(1, 38, 180),
(1, 39, 160),
(2, 1, 80),
(2, 2, 80),
(2, 3, 80),
(2, 4, 80),
(2, 36, 140),
(2, 37, 85),
(2, 38, 190),
(2, 39, 165),
(3, 1, 96),
(3, 2, 96),
(3, 3, 96),
(3, 4, 96),
(3, 40, 110),
(3, 41, 130),
(3, 42, 150),
(3, 43, 160),
(4, 1, 96),
(4, 2, 96),
(4, 3, 96),
(4, 4, 96),
(5, 1, 100),
(5, 2, 100),
(5, 3, 100),
(5, 4, 100),
(6, 1, 120),
(6, 2, 120),
(6, 3, 120),
(6, 4, 120),
(7, 1, 99),
(7, 2, 99),
(7, 3, 99),
(7, 4, 99),
(8, 1, 130),
(8, 2, 130),
(8, 3, 130),
(8, 4, 130),
(9, 1, 98),
(9, 2, 98),
(9, 3, 98),
(9, 4, 98),
(10, 1, 98),
(10, 2, 98),
(10, 3, 98),
(10, 4, 98),
(11, 1, 98),
(11, 2, 98),
(11, 3, 98),
(11, 4, 98),
(12, 1, 90),
(12, 2, 90),
(12, 3, 90),
(12, 4, 90),
(13, 1, 85),
(13, 2, 85),
(13, 3, 85),
(13, 4, 85),
(14, 1, 100),
(14, 2, 100),
(14, 3, 100),
(14, 4, 100),
(15, 1, 100),
(15, 2, 100),
(15, 3, 100),
(15, 4, 100);

-- --------------------------------------------------------

--
-- Table structure for table `MannschaftStaffel`
--

CREATE TABLE `MannschaftStaffel` (
  `mannschaft_ID` int(11) NOT NULL,
  `staffel_ID` int(11) NOT NULL,
  `schwimmzeit` time(4) NOT NULL,
  `strafzeit` time(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `MannschaftStaffel`
--

INSERT INTO `MannschaftStaffel` (`mannschaft_ID`, `staffel_ID`, `schwimmzeit`, `strafzeit`) VALUES
(1, 1, '00:03:29.5500', '00:00:00.0000'),
(1, 2, '00:05:47.2000', '00:00:00.0000'),
(1, 3, '00:03:10.4700', '00:00:00.0000'),
(1, 4, '00:01:20.0000', '00:00:00.0000'),
(1, 5, '00:02:14.3800', '00:00:00.0000'),
(1, 6, '00:04:34.4100', '00:00:00.0000'),
(2, 1, '00:03:41.5500', '00:00:05.0000'),
(2, 2, '00:05:39.2000', '00:00:00.0000'),
(2, 3, '00:03:10.4700', '00:00:00.0000'),
(2, 4, '00:01:20.0000', '00:00:00.0000'),
(2, 5, '00:02:14.3800', '00:00:00.0000'),
(2, 6, '00:04:34.4100', '00:00:00.0000'),
(3, 1, '00:03:16.5500', '00:00:05.0000'),
(3, 2, '00:05:37.2000', '00:00:00.0000'),
(3, 3, '00:03:10.4700', '00:00:00.0000'),
(3, 4, '00:01:20.0000', '00:00:00.0000'),
(3, 5, '00:02:14.3800', '00:00:00.0000'),
(3, 6, '00:04:34.4100', '00:00:00.0000'),
(4, 1, '00:03:15.5500', '00:00:00.0000'),
(4, 2, '00:05:46.2000', '00:00:05.0000'),
(4, 3, '00:03:10.4700', '00:00:00.0000'),
(4, 4, '00:01:20.0000', '00:00:00.0000'),
(4, 5, '00:02:14.3800', '00:00:00.0000'),
(4, 6, '00:04:34.4100', '00:00:00.0000'),
(5, 1, '00:03:53.5500', '00:00:00.0000'),
(5, 2, '00:05:28.2000', '00:00:00.0000'),
(5, 3, '00:03:10.4700', '00:00:00.0000'),
(5, 4, '00:01:20.0000', '00:00:00.0000'),
(5, 5, '00:02:14.3800', '00:00:00.0000'),
(5, 6, '00:04:34.4100', '00:00:00.0000'),
(6, 1, '00:03:19.5500', '00:00:00.0000'),
(6, 2, '00:05:28.2000', '00:00:10.0000'),
(6, 3, '00:03:10.4700', '00:00:00.0000'),
(6, 4, '00:01:20.0000', '00:00:00.0000'),
(6, 5, '00:02:14.3800', '00:00:00.0000'),
(6, 6, '00:04:34.4100', '00:00:00.0000'),
(7, 1, '00:03:43.5500', '00:00:00.0000'),
(7, 2, '00:05:36.2000', '00:00:00.0000'),
(7, 3, '00:03:10.4700', '00:00:00.0000'),
(7, 4, '00:01:20.0000', '00:00:00.0000'),
(7, 5, '00:02:14.3800', '00:00:00.0000'),
(7, 6, '00:04:34.4100', '00:00:00.0000'),
(8, 1, '00:03:12.5500', '00:00:00.0000'),
(8, 2, '00:05:51.2000', '00:00:00.0000'),
(8, 3, '00:03:10.4700', '00:00:00.0000'),
(8, 4, '00:01:20.0000', '00:00:00.0000'),
(8, 5, '00:02:14.3800', '00:00:00.0000'),
(8, 6, '00:04:34.4100', '00:00:00.0000'),
(9, 1, '00:03:36.5500', '00:00:20.0000'),
(9, 2, '00:05:34.2000', '00:00:05.0000'),
(9, 3, '00:03:10.4700', '00:00:00.0000'),
(9, 4, '00:01:20.0000', '00:00:00.0000'),
(9, 5, '00:02:14.3800', '00:00:00.0000'),
(9, 6, '00:04:34.4100', '00:00:00.0000'),
(10, 1, '00:03:26.5500', '00:00:00.0000'),
(10, 2, '00:05:28.2000', '00:00:20.0000'),
(10, 3, '00:03:10.4700', '00:00:00.0000'),
(10, 4, '00:01:20.0000', '00:00:00.0000'),
(10, 5, '00:02:14.3800', '00:00:00.0000'),
(10, 6, '00:04:34.4100', '00:00:00.0000'),
(11, 1, '00:03:33.5500', '00:00:00.0000'),
(11, 2, '00:05:19.2000', '00:00:00.0000'),
(11, 3, '00:03:10.4700', '00:00:00.0000'),
(11, 4, '00:01:20.0000', '00:00:00.0000'),
(11, 5, '00:02:14.3800', '00:00:00.0000'),
(11, 6, '00:04:34.4100', '00:00:00.0000'),
(12, 1, '00:02:56.5500', '00:00:10.0000'),
(12, 2, '00:05:12.2000', '00:00:05.0000'),
(12, 3, '00:03:10.4700', '00:00:00.0000'),
(12, 4, '00:01:20.0000', '00:00:00.0000'),
(12, 5, '00:02:14.3800', '00:00:00.0000'),
(12, 6, '00:04:34.4100', '00:00:00.0000'),
(13, 1, '00:03:23.5500', '00:00:00.0000'),
(13, 2, '00:05:56.2000', '00:00:00.0000'),
(13, 3, '00:03:10.4700', '00:00:00.0000'),
(13, 4, '00:01:20.0000', '00:00:00.0000'),
(13, 5, '00:02:14.3800', '00:00:00.0000'),
(13, 6, '00:04:34.4100', '00:00:00.0000'),
(14, 1, '00:03:13.5500', '00:00:00.0000'),
(14, 2, '00:05:56.2000', '00:00:00.0000'),
(14, 3, '00:03:10.4700', '00:00:00.0000'),
(14, 4, '00:01:20.0000', '00:00:00.0000'),
(14, 5, '00:02:14.3800', '00:00:00.0000'),
(14, 6, '00:04:34.4100', '00:00:00.0000'),
(15, 1, '00:03:13.5500', '00:00:00.0000'),
(15, 2, '00:05:46.2000', '00:00:10.0000'),
(15, 3, '00:03:10.4700', '00:00:00.0000'),
(15, 4, '00:01:20.0000', '00:00:00.0000'),
(15, 5, '00:02:14.3800', '00:00:00.0000'),
(15, 6, '00:04:34.4100', '00:00:00.0000');

-- --------------------------------------------------------

--
-- Table structure for table `MannschaftWertung`
--

CREATE TABLE `MannschaftWertung` (
  `mannschaft_ID` int(11) NOT NULL,
  `wertung_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `MannschaftWertung`
--

INSERT INTO `MannschaftWertung` (`mannschaft_ID`, `wertung_ID`) VALUES
(1, 1),
(1, 2),
(2, 1),
(3, 2),
(4, 3),
(5, 1),
(6, 3),
(7, 3),
(8, 2),
(9, 4),
(10, 6),
(11, 5),
(12, 5),
(13, 6),
(14, 6),
(15, 4);

-- --------------------------------------------------------

--
-- Table structure for table `Protokoll`
--

CREATE TABLE `Protokoll` (
  `Nr` int(11) NOT NULL,
  `Name` varchar(64) NOT NULL,
  `max_Punkte` int(11) NOT NULL,
  `station_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Protokoll`
--

INSERT INTO `Protokoll` (`Nr`, `Name`, `max_Punkte`, `station_ID`) VALUES
(1, 'Verletzer 1', 150, 1),
(2, 'Verletzer 2', 170, 1),
(3, 'Verletzer 3', 190, 1),
(4, 'Verletzer 4', 130, 1),
(6, 'Kopfplatzwunde', 150, 2),
(7, 'Erwachsener 1', 150, 3),
(8, 'Erwachsener 2', 150, 3),
(9, 'Jugendlicher', 160, 3),
(10, 'Kind', 180, 3),
(11, 'Ertrinkende Person', 220, 4),
(12, 'Schock', 120, 4),
(13, 'Kopfplatzwunde-Wasserrettung', 150, 4),
(14, 'ET-1', 10, 5),
(15, 'ET-2', 10, 5),
(16, 'ET-3', 10, 5),
(17, 'ET-4', 10, 5),
(18, 'ET-5', 10, 5),
(19, 'ET-6', 10, 5),
(20, 'ET-7', 10, 5),
(21, 'ET-8', 10, 5),
(22, 'ET-9', 10, 5),
(23, 'ET-10', 10, 5),
(24, 'ET-11', 10, 5),
(28, 'GT-1', 10, 7),
(30, 'Bewusstlose Person', 150, 4),
(32, 'Bewusstlose Person', 150, 2),
(34, 'GT-2', 10, 7),
(36, 'Einsatz Erste-Hilfe', 150, 23),
(37, 'Erste-Hilfe Materialkenntnis', 100, 23),
(38, 'CPR Erwachsener', 200, 24),
(39, 'CPR Kind', 180, 24),
(40, 'Grundknoten', 120, 25),
(41, 'Spezialknoten', 150, 25),
(42, 'Streckentauchen', 160, 26),
(43, 'Tieftauchen', 180, 26);

-- --------------------------------------------------------

--
-- Table structure for table `Question`
--

CREATE TABLE `Question` (
  `ID` int(11) NOT NULL,
  `QuestionPool_ID` int(11) NOT NULL,
  `Text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Question`
--

INSERT INTO `Question` (`ID`, `QuestionPool_ID`, `Text`) VALUES
(16, 9, 'Wie lautet der Notruf in Deutschland?'),
(21, 10, 'Welches Rettungsmittel ist für einen bewusstlosen Ertrinkenden am besten geeignet?'),
(31, 1, '1'),
(32, 1, '2'),
(33, 1, '3'),
(34, 1, '4'),
(35, 1, '5'),
(36, 1, '6'),
(37, 1, '7'),
(38, 1, '8'),
(39, 1, '9'),
(40, 1, '10');

-- --------------------------------------------------------

--
-- Table structure for table `QuestionForm`
--

CREATE TABLE `QuestionForm` (
  `ID` int(11) NOT NULL,
  `Station_ID` int(11) NOT NULL,
  `Titel` varchar(255) NOT NULL,
  `time_limit` int(11) DEFAULT 180 COMMENT 'Zeitlimit in Sekunden'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `QuestionForm`
--

INSERT INTO `QuestionForm` (`ID`, `Station_ID`, `Titel`, `time_limit`) VALUES
(46, 22, 'Waitingpoint-Formular (1/3)', 180),
(47, 22, 'Waitingpoint-Formular (2/3)', 180),
(48, 22, 'Waitingpoint-Formular (3/3)', 180),
(49, 22, 'Waitingpoint-Formular (1/3)', 180),
(50, 22, 'Waitingpoint-Formular (2/3)', 180),
(51, 22, 'Waitingpoint-Formular (3/3)', 180),
(52, 22, 'Waitingpoint-Formular (1/3)', 180),
(53, 22, 'Waitingpoint-Formular (2/3)', 180),
(54, 22, 'Waitingpoint-Formular (3/3)', 180),
(55, 22, 'Waitingpoint-Formular (1/3)', 180),
(56, 22, 'Waitingpoint-Formular (2/3)', 180),
(57, 22, 'Waitingpoint-Formular (3/3)', 180),
(58, 22, 'Waitingpoint-Formular (1/3)', 180),
(59, 22, 'Waitingpoint-Formular (2/3)', 180),
(60, 22, 'Waitingpoint-Formular (3/3)', 180),
(61, 22, 'Waitingpoint-Formular (1/3)', 180),
(62, 22, 'Waitingpoint-Formular (2/3)', 180),
(63, 22, 'Waitingpoint-Formular (3/3)', 180),
(64, 22, 'Waitingpoint-Formular (1/3)', 180),
(65, 22, 'Waitingpoint-Formular (2/3)', 180),
(66, 22, 'Waitingpoint-Formular (3/3)', 180),
(67, 22, 'Waitingpoint-Formular (1/3)', 180),
(68, 22, 'Waitingpoint-Formular (2/3)', 180),
(69, 22, 'Waitingpoint-Formular (3/3)', 180),
(70, 22, 'Waitingpoint-Formular (1/3)', 180),
(71, 22, 'Waitingpoint-Formular (2/3)', 180),
(72, 22, 'Waitingpoint-Formular (3/3)', 180),
(73, 22, 'Waitingpoint-Formular (1/3)', 180),
(74, 22, 'Waitingpoint-Formular (2/3)', 180),
(75, 22, 'Waitingpoint-Formular (3/3)', 180),
(76, 22, 'Waitingpoint-Formular (1/3)', 180),
(77, 22, 'Waitingpoint-Formular (2/3)', 180),
(78, 22, 'Waitingpoint-Formular (3/3)', 180),
(79, 22, 'Waitingpoint-Formular (1/3)', 180),
(80, 22, 'Waitingpoint-Formular (2/3)', 180),
(81, 22, 'Waitingpoint-Formular (3/3)', 180),
(82, 22, 'Waitingpoint-Formular (1/3)', 180),
(83, 22, 'Waitingpoint-Formular (2/3)', 180),
(84, 22, 'Waitingpoint-Formular (3/3)', 180),
(85, 22, 'Waitingpoint-Formular (1/3)', 180),
(86, 22, 'Waitingpoint-Formular (2/3)', 180),
(87, 22, 'Waitingpoint-Formular (3/3)', 180),
(88, 22, 'Waitingpoint-Formular (1/3)', 180),
(89, 22, 'Waitingpoint-Formular (2/3)', 180),
(90, 22, 'Waitingpoint-Formular (3/3)', 180);

-- --------------------------------------------------------

--
-- Table structure for table `QuestionPool`
--

CREATE TABLE `QuestionPool` (
  `ID` int(11) NOT NULL,
  `Name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `QuestionPool`
--

INSERT INTO `QuestionPool` (`ID`, `Name`) VALUES
(9, 'Erste Hilfe Grundlagen'),
(1, 'Waitingpoints'),
(10, 'Wasserrettung Theorie');

-- --------------------------------------------------------

--
-- Table structure for table `ResultConfiguration`
--

CREATE TABLE `ResultConfiguration` (
  `ID` int(11) NOT NULL,
  `Key` varchar(64) NOT NULL,
  `Value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ResultConfiguration`
--

INSERT INTO `ResultConfiguration` (`ID`, `Key`, `Value`) VALUES
(1, 'SHARE_SWIMMING', '50'),
(2, 'SHARE_PARCOURS', '50'),
(3, 'TOTAL_POINTS', '12000'),
(4, 'DEDUCTION_INTERVAL_MS', '100'),
(5, 'POINTS_DEDUCTION', '1');

-- --------------------------------------------------------

--
-- Table structure for table `Staffel`
--

CREATE TABLE `Staffel` (
  `ID` int(11) NOT NULL,
  `name` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Staffel`
--

INSERT INTO `Staffel` (`ID`, `name`) VALUES
(1, 'Leinenstaffel'),
(2, 'Kleiderstaffel'),
(3, 'Rettungsmittelstaffel'),
(4, 'Sprintstaffel'),
(5, 'Tauchstaffel'),
(6, 'Kombi-Staffel');

-- --------------------------------------------------------

--
-- Table structure for table `Station`
--

CREATE TABLE `Station` (
  `ID` int(11) NOT NULL,
  `name` varchar(32) DEFAULT NULL,
  `Nr` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Station`
--

INSERT INTO `Station` (`ID`, `name`, `Nr`) VALUES
(1, 'MANV', 0),
(2, 'Fahrradunfall', 0),
(3, 'Reanimation', 0),
(4, 'Wasserrettung', 0),
(5, 'Einzel-Theorie', 0),
(7, 'Gruppentheorie', 0),
(22, 'Waitingpoints', 2);

-- --------------------------------------------------------

--
-- Table structure for table `StationWeight`
--

CREATE TABLE `StationWeight` (
  `station_ID` int(11) NOT NULL,
  `weight` int(11) NOT NULL DEFAULT 100
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `StationWeight`
--

INSERT INTO `StationWeight` (`station_ID`, `weight`) VALUES
(1, 100),
(2, 100),
(3, 100),
(4, 100),
(5, 100),
(7, 100),
(22, 100);

-- --------------------------------------------------------

--
-- Table structure for table `TeamForm`
--

CREATE TABLE `TeamForm` (
  `team_ID` int(11) NOT NULL,
  `form_ID` int(11) NOT NULL,
  `sequence` int(11) NOT NULL DEFAULT 0,
  `completed` tinyint(1) NOT NULL DEFAULT 0,
  `points` int(11) DEFAULT 0,
  `completion_date` datetime DEFAULT NULL,
  `token` varchar(32) DEFAULT NULL,
  `start_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `TeamForm`
--

INSERT INTO `TeamForm` (`team_ID`, `form_ID`, `sequence`, `completed`, `points`, `completion_date`, `token`, `start_time`) VALUES
(1, 46, 1, 0, 0, NULL, 'qhP3E7VP40kDcXmw', '2025-05-04 20:01:12'),
(1, 47, 2, 0, 0, NULL, 'pQJbq13Won3uHpaw', '2025-05-04 20:10:32'),
(1, 48, 3, 0, 0, NULL, '1OD8aDuDjqCYlEjf', NULL),
(2, 49, 1, 0, 0, NULL, 'iKnHAX2YIqkwwetj', NULL),
(2, 50, 2, 0, 0, NULL, '0fH4wntwLcdZqlKh', NULL),
(2, 51, 3, 0, 0, NULL, 'GRMxFAx2xT3997fk', NULL),
(3, 52, 1, 0, 0, NULL, 'UoMjbJ9TAW3eZ1GE', NULL),
(3, 53, 2, 0, 0, NULL, '83TC7UNlPVb4iDFb', NULL),
(3, 54, 3, 0, 0, NULL, 'XbACpGyUK18nk8wX', NULL),
(4, 55, 1, 0, 0, NULL, '0HadmpDSv9mEY7Dc', NULL),
(4, 56, 2, 0, 0, NULL, 'ydMeZFFj4DfnNdoN', NULL),
(4, 57, 3, 1, 3, '2025-05-04 18:40:35', 'SG37Xy0tJHyPJTWW', '2025-05-04 18:40:27'),
(5, 58, 1, 0, 0, NULL, 'BXBLPuNZ9AEEIwW8', NULL),
(5, 59, 2, 0, 0, NULL, 'AibUw1QgPuje6CpI', NULL),
(5, 60, 3, 0, 0, NULL, 'wdxrTGOkLuwrVZBx', NULL),
(6, 61, 1, 0, 0, NULL, '7yDc0DKdENdB4Kil', '2025-05-04 20:11:31'),
(6, 62, 2, 0, 0, NULL, 'Y2ziGXmghVgPbSJl', NULL),
(6, 63, 3, 0, 0, NULL, 'D97n4Hst5yIiNcBr', '2025-05-04 20:11:38'),
(7, 64, 1, 0, 0, NULL, 'apRh4BYgLscWSu9B', NULL),
(7, 65, 2, 0, 0, NULL, 'pEy7D2l4q0GhI5jQ', NULL),
(7, 66, 3, 0, 0, NULL, 'tdj4wa9HELH5ejXU', NULL),
(8, 67, 1, 1, 1, '2025-05-04 18:28:35', 'pG3NWkvAubuF21Ma', '2025-05-04 18:28:26'),
(8, 68, 2, 0, 0, NULL, 'XwfrhqVP5a9HoYpJ', NULL),
(8, 69, 3, 0, 0, NULL, 'wp4Vl5ZRlP5et9gs', NULL),
(9, 70, 1, 0, 0, NULL, 'aO86iyjjz90UD5It', NULL),
(9, 71, 2, 0, 0, NULL, 'O7nId4U4J7uyufnV', NULL),
(9, 72, 3, 0, 0, NULL, 'cgxWXlo1fdUigscU', NULL),
(10, 73, 1, 0, 0, NULL, 'EKB1QquSpFu9ro1O', NULL),
(10, 74, 2, 0, 0, NULL, 'eZUVbwWicCVHH22H', NULL),
(10, 75, 3, 0, 0, NULL, 'Agoh7zTQ0K4xIcH7', NULL),
(11, 76, 1, 0, 0, NULL, 'UaBnRD0N64mIoTiW', NULL),
(11, 77, 2, 0, 0, NULL, 'aQ6kwdCyEeNHvmtv', NULL),
(11, 78, 3, 0, 0, NULL, 'xjw9glONcjAX9Vvj', NULL),
(12, 79, 1, 0, 0, NULL, 'hO3uYYNQVByLNGPl', '2025-05-04 17:04:25'),
(12, 80, 2, 0, 0, NULL, '3NrxiOy0LTo1kAq3', '2025-05-04 19:33:24'),
(12, 81, 3, 0, 0, NULL, 'pILrONq8lOVwMwZy', '2025-05-04 19:47:15'),
(13, 82, 1, 0, 0, NULL, '9drSqeRjnm1hYjCM', '2025-05-04 19:53:05'),
(13, 83, 2, 0, 0, NULL, '8a4ZKXIRUMCmWckO', '2025-05-04 17:59:44'),
(13, 84, 3, 0, 0, NULL, 'nL7jZAzEnYyQZoJ3', '2025-05-04 17:59:49'),
(14, 85, 1, 1, 1, '2025-05-10 17:55:18', 'sHsH3Bqp9YcHeI9D', '2025-05-10 17:55:05'),
(14, 86, 2, 1, 1, '2025-05-10 17:55:38', 'O0o2FfZdCbCHJF0k', '2025-05-10 17:55:34'),
(14, 87, 3, 1, 1, '2025-05-03 12:03:15', 'WbtuG6uaEOgYotT2', '2025-05-03 12:03:07'),
(15, 88, 1, 1, 1, '2025-05-10 17:54:12', 'qW1JtMzMzPKNmKDr', '2025-05-10 17:53:23'),
(15, 89, 2, 1, 1, '2025-05-10 17:54:45', 'nCykeXnQJWmJuwXs', '2025-05-10 17:54:20'),
(15, 90, 3, 1, 2, '2025-05-10 17:53:47', '9gK7vMrbVMn1cepf', '2025-05-10 17:53:41');

-- --------------------------------------------------------

--
-- Table structure for table `User`
--

CREATE TABLE `User` (
  `ID` int(11) NOT NULL,
  `username` varchar(32) NOT NULL,
  `passwordHash` varchar(99) NOT NULL,
  `acc_typ` varchar(16) NOT NULL,
  `mannschaft_ID` int(11) DEFAULT NULL,
  `station_ID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `User`
--

INSERT INTO `User` (`ID`, `username`, `passwordHash`, `acc_typ`, `mannschaft_ID`, `station_ID`) VALUES
(15, 'admin', '5ede13f8c4f4b1416e9c7837629fd1bf', 'Wettkampfleitung', NULL, NULL),
(20, 'team', 'a723343c70f021db397ec74aa6b57bc4', 'Teilnehmer', 15, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `Wertungsklasse`
--

CREATE TABLE `Wertungsklasse` (
  `ID` int(11) NOT NULL,
  `name` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Wertungsklasse`
--

INSERT INTO `Wertungsklasse` (`ID`, `name`) VALUES
(1, 'Thüringen-Herren'),
(2, 'Thüringen-Damen'),
(3, 'Thüringen-Gemischt'),
(4, 'Sachsen-Damen'),
(5, 'Sachsen-Gemischt'),
(6, 'Sachsen-Herren');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Answer`
--
ALTER TABLE `Answer`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `fk_answer_question` (`Question_ID`);

--
-- Indexes for table `FormQuestion`
--
ALTER TABLE `FormQuestion`
  ADD PRIMARY KEY (`form_ID`,`question_ID`),
  ADD KEY `question_ID` (`question_ID`);

--
-- Indexes for table `Mannschaft`
--
ALTER TABLE `Mannschaft`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `MannschaftProtokoll`
--
ALTER TABLE `MannschaftProtokoll`
  ADD PRIMARY KEY (`mannschaft_ID`,`protokoll_Nr`),
  ADD KEY `protokoll_Nr` (`protokoll_Nr`);

--
-- Indexes for table `MannschaftStaffel`
--
ALTER TABLE `MannschaftStaffel`
  ADD PRIMARY KEY (`mannschaft_ID`,`staffel_ID`),
  ADD KEY `staffel_ID` (`staffel_ID`);

--
-- Indexes for table `MannschaftWertung`
--
ALTER TABLE `MannschaftWertung`
  ADD PRIMARY KEY (`mannschaft_ID`,`wertung_ID`),
  ADD KEY `wertung_ID` (`wertung_ID`);

--
-- Indexes for table `Protokoll`
--
ALTER TABLE `Protokoll`
  ADD PRIMARY KEY (`Nr`),
  ADD UNIQUE KEY `Nr` (`Nr`),
  ADD KEY `station_Nr` (`station_ID`);

--
-- Indexes for table `Question`
--
ALTER TABLE `Question`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `uk_QuestionText` (`Text`(191)),
  ADD KEY `fk_question_pool` (`QuestionPool_ID`);

--
-- Indexes for table `QuestionForm`
--
ALTER TABLE `QuestionForm`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `fk_questionformular_station` (`Station_ID`),
  ADD KEY `uk_Titel` (`Titel`) USING BTREE;

--
-- Indexes for table `QuestionPool`
--
ALTER TABLE `QuestionPool`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `uk_PoolName` (`Name`);

--
-- Indexes for table `ResultConfiguration`
--
ALTER TABLE `ResultConfiguration`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `uk_ConfigKey` (`Key`);

--
-- Indexes for table `Staffel`
--
ALTER TABLE `Staffel`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `Station`
--
ALTER TABLE `Station`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `StationWeight`
--
ALTER TABLE `StationWeight`
  ADD PRIMARY KEY (`station_ID`);

--
-- Indexes for table `TeamForm`
--
ALTER TABLE `TeamForm`
  ADD PRIMARY KEY (`team_ID`,`form_ID`),
  ADD KEY `form_ID` (`form_ID`),
  ADD KEY `idx_teamform_starttime` (`start_time`);

--
-- Indexes for table `User`
--
ALTER TABLE `User`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `mannschaft_ID` (`mannschaft_ID`),
  ADD KEY `station_Nr` (`station_ID`);

--
-- Indexes for table `Wertungsklasse`
--
ALTER TABLE `Wertungsklasse`
  ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Answer`
--
ALTER TABLE `Answer`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT for table `Mannschaft`
--
ALTER TABLE `Mannschaft`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `Protokoll`
--
ALTER TABLE `Protokoll`
  MODIFY `Nr` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `Question`
--
ALTER TABLE `Question`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `QuestionForm`
--
ALTER TABLE `QuestionForm`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT for table `QuestionPool`
--
ALTER TABLE `QuestionPool`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `Staffel`
--
ALTER TABLE `Staffel`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `Station`
--
ALTER TABLE `Station`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `User`
--
ALTER TABLE `User`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `Wertungsklasse`
--
ALTER TABLE `Wertungsklasse`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Answer`
--
ALTER TABLE `Answer`
  ADD CONSTRAINT `fk_answer_question` FOREIGN KEY (`Question_ID`) REFERENCES `Question` (`ID`);

--
-- Constraints for table `FormQuestion`
--
ALTER TABLE `FormQuestion`
  ADD CONSTRAINT `FormQuestion_ibfk_1` FOREIGN KEY (`form_ID`) REFERENCES `QuestionForm` (`ID`),
  ADD CONSTRAINT `FormQuestion_ibfk_2` FOREIGN KEY (`question_ID`) REFERENCES `Question` (`ID`);

--
-- Constraints for table `MannschaftProtokoll`
--
ALTER TABLE `MannschaftProtokoll`
  ADD CONSTRAINT `MannschaftProtokoll_ibfk_1` FOREIGN KEY (`mannschaft_ID`) REFERENCES `Mannschaft` (`ID`),
  ADD CONSTRAINT `MannschaftProtokoll_ibfk_2` FOREIGN KEY (`protokoll_Nr`) REFERENCES `Protokoll` (`Nr`);

--
-- Constraints for table `MannschaftStaffel`
--
ALTER TABLE `MannschaftStaffel`
  ADD CONSTRAINT `MannschaftStaffel_ibfk_1` FOREIGN KEY (`mannschaft_ID`) REFERENCES `Mannschaft` (`ID`),
  ADD CONSTRAINT `MannschaftStaffel_ibfk_2` FOREIGN KEY (`staffel_ID`) REFERENCES `Staffel` (`ID`);

--
-- Constraints for table `MannschaftWertung`
--
ALTER TABLE `MannschaftWertung`
  ADD CONSTRAINT `MannschaftWertung_ibfk_1` FOREIGN KEY (`mannschaft_ID`) REFERENCES `Mannschaft` (`ID`),
  ADD CONSTRAINT `MannschaftWertung_ibfk_2` FOREIGN KEY (`wertung_ID`) REFERENCES `Wertungsklasse` (`ID`);

--
-- Constraints for table `Protokoll`
--
ALTER TABLE `Protokoll`
  ADD CONSTRAINT `Protokoll_ibfk_1` FOREIGN KEY (`station_ID`) REFERENCES `Station` (`ID`);

--
-- Constraints for table `Question`
--
ALTER TABLE `Question`
  ADD CONSTRAINT `fk_question_pool` FOREIGN KEY (`QuestionPool_ID`) REFERENCES `QuestionPool` (`ID`);

--
-- Constraints for table `QuestionForm`
--
ALTER TABLE `QuestionForm`
  ADD CONSTRAINT `fk_questionformular_station` FOREIGN KEY (`Station_ID`) REFERENCES `Station` (`ID`);

--
-- Constraints for table `StationWeight`
--
ALTER TABLE `StationWeight`
  ADD CONSTRAINT `fk_stationweight_station` FOREIGN KEY (`station_ID`) REFERENCES `Station` (`ID`);

--
-- Constraints for table `TeamForm`
--
ALTER TABLE `TeamForm`
  ADD CONSTRAINT `TeamForm_ibfk_1` FOREIGN KEY (`team_ID`) REFERENCES `Mannschaft` (`ID`),
  ADD CONSTRAINT `TeamForm_ibfk_2` FOREIGN KEY (`form_ID`) REFERENCES `QuestionForm` (`ID`);

--
-- Constraints for table `User`
--
ALTER TABLE `User`
  ADD CONSTRAINT `User_ibfk_1` FOREIGN KEY (`mannschaft_ID`) REFERENCES `Mannschaft` (`ID`),
  ADD CONSTRAINT `User_ibfk_2` FOREIGN KEY (`station_ID`) REFERENCES `Station` (`ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
