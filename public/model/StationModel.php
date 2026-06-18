<?php

namespace Station;

use PDO;
use PDOException;

/**
 * Klasse zur Verwaltung von Stations-Einträgen in der Datenbank.
 */
class StationModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function create(array $entry)
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO `Station` (name, Nr) VALUES (:name, :nr)");
            $stmt->bindParam(':name', $entry['name'], PDO::PARAM_STR);
            $stmt->bindParam(':nr', $entry['Nr'], PDO::PARAM_INT);
            $stmt->execute();
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error in Station::create: " . $e->getMessage());
            return false;
        }
    }

    public function read(int $id = null): ?array
    {
        try {
            if ($id === null) {
                $stmt = $this->db->query("SELECT * FROM `Station` ORDER BY name");
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $stmt = $this->db->prepare("SELECT * FROM `Station` WHERE ID = :id");
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            error_log("Fehler in Station::read: " . $e->getMessage());
            return null;
        }
    }

    public function updateOrInsert(int $id, string $name, string $nr): string
    {
        try {
            $queryCheck = "SELECT COUNT(*) AS count FROM Station WHERE ID = :id";
            $stmtCheck = $this->db->prepare($queryCheck);
            $stmtCheck->execute([':id' => $id]);
            $result = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if ($result['count'] > 0) {
                $queryUpdate = "UPDATE Station SET name = :name, Nr = :nr WHERE ID = :id";
                $stmtUpdate = $this->db->prepare($queryUpdate);
                $stmtUpdate->execute([
                    ':name' => $name,
                    ':id' => $id,
                    ':nr' => $nr
                ]);
            } else {
                $queryInsert = "INSERT INTO Station (name, Nr) VALUES (:name, :nr)";
                $stmtInsert = $this->db->prepare($queryInsert);
                $stmtInsert->execute([
                    ':nr' => $nr,
                    ':name' => $name
                ]);
            }
            return true;
        } catch (PDOException $e) {
            error_log("Error in Station::updateOrInsert: " . $e->getMessage());
            return "Fehler aufgetreten.";
        }
    }

    /**
     * Löscht eine Station aus der Datenbank und entfernt alle zugehörigen Protokolle
     * sowie die zugehörigen Punkte in der MannschaftProtokoll-Tabelle.
     *
     * Vorgehensweise:
     * 1. Starte eine Transaktion.
     * 2. Hole alle Protocol-IDs, die dieser Station zugeordnet sind.
     * 3. Lösche in MannschaftProtokoll alle Einträge, die zu diesen Protokollen gehören.
     * 4. Lösche die Protokolle, die der Station zugeordnet sind.
     * 5. Lösche alle FormCollections und deren abhängige Daten, die mit dieser Station verknüpft sind.
     * 6. Lösche das Gewicht der Station aus der StationWeight-Tabelle.
     * 7. Lösche abschließend die Station.
     * 8. Committe die Transaktion.
     *
     * @param int $id Die ID der zu löschenden Station.
     * @return bool True bei Erfolg, false bei Fehler.
     */
    public function delete(int $id): bool
    {
        try {
            // Transaktion starten
            $this->db->beginTransaction();

            // 1. Protocol-IDs der Station abrufen
            $stmt = $this->db->prepare("SELECT Nr FROM Protokoll WHERE station_ID = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $protocolIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // 2. Falls Protokolle vorhanden sind, lösche die zugehörigen Einträge in MannschaftProtokoll
            if (!empty($protocolIds)) {
                $inClause = implode(',', array_map('intval', $protocolIds));
                $stmt = $this->db->prepare("DELETE FROM MannschaftProtokoll WHERE protokoll_Nr IN ($inClause)");
                $stmt->execute();
            }

            // 3. Lösche alle Protokolle, die der Station zugeordnet sind
            $stmt = $this->db->prepare("DELETE FROM Protokoll WHERE station_ID = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            // 4. FormCollection-IDs der Station abrufen
            $stmt = $this->db->prepare("SELECT ID FROM FormCollection WHERE station_ID = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $collectionIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // 5. Lösche alle FormCollections, die dieser Station zugeordnet sind
            if (!empty($collectionIds)) {
                $inClause = implode(',', array_map('intval', $collectionIds));
                $stmt = $this->db->prepare("DELETE FROM FormCollection WHERE ID IN ($inClause)");
                $stmt->execute();
            }

            // 6. Lösche den Gewichtungseintrag, falls vorhanden
            $stmt = $this->db->prepare("DELETE FROM StationWeight WHERE station_ID = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            // 6b. Lösche alle Wertung-Zuordnungen dieser Station
            $stmt = $this->db->prepare("DELETE FROM WertungStation WHERE station_ID = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            // 7. Lösche die Station selbst
            $stmt = $this->db->prepare("DELETE FROM Station WHERE ID = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            // Transaktion erfolgreich beenden
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error in Station::delete: " . $e->getMessage());
            return false;
        }
    }

    public function existsByName(string $name)
    {
        try {
            error_log("Station::existsByName - Überprüfe Station mit Namen: " . $name);
            $stmt = $this->db->prepare("SELECT ID FROM Station WHERE name = :name");
            $stmt->execute([':name' => $name]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result && isset($result['ID'])) {
                error_log("Station::existsByName - Station existiert mit ID: " . $result['ID']);
                return $result['ID'];
            } else {
                error_log("Station::existsByName - Station existiert nicht.");
                return false;
            }
        } catch (PDOException $e) {
            error_log("Fehler in Station::existsByName: " . $e->getMessage());
            return false;
        }
    }

    public function existsByNr(int $nr)
    {
        try {
            $stmt = $this->db->prepare("SELECT ID FROM Station WHERE Nr = :nr");
            $stmt->execute([':nr' => $nr]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result ? $result['ID'] : false;
        } catch (PDOException $e) {
            error_log("Fehler in Station::existsByNr: " . $e->getMessage());
            return false;
        }
    }

    public function getStationNames(string $sortBy = 'name'): array
    {
        try {
            $orderBy = $sortBy === 'nr' ? 'Nr, name' : 'name';
            $stmt = $this->db->query("SELECT name FROM Station ORDER BY $orderBy");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log("Fehler in Station::getStationNames: " . $e->getMessage());
            return [];
        }
    }

    public function getProtocolCount(int $stationId): int
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM Protokoll WHERE station_ID = :id");
            $stmt->bindParam(':id', $stationId, PDO::PARAM_INT);
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Fehler in Station::getProtocolCount: " . $e->getMessage());
            return 0;
        }
    }

    public function getQuestionFormCount(int $stationId): int
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM FormCollection WHERE station_ID = :id");
            $stmt->bindParam(':id', $stationId, PDO::PARAM_INT);
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Fehler in Station::getQuestionFormCount: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Liefert alle Wertungsklassen (ID + Name) für die Zuordnungs-Dropdowns.
     *
     * @return array Liste mit ['ID' => int, 'name' => string].
     */
    public function getAllWertungen(): array
    {
        try {
            $stmt = $this->db->query("SELECT ID, name FROM Wertungsklasse ORDER BY name");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in Station::getAllWertungen: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Liefert die IDs der einer Wertung zugeordneten Stationen.
     *
     * @param int $wertungId ID der Wertungsklasse.
     * @return int[] Liste der zugeordneten Station-IDs.
     */
    public function getStationsByWertung(int $wertungId): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT station_ID FROM WertungStation WHERE wertung_ID = :id"
            );
            $stmt->bindParam(':id', $wertungId, PDO::PARAM_INT);
            $stmt->execute();
            return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
        } catch (PDOException $e) {
            error_log("Error in Station::getStationsByWertung: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Synchronisiert die Stations-Zuordnungen einer Wertung auf genau die
     * übergebene ID-Liste (alte Zuordnungen werden ersetzt). Transaktional.
     *
     * @param int   $wertungId  ID der Wertungsklasse.
     * @param int[] $stationIds Gewünschte Station-IDs.
     * @return bool True bei Erfolg, false bei Fehler.
     */
    public function setStationsForWertung(int $wertungId, array $stationIds): bool
    {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("DELETE FROM WertungStation WHERE wertung_ID = :id");
            $stmt->bindParam(':id', $wertungId, PDO::PARAM_INT);
            $stmt->execute();

            $insert = $this->db->prepare(
                "INSERT INTO WertungStation (wertung_ID, station_ID) VALUES (:wertung_ID, :station_ID)"
            );
            foreach (array_unique(array_map('intval', $stationIds)) as $stationId) {
                $insert->execute([
                    ':wertung_ID' => $wertungId,
                    ':station_ID' => $stationId,
                ]);
            }

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error in Station::setStationsForWertung: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Liefert je Wertung die Liste ihrer zugeordneten Stationsnamen
     * (für den Übersichts-Tab). Wertungen ohne Zuordnung erscheinen mit
     * leerer Stationsliste.
     *
     * @return array Liste mit ['wertung_id' => int, 'wertung_name' => string, 'stationen' => string[]].
     */
    public function getAssignmentOverview(): array
    {
        try {
            $stmt = $this->db->query(
                "SELECT wk.ID AS wertung_id, wk.name AS wertung_name, s.name AS station_name
                 FROM Wertungsklasse wk
                 LEFT JOIN WertungStation ws ON ws.wertung_ID = wk.ID
                 LEFT JOIN Station s ON s.ID = ws.station_ID
                 ORDER BY wk.name, s.name"
            );

            $overview = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $id = (int)$row['wertung_id'];
                if (!isset($overview[$id])) {
                    $overview[$id] = [
                        'wertung_id'   => $id,
                        'wertung_name' => $row['wertung_name'],
                        'stationen'    => [],
                    ];
                }
                if (!is_null($row['station_name'])) {
                    $overview[$id]['stationen'][] = $row['station_name'];
                }
            }
            return array_values($overview);
        } catch (PDOException $e) {
            error_log("Error in Station::getAssignmentOverview: " . $e->getMessage());
            return [];
        }
    }
}