<?php
/**
 * Timer-Updater für TeamFormInstances - FormCollection-System
 *
 * Dieses Skript wird regelmäßig über einen Cron-Job aufgerufen und
 * überprüft alle laufenden TeamFormInstances, ob deren Timer abgelaufen ist.
 * Falls ja, werden die Formulare automatisch als abgeschlossen markiert.
 */

// Fehlerberichterstattung aktivieren
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Debug-Log-Datei
$logFile = '../logs/timer_updater_debug.log';
$debugLog = fopen($logFile, 'a');

function debugLog($message)
{
    global $debugLog;
    fwrite($debugLog, "[" . date('Y-m-d H:i:s') . "] " . $message . "\n");
}

function logMessage($message)
{
    global $debugLog;
    fwrite($debugLog, "[" . date('Y-m-d H:i:s') . "] " . $message . "\n");
    echo "[" . date('Y-m-d H:i:s') . "] " . $message . "\n";
}

debugLog("=== Timer-Updater für FormCollection-System gestartet ===");

require_once '../db/DbConnection.php';
require_once '../model/TeamFormInstanceModel.php';

use FormCollection\TeamFormInstanceModel;

// Datenbankverbindung prüfen
if (!isset($conn) || !($conn instanceof PDO)) {
    debugLog("FEHLER: Datenbankverbindung nicht verfügbar.");
    fclose($debugLog);
    exit(1);
}

debugLog("Datenbankverbindung hergestellt.");

try {
    // TeamFormInstanceModel instanziieren
    $teamFormInstanceModel = new TeamFormInstanceModel($conn);
    debugLog("TeamFormInstanceModel instanziiert.");

    // System-Informationen loggen
    debugLog("PHP-Version: " . phpversion());
    debugLog("Server-Zeitzone: " . date_default_timezone_get());
    debugLog("Aktuelle Server-Zeit: " . date('Y-m-d H:i:s'));

    // Prüfen, ob TeamFormInstance-Tabelle existiert
    try {
        $stmtCheckTable = $conn->prepare("SHOW TABLES LIKE 'TeamFormInstance'");
        $stmtCheckTable->execute();
        $tableExists = $stmtCheckTable->rowCount() > 0;
        debugLog("TeamFormInstance Tabelle existiert: " . ($tableExists ? "Ja" : "Nein"));

        if (!$tableExists) {
            debugLog("FEHLER: TeamFormInstance-Tabelle existiert nicht!");
            throw new Exception("TeamFormInstance table not found");
        }
    } catch (PDOException $e) {
        debugLog("FEHLER beim Prüfen der TeamFormInstance-Tabelle: " . $e->getMessage());
        throw $e;
    }

    logMessage("Timer-Update für FormCollection-System gestartet...");

    // Laufende Timer abrufen und loggen
    try {
        $stmt = $conn->prepare(
            "SELECT tfi.ID, tfi.team_ID, tfi.collection_ID, tfi.formNumber, 
                    tfi.startTime, fc.timeLimit, fc.name as collectionName,
                    m.Teamname, TIMESTAMPDIFF(SECOND, tfi.startTime, NOW()) as elapsedSeconds
             FROM TeamFormInstance tfi
             JOIN FormCollection fc ON tfi.collection_ID = fc.ID
             JOIN Mannschaft m ON tfi.team_ID = m.ID
             WHERE tfi.completed = 0 
             AND tfi.startTime IS NOT NULL"
        );
        $stmt->execute();
        $runningTimers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        debugLog("Laufende Timer gefunden: " . count($runningTimers));
        foreach ($runningTimers as $timer) {
            $remainingTime = $timer['timeLimit'] - $timer['elapsedSeconds'];
            debugLog("Timer: Team {$timer['Teamname']}, Collection {$timer['collectionName']}, " .
                "Form {$timer['formNumber']}, Elapsed: {$timer['elapsedSeconds']}s, " .
                "Remaining: {$remainingTime}s");
        }
    } catch (PDOException $e) {
        debugLog("FEHLER beim Abrufen der laufenden Timer: " . $e->getMessage());
        throw $e;
    }

    // Abgelaufene Instances verarbeiten
    $stats = $teamFormInstanceModel->processExpiredInstances();

    // Statistik ausgeben
    logMessage("Timer-Update abgeschlossen:");
    logMessage("  - Verarbeitete Instances: " . $stats['processed']);
    logMessage("  - Abgelaufene Instances: " . $stats['expired']);
    logMessage("  - Erfolgreich abgeschlossen: " . $stats['expired']);
    logMessage("  - Fehler: " . $stats['errors']);

    if ($stats['errors'] > 0) {
        logMessage("  - WARNUNG: Es gab Fehler beim Verarbeiten einiger Instances!");
    }

    // Zusätzliche Statistiken
    try {
        $stmt = $conn->prepare(
            "SELECT 
                COUNT(*) as totalInstances,
                COUNT(CASE WHEN completed = 1 THEN 1 END) as completedInstances,
                COUNT(CASE WHEN startTime IS NOT NULL AND completed = 0 THEN 1 END) as runningInstances,
                COUNT(CASE WHEN startTime IS NULL AND completed = 0 THEN 1 END) as pendingInstances
             FROM TeamFormInstance"
        );
        $stmt->execute();
        $systemStats = $stmt->fetch(PDO::FETCH_ASSOC);

        logMessage("System-Statistiken:");
        logMessage("  - Gesamt Instances: " . $systemStats['totalInstances']);
        logMessage("  - Abgeschlossen: " . $systemStats['completedInstances']);
        logMessage("  - Laufend: " . $systemStats['runningInstances']);
        logMessage("  - Ausstehend: " . $systemStats['pendingInstances']);

    } catch (PDOException $e) {
        debugLog("FEHLER beim Abrufen der System-Statistiken: " . $e->getMessage());
    }

    // Debug-Ausgabe beenden
    debugLog("=== Timer-Updater für FormCollection-System beendet ===\n");
    fclose($debugLog);

    exit(0);

} catch (Exception $e) {
    debugLog("Schwerwiegender Fehler: " . $e->getMessage());
    debugLog("Stack Trace: " . $e->getTraceAsString());

    // E-Mail-Benachrichtigung bei kritischen Fehlern (optional)
    $errorMessage = "Timer-Updater Fehler: " . $e->getMessage() . "\n\n" .
        "Zeit: " . date('Y-m-d H:i:s') . "\n" .
        "Server: " . ($_SERVER['HTTP_HOST'] ?? 'unknown') . "\n\n" .
        "Stack Trace:\n" . $e->getTraceAsString();

    error_log($errorMessage);

    fclose($debugLog);
    exit(1);
}