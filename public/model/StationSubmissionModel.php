<?php

namespace Model;

use PDO;
use PDOException;

/**
 * Class StationSubmissionModel
 *
 * Dieses Modell verwaltet Daten im Zusammenhang mit Stationen,
 * wie das Abrufen von Stationen, Protokollen, Mannschaften und das Speichern von Ergebnissen.
 */
class StationSubmissionModel {
    /**
     * @var PDO Datenbankverbindung
     */
    private PDO $db;

    /**
     * Konstruktor: Initialisiert die Datenbankverbindung.
     *
     * @param PDO $db Eine gültige PDO-Datenbankverbindung
     */
    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Liefert alle Stationen aus der Tabelle "Station", sortiert nach der Stationsnummer.
     *
     * @return array Eine Liste der Stationen als assoziatives Array.
     */
    public function getAllStations(): array {
        try {
            $stmt = $this->db->query("SELECT * FROM Station ORDER BY ID");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Bei einem Fehler wird ein leeres Array zurückgegeben.
            return [];
        }
    }

    /**
     * Liefert alle Protokolle (Ergebnisvorlagen) für eine bestimmte Station.
     *
     * @param int $stationID Die Nummer der Station.
     * @return array Eine Liste der Protokolle als assoziatives Array.
     */
    public function getProtocolsByStation(int $stationID): array {
        $stmt = $this->db->prepare("SELECT * FROM Protokoll WHERE station_ID = :stationID ORDER BY Nr");
        $stmt->execute([':stationID' => $stationID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Liefert alle Mannschaften (Teams), die am Wettkampf teilnehmen, sortiert nach Teamname.
     *
     * @return array Eine Liste der Mannschaften als assoziatives Array.
     */
    public function getTeams(): array {
        $stmt = $this->db->query("SELECT * FROM Mannschaft ORDER BY Teamname");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Ermittelt, welche Mannschaften bereits Ergebnisse für eine bestimmte Station eingetragen haben.
     *
     * @param int $stationID Die Nummer der Station.
     * @return array Eine Liste der Mannschafts-IDs, für die Ergebnisse eingetragen wurden.
     */
    public function getSubmittedTeams(int $stationID): array {
        $stmt = $this->db->prepare(
            "SELECT DISTINCT mannschaft_ID FROM MannschaftProtokoll 
             WHERE protokoll_Nr IN (SELECT Nr FROM Protokoll WHERE station_ID = :stationID)"
        );
        $stmt->execute([':stationID' => $stationID]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getStationByID(int $id): array {
        $stmt = $this->db->prepare("SELECT * FROM Station WHERE ID = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Speichert die eingegebenen Ergebnisse einer Station in der Tabelle "MannschaftProtokoll".
     *
     * Für jedes Team und jedes ProtocolModel wird geprüft, ob bereits ein Eintrag existiert:
     * - Falls ja, wird der Eintrag aktualisiert.
     * - Falls nein, wird ein neuer Eintrag eingefügt.
     *
     * @param array $results Ein assoziatives Array, in dem der Schlüssel die Team-ID ist und
     *                       die Werte die erreichten Punkte für die jeweiligen Protokolle enthalten.
     *                       Struktur: [teamID => [protocolNr => erreichte_Punkte, ...], ...]
     * @return bool True bei Erfolg, false bei einem Fehler.
     */
    public function saveResults(array $results): bool {
        try {
            // Wenn keine Ergebnisse übergeben wurden, als Erfolg werten
            if (empty($results)) {
                return true;
            }

            // Vorbereitung der SQL-Statements für Select, Insert und Update
            $stmtSelect = $this->db->prepare("SELECT COUNT(*) as count FROM MannschaftProtokoll WHERE mannschaft_ID = :teamID AND protokoll_Nr = :protocolNr");
            $stmtInsert = $this->db->prepare("INSERT INTO MannschaftProtokoll (mannschaft_ID, protokoll_Nr, erreichte_Punkte) VALUES (:teamID, :protocolNr, :points)");
            $stmtUpdate = $this->db->prepare("UPDATE MannschaftProtokoll SET erreichte_Punkte = :points WHERE mannschaft_ID = :teamID AND protokoll_Nr = :protocolNr");

            // Verarbeitung der Ergebnisse für jedes Team und jedes Protokoll
            foreach ($results as $teamID => $teamResults) {
                foreach ($teamResults as $protocolNr => $points) {
                    // Leere Werte überspringen
                    if ($points === "" || $points === null) {
                        continue;
                    }

                    // Prüfen, ob bereits ein Eintrag für das Team und das Protokoll vorhanden ist
                    $stmtSelect->execute([
                        ':teamID' => $teamID,
                        ':protocolNr' => $protocolNr
                    ]);
                    $count = $stmtSelect->fetch(PDO::FETCH_ASSOC)['count'];
                    if ($count > 0) {
                        // Aktualisiere den bestehenden Eintrag
                        $stmtUpdate->execute([
                            ':points' => $points,
                            ':teamID' => $teamID,
                            ':protocolNr' => $protocolNr
                        ]);
                    } else {
                        // Füge einen neuen Eintrag ein
                        $stmtInsert->execute([
                            ':teamID' => $teamID,
                            ':protocolNr' => $protocolNr,
                            ':points' => $points
                        ]);
                    }
                }
            }
            return true;
        } catch (PDOException $e) {
            // Bei einem Fehler wird false zurückgegeben.
            return false;
        }
    }
}
