-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: db
-- Generation Time: Apr 30, 2025 at 04:41 PM
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
(19, 5, 'Ich kann nicht lesen', 0),
(20, 16, '110', 0),
(21, 16, '112', 1),
(22, 16, '911', 0),
(23, 16, '115', 0),
(24, 17, 'Eigenschutz', 1),
(25, 17, 'Rettung des Verletzten', 0),
(26, 17, 'Notruf absetzen', 0),
(27, 17, 'Wundversorgung', 0),
(28, 18, '15 Kompressionen, 1 Beatmung', 0),
(29, 18, '30 Kompressionen, 2 Beatmungen', 1),
(30, 18, '5 Kompressionen, 1 Beatmung', 0),
(31, 18, '10 Kompressionen, 5 Beatmungen', 0),
(32, 19, '15:2', 0),
(33, 19, '30:2', 1),
(34, 19, '5:1', 0),
(35, 19, '100:1', 0),
(36, 21, 'Rettungsring', 0),
(37, 21, 'Rettungsboje', 0),
(38, 21, 'Rettungsbrett', 1),
(39, 21, 'Rettungsleine', 0),
(40, 22, 'Von vorne, mit direktem Augenkontakt', 0),
(41, 22, 'Von der Seite, mit Rettungsmittel voraus', 0),
(42, 22, 'Von hinten, um Umklammerungen zu vermeiden', 1),
(43, 22, 'Von unten, durch Tauchen', 0);

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
(15, 16),
(15, 17),
(15, 18),
(18, 21),
(18, 22),
(18, 25),
(19, 26),
(19, 28),
(19, 29),
(20, 27),
(20, 30);

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
(1, 1, 'Testfrage?'),
(3, 1, 'Frage 2?'),
(4, 1, 'Frage 3?'),
(5, 1, 'Welche Farbe hat ein Rettungsreifen'),
(16, 9, 'Wie lautet der Notruf in Deutschland?'),
(17, 9, 'Welche Maßnahme hat bei einem Notfall oberste Priorität?'),
(18, 9, 'Wie viele Zyklen umfasst die Herz-Lungen-Wiederbelebung?'),
(19, 9, 'Welches Verhältnis zwischen Herzdruckmassage und Beatmung ist korrekt?'),
(20, 9, 'Was ist bei einer Verbrennung dritten Grades zu beachten?'),
(21, 10, 'Welches Rettungsmittel ist für einen bewusstlosen Ertrinkenden am besten geeignet?'),
(22, 10, 'Wie nähert man sich einem panischen Ertrinkenden?'),
(23, 10, 'Was ist die korrekte Position beim Abschleppen eines bewusstlosen Ertrinkenden?'),
(24, 10, 'Welche maximale Entfernung sollte man beim Rettungsschwimmen zum Opfer schwimmen?'),
(25, 10, 'Was ist der Unterschied zwischen einem aktiven und passiven Ertrinkenden?'),
(26, 11, 'Welche Schwimmstrecke muss für das DRSA Bronze absolviert werden?'),
(27, 11, 'Welche Kleidung muss beim DRSA Bronze getragen werden?'),
(28, 11, 'Wie lange muss man beim DRSA Bronze tauchen können?'),
(29, 11, 'Welche Altersgrenze gilt für das DRSA Bronze?'),
(30, 11, 'Welche Transporttechniken werden beim DRSA Bronze geprüft?');

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
(15, 23, 'Wartestation: Erste Hilfe Grundlagen', 300),
(16, 24, 'Wartestation: CPR Quiz', 300),
(17, 25, 'Theorie: Knotenkunde', 600),
(18, 26, 'Theorie: Sicheres Tauchen', 600),
(19, 22, 'DSRBronze (1/2)', 180),
(20, 22, 'DSRBronze (2/2)', 180);

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
(11, 'DRSA Bronze Prüfungsfragen'),
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
(7, 100);

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
  `token` varchar(32) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `TeamForm`
--

INSERT INTO `TeamForm` (`team_ID`, `form_ID`, `sequence`, `completed`, `points`, `completion_date`, `token`) VALUES
(1, 15, 1, 1, 3, '2025-04-25 10:15:00', '1c8e5765bc7f2cc'),
(1, 16, 2, 1, 2, '2025-04-25 11:30:00', '95780089a29c9e5'),
(2, 15, 1, 1, 2, '2025-04-25 09:45:00', '1c8e5765bc7f2cc'),
(2, 16, 2, 0, 0, NULL, '95780089a29c9e5'),
(3, 17, 1, 1, 3, '2025-04-25 13:15:00', 'e81b38212a80ff6'),
(3, 18, 2, 1, 3, '2025-04-25 14:30:00', 'c65ff9875b49abe');

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
(4, 'teilnehmer1', '$2y$10$8Y3M.qtDJ7PQu0JdbT6TH.BzxA.F.n8CQSDY4XY2yAGBQCUzUwZvi', 'Teilnehmer', 1, NULL),
(5, 'teilnehmer2', '$2y$10$8Y3M.qtDJ7PQu0JdbT6TH.BzxA.F.n8CQSDY4XY2yAGBQCUzUwZvi', 'Teilnehmer', 2, NULL),
(6, 'schiedsrichter1', '$2y$10$6EyoZGWGCjTgPSRY6PFqhuqfYlQPr7Rmu9jAJAaH8/Wt9Yvf0nYOK', 'Schiedsrichter', NULL, 1),
(7, 'schiedsrichter2', '$2y$10$6EyoZGWGCjTgPSRY6PFqhuqfYlQPr7Rmu9jAJAaH8/Wt9Yvf0nYOK', 'Schiedsrichter', NULL, 2),
(8, 'helfer1', '$2y$10$kzz7NURtqR9p8kbg3ssV8.M53Y3iHwdxeJIUafcvvy/O/cvRCGhfC', 'Helfer', NULL, 1),
(9, 'helfer2', '$2y$10$kzz7NURtqR9p8kbg3ssV8.M53Y3iHwdxeJIUafcvvy/O/cvRCGhfC', 'Helfer', NULL, 2),
(10, 'mime1', '$2y$10$xwZ15cI.z3K1JUwM09IoxOhFKuL4KkNxUUvM1b5jMDaKm1K1aCeBW', 'Mime', NULL, 3),
(11, 'mime2', '$2y$10$xwZ15cI.z3K1JUwM09IoxOhFKuL4KkNxUUvM1b5jMDaKm1K1aCeBW', 'Mime', NULL, 4),
(12, 'wettkampfleiter1', '$2y$10$7FpXXf02UoVYKrFtgOAx9eIFQ/GVz8HuJb7wBj3vBMR/A4QR4YPOm', 'Wettkampfleitung', NULL, NULL),
(13, 'wettkampfleiter2', '$2y$10$7FpXXf02UoVYKrFtgOAx9eIFQ/GVz8HuJb7wBj3vBMR/A4QR4YPOm', 'Wettkampfleitung', NULL, NULL),
(15, 'admin', '5ede13f8c4f4b1416e9c7837629fd1bf', 'Wettkampfleitung', NULL, NULL);

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
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

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
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `QuestionForm`
--
ALTER TABLE `QuestionForm`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

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
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

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
