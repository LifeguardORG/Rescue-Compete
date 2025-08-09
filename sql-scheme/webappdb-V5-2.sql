-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: db
-- Generation Time: Aug 06, 2025 at 12:10 PM
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

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`webapp`@`%` PROCEDURE `autoSubmitExpiredForm` (IN `instanceId` INT)   BEGIN
    DECLARE teamId INT;
    DECLARE collectionId INT;
    DECLARE formNumber INT;
    DECLARE correctAnswers INT DEFAULT 0;
    DECLARE totalQuestions INT;
    
    -- Hole Instance-Informationen
    SELECT team_ID, collection_ID, formNumber
    INTO teamId, collectionId, formNumber
    FROM TeamFormInstance
    WHERE ID = instanceId AND completed = 0;
    
    -- Prüfe, ob Instance gefunden wurde
    IF teamId IS NOT NULL THEN
        -- Berechne Punkte basierend auf gespeicherten Antworten
        SELECT COUNT(*) INTO correctAnswers
        FROM TeamFormAnswer tfa
        JOIN Answer a ON tfa.answer_ID = a.ID
        WHERE tfa.teamFormInstance_ID = instanceId AND a.IsCorrect = 1;
        
        -- Hole Gesamtfragenzahl für diese Instance
        SELECT JSON_LENGTH(assignedQuestions) INTO totalQuestions
        FROM TeamFormInstance
        WHERE ID = instanceId;
        
        -- Markiere als abgeschlossen
        UPDATE TeamFormInstance
        SET completed = 1,
            points = correctAnswers,
            completionDate = NOW()
        WHERE ID = instanceId;
        
        SELECT CONCAT('Form auto-submitted for Team ', teamId, 
                      '. Score: ', correctAnswers, '/', COALESCE(totalQuestions, 0)) as result;
    ELSE
        SELECT 'Instance not found or already completed' as result;
    END IF;
END$$

CREATE DEFINER=`webapp`@`%` PROCEDURE `createTeamFormInstance` (IN `teamId` INT, IN `collectionId` INT, IN `formNumber` INT)   BEGIN
    DECLARE totalQuestions INT;
    DECLARE formsCount INT;
    DECLARE questionsForThisForm INT;
    DECLARE instanceId INT;
    DECLARE questionIds JSON;
    
    -- Prüfe, ob bereits eine Instance existiert
    SELECT ID INTO instanceId
    FROM TeamFormInstance
    WHERE team_ID = teamId AND collection_ID = collectionId AND formNumber = formNumber;
    
    IF instanceId IS NOT NULL THEN
        SELECT instanceId as existing_instance_id, 'Instance already exists' as message;
    ELSE
        -- Hole Collection-Informationen
        SELECT fc.totalQuestions, fc.formsCount 
        INTO totalQuestions, formsCount
        FROM FormCollection fc
        WHERE fc.ID = collectionId;
        
        -- Berechne Fragenanzahl für dieses Formular
        SET questionsForThisForm = distributeQuestionsToForms(totalQuestions, formsCount, formNumber);
        
        -- Hole zufällige Fragen für dieses Formular
        SET @sql = CONCAT('SELECT JSON_ARRAYAGG(cq.question_ID) INTO @questionIds
                           FROM (
                               SELECT cq.question_ID
                               FROM CollectionQuestion cq
                               WHERE cq.collection_ID = ', collectionId, '
                               ORDER BY RAND()
                               LIMIT ', questionsForThisForm, '
                           ) cq');
        
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
        
        SET questionIds = @questionIds;
        
        -- Erstelle TeamFormInstance
        INSERT INTO TeamFormInstance (team_ID, collection_ID, formNumber, assignedQuestions)
        VALUES (teamId, collectionId, formNumber, questionIds);
        
        SET instanceId = LAST_INSERT_ID();
        
        SELECT instanceId as created_instance_id, questionIds as assigned_questions, 'Instance created successfully' as message;
    END IF;
END$$

CREATE DEFINER=`webapp`@`%` PROCEDURE `deleteFormCollection` (IN `collectionId` INT)   BEGIN
    DECLARE collectionName VARCHAR(255);
    DECLARE affectedTeams INT DEFAULT 0;
    DECLARE affectedInstances INT DEFAULT 0;
    
    -- Hole Collection-Name für Logging
    SELECT name INTO collectionName
    FROM FormCollection
    WHERE ID = collectionId;
    
    -- Zähle betroffene Einträge
    SELECT COUNT(DISTINCT team_ID) INTO affectedTeams
    FROM TeamFormInstance
    WHERE collection_ID = collectionId;
    
    SELECT COUNT(*) INTO affectedInstances
    FROM TeamFormInstance
    WHERE collection_ID = collectionId;
    
    -- Lösche Collection (CASCADE löscht automatisch abhängige Einträge)
    DELETE FROM FormCollection WHERE ID = collectionId;
    
    -- Ausgabe der Löschstatistik
    SELECT CONCAT('Collection "', COALESCE(collectionName, 'Unknown'), '" gelöscht. ',
                  'Betroffene Teams: ', affectedTeams, ', ',
                  'Gelöschte Instanzen: ', affectedInstances) as result;
END$$

CREATE DEFINER=`webapp`@`%` PROCEDURE `saveFormAnswer` (IN `instanceId` INT, IN `questionId` INT, IN `answerId` INT)   BEGIN
    INSERT INTO TeamFormAnswer (teamFormInstance_ID, question_ID, answer_ID)
    VALUES (instanceId, questionId, answerId)
    ON DUPLICATE KEY UPDATE 
        answer_ID = VALUES(answer_ID),
        savedAt = CURRENT_TIMESTAMP;
END$$

--
-- Functions
--
CREATE DEFINER=`webapp`@`%` FUNCTION `distributeQuestionsToForms` (`totalQuestions` INT, `formsCount` INT, `formNumber` INT) RETURNS INT(11) DETERMINISTIC BEGIN
    DECLARE questionsPerForm INT;
    DECLARE remainder INT;
    DECLARE result INT;
    
    SET questionsPerForm = totalQuestions DIV formsCount;
    SET remainder = totalQuestions MOD formsCount;
    
    -- Erste Formulare bekommen eine zusätzliche Frage bei ungerader Verteilung
    IF formNumber <= remainder THEN
        SET result = questionsPerForm + 1;
    ELSE
        SET result = questionsPerForm;
    END IF;
    
    RETURN result;
END$$

CREATE DEFINER=`webapp`@`%` FUNCTION `getQuestionStartIndex` (`totalQuestions` INT, `formsCount` INT, `formNumber` INT) RETURNS INT(11) DETERMINISTIC BEGIN
    DECLARE questionsPerForm INT;
    DECLARE remainder INT;
    DECLARE startIndex INT DEFAULT 0;
    DECLARE i INT DEFAULT 1;
    
    SET questionsPerForm = totalQuestions DIV formsCount;
    SET remainder = totalQuestions MOD formsCount;
    
    -- Berechne Startindex für das gewünschte Formular
    WHILE i < formNumber DO
        IF i <= remainder THEN
            SET startIndex = startIndex + questionsPerForm + 1;
        ELSE
            SET startIndex = startIndex + questionsPerForm;
        END IF;
        SET i = i + 1;
    END WHILE;
    
    RETURN startIndex;
END$$

CREATE DEFINER=`webapp`@`%` FUNCTION `isValidToken` (`tokenValue` VARCHAR(32)) RETURNS TINYINT(1) DETERMINISTIC READS SQL DATA BEGIN
    DECLARE tokenCount INT DEFAULT 0;
    
    SELECT COUNT(*) INTO tokenCount
    FROM TeamFormInstance
    WHERE token = tokenValue;
    
    RETURN tokenCount > 0;
END$$

CREATE DEFINER=`webapp`@`%` FUNCTION `resolveFormToken` (`tokenCode` VARCHAR(12)) RETURNS LONGTEXT CHARSET utf8mb4 COLLATE utf8mb4_bin DETERMINISTIC READS SQL DATA BEGIN
    DECLARE result JSON DEFAULT NULL;
    
    -- Suche in CollectionFormToken
    SELECT JSON_OBJECT(
        'type', 'collection',
        'collection_id', cft.collection_ID,
        'form_number', cft.formNumber,
        'collection_name', fc.name
    ) INTO result
    FROM CollectionFormToken cft
    JOIN FormCollection fc ON cft.collection_ID = fc.ID
    WHERE cft.token = tokenCode
    LIMIT 1;
    
    RETURN result;
END$$

DELIMITER ;

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
-- Table structure for table `CollectionFormToken`
--

CREATE TABLE `CollectionFormToken` (
  `ID` int(11) NOT NULL,
  `collection_ID` int(11) NOT NULL,
  `formNumber` int(11) NOT NULL,
  `token` varchar(12) NOT NULL COMMENT 'MD5-Hash (erste 12 Zeichen)',
  `createdAt` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `CollectionFormToken`
--

INSERT INTO `CollectionFormToken` (`ID`, `collection_ID`, `formNumber`, `token`, `createdAt`) VALUES
(1, 1, 1, '0c4ebc9aa784', '2025-07-16 10:22:15'),
(2, 1, 2, '861a15742b86', '2025-07-16 10:22:15'),
(3, 1, 3, 'd8d4a4e2a81d', '2025-07-16 10:22:15'),
(4, 1, 4, 'ab7ffd466103', '2025-07-16 10:22:15'),
(5, 2, 1, 'b0e632d10c2d', '2025-07-30 11:46:48'),
(6, 2, 2, '474762470133', '2025-07-30 11:46:48'),
(7, 2, 3, '4ac1801c7d35', '2025-07-30 11:46:48'),
(8, 2, 4, '6f755032b65d', '2025-07-30 11:46:48');

-- --------------------------------------------------------

--
-- Stand-in structure for view `CollectionOverview`
-- (See below for the actual view)
--
CREATE TABLE `CollectionOverview` (
`ID` int(11)
,`name` varchar(255)
,`description` text
,`timeLimit` int(11)
,`totalQuestions` int(11)
,`formsCount` int(11)
,`createdAt` timestamp
,`stationName` varchar(32)
,`assignedTeams` bigint(21)
,`completedForms` bigint(21)
,`totalInstances` bigint(21)
,`averagePoints` decimal(14,4)
,`completionRate` decimal(25,1)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `CollectionPerformance`
-- (See below for the actual view)
--
CREATE TABLE `CollectionPerformance` (
`collectionId` int(11)
,`collectionName` varchar(255)
,`formsCount` int(11)
,`totalQuestions` int(11)
,`timeLimit` int(11)
,`teamsAssigned` bigint(21)
,`totalInstances` bigint(21)
,`completedInstances` bigint(21)
,`averageScore` decimal(14,4)
,`maxScore` bigint(11)
,`minScore` bigint(11)
,`completionRate` decimal(25,1)
);

-- --------------------------------------------------------

--
-- Table structure for table `CollectionQuestion`
--

CREATE TABLE `CollectionQuestion` (
  `collection_ID` int(11) NOT NULL,
  `question_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `CollectionQuestion`
--

INSERT INTO `CollectionQuestion` (`collection_ID`, `question_ID`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(1, 7),
(1, 8),
(1, 9),
(1, 10),
(1, 11),
(1, 12),
(1, 13),
(1, 14),
(1, 15),
(1, 16),
(1, 17),
(1, 18),
(1, 19),
(1, 20),
(1, 21),
(1, 22),
(1, 23),
(1, 24),
(1, 25),
(1, 26),
(1, 27),
(1, 28),
(1, 29),
(1, 30),
(1, 31),
(1, 32),
(1, 33),
(1, 34),
(1, 35),
(1, 36),
(1, 37),
(1, 38),
(1, 39),
(2, 1),
(2, 2),
(2, 3),
(2, 4),
(2, 5),
(2, 6),
(2, 7),
(2, 8),
(2, 9),
(2, 10),
(2, 11),
(2, 12),
(2, 13),
(2, 14),
(2, 15),
(2, 16),
(2, 17),
(2, 18),
(2, 19),
(2, 20),
(2, 21),
(2, 22),
(2, 23),
(2, 24),
(2, 25),
(2, 26),
(2, 27),
(2, 28),
(2, 29),
(2, 30),
(2, 31),
(2, 32),
(2, 33),
(2, 34),
(2, 35),
(2, 36),
(2, 37),
(2, 38),
(2, 39);

-- --------------------------------------------------------

--
-- Table structure for table `FormCollection`
--

CREATE TABLE `FormCollection` (
  `ID` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `timeLimit` int(11) DEFAULT 180 COMMENT 'Zeitlimit in Sekunden pro Formular',
  `totalQuestions` int(11) NOT NULL,
  `formsCount` int(11) NOT NULL,
  `createdAt` timestamp NULL DEFAULT current_timestamp(),
  `station_ID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `FormCollection`
--

INSERT INTO `FormCollection` (`ID`, `name`, `description`, `timeLimit`, `totalQuestions`, `formsCount`, `createdAt`, `station_ID`) VALUES
(1, 'Erste-Hilfe-Quiz', 'Grundlagen der Ersten Hilfe für Rettungsschwimmer', 180, 39, 4, '2025-07-16 10:22:15', 6),
(2, 'test', 'ghlind', 180, 39, 4, '2025-07-30 11:46:48', 6);

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
(3, '2. im Schwimmen, 2. im Parcours', 'deine Tante', 'Teppich', NULL);

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
(3, 1);

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
(5, 'POINTS_DEDUCTION', '1'),
(6, 'SCHEMA_VERSION', '4.8'),
(7, 'FORMCOLLECTION_ENABLED', '1'),
(8, 'LEGACY_CLEANUP_COMPLETED', '1');

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

--
-- Dumping data for table `StationWeight`
--

INSERT INTO `StationWeight` (`station_ID`, `weight`) VALUES
(1, 100),
(5, 100),
(6, 100);

-- --------------------------------------------------------

--
-- Stand-in structure for view `TeamCollectionProgress`
-- (See below for the actual view)
--
CREATE TABLE `TeamCollectionProgress` (
`teamId` int(11)
,`Teamname` varchar(100)
,`Kreisverband` varchar(32)
,`collectionId` int(11)
,`collectionName` varchar(255)
,`totalForms` bigint(21)
,`completedForms` bigint(21)
,`totalPoints` decimal(32,0)
,`completionPercentage` decimal(25,1)
);

-- --------------------------------------------------------

--
-- Table structure for table `TeamFormAnswer`
--

CREATE TABLE `TeamFormAnswer` (
  `ID` int(11) NOT NULL,
  `teamFormInstance_ID` int(11) NOT NULL,
  `question_ID` int(11) NOT NULL,
  `answer_ID` int(11) NOT NULL,
  `savedAt` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `TeamFormInstance`
--

CREATE TABLE `TeamFormInstance` (
  `ID` int(11) NOT NULL,
  `team_ID` int(11) NOT NULL,
  `collection_ID` int(11) NOT NULL,
  `formNumber` int(11) NOT NULL COMMENT 'Formnummer innerhalb der Collection (1,2,3,4)',
  `completed` tinyint(1) DEFAULT 0,
  `points` int(11) DEFAULT 0,
  `startTime` datetime DEFAULT NULL,
  `completionDate` datetime DEFAULT NULL,
  `token` varchar(32) DEFAULT NULL,
  `assignedQuestions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Array der zugewiesenen Fragen-IDs' CHECK (json_valid(`assignedQuestions`)),
  `createdAt` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `TeamFormInstance`
--
DELIMITER $$
CREATE TRIGGER `tr_teamforminstance_questions` BEFORE INSERT ON `TeamFormInstance` FOR EACH ROW BEGIN
    DECLARE totalQuestions INT;
    DECLARE formsCount INT;
    DECLARE questionsForThisForm INT;
    
    -- Nur wenn keine Fragen zugewiesen wurden
    IF NEW.assignedQuestions IS NULL THEN
        -- Hole Collection-Informationen
        SELECT fc.totalQuestions, fc.formsCount 
        INTO totalQuestions, formsCount
        FROM FormCollection fc
        WHERE fc.ID = NEW.collection_ID;
        
        -- Berechne Fragenanzahl für dieses Formular
        SET questionsForThisForm = distributeQuestionsToForms(totalQuestions, formsCount, NEW.formNumber);
        
        -- Setze leeres JSON-Array (wird später gefüllt)
        SET NEW.assignedQuestions = JSON_ARRAY();
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_teamforminstance_token` BEFORE INSERT ON `TeamFormInstance` FOR EACH ROW BEGIN
    IF NEW.token IS NULL THEN
        SET NEW.token = CONCAT(
            SUBSTRING(MD5(CONCAT(NEW.team_ID, NEW.collection_ID, NEW.formNumber, NOW())), 1, 16),
            SUBSTRING(MD5(RAND()), 1, 16)
        );
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Stand-in structure for view `TeamFormStatistics`
-- (See below for the actual view)
--
CREATE TABLE `TeamFormStatistics` (
`instanceId` int(11)
,`Teamname` varchar(100)
,`Kreisverband` varchar(32)
,`collectionName` varchar(255)
,`formNumber` int(11)
,`completed` tinyint(1)
,`points` int(11)
,`assignedQuestionsCount` int(10)
,`answeredQuestions` bigint(21)
,`status` varchar(15)
,`startTime` datetime
,`completionDate` datetime
,`token` varchar(32)
);

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
(31, 'admin', '5ede13f8c4f4b1416e9c7837629fd1bf', 'Admin', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `Wertungsklasse`
--

CREATE TABLE `Wertungsklasse` (
  `ID` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Wertungsklasse`
--

INSERT INTO `Wertungsklasse` (`ID`, `name`) VALUES
(1, 'Wertung 1'),
(3, 'Behinderte');

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
-- Indexes for table `CollectionFormToken`
--
ALTER TABLE `CollectionFormToken`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `uk_collectionformtoken_token` (`token`),
  ADD UNIQUE KEY `uk_collectionformtoken_collection_form` (`collection_ID`,`formNumber`),
  ADD KEY `idx_collectionformtoken_collection` (`collection_ID`);

--
-- Indexes for table `CollectionQuestion`
--
ALTER TABLE `CollectionQuestion`
  ADD PRIMARY KEY (`collection_ID`,`question_ID`),
  ADD KEY `idx_collectionquestion_question` (`question_ID`);

--
-- Indexes for table `FormCollection`
--
ALTER TABLE `FormCollection`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `idx_formcollection_station` (`station_ID`);

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
-- Indexes for table `TeamFormAnswer`
--
ALTER TABLE `TeamFormAnswer`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `uk_teamformanswer_instance_question` (`teamFormInstance_ID`,`question_ID`),
  ADD KEY `idx_teamformanswer_instance` (`teamFormInstance_ID`),
  ADD KEY `idx_teamformanswer_question` (`question_ID`),
  ADD KEY `idx_teamformanswer_answer` (`answer_ID`);

--
-- Indexes for table `TeamFormInstance`
--
ALTER TABLE `TeamFormInstance`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `uk_teamforminstance_team_collection_form` (`team_ID`,`collection_ID`,`formNumber`),
  ADD UNIQUE KEY `uk_teamforminstance_token` (`token`),
  ADD KEY `idx_teamforminstance_team` (`team_ID`),
  ADD KEY `idx_teamforminstance_collection` (`collection_ID`),
  ADD KEY `idx_teamforminstance_starttime` (`startTime`),
  ADD KEY `idx_teamforminstance_completed` (`completed`);

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
-- AUTO_INCREMENT for table `CollectionFormToken`
--
ALTER TABLE `CollectionFormToken`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `FormCollection`
--
ALTER TABLE `FormCollection`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
-- AUTO_INCREMENT for table `QuestionPool`
--
ALTER TABLE `QuestionPool`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `ResultConfiguration`
--
ALTER TABLE `ResultConfiguration`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
-- AUTO_INCREMENT for table `TeamFormAnswer`
--
ALTER TABLE `TeamFormAnswer`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `TeamFormInstance`
--
ALTER TABLE `TeamFormInstance`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `User`
--
ALTER TABLE `User`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `Wertungsklasse`
--
ALTER TABLE `Wertungsklasse`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

-- --------------------------------------------------------

--
-- Structure for view `CollectionOverview`
--
DROP TABLE IF EXISTS `CollectionOverview`;

CREATE ALGORITHM=UNDEFINED DEFINER=`webapp`@`%` SQL SECURITY DEFINER VIEW `CollectionOverview`  AS SELECT `fc`.`ID` AS `ID`, `fc`.`name` AS `name`, `fc`.`description` AS `description`, `fc`.`timeLimit` AS `timeLimit`, `fc`.`totalQuestions` AS `totalQuestions`, `fc`.`formsCount` AS `formsCount`, `fc`.`createdAt` AS `createdAt`, `s`.`name` AS `stationName`, count(distinct `tfi`.`team_ID`) AS `assignedTeams`, count(case when `tfi`.`completed` = 1 then `tfi`.`ID` end) AS `completedForms`, count(`tfi`.`ID`) AS `totalInstances`, avg(case when `tfi`.`completed` = 1 then `tfi`.`points` end) AS `averagePoints`, round(count(case when `tfi`.`completed` = 1 then `tfi`.`ID` end) / count(`tfi`.`ID`) * 100,1) AS `completionRate` FROM ((`FormCollection` `fc` left join `Station` `s` on(`fc`.`station_ID` = `s`.`ID`)) left join `TeamFormInstance` `tfi` on(`fc`.`ID` = `tfi`.`collection_ID`)) GROUP BY `fc`.`ID`, `fc`.`name`, `fc`.`description`, `fc`.`timeLimit`, `fc`.`totalQuestions`, `fc`.`formsCount`, `fc`.`createdAt`, `s`.`name` ;

-- --------------------------------------------------------

--
-- Structure for view `CollectionPerformance`
--
DROP TABLE IF EXISTS `CollectionPerformance`;

CREATE ALGORITHM=UNDEFINED DEFINER=`webapp`@`%` SQL SECURITY DEFINER VIEW `CollectionPerformance`  AS SELECT `fc`.`ID` AS `collectionId`, `fc`.`name` AS `collectionName`, `fc`.`formsCount` AS `formsCount`, `fc`.`totalQuestions` AS `totalQuestions`, `fc`.`timeLimit` AS `timeLimit`, count(distinct `tfi`.`team_ID`) AS `teamsAssigned`, count(`tfi`.`ID`) AS `totalInstances`, count(case when `tfi`.`completed` = 1 then 1 end) AS `completedInstances`, avg(case when `tfi`.`completed` = 1 then `tfi`.`points` end) AS `averageScore`, max(case when `tfi`.`completed` = 1 then `tfi`.`points` end) AS `maxScore`, min(case when `tfi`.`completed` = 1 then `tfi`.`points` end) AS `minScore`, round(count(case when `tfi`.`completed` = 1 then 1 end) / count(`tfi`.`ID`) * 100,1) AS `completionRate` FROM (`FormCollection` `fc` left join `TeamFormInstance` `tfi` on(`fc`.`ID` = `tfi`.`collection_ID`)) GROUP BY `fc`.`ID`, `fc`.`name`, `fc`.`formsCount`, `fc`.`totalQuestions`, `fc`.`timeLimit` ;

-- --------------------------------------------------------

--
-- Structure for view `TeamCollectionProgress`
--
DROP TABLE IF EXISTS `TeamCollectionProgress`;

CREATE ALGORITHM=UNDEFINED DEFINER=`webapp`@`%` SQL SECURITY DEFINER VIEW `TeamCollectionProgress`  AS SELECT `m`.`ID` AS `teamId`, `m`.`Teamname` AS `Teamname`, `m`.`Kreisverband` AS `Kreisverband`, `fc`.`ID` AS `collectionId`, `fc`.`name` AS `collectionName`, count(`tfi`.`ID`) AS `totalForms`, count(case when `tfi`.`completed` = 1 then `tfi`.`ID` end) AS `completedForms`, sum(case when `tfi`.`completed` = 1 then `tfi`.`points` else 0 end) AS `totalPoints`, round(count(case when `tfi`.`completed` = 1 then `tfi`.`ID` end) / count(`tfi`.`ID`) * 100,1) AS `completionPercentage` FROM ((`Mannschaft` `m` join `FormCollection` `fc`) left join `TeamFormInstance` `tfi` on(`m`.`ID` = `tfi`.`team_ID` and `fc`.`ID` = `tfi`.`collection_ID`)) GROUP BY `m`.`ID`, `m`.`Teamname`, `m`.`Kreisverband`, `fc`.`ID`, `fc`.`name` ;

-- --------------------------------------------------------

--
-- Structure for view `TeamFormStatistics`
--
DROP TABLE IF EXISTS `TeamFormStatistics`;

CREATE ALGORITHM=UNDEFINED DEFINER=`webapp`@`%` SQL SECURITY DEFINER VIEW `TeamFormStatistics`  AS SELECT `tfi`.`ID` AS `instanceId`, `m`.`Teamname` AS `Teamname`, `m`.`Kreisverband` AS `Kreisverband`, `fc`.`name` AS `collectionName`, `tfi`.`formNumber` AS `formNumber`, `tfi`.`completed` AS `completed`, `tfi`.`points` AS `points`, json_length(`tfi`.`assignedQuestions`) AS `assignedQuestionsCount`, coalesce((select count(0) from `TeamFormAnswer` where `TeamFormAnswer`.`teamFormInstance_ID` = `tfi`.`ID`),0) AS `answeredQuestions`, CASE WHEN `tfi`.`completed` = 1 THEN 'Abgeschlossen' WHEN `tfi`.`startTime` is not null THEN 'In Bearbeitung' ELSE 'Nicht gestartet' END AS `status`, `tfi`.`startTime` AS `startTime`, `tfi`.`completionDate` AS `completionDate`, `tfi`.`token` AS `token` FROM ((`TeamFormInstance` `tfi` join `Mannschaft` `m` on(`tfi`.`team_ID` = `m`.`ID`)) join `FormCollection` `fc` on(`tfi`.`collection_ID` = `fc`.`ID`)) ORDER BY `m`.`Teamname` ASC, `fc`.`name` ASC, `tfi`.`formNumber` ASC ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Answer`
--
ALTER TABLE `Answer`
  ADD CONSTRAINT `fk_answer_question` FOREIGN KEY (`Question_ID`) REFERENCES `Question` (`ID`);

--
-- Constraints for table `CollectionFormToken`
--
ALTER TABLE `CollectionFormToken`
  ADD CONSTRAINT `fk_collectionformtoken_collection` FOREIGN KEY (`collection_ID`) REFERENCES `FormCollection` (`ID`) ON DELETE CASCADE;

--
-- Constraints for table `CollectionQuestion`
--
ALTER TABLE `CollectionQuestion`
  ADD CONSTRAINT `fk_collectionquestion_collection` FOREIGN KEY (`collection_ID`) REFERENCES `FormCollection` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_collectionquestion_question` FOREIGN KEY (`question_ID`) REFERENCES `Question` (`ID`) ON DELETE CASCADE;

--
-- Constraints for table `FormCollection`
--
ALTER TABLE `FormCollection`
  ADD CONSTRAINT `fk_formcollection_station` FOREIGN KEY (`station_ID`) REFERENCES `Station` (`ID`) ON DELETE SET NULL;

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
-- Constraints for table `StationWeight`
--
ALTER TABLE `StationWeight`
  ADD CONSTRAINT `fk_stationweight_station` FOREIGN KEY (`station_ID`) REFERENCES `Station` (`ID`);

--
-- Constraints for table `TeamFormAnswer`
--
ALTER TABLE `TeamFormAnswer`
  ADD CONSTRAINT `fk_teamformanswer_answer` FOREIGN KEY (`answer_ID`) REFERENCES `Answer` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_teamformanswer_instance` FOREIGN KEY (`teamFormInstance_ID`) REFERENCES `TeamFormInstance` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_teamformanswer_question` FOREIGN KEY (`question_ID`) REFERENCES `Question` (`ID`) ON DELETE CASCADE;

--
-- Constraints for table `TeamFormInstance`
--
ALTER TABLE `TeamFormInstance`
  ADD CONSTRAINT `fk_teamforminstance_collection` FOREIGN KEY (`collection_ID`) REFERENCES `FormCollection` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_teamforminstance_team` FOREIGN KEY (`team_ID`) REFERENCES `Mannschaft` (`ID`) ON DELETE CASCADE;

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
