<?php

namespace Station;

use PDO;
use PDOException;

/**
 * Klasse zur Verwaltung der Verknüpfung zwischen Mannschaften und Staffeln.
 */
class MannschaftStaffel
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
     * Fügt eine Verknüpfung zwischen TeamModel und Staffel hinzu.
     *
     * @param array $entry Eingabedaten: mannschaft_ID, staffel_ID, zeit.
     * @return bool True bei Erfolg, false bei Fehler.
     */
    public function create(array $entry): bool
    {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO MannschaftStaffel (mannschaft_ID, staffel_ID, zeit)
                 VALUES (:mannschaft_ID, :staffel_ID, :zeit)"
            );
            $stmt->bindParam(':mannschaft_ID', $entry['mannschaft_ID'], PDO::PARAM_INT);
            $stmt->bindParam(':staffel_ID', $entry['staffel_ID'], PDO::PARAM_INT);
            $stmt->bindParam(':zeit', $entry['zeit']);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in MannschaftStaffel::create: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Liest Verknüpfungen aus der Datenbank.
     *
     * @param int|null $mannschaft_ID Optional: Mannschafts-ID zum Filtern.
     * @return array Ein oder mehrere Verknüpfungen.
     */
    public function read(?int $mannschaft_ID = null): array
    {
        try {
            if ($mannschaft_ID === null) {
                $stmt = $this->db->query("SELECT * FROM MannschaftStaffel");
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $stmt = $this->db->prepare(
                    "SELECT * FROM MannschaftStaffel WHERE mannschaft_ID = :mannschaft_ID"
                );
                $stmt->bindParam(':mannschaft_ID', $mannschaft_ID, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            error_log("Error in MannschaftStaffel::read: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Fügt eine Verknüpfung hinzu oder aktualisiert sie, falls sie bereits existiert.
     *
     * @param array $entry Eingabedaten:
     *                     - mannschaft_ID: ID der TeamModel.
     *                     - staffel_ID: ID der Staffel.
     *                     - zeit: Zeit der Staffel (time).
     * @return string Statusmeldung, ob ein Eintrag aktualisiert oder neu hinzugefügt wurde.
     */
    public function updateOrInsert(array $entry): string
    {
        try {
            $stmtCheck = $this->db->prepare(
                "SELECT COUNT(*) AS count FROM MannschaftStaffel 
                 WHERE mannschaft_ID = :mannschaft_ID AND staffel_ID = :staffel_ID"
            );
            $stmtCheck->execute([
                ':mannschaft_ID' => $entry['mannschaft_ID'],
                ':staffel_ID' => $entry['staffel_ID']
            ]);
            $result = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if ($result['count'] > 0) {
                $stmtUpdate = $this->db->prepare(
                    "UPDATE MannschaftStaffel 
                     SET zeit = :zeit 
                     WHERE mannschaft_ID = :mannschaft_ID AND staffel_ID = :staffel_ID"
                );
                $stmtUpdate->execute([
                    ':mannschaft_ID' => $entry['mannschaft_ID'],
                    ':staffel_ID' => $entry['staffel_ID'],
                    ':zeit' => $entry['zeit']
                ]);
                return "Eintrag aktualisiert.";
            } else {
                $stmtInsert = $this->db->prepare(
                    "INSERT INTO MannschaftStaffel (mannschaft_ID, staffel_ID, zeit) 
                     VALUES (:mannschaft_ID, :staffel_ID, :zeit)"
                );
                $stmtInsert->execute([
                    ':mannschaft_ID' => $entry['mannschaft_ID'],
                    ':staffel_ID' => $entry['staffel_ID'],
                    ':zeit' => $entry['zeit']
                ]);
                return "Ein neuer Eintrag wurde hinzugefügt.";
            }
        } catch (PDOException $e) {
            error_log("Error in MannschaftStaffel::updateOrInsert: " . $e->getMessage());
            return "Fehler aufgetreten.";
        }
    }

    /**
     * Löscht eine Verknüpfung.
     *
     * @param int $mannschaft_ID Mannschafts-ID.
     * @param int $staffel_ID Staffel-ID.
     * @return bool True bei Erfolg, false bei Fehler.
     */
    public function delete(int $mannschaft_ID, int $staffel_ID): bool
    {
        try {
            $stmt = $this->db->prepare(
                "DELETE FROM MannschaftStaffel WHERE mannschaft_ID = :mannschaft_ID AND staffel_ID = :staffel_ID"
            );
            $stmt->bindParam(':mannschaft_ID', $mannschaft_ID, PDO::PARAM_INT);
            $stmt->bindParam(':staffel_ID', $staffel_ID, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in MannschaftStaffel::delete: " . $e->getMessage());
            return false;
        }
    }
}

