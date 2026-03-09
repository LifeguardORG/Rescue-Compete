<?php
namespace QuestionForm;

use PDO;
use PDOException;

class FormManagementModel
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Erstellt ein neues Frageformular in der Datenbank.
     *
     * @param array $entry Array mit 'titel' und 'station_id'
     * @return int|null Die ID des neuen Eintrags oder null bei einem Fehler
     */
    public function create($entry)
    {
        try {
            $query = "INSERT INTO `QuestionForm` (Titel, Station_ID) 
                      VALUES (:Titel, :Station_ID)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':Titel', $entry['titel'], PDO::PARAM_STR);
            $stmt->bindParam(':Station_ID', $entry['station_id'], PDO::PARAM_INT);
            if ($stmt->execute()) {
                return $this->db->lastInsertId();
            }
            return null;
        } catch (PDOException $e) {
            error_log("Error in FormManagementModel::create: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Liest ein oder mehrere Frageformulare aus der Datenbank.
     *
     * @param int|null $id Optional: ID eines spezifischen Formulars. Wenn null, werden alle Formulare zurückgegeben.
     * @return array Ein Array mit Formulardaten
     */
    public function read($id = null): array
    {
        try {
            if ($id === null) {
                $stmt = $this->db->query("SELECT qf.ID, qf.Titel AS titel, qf.Station_ID, s.name AS station_name 
                    FROM `QuestionForm` qf 
                    JOIN Station s ON qf.Station_ID = s.ID");
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $stmt = $this->db->prepare("SELECT * FROM `QuestionForm` WHERE ID = :id");
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
            }
        } catch (PDOException $e) {
            error_log("Error in FormManagementModel::read: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Löscht ein Frageformular und alle zugehörigen Verknüpfungen.
     *
     * @param int $id ID des zu löschenden Formulars
     * @return bool True bei Erfolg, false bei Fehler
     */
    public function delete(int $id): bool
    {
        try {
            // Beginne Transaktion
            $this->db->beginTransaction();

            // Zuerst alle Verknüpfungen in FormQuestion löschen
            $stmtDeleteFormQuestions = $this->db->prepare(
                "DELETE FROM FormQuestion WHERE Form_ID = :id"
            );
            $stmtDeleteFormQuestions->bindParam(':id', $id, PDO::PARAM_INT);
            $stmtDeleteFormQuestions->execute();

            // Alle TeamForm-Einträge für dieses Formular löschen
            $stmtDeleteTeamForms = $this->db->prepare(
                "DELETE FROM TeamForm WHERE form_ID = :id"
            );
            $stmtDeleteTeamForms->bindParam(':id', $id, PDO::PARAM_INT);
            $stmtDeleteTeamForms->execute();

            // Dann das Formular selbst löschen
            $stmtDeleteForm = $this->db->prepare("DELETE FROM `QuestionForm` WHERE ID = :id");
            $stmtDeleteForm->bindParam(':id', $id, PDO::PARAM_INT);
            $result = $stmtDeleteForm->execute();

            // Commit der Transaktion
            $this->db->commit();

            return $result;
        } catch (PDOException $e) {
            // Rollback im Fehlerfall
            $this->db->rollBack();
            error_log("Error in FormManagementModel::delete: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Erstellt ein neues Frageformular mit Token in der Datenbank.
     *
     * @param array $entry Array mit den Eintragsdaten (titel, station_id, token)
     * @return int|null Die ID des neuen Eintrags oder null bei einem Fehler
     */
    public function createWithToken(array $entry): ?int
    {
        try {
            $query = "INSERT INTO `QuestionForm` (Titel, Station_ID, time_limit) 
                  VALUES (:Titel, :Station_ID, :time_limit)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':Titel', $entry['titel'], PDO::PARAM_STR);
            $stmt->bindParam(':Station_ID', $entry['station_id'], PDO::PARAM_INT);

            // Optional: Zeitlimit (Standard: 180 Sekunden)
            $timeLimit = isset($entry['time_limit']) ? intval($entry['time_limit']) : 180;
            $stmt->bindParam(':time_limit', $timeLimit, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $formId = $this->db->lastInsertId();

                // Wenn ein Token übergeben wurde, erstellen wir einen TeamForm-Eintrag
                if (isset($entry['token'])) {
                    $teamFormStmt = $this->db->prepare(
                        "INSERT INTO TeamForm (form_ID, token) VALUES (:form_id, :token)"
                    );
                    $teamFormStmt->bindParam(':form_id', $formId, PDO::PARAM_INT);
                    $teamFormStmt->bindParam(':token', $entry['token'], PDO::PARAM_STR);
                    $teamFormStmt->execute();
                }

                return $formId;
            }
            return null;
        } catch (PDOException $e) {
            error_log("Error in FormManagementModel::createWithToken: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Prüft, ob ein Token bereits in der Datenbank existiert.
     *
     * @param string $token Das zu prüfende Token
     * @return bool True, wenn das Token existiert, sonst false
     */
    public function tokenExists(string $token = ""): bool
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) FROM TeamForm WHERE token = :token"
            );
            $stmt->bindParam(':token', $token, PDO::PARAM_STR);
            $stmt->execute();
            return (int)$stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error in FormManagementModel::tokenExists: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Fügt eine Frage zu einem Formular hinzu.
     *
     * @param int $questionId ID der Frage
     * @param int $formId ID des Formulars
     * @return bool True bei Erfolg, sonst false
     */
    public function addQuestionToForm($questionId, $formId): bool
    {
        try {
            // Prüfen, ob die Verknüpfung bereits existiert
            $checkStmt = $this->db->prepare(
                "SELECT COUNT(*) FROM FormQuestion 
                 WHERE Form_ID = :form_id AND Question_ID = :question_id"
            );
            $checkStmt->bindParam(':form_id', $formId, PDO::PARAM_INT);
            $checkStmt->bindParam(':question_id', $questionId, PDO::PARAM_INT);
            $checkStmt->execute();

            if ($checkStmt->fetchColumn() > 0) {
                // Verknüpfung existiert bereits
                return true;
            }

            // Neue Verknüpfung erstellen
            $stmt = $this->db->prepare(
                "INSERT INTO FormQuestion (Form_ID, Question_ID) 
                 VALUES (:form_id, :question_id)"
            );
            $stmt->bindParam(':form_id', $formId, PDO::PARAM_INT);
            $stmt->bindParam(':question_id', $questionId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in FormManagementModel::addQuestionToForm: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Zählt die Anzahl der Fragen in einem Formular.
     *
     * @param int $formId ID des Formulars
     * @return int Anzahl der Fragen
     */
    public function getFormQuestionCount($formId): int
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) FROM FormQuestion 
                 WHERE Form_ID = :form_id"
            );
            $stmt->bindParam(':form_id', $formId, PDO::PARAM_INT);
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error in FormManagementModel::getFormQuestionCount: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Holt alle Token für Formulare aus der TeamForm-Tabelle.
     *
     * @return array Assoziatives Array mit form_ID als Schlüssel und token als Wert
     */
    public function getFormTokens(): array
    {
        try {
            $stmt = $this->db->query(
                "SELECT form_ID, token 
                 FROM TeamForm 
                 WHERE token IS NOT NULL 
                 GROUP BY form_ID"
            );

            $tokens = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $tokens[$row['form_ID']] = $row['token'];
            }

            return $tokens;
        } catch (PDOException $e) {
            error_log("Error in FormManagementModel::getFormTokens: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Holt alle Formulare mit einem bestimmten Titel
     *
     * @param string $title Der zu suchende Titel
     * @return array Ein Array mit Formularen, die den angegebenen Titel haben
     */
    public function getFormsByTitle(string $title): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT qf.ID, qf.Titel, qf.Station_ID, s.name AS station_name 
             FROM `QuestionForm` qf 
             JOIN Station s ON qf.Station_ID = s.ID
             WHERE qf.Titel = :title"
            );
            $stmt->bindParam(':title', $title, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in FormManagementModel::getFormsByTitle: " . $e->getMessage());
            return [];
        }
    }
}