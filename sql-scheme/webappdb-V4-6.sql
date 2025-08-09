-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: db
-- Generation Time: May 13, 2025 at 01:39 AM
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
(1, 1, 'Leber', 0),
(2, 1, 'Haut', 1),
(3, 1, 'Lunge', 0),
(4, 1, 'Gehirn', 0),
(5, 2, '196', 0),
(6, 2, '206', 1),
(7, 2, '216', 0),
(8, 2, '226', 0),
(9, 3, 'Produktion von Insulin', 0),
(10, 3, 'Blutdruckregulation', 0),
(11, 3, 'Entgiftung und Stoffwechsel', 1),
(12, 3, 'Sauerstofftransport', 0),
(13, 4, 'Bizeps', 0),
(14, 4, 'Herzmuskel', 0),
(15, 4, 'Zungenmuskel', 0),
(16, 4, 'Masseter (Kaumuskel)', 1),
(17, 5, '2', 1),
(18, 5, '3', 0),
(19, 5, '4', 0),
(20, 5, '5', 0),
(21, 6, 'Hecht', 0),
(22, 6, 'Wels', 1),
(23, 6, 'Karpfen', 0),
(24, 6, 'Zander', 0),
(25, 7, 'Rotfeder', 0),
(26, 7, 'Lachs', 1),
(27, 7, 'Hecht', 0),
(28, 7, 'Zander', 0),
(29, 8, 'Regenbogenforelle', 0),
(30, 8, 'Bachforelle', 1),
(31, 8, 'Barsch', 0),
(32, 8, 'Aal', 0),
(33, 9, 'Hering', 0),
(34, 9, 'Wels', 0),
(35, 9, 'Flunder', 1),
(36, 9, 'Karpfen', 0),
(37, 10, 'Brustschwimmen', 0),
(38, 10, 'Rückenschwimmen', 0),
(39, 10, 'Kraulschwimmen', 1),
(40, 10, 'Schmetterlingsschwimmen', 0),
(41, 11, 'Kraulschwimmen', 0),
(42, 11, 'Brustschwimmen', 1),
(43, 11, 'Rückenschwimmen', 0),
(44, 11, 'Schmetterlingsschwimmen', 0),
(45, 12, 'Eine spezifische Schwimmtechnik', 0),
(46, 12, 'Eine Serie von Schwimmübungen', 0),
(47, 12, 'Eine Schwimmdisziplin, die verschiedene Techniken kombiniert', 1),
(48, 12, 'Eine spezielle Art von Schwimmbecken', 0),
(49, 13, 'Prinzip der Spezifität', 0),
(50, 13, 'Prinzip der Reversibilität', 0),
(51, 13, 'Prinzip der Progression', 1),
(52, 13, 'Prinzip der Individualität', 0),
(53, 14, 'Hypertrophie', 0),
(54, 14, 'Erholungsphase', 0),
(55, 14, 'Superkompensation', 1),
(56, 14, 'Trainingsadaptation', 0),
(57, 15, 'Verbesserung der Flexibilität', 0),
(58, 15, 'Erhöhung der Ausdauer', 0),
(59, 15, 'Unterstützung der schnellen Energiebereitstellung', 1),
(60, 15, 'Förderung der Fettverbrennung', 0),
(61, 16, '60 - 80 Schläge pro Minute', 1),
(62, 16, '80 - 100 Schläge pro Minute', 0),
(63, 16, '50 - 70 Schläge pro Minute', 0),
(64, 16, '30 - 40 Schläge pro Minute', 0),
(65, 17, 'In stabiler Seitenlage', 0),
(66, 17, 'Flach.', 0),
(67, 17, 'Mit erhöhtem Oberkörper.', 1),
(68, 17, 'in Bauchlage', 0),
(69, 18, 'Erwärmung der betroffenen Gliedmaßen von außen, gleichzeitig heiße Getränke von innen.', 0),
(70, 18, 'Betreffende Körperteile vorsichtig mit Schnee abreiben, um die Durchblutung anzuregen.', 0),
(71, 18, 'Erwärmung des Verletzten durch heiße Getränke, keine direkte Erwärmung der erfrorenen Körperteile von außen, keimarmen Verband anlegen.', 1),
(72, 18, 'betreffende Körperteile zeitnah unter handwarmes Wasser halten.', 0),
(73, 19, 'Weil es sofort zu Entzündungen im Bereich der Hörmuschel kommen kann.', 0),
(74, 19, 'Weil das Orientierungsvermögen gestört ist, sobald Wasser in das Mittelohr eindringt.', 1),
(75, 19, 'Weil es zu Gehirnschäden führen kann.', 0),
(76, 19, 'Weil es zu Weiterleitungsstörungen der Hörinformation kommen kann.', 0),
(77, 20, '35-40 Atemzüge pro Minute', 1),
(78, 20, '20-30 Atemzüge pro Minute', 0),
(79, 20, '16-25 Atemzüge pro Minute', 0),
(80, 20, '12-16 Atemzüge pro Minute', 0),
(81, 21, 'Atemstillstand', 0),
(82, 21, 'Hyperventilation', 1),
(83, 21, 'Leberzirrhose', 0),
(84, 21, 'Schenkelhalsfraktur', 0),
(85, 22, 'M. sternocleidomastoideus (Kopfwender)', 1),
(86, 22, 'M. rectus abdominis (Bauchmuskel)', 0),
(87, 22, 'M. latissimus dorsi (Rückenmuskel)', 0),
(88, 22, 'M. omohyoideus (Zungenbeinmuskel)', 0),
(89, 23, '1863', 0),
(90, 23, '1866', 1),
(91, 23, '1876', 0),
(92, 23, '1883', 0),
(93, 24, 'Aufbau von Krankenhäusern', 0),
(94, 24, 'Unterstützung der Kriegsverwundeten', 1),
(95, 24, 'Förderung der internationalen Zusammenarbeit', 0),
(96, 24, 'Ausbildung von medizinischem Personal', 0),
(97, 25, 'Deutsch-Französischer Krieg', 1),
(98, 25, 'Erster Weltkrieg', 0),
(99, 25, 'Zweiter Weltkrieg', 0),
(100, 25, 'Deutsch-Dänischer Krieg', 0),
(101, 26, 'Genfer Abkommen', 1),
(102, 26, 'Wiener Konvention', 0),
(103, 26, 'Haager Abkommen', 0),
(104, 26, 'Pariser Friedensvertrag', 0),
(105, 27, 'Über 100', 0),
(106, 27, 'Über 150', 0),
(107, 27, 'Über 190', 1),
(108, 27, 'Über 200', 0),
(109, 28, 'DNA', 0),
(110, 28, 'ATP', 1),
(111, 28, 'RNA', 0),
(112, 28, 'ADP', 0),
(113, 29, 'Freischwimmende Organismen', 0),
(114, 29, 'Organismen auf dem Seegrund', 1),
(115, 29, 'Schwebeorganismen im Wasser', 0),
(116, 29, 'Parasiten im Wasserkörper', 0),
(117, 30, 'Glukose', 0),
(118, 30, 'Ethanol', 0),
(119, 30, 'Glycerin', 0),
(120, 30, 'Amylase', 1),
(121, 31, 'Mitochondrien', 0),
(122, 31, 'Zellkern', 0),
(123, 31, 'Ribosomen', 1),
(124, 31, 'Golgi-Apparat', 0),
(125, 32, 'Immunabwehr', 0),
(126, 32, 'Sauerstofftransport', 1),
(127, 32, 'Blutgerinnung', 0),
(128, 32, 'Nährstofftransport', 0),
(129, 33, 'Fische', 0),
(130, 33, 'Algen', 1),
(131, 33, 'Insektenlarven', 0),
(132, 33, 'Amphibien', 0),
(133, 34, 'Schwimmende Personen ins Rettungsboot übernehmen und das gekenterte Boot aufrichten', 0),
(134, 34, 'Anzahl der Bootsinsassen erfragen', 1),
(135, 34, 'Unter das Segel tauchen und kontrollieren, ob sich eine Person darunter befindet', 0),
(136, 35, 'Weil das seewärts über Grund ablaufende Wasser Nichtschwimmer, Kinder und ältere Personen besonders gefährdet', 1),
(137, 35, 'Weil es durch die Wasserbewegung kurzfristig zur Bildung von Strudeln kommen kann', 0),
(138, 35, 'Weil schnell große Mengen Sand abgetragen werden könnte', 0),
(139, 36, 'liegt hauptsächlich vor dem Wehr', 0),
(140, 36, 'ist nicht vorhanden', 0),
(141, 36, 'liegt hinter dem Wehr (Wasserwalzen, einschließlich schwimmfähiger Gegenstände)', 1),
(142, 37, 'Die Steuerbordseite.', 0),
(143, 37, 'Die Backbordseite.', 0),
(144, 37, 'Das Heck des Bootes.', 1),
(145, 37, 'Der Bug des Boots.', 0),
(146, 38, 'In Ufernähe auf der Innenseite einer Kurve.', 0),
(147, 38, 'In der Nähe des Ufers.', 0),
(148, 38, 'In der Mitte des Stromes.', 1),
(149, 39, '1876', 0),
(150, 39, '1883', 1),
(151, 39, '1887', 0),
(152, 39, '1898', 0);

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
(1, 5),
(1, 8),
(1, 9),
(2, 1),
(2, 6),
(3, 2),
(3, 4),
(4, 3),
(4, 7),
(5, 2),
(5, 6),
(5, 9),
(6, 1),
(6, 7),
(7, 4),
(7, 8),
(8, 3),
(8, 5),
(9, 1),
(9, 6),
(9, 8),
(10, 5),
(10, 9),
(11, 2),
(11, 4),
(12, 3),
(12, 7),
(13, 1),
(13, 5),
(13, 7),
(14, 6),
(14, 8),
(15, 3),
(15, 4),
(16, 2),
(16, 9);

-- --------------------------------------------------------

--
-- Table structure for table `Mannschaft`
--

CREATE TABLE `Mannschaft` (
  `ID` int(11) NOT NULL,
  `Teamname` varchar(100) NOT NULL,
  `Kreisverband` varchar(32) NOT NULL,
  `Landesverband` varchar(32) NOT NULL,
  `Gesamtpunkte` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Mannschaft`
--

INSERT INTO `Mannschaft` (`ID`, `Teamname`, `Kreisverband`, `Landesverband`, `Gesamtpunkte`) VALUES
(1, '1. im Schwimmen, 3. im Parcours', 'deine Oma', 'Toilette', NULL),
(2, '3. im Schwimmen, 1. im Parcours', 'dein Opa', 'Waschbecken', NULL),
(3, '2. im Schwimmen, 2. im Parcours', 'deine Tante', 'Teppich', NULL),
(4, 'Looser', 'dein Onkel', 'Badewanne', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `MannschaftProtokoll`
--

CREATE TABLE `MannschaftProtokoll` (
  `mannschaft_ID` int(11) NOT NULL,
  `protokoll_Nr` int(11) NOT NULL,
  `erreichte_Punkte` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(2, 1),
(3, 1),
(3, 2),
(4, 2);

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
(1, 'Betroffener 1: Chlorgasvergiftung (rote Augen, Atemnot, Husten)', 200, 5),
(2, 'Gesamteindruck/Führung/Lagemeldung', 190, 5),
(3, 'Betroffener 2: Reanimation (Erwachsener)', 220, 5),
(5, 'Betroffener 1: Badbetreiber Wirbelsäulenverletzung', 170, 1),
(6, 'Betroffener 2: Bademeister', 160, 1);

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
(1, 1, 'Welches ist das größte Organ des menschlichen Körpers?'),
(2, 1, 'Wie viele Knochen hat ein erwachsener Mensch?'),
(3, 1, 'Welche Funktion hat die Leber im menschlichen Körper?'),
(4, 1, 'Welcher Muskel ist der stärkste im menschlichen Körper, basierend auf seiner Größe?'),
(5, 1, 'Wie viele Herzkammern hat das menschliche Herz?'),
(6, 1, 'Welcher Fisch gilt als der größte Süßwasserfisch in Deutschland?'),
(7, 1, 'Welche der folgenden Fischarten ist anadrom?'),
(8, 1, 'Welche Fischart ist für ihre markanten roten Punkte auf dem Körper bekannt?'),
(9, 1, 'Welcher Fisch ist bekannt für seine Fähigkeit, im Brackwasser zu leben, das sowohl Süß- als auch Salzwassereinflüsse hat?'),
(10, 1, 'Welche Schwimmtechnik wird oft als die schnellste angesehen?'),
(11, 1, 'Welche Schwimmtechnik wird als die älteste bekannte Schwimmtechnik angesehen?'),
(12, 1, 'Was bezeichnet man im Schwimmen als \"Lagen\"?'),
(13, 1, 'Welches Prinzip beschreibt die Notwendigkeit, Trainingsreize schrittweise zu erhöhen, um Leistungssteigerungen zu erzielen?'),
(14, 1, 'Welcher Begriff bezeichnet die Zeit, die ein Muskel braucht, um sich nach einer Belastung zu erholen?'),
(15, 1, 'Was ist die Hauptfunktion von Kreatin im menschlichen Körper in Bezug auf sportliche Leistung?'),
(16, 1, 'Wie hoch ist die Herzfrequenz des erwachsenen Menschen pro Minute in Ruhe?'),
(17, 1, 'Wie lagert man einen Verletzten mit Atemnot, der schaumiges Blut abhustet?'),
(18, 1, 'Welche Maßnahmen sind bei einem Verletzten mit erfrorenen Gliedmaßen notwendig?'),
(19, 1, 'Warum ist eine Verletzung des Trommelfells besonders gefährlich?'),
(20, 1, 'Wie hoch ist die Atemfrequenz eines Säugling pro Minute in Ruhe?'),
(21, 1, 'Eine Alkalisierung des Blutes erfolgt bei welchem Krankheitsbild?'),
(22, 1, 'Welcher Muskel kommt als Einatemhilfsmuskel zum Einsatz bei einer Ruhe- oder\r\nBelastungsdypnoe?'),
(23, 1, 'Wann wurde das Deutsche Rote Kreuz (DRK) offiziell gegründet?'),
(24, 1, 'Was war die ursprüngliche Aufgabe des Deutschen Roten Kreuzes?'),
(25, 1, 'In welchem Krieg war das Deutsche Rote Kreuz erstmals aktiv tätig?'),
(26, 1, 'Welches Abkommen regelt die humanitären Prinzipien des Roten Kreuzes?'),
(27, 1, 'Wie viele nationale Rotkreuz- und Rothalbmond-Gesellschaften gibt es weltweit?'),
(28, 1, 'Welches Molekül ist die primäre Energiequelle für zelluläre Prozesse?'),
(29, 1, 'Was bezeichnet der Begriff „Benthos“?'),
(30, 1, 'Welche der folgenden Substanzen ist ein Enzym?'),
(31, 1, 'Wo findet die Proteinbiosynthese statt?'),
(32, 1, 'Was ist die Hauptfunktion der roten Blutkörperchen?'),
(33, 1, 'Welche der folgenden Organismen sind Primärproduzenten in einem See?'),
(34, 1, 'Welche Maßnahme wird bei einem Segelbootunfall zuerst durchgeführt?'),
(35, 1, 'Warum hat der Rettungsschwimmer bei Brandung besonders den\r\nFlachwasserbereich zu beobachten?'),
(36, 1, 'Die Hauptgefahrenzone bei einem Wehr'),
(37, 1, 'Welche Stelle eines Ruderbootes mit Spiegelheck eignet sich am besten zur\r\nÜbernahme einesVerunglückten ins Boot?'),
(38, 1, 'Wo schwimmt man mit dem größten Kraftaufwand gegen den Strom?'),
(39, 1, 'Wann wurde die Wasserwacht des DRK gegründet?');

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
(1, 6, 'Waitingpoint-Formular (1/4)', 180),
(2, 6, 'Waitingpoint-Formular (2/4)', 180),
(3, 6, 'Waitingpoint-Formular (3/4)', 180),
(4, 6, 'Waitingpoint-Formular (4/4)', 180),
(5, 6, 'Waitingpoint-Formular (1/4)', 180),
(6, 6, 'Waitingpoint-Formular (2/4)', 180),
(7, 6, 'Waitingpoint-Formular (3/4)', 180),
(8, 6, 'Waitingpoint-Formular (4/4)', 180),
(9, 6, 'Waitingpoint-Formular (1/4)', 180),
(10, 6, 'Waitingpoint-Formular (2/4)', 180),
(11, 6, 'Waitingpoint-Formular (3/4)', 180),
(12, 6, 'Waitingpoint-Formular (4/4)', 180),
(13, 6, 'Waitingpoint-Formular (1/4)', 180),
(14, 6, 'Waitingpoint-Formular (2/4)', 180),
(15, 6, 'Waitingpoint-Formular (3/4)', 180),
(16, 6, 'Waitingpoint-Formular (4/4)', 180);

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
(1, 'waitingpoints');

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
(3, 'TOTAL_POINTS', '6000'),
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
(2, 'Tauchstaffel'),
(3, 'Beachlauf');

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
(1, 'Sturz', 1),
(5, 'Chlor', 5),
(6, 'Waitingpoint', 9);

-- --------------------------------------------------------

--
-- Table structure for table `StationWeight`
--

CREATE TABLE `StationWeight` (
  `station_ID` int(11) NOT NULL,
  `weight` int(11) NOT NULL DEFAULT 100
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(1, 1, 1, 1, 0, '2025-05-12 19:17:20', 'LX5cYWYML1zQxZ2j', '2025-05-12 19:17:12'),
(1, 2, 2, 1, 2, '2025-05-12 19:16:21', 'ELUmq6XLRh4VeL41', '2025-05-12 19:16:12'),
(1, 3, 3, 1, 1, '2025-05-12 19:13:12', 'cQPjQHBi5o8oIq8k', '2025-05-12 19:12:48'),
(1, 4, 4, 1, 2, '2025-05-12 19:11:55', 'yE7qPn1gqvZI8ejw', '2025-05-12 19:11:27'),
(2, 5, 1, 1, 2, '2025-05-12 23:13:43', 'DkZAvPou03Pj8Bm5', '2025-05-12 23:13:36'),
(2, 6, 2, 1, 2, '2025-05-12 23:13:14', 'mNY6YvSBhSJg3wfP', '2025-05-12 23:13:07'),
(2, 7, 3, 1, 0, '2025-05-12 19:21:33', '7Q0a42StFJyZD4WF', '2025-05-12 19:21:26'),
(2, 8, 4, 1, 1, '2025-05-12 19:19:36', 'IvsK5wI5T4WbQdir', '2025-05-12 19:19:19'),
(3, 9, 1, 1, 1, '2025-05-12 23:12:41', 'alKpqiaNV6iKV6x4', '2025-05-12 23:12:36'),
(3, 10, 2, 0, 0, NULL, '8fjYmQLmxaYWW6mu', '2025-05-12 23:12:47'),
(3, 11, 3, 1, 1, '2025-05-12 23:13:30', 'TeMn0rLekBTceCH2', '2025-05-12 23:13:23'),
(3, 12, 4, 1, 1, '2025-05-12 23:30:55', 'yMrG3QS6n7Ik5Eiq', '2025-05-12 23:12:59'),
(4, 13, 1, 0, 0, NULL, '5Qcjt5stjs8r2WpV', NULL),
(4, 14, 2, 0, 0, NULL, 'ZmIeeIop9zrjwWZ7', NULL),
(4, 15, 3, 0, 0, NULL, 'nMtnHZYZHUumx9ZX', NULL),
(4, 16, 4, 0, 0, NULL, 'm9EBOyIBMtaQ0ZaP', NULL);

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
(19, 'jonas.richter', '8726b3e07a4be80785bfe94008af4e3a', 'Wettkampfleitung', NULL, NULL),
(20, 'sven.meiburg', 'ee42f0374b76680a5959f1b6f28d29d8', 'Wettkampfleitung', NULL, NULL),
(21, 'schiri1', '69f7bdb058f18bc7b777b9b63747c2a1', 'Schiedsrichter', NULL, NULL),
(22, 'schiri2', '69f7bdb058f18bc7b777b9b63747c2a1', 'Schiedsrichter', NULL, NULL),
(27, '1', '86f763b513dba0ea7b9da681af1871ac', 'Teilnehmer', 1, NULL),
(28, '2', 'c869c48dcc3381ed5e3420254e91c345', 'Teilnehmer', 2, NULL),
(29, '3', 'f815a17b1d28e3f8128f40b3719c8b4b', 'Teilnehmer', 3, NULL),
(30, '4', '986049af092fd87e7d2e4402eb596d71', 'Teilnehmer', 4, NULL);

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
(1, 'Wertung 1'),
(2, 'Wertung 2');

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
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=161;

--
-- AUTO_INCREMENT for table `Mannschaft`
--
ALTER TABLE `Mannschaft`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `Protokoll`
--
ALTER TABLE `Protokoll`
  MODIFY `Nr` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `Question`
--
ALTER TABLE `Question`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `QuestionForm`
--
ALTER TABLE `QuestionForm`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `QuestionPool`
--
ALTER TABLE `QuestionPool`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `ResultConfiguration`
--
ALTER TABLE `ResultConfiguration`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `Staffel`
--
ALTER TABLE `Staffel`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `Station`
--
ALTER TABLE `Station`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `User`
--
ALTER TABLE `User`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `Wertungsklasse`
--
ALTER TABLE `Wertungsklasse`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
