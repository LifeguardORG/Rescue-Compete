<?php

namespace Station;

use PDO;
use PDOException;

/**
 * Klasse zur Verwaltung der Verknüpfung zwischen Mannschaften und Protokollen.
 */
class MannschaftProtokoll
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
     * Fügt eine Verknüpfung zwischen TeamModel und ProtocolModel hinzu.
     *
     * @param array $entry Eingabedaten: mannschaft_ID, protokoll_Nr, erreichte_Punkte.
     * @return bool True bei Erfolg, false bei Fehler.
     */
    public function create(array $entry): bool
    {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO MannschaftProtokoll (mannschaft_ID, protokoll_Nr, erreichte_Punkte)
                 VALUES (:mannschaft_ID, :protokoll_Nr, :erreichte_Punkte)"
            );
            $stmt->bindParam(':mannschaft_ID', $entry['mannschaft_ID'], PDO::PARAM_INT);
            $stmt->bindParam(':protokoll_Nr', $entry['protokoll_Nr'], PDO::PARAM_INT);
            $stmt->bindParam(':erreichte_Punkte', $entry['erreichte_Punkte'], PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in MannschaftProtokoll::create: " . $e->getMessage());
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
                $stmt = $this->db->query("SELECT * FROM MannschaftProtokoll");
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $stmt = $this->db->prepare(
                    "SELECT * FROM MannschaftProtokoll WHERE mannschaft_ID = :mannschaft_ID"
                );
                $stmt->bindParam(':mannschaft_ID', $mannschaft_ID, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            error_log("Error in MannschaftProtokoll::read: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Fügt eine Verknüpfung hinzu oder aktualisiert sie, falls sie bereits existiert.
     *
     * @param array $entry Eingabedaten:
     *                     - mannschaft_ID: ID der TeamModel.
     *                     - protokoll_Nr: Nummer des Protokolls.
     *                     - erreichte_Punkte: Erreichte Punkte der TeamModel im ProtocolModel (int).
     * @return string Statusmeldung, ob ein Eintrag aktualisiert oder neu hinzugefügt wurde.
     */
    public function updateOrInsert(array $entry): string
    {
        try {
            // Prüfe, ob die Verknüpfung bereits existiert.
            $stmtCheck = $this->db->prepare(
                "SELECT COUNT(*) AS count FROM MannschaftProtokoll 
                 WHERE mannschaft_ID = :mannschaft_ID AND protokoll_Nr = :protokoll_Nr"
            );
            $stmtCheck->execute([
                ':mannschaft_ID' => $entry['mannschaft_ID'],
                ':protokoll_Nr' => $entry['protokoll_Nr']
            ]);
            $result = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if ($result['count'] > 0) {
                // Falls die Verknüpfung existiert, aktualisiere den Eintrag.
                $stmtUpdate = $this->db->prepare(
                    "UPDATE MannschaftProtokoll 
                     SET erreichte_Punkte = :erreichte_Punkte 
                     WHERE mannschaft_ID = :mannschaft_ID AND protokoll_Nr = :protokoll_Nr"
                );
                $stmtUpdate->execute([
                    ':mannschaft_ID' => $entry['mannschaft_ID'],
                    ':protokoll_Nr' => $entry['protokoll_Nr'],
                    ':erreichte_Punkte' => $entry['erreichte_Punkte']
                ]);
                return "Eintrag aktualisiert.";
            } else {
                // Falls die Verknüpfung nicht existiert, füge einen neuen Eintrag hinzu.
                $stmtInsert = $this->db->prepare(
                    "INSERT INTO MannschaftProtokoll (mannschaft_ID, protokoll_Nr, erreichte_Punkte) 
                     VALUES (:mannschaft_ID, :protokoll_Nr, :erreichte_Punkte)"
                );
                $stmtInsert->execute([
                    ':mannschaft_ID' => $entry['mannschaft_ID'],
                    ':protokoll_Nr' => $entry['protokoll_Nr'],
                    ':erreichte_Punkte' => $entry['erreichte_Punkte']
                ]);
                return "Ein neuer Eintrag wurde hinzugefügt.";
            }
        } catch (PDOException $e) {
            // Logge Fehler und gib eine Fehlermeldung zurück.
            error_log("Error in MannschaftProtokoll::updateOrInsert: " . $e->getMessage());
            return "Fehler aufgetreten.";
        }
    }

    /**
     * Löscht eine Verknüpfung.
     *
     * @param int $mannschaft_ID Mannschafts-ID.
     * @param int $protokoll_Nr Protokollnummer.
     * @return bool True bei Erfolg, false bei Fehler.
     */
    public function delete(int $mannschaft_ID, int $protokoll_Nr): bool
    {
        try {
            $stmt = $this->db->prepare(
                "DELETE FROM MannschaftProtokoll WHERE mannschaft_ID = :mannschaft_ID AND protokoll_Nr = :protokoll_Nr"
            );
            $stmt->bindParam(':mannschaft_ID', $mannschaft_ID, PDO::PARAM_INT);
            $stmt->bindParam(':protokoll_Nr', $protokoll_Nr, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in MannschaftProtokoll::delete: " . $e->getMessage());
            return false;
        }
    }
}
