<?php
namespace Question;

use PDO;
use PDOException;

class QuestionModel {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Erstellt eine neue Frage in der Datenbank.
     *
     * @param array $entry Enthält 'pool_id' (oder 'formular_id') und 'text'.
     * @return int|null Die ID der neuen Frage oder null bei Fehler.
     */
    public function create(array $entry): ?int {
        try {
            // Unterstützt sowohl 'pool_id' als auch 'formular_id' für Abwärtskompatibilität
            $poolId = $entry['pool_id'] ?? ($entry['formular_id'] ?? null);

            $stmt = $this->db->prepare(
                "INSERT INTO Question (QuestionPool_ID, Text) 
                 VALUES (:pool_id, :text)"
            );
            $stmt->bindParam(':pool_id', $poolId, PDO::PARAM_INT);
            $stmt->bindParam(':text', $entry['text'], PDO::PARAM_STR);
            $stmt->execute();
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error in QuestionModel::create: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Liest eine oder alle Fragen aus der Datenbank.
     *
     * @param int|null $id Optional: ID einer spezifischen Frage. Wenn null, werden alle Fragen zurückgegeben.
     * @return array|null Ein Array mit Fragen oder null bei Fehler.
     */
    public function read($id = null): ?array {
        try {
            if ($id === null) {
                // Alle Fragen mit Pool-Informationen
                $stmt = $this->db->query(
                    "SELECT q.*, qp.Name as pool_name 
                     FROM Question q 
                     JOIN QuestionPool qp ON q.QuestionPool_ID = qp.ID 
                     ORDER BY q.QuestionPool_ID, q.ID"
                );
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                // Spezifische Frage
                $stmt = $this->db->prepare(
                    "SELECT q.*, qp.Name as pool_name 
                     FROM Question q 
                     JOIN QuestionPool qp ON q.QuestionPool_ID = qp.ID 
                     WHERE q.ID = :id"
                );
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            error_log("Error in QuestionModel::read: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Liefert alle Fragen zurück.
     *
     * @return array Array aller Fragen
     */
    public function getAllQuestions(): array {
        try {
            $stmt = $this->db->query(
                "SELECT q.*, qp.Name as pool_name 
                 FROM Question q 
                 LEFT JOIN QuestionPool qp ON q.QuestionPool_ID = qp.ID 
                 ORDER BY q.ID"
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in QuestionModel::getAllQuestions: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Aktualisiert eine bestehende Frage.
     *
     * @param int $id ID der zu aktualisierenden Frage
     * @param array $entry Neue Werte für 'pool_id' und 'text'
     * @return bool Gibt true zurück, wenn die Aktualisierung erfolgreich war, sonst false.
     */
    public function update($id, array $entry): bool {
        try {
            $stmt = $this->db->prepare(
                "UPDATE Question 
                 SET QuestionPool_ID = :pool_id, Text = :text 
                 WHERE ID = :id"
            );
            // Unterstützt sowohl 'pool_id' als auch 'poolId' für Flexibilität
            $poolId = $entry['pool_id'] ?? ($entry['poolId'] ?? null);

            $stmt->bindParam(':pool_id', $poolId, PDO::PARAM_INT);
            $stmt->bindParam(':text', $entry['text'], PDO::PARAM_STR);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in QuestionModel::update: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Löscht eine Frage und alle zugehörigen Antworten und Verknüpfungen.
     *
     * @param int $id ID der zu löschenden Frage
     * @return bool Gibt true zurück, wenn die Löschung erfolgreich war, sonst false.
     */
    public function delete($id): bool {
        try {
            // Transaktion starten
            $this->db->beginTransaction();

            // Zuerst alle TeamFormAnswer-Einträge löschen, die auf diese Frage verweisen
            $stmtDeleteTeamFormAnswers = $this->db->prepare(
                "DELETE FROM TeamFormAnswer 
                 WHERE answer_ID IN (
                     SELECT ID FROM Answer WHERE Question_ID = :id
                 )"
            );
            $stmtDeleteTeamFormAnswers->bindParam(':id', $id, PDO::PARAM_INT);
            $stmtDeleteTeamFormAnswers->execute();

            // Dann alle zugehörigen Antworten löschen
            $stmtDeleteAnswers = $this->db->prepare(
                "DELETE FROM Answer WHERE Question_ID = :id"
            );
            $stmtDeleteAnswers->bindParam(':id', $id, PDO::PARAM_INT);
            $stmtDeleteAnswers->execute();

            // Alle Verknüpfungen in CollectionQuestion löschen (neue Struktur)
            $stmtDeleteCollectionQuestions = $this->db->prepare(
                "DELETE FROM CollectionQuestion WHERE question_ID = :id"
            );
            $stmtDeleteCollectionQuestions->bindParam(':id', $id, PDO::PARAM_INT);
            $stmtDeleteCollectionQuestions->execute();

            // Falls FormQuestion-Tabelle noch existiert (Abwärtskompatibilität)
            try {
                $stmtDeleteFormQuestions = $this->db->prepare(
                    "DELETE FROM FormQuestion WHERE Question_ID = :id"
                );
                $stmtDeleteFormQuestions->bindParam(':id', $id, PDO::PARAM_INT);
                $stmtDeleteFormQuestions->execute();
            } catch (PDOException $e) {
                // FormQuestion-Tabelle existiert nicht mehr - das ist OK
                error_log("FormQuestion table not found (expected for new structure): " . $e->getMessage());
            }

            // Dann die Frage selbst löschen
            $stmtDeleteQuestion = $this->db->prepare(
                "DELETE FROM Question WHERE ID = :id"
            );
            $stmtDeleteQuestion->bindParam(':id', $id, PDO::PARAM_INT);
            $result = $stmtDeleteQuestion->execute();

            // Transaktion abschließen
            $this->db->commit();

            return $result;
        } catch (PDOException $e) {
            // Rollback bei Fehler
            $this->db->rollBack();
            error_log("Error in QuestionModel::delete: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Holt alle Fragen eines bestimmten Fragenpools.
     *
     * @param int $poolId ID des Fragenpools
     * @return array Liste der Fragen des Pools
     */
    public function getQuestionsByPool($poolId): array {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM Question 
                 WHERE QuestionPool_ID = :pool_id 
                 ORDER BY ID"
            );
            $stmt->bindParam(':pool_id', $poolId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in QuestionModel::getQuestionsByPool: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Sucht eine Frage anhand ihres Textes.
     *
     * @param string $text Der zu suchende Fragetext
     * @return array|null Die gefundene Frage oder null, wenn nicht gefunden
     */
    public function getQuestionByText(string $text): ?array {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM Question 
                 WHERE Text = :text 
                 LIMIT 1"
            );
            $stmt->bindParam(':text', $text, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Error in QuestionModel::getQuestionByText: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Holt zufällige Fragen aus einem bestimmten Fragenpool.
     *
     * @param int $poolId ID des Fragenpools
     * @param int $count Anzahl der zu holenden Fragen
     * @return array Liste zufälliger Fragen aus dem Pool
     */
    public function getRandomQuestionsFromPool($poolId, $count): array {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM Question 
                 WHERE QuestionPool_ID = :pool_id 
                 ORDER BY RAND() 
                 LIMIT :count"
            );
            $stmt->bindParam(':pool_id', $poolId, PDO::PARAM_INT);
            $stmt->bindParam(':count', $count, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in QuestionModel::getRandomQuestionsFromPool: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Holt alle Fragen eines bestimmten Formulars (Abwärtskompatibilität).
     *
     * @param int $formularId ID des Formulars
     * @return array Liste der Fragen des Formulars
     * @deprecated Diese Methode ist für Abwärtskompatibilität - verwende getQuestionsByPool()
     */
    public function getQuestionsByFormular($formularId): array {
        try {
            // Versuche zuerst die alte FormQuestion-Tabelle
            $stmt = $this->db->prepare(
                "SELECT q.* FROM Question q 
                 JOIN FormQuestion fq ON q.ID = fq.Question_ID
                 WHERE fq.Form_ID = :formular_id 
                 ORDER BY q.ID"
            );
            $stmt->bindParam(':formular_id', $formularId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in QuestionModel::getQuestionsByFormular (legacy): " . $e->getMessage());
            // Fallback: verwende getQuestionsByPool als Ersatz
            return $this->getQuestionsByPool($formularId);
        }
    }
}