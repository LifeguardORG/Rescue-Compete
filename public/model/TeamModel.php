<?php

namespace Mannschaft;

use PDO;
use PDOException;

class TeamModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

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

    public function updateTeam(int $id, array $entry): bool
    {
        try {
            $query = "UPDATE Mannschaft 
                      SET Teamname = :Teamname, Kreisverband = :Kreisverband, Landesverband = :Landesverband 
                      WHERE ID = :id";
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

    public function delete(int $id): bool
    {
        try {
            $this->db->beginTransaction();

            // Lösche TeamFormAnswer-Einträge über TeamFormInstance
            $stmt = $this->db->prepare("
                DELETE tfa FROM TeamFormAnswer tfa 
                INNER JOIN TeamFormInstance tfi ON tfa.teamFormInstance_ID = tfi.ID 
                WHERE tfi.team_ID = :id
            ");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            // Lösche TeamFormInstance-Einträge
            $stmt = $this->db->prepare("DELETE FROM TeamFormInstance WHERE team_ID = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            // Lösche User-Einträge
            $stmt = $this->db->prepare("DELETE FROM User WHERE mannschaft_ID = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            // Lösche MannschaftProtokoll-Einträge
            $stmt = $this->db->prepare("DELETE FROM MannschaftProtokoll WHERE mannschaft_ID = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            // Lösche MannschaftStaffel-Einträge
            $stmt = $this->db->prepare("DELETE FROM MannschaftStaffel WHERE mannschaft_ID = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            // Lösche MannschaftWertung-Einträge
            $stmt = $this->db->prepare("DELETE FROM MannschaftWertung WHERE mannschaft_ID = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            // Lösche die Mannschaft selbst
            $stmt = $this->db->prepare("DELETE FROM Mannschaft WHERE ID = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $result = $stmt->execute();

            if ($result && $stmt->rowCount() > 0) {
                $this->db->commit();
                return true;
            } else {
                $this->db->rollBack();
                error_log("No rows affected when deleting team with ID: " . $id);
                return false;
            }
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error in delete: " . $e->getMessage());
            return false;
        }
    }

    public function getAllMannschaften(): array
    {
        try {
            $query = "SELECT ID, Teamname, Kreisverband, Landesverband FROM Mannschaft ORDER BY Teamname";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getAllMannschaften: " . $e->getMessage());
            return [];
        }
    }

    public function addOrUpdateTeam(array $entry, bool $confirmUpdate = false, $providedDuplicateId = null): array
    {
        try {
            // Falls bestätigt wird, das Duplikat zu aktualisieren
            if ($confirmUpdate && $providedDuplicateId !== null) {
                $updated = $this->updateTeam($providedDuplicateId, $entry);
                if ($updated) {
                    return ['status' => 'updated', 'message' => 'Eintrag aktualisiert.'];
                } else {
                    return ['status' => 'error', 'message' => 'Fehler beim Aktualisieren der Mannschaft.'];
                }
            }

            // Prüfe auf Duplikate: Nur Teamname muss eindeutig sein
            $query = "SELECT ID, Kreisverband, Landesverband FROM Mannschaft WHERE Teamname = :Teamname";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':Teamname' => $entry['Teamname']]);
            $duplicate = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$confirmUpdate && $duplicate) {
                return [
                    'status'       => 'duplicate',
                    'duplicate_id' => $duplicate['ID'],
                    'existing_data' => [
                        'Kreisverband' => $duplicate['Kreisverband'],
                        'Landesverband' => $duplicate['Landesverband']
                    ]
                ];
            }

            // Neuen Datensatz anlegen
            $newId = $this->create($entry);
            if ($newId) {
                return ['status' => 'created', 'message' => 'Neue Mannschaft erfolgreich erstellt.', 'new_id' => $newId];
            } else {
                return ['status' => 'error', 'message' => 'Fehler beim Erstellen der Mannschaft.'];
            }
        } catch (PDOException $e) {
            error_log("Error in addOrUpdateTeam: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Ein Datenbankfehler ist aufgetreten.'];
        }
    }

    public function read(int $id = null): ?array
    {
        try {
            if ($id !== null) {
                $stmt = $this->db->prepare("SELECT * FROM Mannschaft WHERE ID = :id");
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return $result ?: null;
            } else {
                $stmt = $this->db->query("SELECT * FROM Mannschaft ORDER BY Teamname");
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            error_log("Error in TeamModel::read: " . $e->getMessage());
            return null;
        }
    }

    public function teamExists(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM Mannschaft WHERE ID = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error in teamExists: " . $e->getMessage());
            return false;
        }
    }
}