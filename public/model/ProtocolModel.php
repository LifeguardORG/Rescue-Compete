<?php

namespace Protocol;

use PDO;
use PDOException;

/**
 * Diese Klasse verwaltet ProtocolModel-Einträge in der Datenbank.
 */
class ProtocolModel
{
    private PDO $db;

    /**
     * Konstruktor zur Initialisierung der PDO-Datenbankverbindung.
     *
     * @param PDO $db Die PDO-Datenbankverbindung.
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Fügt einen neuen Protokolleintrag in die Datenbank ein.
     *
     * @param array $entry Enthält die Felder 'Name', 'max_Punkte' und 'station_Nr'.
     * @return mixed Gibt die zuletzt generierte Nummer (ID) zurück oder null bei Fehler.
     */
    public function create(array $entry)
    {
        try {
            $query = "INSERT INTO `Protokoll` (Name, max_Punkte, station_ID) 
                  VALUES (:Name, :max_Punkte, :station_ID)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':Name', $entry['Name'], PDO::PARAM_STR);
            $stmt->bindParam(':max_Punkte', $entry['max_Punkte'], PDO::PARAM_INT);
            $stmt->bindParam(':station_ID', $entry['station_ID'], PDO::PARAM_INT);

            // Prüfen Sie, ob die station_ID in der Datenbank existiert
            $checkStmt = $this->db->prepare("SELECT COUNT(*) FROM Station WHERE ID = :id");
            $checkStmt->execute([':id' => $entry['station_ID']]);
            $exists = $checkStmt->fetchColumn();

            if ($stmt->execute()) {
                $lastId = $this->db->lastInsertId();
                error_log("Insert erfolgreich, letzte ID: " . $lastId);
                return $lastId;
            } else {
                error_log("Fehler beim Insert: " . print_r($stmt->errorInfo(), true));
                return null;
            }
        } catch (PDOException $e) {
            error_log("Error in ProtocolModel::create: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Liest Protokolleinträge aus der Datenbank.
     *
     * @param int|null $nr Optional: Wenn angegeben, wird der Eintrag mit dieser Nr zurückgegeben.
     * @return array Gibt ein Array der gefundenen Einträge oder einen leeren Array bei Fehler zurück.
     */
    public function read(int $nr = null): array
    {
        try {
            if ($nr === null) {
                $stmt = $this->db->query("SELECT 
                    Protokoll.Nr AS protocol_Nr, 
                    Protokoll.Name, 
                    Protokoll.max_Punkte, 
                    Protokoll.station_ID, 
                    Station.name AS station_name 
                    FROM `Protokoll` 
                    JOIN Station ON Protokoll.station_ID = Station.ID");
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $stmt = $this->db->prepare("SELECT * FROM `Protokoll` WHERE Nr = :nr");
                $stmt->bindParam(':nr', $nr, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            error_log("Error in ProtocolModel::read: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Löscht einen Protokolleintrag anhand der gegebenen Nr.
     *
     * @param int $nr Die Nummer des zu löschenden Eintrags.
     * @return bool Gibt true zurück, wenn der Löschvorgang erfolgreich war, sonst false.
     */
    public function delete(int $nr): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM `Protokoll` WHERE Nr = :nr");
            $stmt->bindParam(':nr', $nr, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in ProtocolModel::delete: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Fügt einen neuen Protokolleintrag hinzu oder aktualisiert einen bestehenden,
     * falls ein Duplikat (gleicher Name und station_Nr) gefunden wird.
     *
     * @param array $entry Enthält die Felder 'Name', 'max_Punkte' und 'station_Nr'.
     * @param bool $confirmUpdate Falls true, wird ein gefundenes Duplikat aktualisiert.
     * @param mixed $providedDuplicateId Optional: Die Nr des gefundenen Duplikats.
     * @return array Gibt einen Status, eine Nachricht und ggf. eine Duplikat-ID oder neue ID zurück.
     */
    public function addOrUpdateProtocol(array $entry, bool $confirmUpdate = false, $providedDuplicateId = null): array
    {
        try {
            // Update, falls eine Bestätigung vorliegt und eine Duplikat-ID übergeben wurde.
            if ($confirmUpdate && $providedDuplicateId !== null) {
                $updated = $this->updateProtocol($providedDuplicateId, $entry);
                if ($updated) {
                    return ['status' => 'updated', 'message' => 'Eintrag aktualisiert.'];
                } else {
                    return ['status' => 'error', 'message' => 'Fehler beim Aktualisieren des Protokolls.'];
                }
            }

            // Duplikatprüfung anhand des Namens UND der station_Nr.
            $query = "SELECT p.*, s.name as station_name FROM Protokoll p 
                      JOIN Station s ON p.station_ID = s.ID
                      WHERE p.Name = :name AND p.station_ID = :station_ID";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':name' => $entry['Name'],
                ':station_ID' => $entry['station_ID']
            ]);
            $duplicate = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$confirmUpdate && $duplicate) {
                $message = "Für die Station " . $duplicate['station_name'] . " gibt es bereits ein ["
                    . $duplicate['Name'] . "] ProtocolModel mit " . $duplicate['max_Punkte']
                    . " Punkten. Möchtest du das aktualisieren?";
                return [
                    'status'       => 'duplicate',
                    'message'      => $message,
                    'duplicate_Nr' => $duplicate['Nr'],
                    'station_ID'   => $entry['station_ID']
                ];
            }

            // Kein Duplikat: Neuer Eintrag.
            $newId = $this->create($entry);
            if ($newId) {
                return ['status' => 'created', 'message' => 'Neuer Eintrag hinzugefügt.', 'new_id' => $newId];
            } else {
                return ['status' => 'error', 'message' => 'Fehler beim Hinzufügen des Protokolls.'];
            }
        } catch (PDOException $e) {
            error_log("Error in ProtocolModel::addOrUpdateProtocol: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Ein Fehler ist aufgetreten.'];
        }
    }

    /**
     * Aktualisiert einen Protokolleintrag anhand der gegebenen Nr.
     *
     * @param int $Nr Die Nummer des Eintrags, der aktualisiert werden soll.
     * @param array $entry Neue Werte: 'Name', 'max_Punkte' und 'station_Nr'.
     * @return bool Gibt true zurück, wenn das Update erfolgreich war, sonst false.
     */
    public function updateProtocol(int $Nr, array $entry): bool
    {
        try {
            $queryUpdate = "UPDATE Protokoll
                        SET Name = :Name, max_Punkte = :max_Punkte, station_ID = :station_ID 
                        WHERE Nr = :Nr";
            $stmtUpdate = $this->db->prepare($queryUpdate);
            $params = [
                ':Nr' => $Nr,
                ':Name' => $entry['Name'],
                ':max_Punkte' => $entry['max_Punkte'],
                ':station_ID' => $entry['station_ID']
            ];
            if ($stmtUpdate->execute($params)) {
                return true;
            } else {
                error_log("Update error: " . print_r($stmtUpdate->errorInfo(), true));
                return false;
            }
        } catch (PDOException $e) {
            error_log("Error in updateProtocol: " . $e->getMessage());
            return false;
        }
    }


    /**
     * Ermittelt die Stationsnummer anhand eines Eingabewerts.
     * Falls ein numerischer Wert übergeben wird, wird dieser direkt zurückgegeben.
     * Andernfalls wird anhand des Namens die entsprechende Nummer ermittelt.
     *
     * @param mixed $input Name oder Nummer.
     * @return mixed Gibt die Stationsnummer oder null zurück.
     */
    public function stationReverseRead($input = null)
    {

        if (empty($input)) {
            return null;
        }

        if (is_numeric($input)) {

            $stmt = $this->db->prepare("SELECT ID, name FROM `Station` WHERE ID = :id");
            $stmt->execute([':id' => (int)$input]);

            return (int)$input;
        }

        if(gettype($input) == "string"){
            $stmt = $this->db->prepare("SELECT ID FROM `Station` WHERE name = :name");
            $stmt->execute([':name' => $input]);
            return $stmt->fetchColumn();
        }

        return null;
    }
}
