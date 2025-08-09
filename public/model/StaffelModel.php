<?php

namespace Staffel;

use PDO;
use PDOException;

/**
 * Klasse zur Verwaltung von Staffeleinträgen in der Datenbank.
 */
class StaffelModel
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
     * Fügt eine neue Staffel in die Datenbank ein.
     *
     * @param array $entry Eingabedaten: name.
     * @return mixed Die ID des neuen Eintrags oder false im Fehlerfall.
     */
    public function create(array $entry)
    {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO Staffel (name) VALUES (:name)"
            );
            $stmt->bindParam(':name', $entry['name']);
            $stmt->execute();
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error in Staffel::create: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Liest Staffeln aus der Datenbank.
     *
     * @param int|null $id Optional: ID einer spezifischen Staffel.
     * @return array Ein oder mehrere Staffeleinträge.
     */
    public function read(?int $id = null): array
    {
        try {
            if ($id === null) {
                $stmt = $this->db->query("SELECT * FROM Staffel");
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $stmt = $this->db->prepare("SELECT * FROM Staffel WHERE ID = :id");
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            error_log("Error in Staffel::read: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Fügt eine Staffel hinzu oder aktualisiert sie, falls sie bereits existiert.
     *
     * @param array $entry Eingabedaten:
     *                     - name: Name der Staffel.
     * @return string Statusmeldung, ob ein Eintrag aktualisiert oder neu hinzugefügt wurde.
     */
    public function updateOrInsert(array $entry): string
    {
        try {
            // Prüfe, ob die Staffel bereits existiert.
            $stmtCheck = $this->db->prepare(
                "SELECT ID FROM Staffel WHERE name = :name LIMIT 1"
            );
            $stmtCheck->execute([':name' => trim($entry['name'])]);
            $result = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if ($result !== false && isset($result['ID'])) {
                // Falls die Staffel existiert, aktualisiere den Eintrag.
                $stmtUpdate = $this->db->prepare(
                    "UPDATE Staffel SET name = :name WHERE ID = :id"
                );
                $stmtUpdate->execute([
                    ':name' => $entry['name'],
                    ':id'   => $result['ID']
                ]);
                return "Eintrag aktualisiert.";
            } else {
                // Falls die Staffel nicht existiert, füge einen neuen Eintrag hinzu.
                $stmtInsert = $this->db->prepare(
                    "INSERT INTO Staffel (name) VALUES (:name)"
                );
                $stmtInsert->execute([
                    ':name' => $entry['name']
                ]);
                return "Ein neuer Eintrag wurde hinzugefügt.";
            }
        } catch (PDOException $e) {
            error_log("Error in Staffel::updateOrInsert: " . $e->getMessage());
            return "Fehler aufgetreten.";
        }
    }

    /**
     * Löscht eine Staffel aus der Datenbank und entfernt alle zugehörigen
     * Mannschaft-Staffel Verknüpfungen.
     *
     * Vorgehensweise:
     * 1. Eine Transaktion starten.
     * 2. Zuerst werden in der Tabelle MannschaftStaffel alle Einträge gelöscht, bei denen
     *    die staffel_ID dem zu löschenden Staffel-Eintrag entspricht.
     * 3. Anschließend wird der Eintrag in der Tabelle Staffel gelöscht.
     * 4. Bei Erfolg wird die Transaktion committet, bei einem Fehler zurückgerollt.
     *
     * @param int $id ID der zu löschenden Staffel.
     * @return bool True bei Erfolg, false bei Fehler.
     */
    public function delete(int $id): bool
    {
        try {
            // Transaktion starten
            $this->db->beginTransaction();

            // Zuerst: Lösche alle Verknüpfungen in MannschaftStaffel, die zur Staffel gehören
            $stmt = $this->db->prepare("DELETE FROM MannschaftStaffel WHERE staffel_ID = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            // Dann: Lösche den Eintrag in der Tabelle Staffel
            $stmt = $this->db->prepare("DELETE FROM Staffel WHERE ID = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            // Transaktion erfolgreich committen
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            // Bei Fehler die Transaktion zurückrollen
            $this->db->rollBack();
            error_log("Error in Staffel::delete: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Prüft, ob eine Staffel mit dem gegebenen Namen existiert.
     *
     * @param string $name Der zu prüfende Staffelname.
     * @return bool True, wenn der Name existiert; ansonsten False.
     */
    public function existsByName(string $name): bool {
        $stmt = $this->db->prepare("SELECT ID FROM Staffel WHERE name = :name LIMIT 1");
        $stmt->execute([':name' => trim($name)]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false;
    }

}
