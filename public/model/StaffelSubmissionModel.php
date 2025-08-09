<?php

namespace Model;

use PDO;
use PDOException;

/**
 * Class StaffelSubmissionModel
 * Verwaltet Staffel-Daten und Schwimm-Ergebnisse
 */
class StaffelSubmissionModel {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Liefert alle Staffeln aus der Tabelle "Staffel", sortiert nach Name
     */
    public function getAllStaffeln(): array {
        try {
            $stmt = $this->db->query("SELECT * FROM Staffel ORDER BY name");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Liefert eine spezifische Staffel anhand der ID
     */
    public function getStaffelById(int $staffelId){
        try {
            $stmt = $this->db->prepare("SELECT * FROM Staffel WHERE ID = :id");
            $stmt->execute([':id' => $staffelId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Liefert alle Mannschaften (Teams), sortiert nach Teamname
     */
    public function getTeams(): array {
        try {
            $stmt = $this->db->query("SELECT * FROM Mannschaft ORDER BY Teamname");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Ermittelt, welche Mannschaften bereits Ergebnisse für eine bestimmte Staffel haben
     */
    public function getSubmittedTeams(int $staffelId): array {
        try {
            $stmt = $this->db->prepare(
                "SELECT DISTINCT mannschaft_ID FROM MannschaftStaffel WHERE staffel_ID = :staffelId"
            );
            $stmt->execute([':staffelId' => $staffelId]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Lädt die bereits gespeicherten Ergebnisse für eine Staffel
     *
     * @param int $staffelId Die ID der Staffel
     * @return array Assoziatives Array mit den Ergebnissen [teamId => ['schwimmzeit' => ..., 'strafzeit' => ...]]
     */
    public function getStaffelResults(int $staffelId): array {
        try {
            $stmt = $this->db->prepare(
                "SELECT mannschaft_ID, schwimmzeit, strafzeit 
                 FROM MannschaftStaffel 
                 WHERE staffel_ID = :staffelId"
            );
            $stmt->execute([':staffelId' => $staffelId]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Umformatierung für einfachere Verwendung in der View
            $formattedResults = [];
            foreach ($results as $result) {
                $formattedResults[$result['mannschaft_ID']] = [
                    'schwimmzeit' => $this->formatTimeForDisplay($result['schwimmzeit']),
                    'strafzeit' => $this->formatTimeForDisplay($result['strafzeit'])
                ];
            }

            return $formattedResults;
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Formatiert eine Zeit aus der Datenbank für die Anzeige im Eingabefeld
     *
     * @param string|null $timeValue Zeit im Format HH:MM:SS.ssss oder null
     * @return string Formatierte Zeit für Eingabefeld oder leerer String
     */
    private function formatTimeForDisplay(?string $timeValue): string {
        if (empty($timeValue) || $timeValue === '00:00:00.0000') {
            return '';
        }

        // Zeit parsen: HH:MM:SS.ssss
        $timeParts = explode(':', $timeValue);
        if (count($timeParts) !== 3) {
            return '';
        }

        $hours = (int)$timeParts[0];
        $minutes = (int)$timeParts[1];
        $secondsParts = explode('.', $timeParts[2]);
        $seconds = (int)$secondsParts[0];
        $milliseconds = isset($secondsParts[1]) ? $secondsParts[1] : '0000';

        // Nur relevante Teile anzeigen
        if ($hours > 0) {
            // Format: H:MM:SS.ss
            return sprintf("%d:%02d:%02d.%s", $hours, $minutes, $seconds, substr($milliseconds, 0, 2));
        } else {
            // Format: MM:SS.ss
            return sprintf("%d:%02d.%s", $minutes, $seconds, substr($milliseconds, 0, 2));
        }
    }

    /**
     * Speichert die Schwimm-Ergebnisse für eine Staffel
     */
    public function saveResults(int $staffelId, array $results): bool {
        try {
            if (empty($results)) {
                return true;
            }

            $stmtSelect = $this->db->prepare(
                "SELECT COUNT(*) as count FROM MannschaftStaffel WHERE mannschaft_ID = :teamId AND staffel_ID = :staffelId"
            );
            $stmtInsert = $this->db->prepare(
                "INSERT INTO MannschaftStaffel (mannschaft_ID, staffel_ID, schwimmzeit, strafzeit) 
                 VALUES (:teamId, :staffelId, :schwimmzeit, :strafzeit)"
            );
            $stmtUpdate = $this->db->prepare(
                "UPDATE MannschaftStaffel 
                 SET schwimmzeit = :schwimmzeit, strafzeit = :strafzeit 
                 WHERE mannschaft_ID = :teamId AND staffel_ID = :staffelId"
            );
            $stmtDelete = $this->db->prepare(
                "DELETE FROM MannschaftStaffel WHERE mannschaft_ID = :teamId AND staffel_ID = :staffelId"
            );

            foreach ($results as $teamId => $teamResults) {
                $schwimmzeitInput = trim($teamResults['geschwommene_zeit'] ?? '');
                $strafzeitInput = trim($teamResults['strafzeit'] ?? '');

                // Überspringe Teams ohne geschwommene Zeit - aber lösche eventuell vorhandene Einträge
                if (empty($schwimmzeitInput)) {
                    // Prüfen ob bereits ein Eintrag existiert und diesen löschen
                    $stmtSelect->execute([
                        ':teamId' => $teamId,
                        ':staffelId' => $staffelId
                    ]);
                    $count = $stmtSelect->fetch(PDO::FETCH_ASSOC)['count'];

                    if ($count > 0) {
                        $stmtDelete->execute([
                            ':teamId' => $teamId,
                            ':staffelId' => $staffelId
                        ]);
                    }
                    continue;
                }

                // Konvertiere Zeiten - nur bei vorhandener Schwimmzeit
                $schwimmzeit = $this->convertTimeToDatabase($schwimmzeitInput);

                // Strafzeit: Wenn leer, dann 00:00:00.0000, sonst konvertieren
                $strafzeit = empty($strafzeitInput) ? '00:00:00.0000' : $this->convertTimeToDatabase($strafzeitInput);

                // Prüfen ob bereits ein Eintrag existiert
                $stmtSelect->execute([
                    ':teamId' => $teamId,
                    ':staffelId' => $staffelId
                ]);
                $count = $stmtSelect->fetch(PDO::FETCH_ASSOC)['count'];

                if ($count > 0) {
                    // Update bestehender Eintrag
                    $stmtUpdate->execute([
                        ':schwimmzeit' => $schwimmzeit,
                        ':strafzeit' => $strafzeit,
                        ':teamId' => $teamId,
                        ':staffelId' => $staffelId
                    ]);
                } else {
                    // Neuer Eintrag
                    $stmtInsert->execute([
                        ':teamId' => $teamId,
                        ':staffelId' => $staffelId,
                        ':schwimmzeit' => $schwimmzeit,
                        ':strafzeit' => $strafzeit
                    ]);
                }
            }

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Konvertiert eine Zeiteingabe in das Datenbankformat TIME(4)
     */
    private function convertTimeToDatabase(string $timeInput): string {
        $timeInput = trim($timeInput);
        if (empty($timeInput)) {
            return '00:00:00.0000';
        }

        // Komma durch Punkt ersetzen
        $timeInput = str_replace(',', '.', $timeInput);

        // Parsing der verschiedenen Formate
        if (strpos($timeInput, ':') === false) {
            // Format: "24.65" -> Sekunden.Millisekunden
            $seconds = floatval($timeInput);
            $wholeSeconds = floor($seconds);
            $milliseconds = ($seconds - $wholeSeconds) * 10000;
            return sprintf("00:00:%02d.%04d", $wholeSeconds, $milliseconds);
        } else {
            $parts = explode(':', $timeInput);
            if (count($parts) === 2) {
                // Format: "1:20" oder "12:24.33"
                $minutes = intval($parts[0]);
                $secondsPart = $parts[1];

                if (strpos($secondsPart, '.') !== false) {
                    $secondsAndMs = explode('.', $secondsPart);
                    $seconds = intval($secondsAndMs[0]);
                    $milliseconds = str_pad($secondsAndMs[1], 4, '0', STR_PAD_RIGHT);
                } else {
                    $seconds = intval($secondsPart);
                    $milliseconds = '0000';
                }

                return sprintf("00:%02d:%02d.%s", $minutes, $seconds, $milliseconds);
            } else if (count($parts) === 3) {
                // Format: "1:12:24.33"
                $hours = intval($parts[0]);
                $minutes = intval($parts[1]);
                $secondsPart = $parts[2];

                if (strpos($secondsPart, '.') !== false) {
                    $secondsAndMs = explode('.', $secondsPart);
                    $seconds = intval($secondsAndMs[0]);
                    $milliseconds = str_pad($secondsAndMs[1], 4, '0', STR_PAD_RIGHT);
                } else {
                    $seconds = intval($secondsPart);
                    $milliseconds = '0000';
                }

                return sprintf("%02d:%02d:%02d.%s", $hours, $minutes, $seconds, $milliseconds);
            }
        }

        return '00:00:00.0000';
    }
}