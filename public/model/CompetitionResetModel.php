<?php
namespace Competition;

use PDO;
use PDOException;

/**
 * Model für das Zurücksetzen von Wettkampfdaten
 * Führt die eigentlichen Datenbankoperationen durch
 */
class CompetitionResetModel
{
    private PDO $db;

    /**
     * Konstruktor: Initialisiert die Datenbankverbindung
     *
     * @param PDO $db Die Datenbankverbindung
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Setzt alle Staffeln und deren Verbindungen zurück
     *
     * @return array Ergebnis mit success-Flag und optionaler Fehlermeldung
     */
    public function resetStaffeln(): array
    {
        try {
            $this->db->beginTransaction();

            // Lösche alle Verbindungen in MannschaftStaffel
            $stmt = $this->db->prepare("DELETE FROM MannschaftStaffel");
            $stmt->execute();

            // Lösche alle Staffeln
            $stmt = $this->db->prepare("DELETE FROM Staffel");
            $stmt->execute();

            $this->db->commit();
            return ['success' => true];
        } catch (PDOException $e) {
            $this->db->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Setzt alle Stationen und deren Verbindungen zurück
     *
     * @return array Ergebnis mit success-Flag und optionaler Fehlermeldung
     */
    public function resetStationen(): array
    {
        try {
            $this->db->beginTransaction();

            // Lösche alle Protokoll-Einträge für Mannschaften
            $stmt = $this->db->prepare("DELETE FROM MannschaftProtokoll");
            $stmt->execute();

            // Lösche alle StationWeight-Einträge
            $stmt = $this->db->prepare("DELETE FROM StationWeight");
            $stmt->execute();

            // Lösche alle Protokolle
            $stmt = $this->db->prepare("DELETE FROM Protokoll");
            $stmt->execute();

            // Setze station_ID in FormCollection auf NULL
            $stmt = $this->db->prepare("UPDATE FormCollection SET station_ID = NULL WHERE station_ID IS NOT NULL");
            $stmt->execute();

            // Setze station_ID in User-Tabelle auf NULL (außer bei Admins)
            $stmt = $this->db->prepare("UPDATE User SET station_ID = NULL WHERE station_ID IS NOT NULL AND acc_typ != 'Admin'");
            $stmt->execute();

            // Lösche alle Stationen
            $stmt = $this->db->prepare("DELETE FROM Station");
            $stmt->execute();

            $this->db->commit();
            return ['success' => true];
        } catch (PDOException $e) {
            $this->db->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Setzt alle Protokolle und deren Verbindungen zurück
     *
     * @return array Ergebnis mit success-Flag und optionaler Fehlermeldung
     */
    public function resetProtokolle(): array
    {
        try {
            $this->db->beginTransaction();

            // Lösche alle Protokoll-Einträge für Mannschaften
            $stmt = $this->db->prepare("DELETE FROM MannschaftProtokoll");
            $stmt->execute();

            // Lösche alle Protokolle
            $stmt = $this->db->prepare("DELETE FROM Protokoll");
            $stmt->execute();

            $this->db->commit();
            return ['success' => true];
        } catch (PDOException $e) {
            $this->db->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Setzt alle Mannschaften und deren Verbindungen zurück
     *
     * @return array Ergebnis mit success-Flag und optionaler Fehlermeldung
     */
    public function resetMannschaften(): array
    {
        try {
            $this->db->beginTransaction();

            // Lösche alle TeamForm-Antworten
            $stmt = $this->db->prepare("DELETE FROM TeamFormAnswer");
            $stmt->execute();

            // Lösche alle TeamForm-Instanzen
            $stmt = $this->db->prepare("DELETE FROM TeamFormInstance");
            $stmt->execute();

            // Lösche Verbindungen zu Mannschaften in MannschaftProtokoll
            $stmt = $this->db->prepare("DELETE FROM MannschaftProtokoll");
            $stmt->execute();

            // Lösche Verbindungen zu Mannschaften in MannschaftStaffel
            $stmt = $this->db->prepare("DELETE FROM MannschaftStaffel");
            $stmt->execute();

            // Lösche Verbindungen zu Mannschaften in MannschaftWertung
            $stmt = $this->db->prepare("DELETE FROM MannschaftWertung");
            $stmt->execute();

            // Setze mannschaft_ID in User-Tabelle auf NULL (außer bei Admins)
            $stmt = $this->db->prepare("UPDATE User SET mannschaft_ID = NULL WHERE mannschaft_ID IS NOT NULL AND acc_typ != 'Admin'");
            $stmt->execute();

            // Lösche alle Mannschaften
            $stmt = $this->db->prepare("DELETE FROM Mannschaft");
            $stmt->execute();

            $this->db->commit();
            return ['success' => true];
        } catch (PDOException $e) {
            $this->db->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Setzt alle Formulare und deren Verbindungen zurück
     *
     * @return array Ergebnis mit success-Flag und optionaler Fehlermeldung
     */
    public function resetFormulare(): array
    {
        try {
            $this->db->beginTransaction();

            // Lösche TeamForm-Antworten
            $stmt = $this->db->prepare("DELETE FROM TeamFormAnswer");
            $stmt->execute();

            // Lösche TeamForm-Instanzen
            $stmt = $this->db->prepare("DELETE FROM TeamFormInstance");
            $stmt->execute();

            // Lösche Collection-Tokens
            $stmt = $this->db->prepare("DELETE FROM CollectionFormToken");
            $stmt->execute();

            // Lösche Collection-Question-Verbindungen
            $stmt = $this->db->prepare("DELETE FROM CollectionQuestion");
            $stmt->execute();

            // Lösche Form-Collections
            $stmt = $this->db->prepare("DELETE FROM FormCollection");
            $stmt->execute();

            // Lösche Antworten
            $stmt = $this->db->prepare("DELETE FROM Answer");
            $stmt->execute();

            // Lösche Fragen
            $stmt = $this->db->prepare("DELETE FROM Question");
            $stmt->execute();

            // Lösche Fragenpools
            $stmt = $this->db->prepare("DELETE FROM QuestionPool");
            $stmt->execute();

            $this->db->commit();
            return ['success' => true];
        } catch (PDOException $e) {
            $this->db->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Setzt alle Wertungen und deren Verbindungen zurück
     *
     * @return array Ergebnis mit success-Flag und optionaler Fehlermeldung
     */
    public function resetWertungen(): array
    {
        try {
            $this->db->beginTransaction();

            // Lösche alle Mannschaft-Wertung-Zuordnungen
            $stmt = $this->db->prepare("DELETE FROM MannschaftWertung");
            $stmt->execute();

            // Lösche alle Wertungsklassen
            $stmt = $this->db->prepare("DELETE FROM Wertungsklasse");
            $stmt->execute();

            $this->db->commit();
            return ['success' => true];
        } catch (PDOException $e) {
            $this->db->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Setzt alle Benutzer zurück, außer dem angegebenen Benutzer und Admin-Accounts
     *
     * @param int $currentUserId ID des aktuell angemeldeten Benutzers
     * @return array Ergebnis mit success-Flag, deletedCount und optionaler Fehlermeldung
     */
    public function resetUsers(int $currentUserId): array
    {
        try {
            $this->db->beginTransaction();

            // Zähle zu löschende Benutzer (außer aktuellem Benutzer und Admin-Accounts)
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM User WHERE ID != :currentUserId AND acc_typ != 'Admin'");
            $stmt->bindParam(':currentUserId', $currentUserId, PDO::PARAM_INT);
            $stmt->execute();
            $deletedCount = $stmt->fetchColumn();

            // Lösche alle Benutzer außer dem aktuell angemeldeten und Admin-Accounts
            $stmt = $this->db->prepare("DELETE FROM User WHERE ID != :currentUserId AND acc_typ != 'Admin'");
            $stmt->bindParam(':currentUserId', $currentUserId, PDO::PARAM_INT);
            $stmt->execute();

            $this->db->commit();
            return ['success' => true, 'deletedCount' => $deletedCount];
        } catch (PDOException $e) {
            $this->db->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Setzt alle Wettkampfdaten zurück
     *
     * @param int $currentUserId ID des aktuell angemeldeten Benutzers
     * @return array Ergebnis mit success-Flag und optionaler Fehlermeldung
     */
    public function resetAll(int $currentUserId): array
    {
        try {
            $this->db->beginTransaction();

            // Lösche in der richtigen Reihenfolge wegen Foreign Key Constraints

            // 1. TeamForm-Daten löschen
            $stmt = $this->db->prepare("DELETE FROM TeamFormAnswer");
            $stmt->execute();

            $stmt = $this->db->prepare("DELETE FROM TeamFormInstance");
            $stmt->execute();

            // 2. Mannschaft-Verbindungen löschen
            $stmt = $this->db->prepare("DELETE FROM MannschaftStaffel");
            $stmt->execute();

            $stmt = $this->db->prepare("DELETE FROM MannschaftProtokoll");
            $stmt->execute();

            $stmt = $this->db->prepare("DELETE FROM MannschaftWertung");
            $stmt->execute();

            // 3. Collection-Daten löschen
            $stmt = $this->db->prepare("DELETE FROM CollectionFormToken");
            $stmt->execute();

            $stmt = $this->db->prepare("DELETE FROM CollectionQuestion");
            $stmt->execute();

            $stmt = $this->db->prepare("DELETE FROM FormCollection");
            $stmt->execute();

            // 4. Station-Verbindungen löschen
            $stmt = $this->db->prepare("DELETE FROM StationWeight");
            $stmt->execute();

            // 5. Benutzer-Verbindungen zurücksetzen (außer bei Admins)
            $stmt = $this->db->prepare("UPDATE User SET station_ID = NULL, mannschaft_ID = NULL WHERE (station_ID IS NOT NULL OR mannschaft_ID IS NOT NULL) AND acc_typ != 'Admin'");
            $stmt->execute();

            // 6. Benutzer löschen (außer aktuellem und Admin-Accounts)
            $stmt = $this->db->prepare("DELETE FROM User WHERE ID != :currentUserId AND acc_typ != 'Admin'");
            $stmt->bindParam(':currentUserId', $currentUserId, PDO::PARAM_INT);
            $stmt->execute();

            // 7. Question-Daten löschen
            $stmt = $this->db->prepare("DELETE FROM Answer");
            $stmt->execute();

            $stmt = $this->db->prepare("DELETE FROM Question");
            $stmt->execute();

            $stmt = $this->db->prepare("DELETE FROM QuestionPool");
            $stmt->execute();

            // 8. Protokolle löschen
            $stmt = $this->db->prepare("DELETE FROM Protokoll");
            $stmt->execute();

            // 9. Haupttabellen löschen
            $stmt = $this->db->prepare("DELETE FROM Staffel");
            $stmt->execute();

            $stmt = $this->db->prepare("DELETE FROM Station");
            $stmt->execute();

            $stmt = $this->db->prepare("DELETE FROM Wertungsklasse");
            $stmt->execute();

            $stmt = $this->db->prepare("DELETE FROM Mannschaft");
            $stmt->execute();

            $this->db->commit();
            return ['success' => true];
        } catch (PDOException $e) {
            $this->db->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}