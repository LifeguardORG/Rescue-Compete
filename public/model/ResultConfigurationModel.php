<?php

namespace Model;

use PDO;
use PDOException;

/**
 * Diese Klasse verwaltet die Ergebnis-Konfiguration.
 * Sie liest und schreibt die Konfiguration in die Datenbank
 * und stellt über die Einbindung von DbConnection.php die Datenbankverbindung bereit.
 */
class ResultConfigurationModel {
    private ?PDO $db;

    /**
     * Konstruktor
     *
     * Baut die Datenbankverbindung auf.
     */
    public function __construct() {
        // Einbinden der DbConnection.php, um die Datenbankverbindung herzustellen.
        require_once __DIR__ . '/../db/DbConnection.php';
        global $conn; // Globale Variable aus DbConnection.php
        if (isset($conn) && $conn instanceof PDO) {
            $this->db = $conn;
        } else {
            $this->db = null;
        }
    }

    /**
     * Liest die Konfiguration aus der Datenbank.
     *
     * @return array Die Konfiguration als assoziatives Array.
     */
    public function getConfig(): array {
        try {
            $query = "SELECT `Key`, `Value` FROM ResultConfiguration";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $config = [];
            foreach ($results as $row) {
                // Wenn der Wert numerisch ist, konvertiere ihn zu einem Integer
                if (is_numeric($row['Value'])) {
                    $config[$row['Key']] = (int)$row['Value'];
                } else {
                    // Prüfe, ob es sich um ein JSON-Array handelt
                    $jsonDecoded = json_decode($row['Value'], true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $config[$row['Key']] = $jsonDecoded;
                    } else {
                        $config[$row['Key']] = $row['Value'];
                    }
                }
            }

            // Lade die Station-Gewichte aus der Datenbank
            $weights = $this->getWeightsFromDatabase();
            if (!empty($weights)) {
                $config['WEIGHTS'] = $weights;
            }

            return $config;
        } catch (PDOException $e) {
            error_log("Error in ResultConfigurationModel::getConfig: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Schreibt die neue Konfiguration in die Datenbank.
     *
     * @param array $config Die neuen Konfigurationseinstellungen.
     * @return bool True, wenn das Schreiben erfolgreich war, andernfalls false.
     */
    public function updateConfig(array $config): bool {
        try {
            // Starte eine Transaktion
            $this->db->beginTransaction();

            // Extrahiere die WEIGHTS für die Datenbank-Aktualisierung
            $weights = $config['WEIGHTS'] ?? [];

            // Aktualisiere die Gewichtungen in der Datenbank
            if (!empty($weights)) {
                $this->updateWeightsInDatabase($weights);
            }

            // Speichere alle anderen Konfigurationsparameter in der ResultConfiguration-Tabelle
            foreach ($config as $key => $value) {
                // Ignoriere WEIGHTS, da diese separat behandelt werden
                if ($key === 'WEIGHTS') continue;

                // Prüfe, ob der Konfigurationsschlüssel bereits existiert
                $checkStmt = $this->db->prepare("SELECT COUNT(*) FROM ResultConfiguration WHERE `Key` = :key");
                $checkStmt->bindParam(':key', $key, PDO::PARAM_STR);
                $checkStmt->execute();

                // Wenn der Wert ein Array ist, konvertiere es zu JSON
                if (is_array($value)) {
                    $value = json_encode($value);
                }

                if ($checkStmt->fetchColumn() > 0) {
                    // Update
                    $updateStmt = $this->db->prepare("UPDATE ResultConfiguration SET `Value` = :value WHERE `Key` = :key");
                    $updateStmt->bindParam(':key', $key, PDO::PARAM_STR);
                    $updateStmt->bindParam(':value', $value, PDO::PARAM_STR);
                    $updateStmt->execute();
                } else {
                    // Insert
                    $insertStmt = $this->db->prepare("INSERT INTO ResultConfiguration (`Key`, `Value`) VALUES (:key, :value)");
                    $insertStmt->bindParam(':key', $key, PDO::PARAM_STR);
                    $insertStmt->bindParam(':value', $value, PDO::PARAM_STR);
                    $insertStmt->execute();
                }
            }

            // Commit der Transaktion
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            // Rollback bei einem Fehler
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Error in ResultConfigurationModel::updateConfig: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Liest die Namen aller Stationen aus der Datenbank aus.
     *
     * @return array Liste der Stationnamen (z. B. ["MANV", "Fahrradunfall", ...]).
     */
    public function getDbStationNames(): array {
        if ($this->db) {
            try {
                $stmt = $this->db->query("SELECT name FROM Station ORDER BY Nr, name");
                return $stmt->fetchAll(PDO::FETCH_COLUMN);
            } catch (PDOException $e) {
                error_log("Error in ResultConfigurationModel::getDbStationNames: " . $e->getMessage());
                return [];
            }
        } else {
            return [];
        }
    }

    /**
     * Liest die Stationsgewichtungen aus der Datenbank.
     *
     * @return array Assoziatives Array mit Stationsnamen als Schlüssel und Gewichtungen als Werte.
     */
    public function getWeightsFromDatabase(): array {
        if (!$this->db) {
            return [];
        }

        try {
            // Abfrage, die Stationsnamen und Gewichtungen verknüpft
            $query = "SELECT s.name, COALESCE(sw.weight, 100) AS weight 
                     FROM Station s
                     LEFT JOIN StationWeight sw ON s.ID = sw.station_ID
                     ORDER BY s.Nr, s.name";
            $stmt = $this->db->query($query);
            $weights = [];

            // Ergebnisse in assoziatives Array umwandeln
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $weights[$row['name']] = (int)$row['weight'];
            }

            return $weights;
        } catch (PDOException $e) {
            error_log("Error in ResultConfigurationModel::getWeightsFromDatabase: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Aktualisiert die Gewichtungen in der Datenbank.
     *
     * @param array $weights Assoziatives Array mit Stationsnamen als Schlüssel und Gewichtungen als Werte.
     * @return bool True bei Erfolg, false bei Fehler.
     */
    public function updateWeightsInDatabase(array $weights): bool {
        if (!$this->db) {
            return false;
        }

        try {
            // Beginne Transaktion
            $this->db->beginTransaction();

            // Für jede Station die Gewichtung aktualisieren
            foreach ($weights as $stationName => $weight) {
                // Hole die Stations-ID anhand des Namens
                $stmtStationId = $this->db->prepare("SELECT ID FROM Station WHERE name = :name");
                $stmtStationId->bindParam(':name', $stationName, PDO::PARAM_STR);
                $stmtStationId->execute();
                $stationId = $stmtStationId->fetchColumn();

                if ($stationId) {
                    // Prüfe, ob bereits eine Gewichtung existiert
                    $stmtCheckWeight = $this->db->prepare("SELECT COUNT(*) FROM StationWeight WHERE station_ID = :id");
                    $stmtCheckWeight->bindParam(':id', $stationId, PDO::PARAM_INT);
                    $stmtCheckWeight->execute();

                    if ($stmtCheckWeight->fetchColumn() > 0) {
                        // Update der Gewichtung
                        $stmtUpdateWeight = $this->db->prepare("UPDATE StationWeight SET weight = :weight WHERE station_ID = :id");
                        $stmtUpdateWeight->bindParam(':weight', $weight, PDO::PARAM_INT);
                        $stmtUpdateWeight->bindParam(':id', $stationId, PDO::PARAM_INT);
                        $stmtUpdateWeight->execute();
                    } else {
                        // Insert der Gewichtung
                        $stmtInsertWeight = $this->db->prepare("INSERT INTO StationWeight (station_ID, weight) VALUES (:id, :weight)");
                        $stmtInsertWeight->bindParam(':id', $stationId, PDO::PARAM_INT);
                        $stmtInsertWeight->bindParam(':weight', $weight, PDO::PARAM_INT);
                        $stmtInsertWeight->execute();
                    }
                }
            }

            // Commit der Transaktion
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            // Rollback bei Fehler
            $this->db->rollBack();
            error_log("Error in ResultConfigurationModel::updateWeightsInDatabase: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Aktualisiert die WEIGHTS in der Konfiguration.
     *
     * Es wird überprüft, ob die in der Konfiguration gespeicherten WEIGHTS (Schlüssel der Stationen)
     * mit den aktuellen Station-Namen aus der Datenbank übereinstimmen. Falls nicht, wird die gesamte
     * WEIGHTS-Liste auf alle Stationen mit dem Standardwert 100 gesetzt. Falls ein Input-Array übergeben
     * wurde und die Stationen übereinstimmen, werden die neuen Werte übernommen.
     *
     * @param array|null $inputWeights Optional: Neue Gewichtungswerte aus einem Formular.
     * @return array Die aktualisierten WEIGHTS.
     */
    public function updateWeights(?array $inputWeights = null): array {
        // Ermitteln der aktuellen Station-Namen aus der DB
        $dbStations = $this->getDbStationNames();

        // Aktuelle Gewichtungen aus der Datenbank laden
        $currentWeights = $this->getWeightsFromDatabase();

        // Falls keine Gewichtungen in der Datenbank vorhanden sind,
        // Standard-Gewichtungen (100) für alle Stationen setzen
        if (empty($currentWeights) && !empty($dbStations)) {
            $currentWeights = array_fill_keys($dbStations, 100);
        }

        // Vergleiche der Station-Namen
        $configStations = array_keys($currentWeights);
        sort($configStations);
        $dbStationsSorted = $dbStations;
        sort($dbStationsSorted);

        if ($dbStationsSorted !== $configStations) {
            // Falls die Station-Namen abweichen, die WEIGHTS-Liste neu setzen
            $newWeights = array_fill_keys($dbStations, 100);

            // Übernehme existierende Gewichtungen, falls vorhanden
            foreach ($dbStations as $station) {
                if (isset($currentWeights[$station])) {
                    $newWeights[$station] = $currentWeights[$station];
                }
            }

            // In der Datenbank aktualisieren
            $this->updateWeightsInDatabase($newWeights);

            return $newWeights;
        } else if (is_array($inputWeights)) {
            // Falls ein Input-Array übergeben wurde, übernehme die neuen Werte
            $updatedWeights = [];
            foreach ($dbStations as $station) {
                $updatedWeights[$station] = isset($inputWeights[$station])
                    ? (int)$inputWeights[$station]
                    : ($currentWeights[$station] ?? 100);
            }

            // In der Datenbank aktualisieren
            $this->updateWeightsInDatabase($updatedWeights);

            return $updatedWeights;
        }

        // Ansonsten die aktuellen Gewichtungen zurückgeben
        return $currentWeights;
    }
}