<?php

namespace model;

use PDO;
use PDOException;

class ScoringModel
{
    // PDO-Datenbankverbindung
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
     * Erstellt eine neue Wertungsklasse in der Datenbank, sofern nicht bereits vorhanden.
     *
     * @param array $entry Array mit den Eintragsdaten (hier: 'name')
     * @return int|null Gibt die neu erzeugte ID zurück oder null, wenn die Wertung bereits existiert oder ein Fehler auftritt.
     */
    public function create(array $entry): ?int
    {
        try {
            // Überprüfen, ob die Wertung bereits existiert
            $check = $this->db->prepare("SELECT COUNT(*) FROM Wertungsklasse WHERE name = :name");
            $check->bindParam(':name', $entry['name']);
            $check->execute();

            if ($check->fetchColumn() > 0) {
                return null; // Eintrag existiert bereits
            }

            // Neuen Eintrag in die Tabelle einfügen
            $stmt = $this->db->prepare("INSERT INTO Wertungsklasse (name) VALUES (:name)");
            $stmt->bindParam(':name', $entry['name']);
            $stmt->execute();
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Fehler in Wertung::create: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Liest einen oder mehrere Einträge der Wertungsklassen aus der Datenbank.
     *
     * @param int|null $id Optional: ID eines spezifischen Eintrags.
     * @return array|null Gibt einen einzelnen Eintrag (bei ID) oder ein Array aller Einträge zurück, oder null bei Fehler.
     */
    public function read(int $id = null): ?array
    {
        try {
            if ($id !== null) {
                // Einzelner Eintrag
                $stmt = $this->db->prepare("SELECT * FROM Wertungsklasse WHERE ID = :id");
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                // Alle Wertungsklassen mit ihren zugehörigen Teams
                $query = "SELECT  w.ID as wertung_id,  w.name as wertung_name,  
                            GROUP_CONCAT(m.Teamname SEPARATOR ', ') as teams FROM  Wertungsklasse w 
                                LEFT JOIN MannschaftWertung mw ON mw.wertung_ID = w.ID
                          LEFT JOIN Mannschaft m ON m.ID = mw.mannschaft_ID
                          GROUP BY w.ID";

                $stmt = $this->db->query($query);
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            error_log("Error in :read: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Holt alle Teams, die einer bestimmten Wertungsklasse zugewiesen sind.
     *
     * @param mixed $wertungInput Name oder ID der Wertungsklasse
     * @return array|null Array mit Team-Daten oder null bei Fehler
     */
    public function getAssignedTeams($wertungInput): ?array
    {
        try {
            $wertungId = $this->reverseRead($wertungInput);
            if (!$wertungId) {
                return [];
            }

            $query = "SELECT m.ID as mannschaft_id, m.Teamname, m.Kreisverband, m.Landesverband 
                      FROM Mannschaft m 
                      INNER JOIN MannschaftWertung mw ON m.ID = mw.mannschaft_ID 
                      WHERE mw.wertung_ID = :wertung_id 
                      ORDER BY m.Teamname";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':wertung_id', $wertungId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getAssignedTeams: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Entfernt mehrere Teams aus einer Wertungsklasse.
     *
     * @param array $teamIds Array von Team-IDs
     * @param mixed $wertungInput Name oder ID der Wertungsklasse
     * @return bool True bei Erfolg, false bei Fehler
     */
    public function removeMultipleTeamsFromWertung(array $teamIds, $wertungInput): bool
    {
        try {
            $wertungId = $this->reverseRead($wertungInput);
            if (!$wertungId) {
                return false;
            }

            $this->db->beginTransaction();

            $stmt = $this->db->prepare("DELETE FROM MannschaftWertung WHERE mannschaft_ID = :mannschaft_id AND wertung_ID = :wertung_id");

            foreach ($teamIds as $teamId) {
                $stmt->bindParam(':mannschaft_id', $teamId, PDO::PARAM_INT);
                $stmt->bindParam(':wertung_id', $wertungId, PDO::PARAM_INT);
                $stmt->execute();
            }

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollback();
            error_log("Error in removeMultipleTeamsFromWertung: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Überprüft, ob eine Wertungsklasse Teams zugewiesen hat.
     *
     * @param mixed $wertungInput Name oder ID der Wertungsklasse
     * @return bool True wenn Teams zugewiesen sind, false wenn nicht
     */
    public function hasAssignedTeams($wertungInput): bool
    {
        try {
            $wertungId = $this->reverseRead($wertungInput);
            if (!$wertungId) {
                return false;
            }

            $stmt = $this->db->prepare("SELECT COUNT(*) FROM MannschaftWertung WHERE wertung_ID = :wertung_id");
            $stmt->bindParam(':wertung_id', $wertungId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error in hasAssignedTeams: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Löscht eine Wertungsklasse aus der Datenbank und entfernt alle zugehörigen Verbindungen in der MannschaftWertung-Tabelle.
     *
     * @param int $id ID des zu löschenden Eintrags.
     * @return bool True bei Erfolg, false bei Fehler.
     */
    public function delete(int $id): bool
    {
        try {
            // Zuerst alle Verbindungen in MannschaftWertung für diese Wertung löschen
            $stmt = $this->db->prepare("DELETE FROM MannschaftWertung WHERE wertung_ID = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            // Anschließend die eigentliche Wertungsklasse löschen
            $stmt = $this->db->prepare("DELETE FROM Wertungsklasse WHERE ID = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in :delete: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Fügt eine neue Wertungsklasse hinzu oder aktualisiert einen bestehenden Eintrag.
     *
     * @param array $entry Array mit den Eintragsdaten (hier: 'name')
     * @param bool $confirmUpdate Gibt an, ob bei einem Duplikat ein Update durchgeführt werden soll.
     * @param mixed $providedDuplicateId Falls vorhanden, die ID des zu aktualisierenden Eintrags.
     * @return array Ein Array mit dem Status und einer Nachricht.
     */
    public function addOrUpdateWertung(array $entry, bool $confirmUpdate = false, $providedDuplicateId = null): array
    {
        try {
            if ($confirmUpdate && $providedDuplicateId !== null) {
                $updated = $this->updateWertung($providedDuplicateId, $entry);
                if ($updated) {
                    return ['status' => 'updated', 'message' => 'Eintrag aktualisiert.'];
                } else {
                    return ['status' => 'error', 'message' => 'Fehler beim Aktualisieren der Wertungsklasse.'];
                }
            }

            // Suche nach Duplikaten
            $query = "SELECT * FROM Wertungsklasse WHERE name = :name";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':name' => $entry['name']]);
            $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $foundName = null;
            foreach ($duplicates as $dup) {
                if (strcasecmp($dup['name'], $entry['name']) === 0 && $foundName === null) {
                    $foundName = $dup;
                }
            }

            if (!$confirmUpdate && $foundName) {
                // Rückgabe eines Duplikats, wenn vorhanden
                return [
                    'status' => 'duplicate',
                    'message' => 'Eine Wertungsklasse mit dem Namen existiert bereits. Möchtest du sie aktualisieren?',
                    'duplicate_id' => $foundName['ID']
                ];
            }

            // Falls kein Duplikat vorliegt, neuen Eintrag anlegen
            $newId = $this->create($entry);
            if ($newId) {
                return ['status' => 'created', 'message' => 'Neuer Eintrag hinzugefügt.', 'new_id' => $newId];
            } else {
                return ['status' => 'error', 'message' => 'Fehler beim Hinzufügen der Wertungsklasse.'];
            }
        } catch (PDOException $e) {
            error_log("Error in addOrUpdateWertung: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Ein Fehler ist aufgetreten.'];
        }
    }

    /**
     * Aktualisiert einen bestehenden Eintrag in der Wertungsklasse.
     *
     * @param int $id ID des zu aktualisierenden Eintrags.
     * @param array $entry Array mit den neuen Daten.
     * @return bool True bei Erfolg, false bei Fehler.
     */
    public function updateWertung(int $id, array $entry): bool
    {
        try {
            $queryUpdate = "UPDATE Wertungsklasse SET name = :name WHERE ID = :id";
            $stmtUpdate = $this->db->prepare($queryUpdate);
            return $stmtUpdate->execute([
                ':name' => $entry['name'],
                ':id'   => $id
            ]);
        } catch (PDOException $e) {
            error_log("Error in updateWertungsklasse: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Sucht anhand des Namens die ID einer Wertungsklasse.
     *
     * @param mixed $input Der Name oder die ID.
     * @return mixed Gibt die ID zurück oder null, wenn nicht gefunden.
     */
    public function reverseRead($input = null)
    {
        if (empty($input)) {
            error_log("Wertung::reverseRead - kein Parameter übergeben.");
            return null;
        }
        if (is_numeric($input)) {
            return (int)$input;
        }
        $stmt = $this->db->prepare("SELECT ID FROM `Wertungsklasse` WHERE name = :name");
        $stmt->execute([':name' => $input]);
        return $stmt->fetchColumn();
    }

    /**
     * Sucht anhand des Teamnamens die ID einer Mannschaft.
     *
     * @param mixed $input Der Teamname oder die ID.
     * @return mixed Gibt die ID zurück oder null, wenn nicht gefunden.
     */
    public function reverseReadMannschaft($input = null)
    {
        if (empty($input)) {
            error_log("Wertung::reverseRead - kein Parameter übergeben.");
            return null;
        }
        if (is_numeric($input)) {
            return (int)$input;
        }
        $stmt = $this->db->prepare("SELECT ID FROM `Mannschaft` WHERE Teamname = :teamname");
        $stmt->execute([':teamname' => $input]);
        return $stmt->fetchColumn();
    }

    /**
     * Erstellt eine Verbindung zwischen einer Mannschaft und einer Wertungsklasse.
     *
     * @param mixed $mannschaft_ID Die ID der Mannschaft.
     * @param mixed $wertung_ID Die ID der Wertungsklasse.
     * @return mixed Gibt die letzte eingefügte ID zurück oder null bei Fehler.
     */
    public function MannschaftWertung($mannschaft_ID, $wertung_ID) {
        try {
            $stmt = $this->db->prepare("INSERT INTO MannschaftWertung (mannschaft_ID, wertung_ID) VALUES (:mannschaft_ID, :wertung_ID)");
            $stmt->bindParam(':mannschaft_ID', $mannschaft_ID);
            $stmt->bindParam(':wertung_ID', $wertung_ID);
            $stmt->execute();
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Fehler in Wertung::MannschaftWertung: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Entfernt eine Verbindung zwischen einer Mannschaft und einer Wertungsklasse.
     *
     * @param mixed $mannschaft_ID Die ID der Mannschaft.
     * @param mixed $wertung_ID Die ID der Wertungsklasse.
     * @return bool True bei Erfolg, false bei Fehler.
     */
    public function killMannschaftWertung($mannschaft_ID, $wertung_ID): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM MannschaftWertung WHERE mannschaft_ID = :mannschaft_ID AND Wertung_ID = :wertung_ID");
            $stmt->bindParam(':mannschaft_ID', $mannschaft_ID);
            $stmt->bindParam(':wertung_ID', $wertung_ID);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in killMannschaftWertung: " . $e->getMessage());
            return false;
        }
    }
}