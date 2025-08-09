<?php
namespace Station;

use PDO;
use PDOException;

/**
 * Klasse zur Verwaltung der Gewichtungen von Stationen
 */
class StationWeightModel
{
    private PDO $db;

    /**
     * Konstruktor.
     *
     * @param PDO $db Die PDO-Datenbankverbindung.
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Liest alle Stationsgewichtungen oder eine spezifische Gewichtung aus der Datenbank.
     *
     * @param int|null $stationId Optional: Die ID der Station, deren Gewichtung abgerufen werden soll
     * @return array|null Ein assoziatives Array mit den Gewichtungen oder null bei einem Fehler
     */
    public function read(?int $stationId = null): ?array
    {
        try {
            if ($stationId === null) {
                // Alle Gewichtungen abrufen, verbunden mit Stationen, um Stationsnamen zu haben
                $query = "SELECT sw.station_ID, sw.weight, s.name as station_name 
                          FROM StationWeight sw
                          JOIN Station s ON sw.station_ID = s.ID
                          ORDER BY s.name";
                $stmt = $this->db->query($query);
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                // Gewichtung für eine bestimmte Station abrufen
                $query = "SELECT sw.station_ID, sw.weight, s.name as station_name 
                          FROM StationWeight sw
                          JOIN Station s ON sw.station_ID = s.ID
                          WHERE sw.station_ID = :stationId";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':stationId', $stationId, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            error_log("Error in StationWeightModel::read: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Setzt die Gewichtung einer Station. Falls bereits eine Gewichtung existiert, wird diese aktualisiert.
     *
     * @param int $stationId Die ID der Station
     * @param int $weight Die neue Gewichtung
     * @return bool True bei Erfolg, false bei einem Fehler
     */
    public function setWeight(int $stationId, int $weight): bool
    {
        try {
            // Prüfen, ob bereits eine Gewichtung existiert
            $checkQuery = "SELECT COUNT(*) FROM StationWeight WHERE station_ID = :stationId";
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->bindParam(':stationId', $stationId, PDO::PARAM_INT);
            $checkStmt->execute();

            if ($checkStmt->fetchColumn() > 0) {
                // Update der bestehenden Gewichtung
                $query = "UPDATE StationWeight SET weight = :weight WHERE station_ID = :stationId";
            } else {
                // Insert einer neuen Gewichtung
                $query = "INSERT INTO StationWeight (station_ID, weight) VALUES (:stationId, :weight)";
            }

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':stationId', $stationId, PDO::PARAM_INT);
            $stmt->bindParam(':weight', $weight, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in StationWeightModel::setWeight: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Entfernt die Gewichtung einer Station.
     *
     * @param int $stationId Die ID der Station
     * @return bool True bei Erfolg, false bei einem Fehler
     */
    public function delete(int $stationId): bool
    {
        try {
            $query = "DELETE FROM StationWeight WHERE station_ID = :stationId";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':stationId', $stationId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in StationWeightModel::delete: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Setzt für alle Stationen eine Standardgewichtung von 100, falls keine Gewichtung existiert.
     *
     * @return bool True bei Erfolg, false bei einem Fehler
     */
    public function initializeWeights(): bool
    {
        try {
            // Starten einer Transaktion
            $this->db->beginTransaction();

            // Alle Stationen abrufen, die noch keine Gewichtung haben
            $query = "SELECT ID FROM Station WHERE ID NOT IN (SELECT station_ID FROM StationWeight)";
            $stmt = $this->db->query($query);
            $stationsWithoutWeight = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Für jede Station ohne Gewichtung eine Standardgewichtung einfügen
            $insertQuery = "INSERT INTO StationWeight (station_ID, weight) VALUES (:stationId, 100)";
            $insertStmt = $this->db->prepare($insertQuery);

            foreach ($stationsWithoutWeight as $stationId) {
                $insertStmt->bindParam(':stationId', $stationId, PDO::PARAM_INT);
                $insertStmt->execute();
            }

            // Transaktion abschließen
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            // Rollback bei einem Fehler
            $this->db->rollBack();
            error_log("Error in StationWeightModel::initializeWeights: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Holt die Gewichtung einer Station. Falls keine Gewichtung existiert, wird der Standardwert 100 zurückgegeben.
     *
     * @param int $stationId Die ID der Station
     * @return int Die Gewichtung
     */
    public function getWeight(int $stationId): int
    {
        try {
            $query = "SELECT weight FROM StationWeight WHERE station_ID = :stationId";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':stationId', $stationId, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? (int)$result['weight'] : 100; // Standardgewichtung 100
        } catch (PDOException $e) {
            error_log("Error in StationWeightModel::getWeight: " . $e->getMessage());
            return 100; // Standardgewichtung 100 im Fehlerfall
        }
    }

    /**
     * Aktualisiert die Gewichtungen aller Stationen gemäß den übergebenen Gewichtungen.
     *
     * @param array $weights Assoziatives Array mit Stations-IDs als Schlüssel und Gewichtungen als Werte
     * @return bool True bei Erfolg, false bei einem Fehler
     */
    public function updateAllWeights(array $weights): bool
    {
        try {
            // Starten einer Transaktion
            $this->db->beginTransaction();

            // Update der Gewichtungen für jede Station
            $updateQuery = "INSERT INTO StationWeight (station_ID, weight) 
                           VALUES (:stationId, :weight) 
                           ON DUPLICATE KEY UPDATE weight = :weight";
            $updateStmt = $this->db->prepare($updateQuery);

            foreach ($weights as $stationId => $weight) {
                $updateStmt->bindParam(':stationId', $stationId, PDO::PARAM_INT);
                $updateStmt->bindParam(':weight', $weight, PDO::PARAM_INT);
                $updateStmt->execute();
            }

            // Transaktion abschließen
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            // Rollback bei einem Fehler
            $this->db->rollBack();
            error_log("Error in StationWeightModel::updateAllWeights: " . $e->getMessage());
            return false;
        }
    }
}
