-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: db
-- Erstellungszeit: 09. Mrz 2025 um 21:30
-- Server-Version: 10.11.10-MariaDB-ubu2204
-- PHP-Version: 8.2.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `webappdb`
--

--
-- Daten für Tabelle `Mannschaft`
--

INSERT INTO `Mannschaft` (`ID`, `Teamname`, `Kreisverband`, `Landesverband`, `Gesamtpunkte`) VALUES
(1, 'Arnstadt-Herren', 'Arnstadt', 'Thüringen', NULL),
(2, 'Arnstadt-Damen', 'Arnstadt', 'Thüringen', NULL),
(3, 'Erfurt-Herren', 'Erfurt', 'Thüringen', NULL),
(4, 'Sömmerda-Damen', 'Sömmerda', 'Thüringen', NULL),
(5, 'Sondershausen-Herren', 'Sondershausen', 'Thüringen', NULL),
(6, 'Greiz-Damen', 'Greiz', 'Thüringen', NULL);

--
-- Daten für Tabelle `MannschaftProtokoll`
--

INSERT INTO `MannschaftProtokoll` (`mannschaft_ID`, `protokoll_Nr`, `erreichte_Punkte`) VALUES
(1, 1, 143),
(1, 2, 134),
(1, 3, 125),
(1, 4, 168),
(1, 5, 128),
(1, 6, 116),
(1, 7, 82),
(1, 8, 168),
(1, 9, 49),
(1, 10, 122),
(1, 11, 114),
(1, 12, 105),
(1, 13, 109),
(1, 14, 93),
(1, 15, 81),
(1, 16, 76),
(1, 17, 52),
(2, 1, 113),
(2, 2, 128),
(2, 3, 130),
(2, 4, 180),
(2, 5, 143),
(2, 6, 106),
(2, 7, 92),
(2, 8, 151),
(2, 9, 148),
(2, 10, 112),
(2, 11, 110),
(2, 12, 120),
(2, 13, 100),
(2, 14, 74),
(2, 15, 76),
(2, 16, 82),
(2, 17, 89),
(3, 1, 147),
(3, 2, 136),
(3, 3, 152),
(3, 4, 164),
(3, 5, 141),
(3, 6, 110),
(3, 7, 86),
(3, 8, 145),
(3, 9, 102),
(3, 10, 95),
(3, 11, 98),
(3, 12, 103),
(3, 13, 89),
(3, 14, 68),
(3, 15, 86),
(3, 16, 76),
(3, 17, 96),
(4, 1, 120),
(4, 2, 132),
(4, 3, 141),
(4, 4, 154),
(4, 5, 133),
(4, 6, 100),
(4, 7, 67),
(4, 8, 167),
(4, 9, 124),
(4, 10, 93),
(4, 11, 87),
(4, 12, 91),
(4, 13, 110),
(4, 14, 86),
(4, 15, 89),
(4, 16, 94),
(4, 17, 95),
(5, 1, 132),
(5, 2, 120),
(5, 3, 141),
(5, 4, 162),
(5, 5, 126),
(5, 6, 104),
(5, 7, 91),
(5, 8, 138),
(5, 9, 118),
(5, 10, 126),
(5, 11, 116),
(5, 12, 65),
(5, 13, 103),
(5, 14, 94),
(5, 15, 76),
(5, 16, 85),
(5, 17, 83),
(6, 1, 132),
(6, 2, 138),
(6, 3, 151),
(6, 4, 170),
(6, 5, 139),
(6, 6, 107),
(6, 7, 86),
(6, 8, 169),
(6, 9, 87),
(6, 10, 105),
(6, 11, 86),
(6, 12, 96),
(6, 13, 98),
(6, 14, 87),
(6, 15, 89),
(6, 16, 92),
(6, 17, 74);

--
-- Daten für Tabelle `MannschaftStaffel`
--

INSERT INTO `MannschaftStaffel` (`mannschaft_ID`, `staffel_ID`, `schwimmzeit`, `strafzeit`) VALUES
(1, 1, '00:04:28.6100', '00:00:00.0000'),
(1, 3, '00:01:48.3900', '00:00:00.0000'),
(1, 4, '00:00:58.3900', '00:00:05.0000'),
(1, 5, '00:03:46.0000', '00:00:00.0000'),
(1, 6, '00:02:01.0000', '00:00:00.0000'),
(1, 7, '00:01:45.3400', '00:00:10.0000'),
(2, 1, '00:04:23.2400', '00:00:00.0000'),
(2, 3, '00:01:25.2400', '00:00:05.0000'),
(2, 4, '00:00:59.2800', '00:00:00.0000'),
(2, 5, '00:03:57.0000', '00:00:05.0000'),
(2, 6, '00:02:32.0000', '00:00:20.0000'),
(2, 7, '00:01:23.2400', '00:00:00.0000'),
(3, 1, '00:04:14.3800', '00:00:00.0000'),
(3, 3, '00:01:36.2300', '00:00:00.0000'),
(3, 4, '00:00:57.4900', '00:00:00.0000'),
(3, 5, '00:03:27.0000', '00:00:00.0000'),
(3, 6, '00:02:35.0000', '00:00:00.0000'),
(3, 7, '00:01:43.5200', '00:00:00.0000'),
(4, 1, '00:03:58.0800', '00:00:00.0000'),
(4, 3, '00:01:32.6900', '00:00:00.0000'),
(4, 4, '00:00:54.3100', '00:00:00.0000'),
(4, 5, '00:03:49.0000', '00:00:05.0000'),
(4, 6, '00:02:18.0000', '00:00:00.0000'),
(4, 7, '00:01:35.5200', '00:00:10.0000'),
(5, 1, '00:04:02.3900', '00:00:00.0000'),
(5, 3, '00:01:44.4800', '00:00:05.0000'),
(5, 4, '00:00:55.1100', '00:00:10.0000'),
(5, 5, '00:03:33.0000', '00:00:00.0000'),
(5, 6, '00:02:38.0000', '00:00:00.0000'),
(5, 7, '00:01:43.4400', '00:00:00.0000'),
(6, 1, '00:03:56.2100', '00:00:00.0000'),
(6, 3, '00:01:41.1000', '00:00:00.0000'),
(6, 4, '00:00:56.2800', '00:00:00.0000'),
(6, 5, '00:03:12.0000', '00:00:00.0000'),
(6, 6, '00:02:22.0000', '00:00:10.0000'),
(6, 7, '00:01:26.3100', '00:00:05.0000');

--
-- Daten für Tabelle `MannschaftWertung`
--

INSERT INTO `MannschaftWertung` (`mannschaft_ID`, `wertung_ID`) VALUES
(1, 1),
(2, 2),
(3, 1),
(4, 2),
(5, 1),
(6, 2);

--
-- Daten für Tabelle `ProtocolModel`
--

INSERT INTO `Protokoll` (`Nr`, `Name`, `max_Punkte`, `station_Nr`) VALUES
(1, 'Erwachsener 1', 150, 1),
(2, 'Erwachsener 2', 150, 1),
(3, 'Jugendlicher', 160, 1),
(4, 'Kind', 180, 1),
(5, 'Bewusstlose Person', 150, 2),
(6, 'Knochenbruch', 120, 2),
(7, 'Schock', 100, 2),
(8, 'Herzinfarkt', 180, 3),
(9, 'starke Blutung', 160, 3),
(10, 'Schock', 140, 3),
(11, 'Ertrinkende Person', 120, 4),
(12, 'Schock', 130, 4),
(13, 'Kopfplatzwunde', 110, 4),
(14, 'Teilnehmender 1', 100, 5),
(15, 'Teilnehmender 2', 100, 5),
(16, 'Teilnehmender 3', 100, 5),
(17, 'Gruppenergebnis', 100, 6);

--
-- Daten für Tabelle `Staffel`
--

INSERT INTO `Staffel` (`ID`, `name`) VALUES
(1, 'Wasserrettungsstaffel'),
(3, 'Leinenstaffel'),
(4, 'Tauchstaffel'),
(5, 'Kombi-Staffel'),
(6, 'Rettungsmittelstaffel'),
(7, 'Kraul-Sprint-Staffel');

--
-- Daten für Tabelle `Station`
--

INSERT INTO `Station` (`Nr`, `name`) VALUES
(1, 'Reanimation'),
(2, 'Fahrradunfall'),
(3, 'Busunfall-MANV'),
(4, 'Wasserrettung'),
(5, 'Einzel-Theorie'),
(6, 'Gruppentheorie');

--
-- Daten für Tabelle `Wertungsklasse`
--

INSERT INTO `Wertungsklasse` (`ID`, `name`) VALUES
(1, 'Herrenwertung'),
(2, 'Damenwertung');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
