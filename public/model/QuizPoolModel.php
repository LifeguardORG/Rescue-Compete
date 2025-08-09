<?php
namespace QuestionPool;

use PDO;
use PDOException;

class QuizPoolModel {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function create(array $entry): ?int {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO QuestionPool (Name) 
                 VALUES (:name)"
            );
            $stmt->bindParam(':name', $entry['name'], PDO::PARAM_STR);
            $stmt->execute();
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error in QuizPoolModel::create: " . $e->getMessage());
            return null;
        }
    }

    public function read($id = null): ?array {
        try {
            if ($id === null) {
                $stmt = $this->db->query("SELECT * FROM QuestionPool ORDER BY Name");
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $stmt = $this->db->prepare("SELECT * FROM QuestionPool WHERE ID = :id");
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            error_log("Error in QuizPoolModel::read: " . $e->getMessage());
            return null;
        }
    }

    public function update($id, array $entry): bool {
        try {
            $stmt = $this->db->prepare(
                "UPDATE QuestionPool 
                 SET Name = :name
                 WHERE ID = :id"
            );
            $stmt->bindParam(':name', $entry['name'], PDO::PARAM_STR);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in QuizPoolModel::update: " . $e->getMessage());
            return false;
        }
    }

    public function delete($id): bool {
        try {
            // Beginn einer Transaktion
            $this->db->beginTransaction();

            // Zuerst alle TeamFormAnswer-Einträge löschen, die zu Fragen dieses Pools gehören
            $stmtDeleteTeamFormAnswers = $this->db->prepare(
                "DELETE FROM TeamFormAnswer 
                 WHERE answer_ID IN (
                     SELECT a.ID FROM Answer a
                     JOIN Question q ON a.Question_ID = q.ID
                     WHERE q.QuestionPool_ID = :id
                 )"
            );
            $stmtDeleteTeamFormAnswers->bindParam(':id', $id, PDO::PARAM_INT);
            $stmtDeleteTeamFormAnswers->execute();

            // Dann alle Antworten für Fragen aus diesem Pool löschen
            $stmtDeleteAnswers = $this->db->prepare(
                "DELETE FROM Answer 
                 WHERE Question_ID IN (SELECT ID FROM Question WHERE QuestionPool_ID = :id)"
            );
            $stmtDeleteAnswers->bindParam(':id', $id, PDO::PARAM_INT);
            $stmtDeleteAnswers->execute();

            // Alle CollectionQuestion-Verknüpfungen löschen (neue Struktur)
            $stmtDeleteCollectionQuestions = $this->db->prepare(
                "DELETE FROM CollectionQuestion 
                 WHERE question_ID IN (SELECT ID FROM Question WHERE QuestionPool_ID = :id)"
            );
            $stmtDeleteCollectionQuestions->bindParam(':id', $id, PDO::PARAM_INT);
            $stmtDeleteCollectionQuestions->execute();

            // Falls FormQuestion-Tabelle noch existiert (Abwärtskompatibilität)
            try {
                $stmtDeleteFormQuestions = $this->db->prepare(
                    "DELETE FROM FormQuestion 
                     WHERE Question_ID IN (SELECT ID FROM Question WHERE QuestionPool_ID = :id)"
                );
                $stmtDeleteFormQuestions->bindParam(':id', $id, PDO::PARAM_INT);
                $stmtDeleteFormQuestions->execute();
            } catch (PDOException $e) {
                // FormQuestion-Tabelle existiert nicht mehr - das ist OK
                error_log("FormQuestion table not found (expected for new structure): " . $e->getMessage());
            }

            // Dann alle Fragen des Pools löschen
            $stmtDeleteQuestions = $this->db->prepare(
                "DELETE FROM Question WHERE QuestionPool_ID = :id"
            );
            $stmtDeleteQuestions->bindParam(':id', $id, PDO::PARAM_INT);
            $stmtDeleteQuestions->execute();

            // Schließlich den Pool selbst löschen
            $stmtDeletePool = $this->db->prepare(
                "DELETE FROM QuestionPool WHERE ID = :id"
            );
            $stmtDeletePool->bindParam(':id', $id, PDO::PARAM_INT);
            $result = $stmtDeletePool->execute();

            // Commit der Transaktion
            $this->db->commit();
            return $result;
        } catch (PDOException $e) {
            // Rollback bei Fehler
            $this->db->rollBack();
            error_log("Error in QuizPoolModel::delete: " . $e->getMessage());
            return false;
        }
    }

    public function getPoolByName($name): ?array {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM QuestionPool 
                 WHERE Name = :name 
                 LIMIT 1"
            );
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Error in QuizPoolModel::getPoolByName: " . $e->getMessage());
            return null;
        }
    }

    public function getQuestionCount($poolId): int {
        try {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) FROM Question 
                 WHERE QuestionPool_ID = :id"
            );
            $stmt->bindParam(':id', $poolId, PDO::PARAM_INT);
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error in QuizPoolModel::getQuestionCount: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Holt alle Fragenpools mit zusätzlichen Informationen.
     *
     * @return array Array aller Fragenpools mit Statistiken
     */
    public function getAllPoolsWithStats(): array {
        try {
            $stmt = $this->db->query(
                "SELECT qp.*, 
                        COUNT(q.ID) as question_count,
                        COUNT(CASE WHEN a.IsCorrect = 1 THEN 1 END) as correct_answers_count
                 FROM QuestionPool qp
                 LEFT JOIN Question q ON qp.ID = q.QuestionPool_ID
                 LEFT JOIN Answer a ON q.ID = a.Question_ID
                 GROUP BY qp.ID, qp.Name
                 ORDER BY qp.Name"
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in QuizPoolModel::getAllPoolsWithStats: " . $e->getMessage());
            return [];
        }
    }
}