<?php

namespace Model;

use PDO;
use PDOException;

// Neue Calculator-Klassen einbinden
require_once __DIR__ . '/SwimmingCalculator.php';
require_once __DIR__ . '/ParcoursCalculator.php';

/**
 * Model für Ergebnisdaten.
 * Delegiert komplexe Berechnungen an spezialisierte Calculator-Klassen.
 */
class ResultModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Liefert Schwimm-Ergebnisse inklusive Details und Berechnungen.
     */
    public function getSwimmingWertungenWithDetails(): array
    {
        try {
            // 1. Konfigurationswerte laden (inklusive WEIGHTS aus StationWeight-Tabelle)
            $configData = $this->loadCompleteConfiguration();

            // 2. Erwartete Staffeln ermitteln
            $expectedStaffeln = $this->db->query("SELECT name FROM Staffel ORDER BY name")
                ->fetchAll(PDO::FETCH_COLUMN);

            // 3. Schwimm-Rohdaten abrufen
            $results = $this->loadSwimmingRawData();

            // 4. Fehlende Staffeln als Platzhalter ergänzen
            $results = $this->addMissingStaffeln($results, $expectedStaffeln);

            // 5. Punkte berechnen mit SwimmingCalculator
            $results = SwimmingCalculator::calculateSwimmingPoints($results, $configData, $expectedStaffeln);

            return $results;
        } catch (PDOException $e) {
            error_log("Error in ResultModel::getSwimmingWertungenWithDetails: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lädt die komplette Konfiguration inklusive WEIGHTS aus der Datenbank.
     *
     * @return array Vollständige Konfiguration mit allen Parametern
     */
    private function loadCompleteConfiguration(): array
    {
        // Basis-Konfiguration aus ResultConfiguration
        $query = "SELECT `Key`, `Value` FROM ResultConfiguration";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $configData = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // Konvertiere numerische Werte
        foreach ($configData as $key => $value) {
            if (is_numeric($value)) {
                $configData[$key] = (int)$value;
            }
        }

        // WEIGHTS aus StationWeight-Tabelle laden
        $weightsQuery = "SELECT s.name, COALESCE(sw.weight, 100) AS weight 
                        FROM Station s
                        LEFT JOIN StationWeight sw ON s.ID = sw.station_ID
                        ORDER BY s.Nr, s.name";
        $weightsStmt = $this->db->query($weightsQuery);
        $weights = [];

        while ($row = $weightsStmt->fetch(PDO::FETCH_ASSOC)) {
            $weights[$row['name']] = (int)$row['weight'];
        }

        $configData['WEIGHTS'] = $weights;

        // DEBUG: Ausgabe der geladenen Konfiguration
        error_log("DEBUG ResultModel - Loaded configuration:");
        foreach ($configData as $key => $value) {
            if ($key === 'WEIGHTS') {
                error_log("  $key: " . json_encode($value));
            } else {
                error_log("  $key: $value");
            }
        }

        return $configData;
    }

    /**
     * Lädt die Schwimm-Rohdaten aus der Datenbank.
     */
    private function loadSwimmingRawData(): array
    {
        $query = "
            SELECT 
                m.Teamname,
                s.name AS staffelName,
                ms.schwimmzeit,
                ms.strafzeit,
                (TIME_TO_SEC(ms.schwimmzeit) + TIME_TO_SEC(IFNULL(ms.strafzeit, '00:00:00'))) * 1000 AS total_ms,
                wk.name AS Wertungsklasse
            FROM Mannschaft m
            LEFT JOIN MannschaftStaffel ms ON m.ID = ms.mannschaft_ID
            LEFT JOIN Staffel s ON ms.staffel_ID = s.ID
            JOIN MannschaftWertung mw ON m.ID = mw.mannschaft_ID
            JOIN Wertungsklasse wk ON mw.wertung_ID = wk.ID
            ORDER BY wk.name, m.Teamname, s.name
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute();

        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $wertung = $row['Wertungsklasse'];
            $team = $row['Teamname'];
            $staffelName = $row['staffelName'];

            if (!isset($results[$wertung])) {
                $results[$wertung] = ['Teams' => []];
            }
            if (!isset($results[$wertung]['Teams'][$team])) {
                $results[$wertung]['Teams'][$team] = [];
            }

            if (!is_null($staffelName)) {
                $swimTime = $row['schwimmzeit'];
                $penaltyTime = $row['strafzeit'] ?? '00:00:00.0000';
                $totalSeconds = SwimmingCalculator::timeStringToSeconds($swimTime) +
                    SwimmingCalculator::timeStringToSeconds($penaltyTime);
                $gesamtzeitFormatted = SwimmingCalculator::formatTimeWithMilliseconds($totalSeconds);
                $totalMs = (int)$row['total_ms'];

                $results[$wertung]['Teams'][$team][$staffelName] = [
                    $swimTime,
                    $penaltyTime,
                    $gesamtzeitFormatted,
                    null, // Punkte werden später berechnet
                    $totalMs
                ];
            }
        }

        return $results;
    }

    /**
     * Ergänzt fehlende Staffeln als Platzhalter.
     */
    private function addMissingStaffeln(array $results, array $expectedStaffeln): array
    {
        foreach ($results as $wertung => &$wertungData) {
            foreach ($wertungData['Teams'] as &$teamData) {
                foreach ($expectedStaffeln as $staffel) {
                    if (!isset($teamData[$staffel])) {
                        $teamData[$staffel] = ['', '00:00:00.0000', '00:00:00.0000', null, 0];
                    }
                }
                ksort($teamData);
            }
        }
        return $results;
    }

    /**
     * Liefert Parcours-Ergebnisse inklusive Details (Rohdaten).
     */
    public function getParcoursWertungenWithDetails(): array
    {
        try {
            $allStations = $this->getExpectedStations();
            $data = $this->initializeParcoursData();
            $mannschaften = $this->loadTeamData($data, $allStations);

            // Standarddaten aus MannschaftProtokoll
            $this->loadProtocolData($data, $mannschaften);

            // Formularpunkte hinzufügen
            $this->addFormPointsToParcoursData($data, $mannschaften);

            // Gesamtpunkte berechnen und sortieren
            $this->calculateTotalPointsAndSort($data);

            return $data;
        } catch (PDOException $e) {
            error_log("Error in ResultModel::getParcoursWertungenWithDetails: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Initialisiert die Parcours-Datenstruktur mit allen Wertungsklassen.
     */
    private function initializeParcoursData(): array
    {
        $data = [];
        $wertungsklassen = $this->db->query("SELECT name FROM Wertungsklasse ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($wertungsklassen as $wertung) {
            $data[$wertung] = ['Teams' => []];
        }
        return $data;
    }

    /**
     * Lädt Team-Daten und initialisiert die Stationen.
     */
    private function loadTeamData(array &$data, array $allStations): array
    {
        $query = "SELECT 
            m.ID as mannschaft_ID,
            m.Teamname,
            wk.name as wertung_name
        FROM 
            Mannschaft m
        JOIN 
            MannschaftWertung mw ON m.ID = mw.mannschaft_ID
        JOIN 
            Wertungsklasse wk ON mw.wertung_ID = wk.ID
        ORDER BY 
            wk.name, m.Teamname";

        $stmt = $this->db->prepare($query);
        $stmt->execute();

        $mannschaften = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $mannschaftId = $row['mannschaft_ID'];
            $teamname = $row['Teamname'];
            $wertung = $row['wertung_name'];

            if (!isset($mannschaften[$mannschaftId])) {
                $mannschaften[$mannschaftId] = [
                    'teamname' => $teamname,
                    'wertungen' => []
                ];
            }

            $mannschaften[$mannschaftId]['wertungen'][] = $wertung;

            if (!isset($data[$wertung]['Teams'][$teamname])) {
                $data[$wertung]['Teams'][$teamname] = [];
                foreach ($allStations as $stationName) {
                    $data[$wertung]['Teams'][$teamname][$stationName] = [];
                }
            }
        }

        return $mannschaften;
    }

    /**
     * Lädt Protokoll-Daten aus MannschaftProtokoll.
     */
    private function loadProtocolData(array &$data, array $mannschaften): void
    {
        $query = "SELECT 
            mp.mannschaft_ID, 
            m.Teamname,
            p.station_ID,
            s.name as station_name,
            p.Name as protokoll_name,
            p.Nr as protokoll_nr,
            mp.erreichte_Punkte,
            p.max_Punkte
        FROM 
            MannschaftProtokoll mp
        JOIN 
            Mannschaft m ON mp.mannschaft_ID = m.ID
        JOIN 
            Protokoll p ON mp.protokoll_Nr = p.Nr
        JOIN 
            Station s ON p.station_ID = s.ID
        ORDER BY 
            mp.mannschaft_ID, p.station_ID, p.Nr";

        $stmt = $this->db->prepare($query);
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $mannschaftId = $row['mannschaft_ID'];
            $teamname = $row['Teamname'];
            $stationName = $row['station_name'];

            if (!isset($mannschaften[$mannschaftId])) continue;

            $wertungen = $mannschaften[$mannschaftId]['wertungen'];

            foreach ($wertungen as $wertung) {
                if (!isset($data[$wertung]['Teams'][$teamname][$stationName])) {
                    $data[$wertung]['Teams'][$teamname][$stationName] = [];
                }

                $protocolEntry = [
                    'protokollNr' => $row['protokoll_nr'],
                    'protokollName' => $row['protokoll_name'],
                    'points' => (int)$row['erreichte_Punkte'],
                    'maxPoints' => (int)$row['max_Punkte']
                ];

                $data[$wertung]['Teams'][$teamname][$stationName][] = $protocolEntry;
            }
        }
    }

    /**
     * Fügt Formular-Punkte zu den Parcours-Daten hinzu.
     */
    private function addFormPointsToParcoursData(array &$data, array $mannschaften): void
    {
        $formPoints = $this->getFormPoints();

        foreach ($formPoints as $mannschaftId => $stations) {
            if (!isset($mannschaften[$mannschaftId])) continue;

            $teamname = $mannschaften[$mannschaftId]['teamname'];
            $wertungen = $mannschaften[$mannschaftId]['wertungen'];

            foreach ($wertungen as $wertung) {
                foreach ($stations as $stationName => $stationData) {
                    if (isset($data[$wertung]['Teams'][$teamname][$stationName])) {
                        $data[$wertung]['Teams'][$teamname][$stationName][] = [
                            'protokollNr' => 'form_' . $stationData['stationId'],
                            'protokollName' => 'Formular',
                            'points' => $stationData['points'],
                            'maxPoints' => $stationData['maxPoints']
                        ];
                    } else {
                        $data[$wertung]['Teams'][$teamname][$stationName] = [[
                            'protokollNr' => 'form_' . $stationData['stationId'],
                            'protokollName' => 'Formular',
                            'points' => $stationData['points'],
                            'maxPoints' => $stationData['maxPoints']
                        ]];
                    }
                }
            }
        }
    }

    /**
     * Berechnet Gesamtpunkte und sortiert Teams.
     */
    private function calculateTotalPointsAndSort(array &$data): void
    {
        foreach ($data as $wertung => &$wertungData) {
            foreach ($wertungData['Teams'] as $teamname => &$teamStations) {
                $totalPoints = 0;

                foreach ($teamStations as $stationName => $protokolle) {
                    if (is_array($protokolle)) {
                        foreach ($protokolle as $protokoll) {
                            $totalPoints += (int)($protokoll['points'] ?? 0);
                        }
                    }
                }

                $teamStations['gesamtpunkte'] = $totalPoints;
            }

            uasort($wertungData['Teams'], function($a, $b) {
                return ($b['gesamtpunkte'] ?? 0) <=> ($a['gesamtpunkte'] ?? 0);
            });
        }
    }

    /**
     * Berechnet adjustierte Parcours-Punkte mit ParcoursCalculator.
     */
    public function getAdjustedParcoursResults(): array
    {
        // Komplette Konfiguration laden (inklusive aktueller WEIGHTS)
        $config = $this->loadCompleteConfiguration();

        $parcoursData = $this->getParcoursWertungenWithDetails();
        $adjustedData = ParcoursCalculator::calculateAdjustedPoints($parcoursData, $config);

        return $adjustedData;
    }

    /**
     * Holt alle abgeschlossenen Formulare mit ihren Punkten.
     */
    public function getFormPoints(): array
    {
        try {
            $query = "SELECT 
                tfi.team_ID as mannschaft_ID,
                m.Teamname,
                fc.station_ID,
                s.name as station_name,
                SUM(tfi.points) as points,
                COUNT(*) as formCount,
                fc.totalQuestions as maxPoints
            FROM 
                TeamFormInstance tfi
            JOIN 
                Mannschaft m ON tfi.team_ID = m.ID
            JOIN 
                FormCollection fc ON tfi.collection_ID = fc.ID
            LEFT JOIN 
                Station s ON fc.station_ID = s.ID
            WHERE 
                tfi.completed = 1
            GROUP BY 
                tfi.team_ID, fc.ID";

            $stmt = $this->db->prepare($query);
            $stmt->execute();

            $results = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $mannschaftId = $row['mannschaft_ID'];
                $stationName = $row['station_name'] ?? 'Unbekannte Station';
                $points = (int)$row['points'];
                $maxPoints = (int)$row['maxPoints'];

                if (!isset($results[$mannschaftId])) {
                    $results[$mannschaftId] = [];
                }

                $results[$mannschaftId][$stationName] = [
                    'points' => $points,
                    'maxPoints' => $maxPoints,
                    'stationId' => $row['station_ID'] ?? 0,
                    'formCount' => (int)$row['formCount']
                ];
            }

            return $results;
        } catch (PDOException $e) {
            error_log("Error in ResultModel::getFormPoints: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Holt alle Wertungsklassen für eine Mannschaft.
     */
    public function getWertungenByMannschaft(int $mannschaftId): array {
        try {
            $stmt = $this->db->prepare(
                "SELECT wk.name 
                 FROM MannschaftWertung mw 
                 JOIN Wertungsklasse wk ON mw.wertung_ID = wk.ID 
                 WHERE mw.mannschaft_ID = ?"
            );
            $stmt->execute([$mannschaftId]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log("Error in ResultModel::getWertungenByMannschaft: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Liefert erwartete Staffel-Namen.
     */
    public function getExpectedStaffeln(): array
    {
        try {
            $stmt = $this->db->query("SELECT name FROM Staffel ORDER BY name");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            if ($e->getCode() === '42S02') {
                return [];
            }
            throw $e;
        }
    }

    /**
     * Liefert erwartete Station-Namen.
     */
    public function getExpectedStations(): array
    {
        try {
            $stmt = $this->db->query("SELECT name FROM Station ORDER BY name");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            if ($e->getCode() === '42S02') {
                return [];
            }
            throw $e;
        }
    }
}