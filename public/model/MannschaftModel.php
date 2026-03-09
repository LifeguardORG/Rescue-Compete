<?php

namespace Mannschaft;

use PDO;
use PDOException;

class MannschaftModel
{
    private PDO $db;

    /**
     * Konstruktor zur Initialisierung der Datenbankverbindung.
     *
     * @param PDO $db Die PDO-Datenbankverbindung.
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Legt einen neuen Datensatz in der Mannschaft-Tabelle an.
     *
     * @param array $entry Enthält die Werte für Teamname, Kreisverband und Landesverband.
     * @return mixed Gibt die zuletzt eingefügte ID zurück oder false bei Fehler.
     */
    public function create(array $entry)
    {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO Mannschaft (Teamname, Kreisverband, Landesverband) 
                 VALUES (:Teamname, :Kreisverband, :Landesverband)"
            );
            $stmt->bindParam(':Teamname', $entry['Teamname']);
            $stmt->bindParam(':Kreisverband', $entry['Kreisverband']);
            $stmt->bindParam(':Landesverband', $entry['Landesverband']);
            $stmt->execute();
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error in create: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Aktualisiert einen vorhandenen Mannschaft-Datensatz.
     *
     * @param int $id Die ID der Mannschaft.
     * @param array $entry Enthält die neuen Werte für Teamname, Kreisverband und Landesverband.
     * @return bool Gibt true zurück, wenn das Update erfolgreich war, sonst false.
     */
    public function updateTeam(int $id, array $entry): bool
    {
        try {
            $query = "UPDATE Mannschaft 
                      SET Teamname = :Teamname, Kreisverband = :Kreisverband, Landesverband = :Landesverband 
                      WHERE id = :id";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                ':Teamname'     => $entry['Teamname'],
                ':Kreisverband' => $entry['Kreisverband'],
                ':Landesverband'=> $entry['Landesverband'],
                ':id'           => $id
            ]);
        } catch (PDOException $e) {
            error_log("Error in updateTeam: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Löscht einen Mannschaft-Datensatz anhand der gegebenen ID und entfernt auch alle zugehörigen Verknüpfungen in:
     * - MannschaftProtokoll
     * - MannschaftStaffel
     * - MannschaftWertung
     *
     * @param int $id Die ID des zu löschenden Datensatzes.
     * @return bool Gibt true zurück, wenn der Datensatz und alle zugehörigen Verknüpfungen erfolgreich gelöscht wurden, sonst false.
     */
    public function delete(int $id): bool
    {
        try {
            // Transaktion starten, um alle Löschvorgänge konsistent durchzuführen
            $this->db->beginTransaction();

            // 1. Lösche alle Verknüpfungen in der Tabelle MannschaftProtokoll, die zu dieser Mannschaft gehören
            $stmt = $this->db->prepare("DELETE FROM MannschaftProtokoll WHERE mannschaft_ID = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            // 2. Lösche alle Verknüpfungen in der Tabelle MannschaftStaffel, die zu dieser Mannschaft gehören
            $stmt = $this->db->prepare("DELETE FROM MannschaftStaffel WHERE mannschaft_ID = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            // 3. Lösche alle Verknüpfungen in der Tabelle MannschaftWertung, die zu dieser Mannschaft gehören
            $stmt = $this->db->prepare("DELETE FROM MannschaftWertung WHERE mannschaft_ID = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            // 4. Lösche schließlich den Datensatz der Mannschaft selbst
            $stmt = $this->db->prepare("DELETE FROM Mannschaft WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            // Alle Löschvorgänge erfolgreich – Transaktion committen
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            // Bei einem Fehler: Transaktion zurückrollen
            $this->db->rollBack();
            error_log("Error in delete: " . $e->getMessage());
            return false;
        }
    }


    /**
     * Ruft alle Mannschaften aus der Datenbank ab.
     *
     * @return array Ein Array mit allen Mannschaften oder ein leeres Array bei Fehler.
     */
    public function getAllMannschaften(): array
    {
        try {
            $query = "SELECT ID, Teamname, Kreisverband, Landesverband FROM Mannschaft";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getAllMannschaften: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Fügt eine neue Mannschaft hinzu oder aktualisiert einen vorhandenen Eintrag,
     * falls ein Duplikat (gleicher Teamname und Kreisverband) existiert.
     *
     * Es können mehrere Mannschaften aus demselben Kreisverband existieren, wenn die Teamnamen unterschiedlich sind.
     *
     * @param array $entry Enthält die Felder 'Teamname', 'Kreisverband' und 'Landesverband'.
     * @param bool $confirmUpdate Falls true und eine Duplikat-ID übergeben wurde, wird ein Update durchgeführt.
     * @param mixed $providedDuplicateId Optional: Übergebene Duplikat-ID aus dem Formular.
     * @return array Enthält den Status, eine Nachricht und ggf. die Duplikat-ID oder die neue ID.
     */
    public function addOrUpdateTeam(array $entry, bool $confirmUpdate = false, $providedDuplicateId = null): array
    {
        try {
            // Falls der Benutzer bereits bestätigt hat, das Duplikat zu aktualisieren,
            // wird das Update für den vorhandenen Datensatz durchgeführt.
            if ($confirmUpdate && $providedDuplicateId !== null) {
                $updated = $this->updateTeam($providedDuplicateId, $entry);
                if ($updated) {
                    return ['status' => 'updated', 'message' => 'Eintrag aktualisiert.'];
                } else {
                    return ['status' => 'error', 'message' => 'Fehler beim Aktualisieren der Mannschaft.'];
                }
            }

            // Prüfe auf Duplikate: Ein Duplikat wird nur gemeldet, wenn sowohl der Teamname als auch der Kreisverband übereinstimmen.
            $query = "SELECT * FROM Mannschaft WHERE Teamname = :Teamname AND Kreisverband = :Kreisverband";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':Teamname'     => $entry['Teamname'],
                ':Kreisverband' => $entry['Kreisverband']
            ]);
            $duplicate = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$confirmUpdate && $duplicate) {
                return [
                    'status'       => 'duplicate',
                    'duplicate_id' => $duplicate['id']
                ];
            }

            // Falls kein Duplikat vorliegt, wird ein neuer Datensatz angelegt.
            $newId = $this->create($entry);
            if ($newId) {
                return ['status' => 'created', 'message' => 'Neuer Eintrag hinzugefügt.', 'new_id' => $newId];
            } else {
                return ['status' => 'error', 'message' => 'Fehler beim Hinzufügen der Mannschaft.'];
            }
        } catch (PDOException $e) {
            error_log("Error in addOrUpdateTeam: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Ein Fehler ist aufgetreten.'];
        }
    }

    /**
     * Liest einen oder mehrere Mannschaft-Einträge aus der Datenbank.
     *
     * @param int|null $id Optional: ID eines spezifischen Eintrags. Wenn null, werden alle Einträge zurückgegeben.
     * @return array|null Ein einzelner Mannschaft-Eintrag (wenn ID angegeben), alle Einträge (wenn keine ID) oder null bei Fehler.
     */
    public function read(int $id = null): ?array
    {
        try {
            if ($id !== null) {
                // Einzelnen Eintrag anhand der ID abrufen
                $stmt = $this->db->prepare("SELECT * FROM Mannschaft WHERE ID = :id");
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return $result ?: null;
            } else {
                // Alle Einträge abrufen und nach Teamname sortieren
                $stmt = $this->db->query("SELECT * FROM Mannschaft ORDER BY Teamname");
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            error_log("Error in MannschaftModel::read: " . $e->getMessage());
            return null;
        }
    }
}


