-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: db
-- Generation Time: Apr 27, 2025 at 03:48 PM
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
(1, 1, 'A', 1),
(2, 1, 'B', 0),
(3, 1, 'C', 0),
(4, 1, 'D', 0),
(8, 3, 'Ja', 0),
(9, 3, 'Nein', 0),
(10, 3, 'Vielleicht', 0),
(11, 3, 'Ameise', 1),
(12, 4, 'Banane', 0),
(13, 4, 'Autoreifen', 1),
(14, 4, 'Glühbirne', 0),
(15, 4, 'Butterbrot', 0),
(16, 5, 'Keine', 0),
(17, 5, 'Butterbrot', 0),
(18, 5, 'Orange', 1),
(19, 5, 'Ich kann nicht lesen', 0);

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
(13, 1),
(13, 5),
(14, 3),
(14, 4);

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
(2, 1, 80),
(2, 2, 80),
(2, 3, 80),
(2, 4, 80),
(3, 1, 96),
(3, 2, 96),
(3, 3, 96),
(3, 4, 96),
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
(34, 'GT-2', 10, 7);

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
(1, 1, 'Testfrage?'),
(3, 1, 'Frage 2?'),
(4, 1, 'Frage 3?'),
(5, 1, 'Welche Farbe hat ein Rettungsreifen');

-- --------------------------------------------------------

--
-- Table structure for table `QuestionForm`
--

CREATE TABLE `QuestionForm` (
  `ID` int(11) NOT NULL,
  `Station_ID` int(11) NOT NULL,
  `Titel` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `time_limit` int(11) DEFAULT 180 COMMENT 'Zeitlimit in Sekunden'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `QuestionForm`
--

INSERT INTO `QuestionForm` (`ID`, `Station_ID`, `Titel`, `token`, `time_limit`) VALUES
(13, 22, 'Waitingpoint-Formular (1/3)', '1GKTYthI2VEFNJdN', 180),
(14, 22, 'Waitingpoint-Formular (2/3)', 'yS6Lj4xJAZFGLOwY', 180);

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
(2, 'Einzel-Theorie'),
(1, 'Waitingpoints');

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
(21, 'Test', 1),
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
(21, 100);

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
  `completion_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  ADD UNIQUE KEY `uk_Token` (`token`),
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
  ADD KEY `form_ID` (`form_ID`);

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
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `Mannschaft`
--
ALTER TABLE `Mannschaft`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `Protokoll`
--
ALTER TABLE `Protokoll`
  MODIFY `Nr` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `Question`
--
ALTER TABLE `Question`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `QuestionForm`
--
ALTER TABLE `QuestionForm`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `QuestionPool`
--
ALTER TABLE `QuestionPool`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `Staffel`
--
ALTER TABLE `Staffel`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `Station`
--
ALTER TABLE `Station`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `User`
--
ALTER TABLE `User`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
