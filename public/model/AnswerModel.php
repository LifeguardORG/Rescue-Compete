<?php
namespace Answer;

use PDO;
use PDOException;

class AnswerModel {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function create(array $entry): ?int {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO Answer (Question_ID, Text, IsCorrect) 
                 VALUES (:question_id, :text, :is_correct)"
            );
            $stmt->bindParam(':question_id', $entry['question_id'], PDO::PARAM_INT);
            $stmt->bindParam(':text', $entry['text'], PDO::PARAM_STR);
            $stmt->bindParam(':is_correct', $entry['is_correct'], PDO::PARAM_INT); // Geändert zu INT
            $stmt->execute();
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error in AnswerModel::create: " . $e->getMessage());
            return null;
        }
    }

    public function read($id = null): ?array {
        try {
            if ($id === null) {
                $stmt = $this->db->query("SELECT * FROM Answer ORDER BY Question_ID, ID");
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $stmt = $this->db->prepare("SELECT * FROM Answer WHERE ID = :id");
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            error_log("Error in AnswerModel::read: " . $e->getMessage());
            return null;
        }
    }

    public function getAnswersByQuestion($questionId): array {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM Answer 
                 WHERE Question_ID = :question_id 
                 ORDER BY ID"
            );
            $stmt->bindParam(':question_id', $questionId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in AnswerModel::getAnswersByQuestion: " . $e->getMessage());
            return [];
        }
    }

    public function update($id, array $entry): bool {
        try {
            $stmt = $this->db->prepare(
                "UPDATE Answer 
                 SET Question_ID = :question_id, Text = :text, IsCorrect = :is_correct 
                 WHERE ID = :id"
            );
            $stmt->bindParam(':question_id', $entry['question_id'], PDO::PARAM_INT);
            $stmt->bindParam(':text', $entry['text'], PDO::PARAM_STR);
            $stmt->bindParam(':is_correct', $entry['is_correct'], PDO::PARAM_INT); // Geändert zu INT
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in AnswerModel::update: " . $e->getMessage());
            return false;
        }
    }

    public function delete($id): bool {
        try {
            // Transaktion starten
            $this->db->beginTransaction();

            // Zuerst alle TeamFormAnswer-Einträge löschen, die auf diese Antwort verweisen
            $stmtDeleteTeamFormAnswers = $this->db->prepare(
                "DELETE FROM TeamFormAnswer WHERE answer_ID = :id"
            );
            $stmtDeleteTeamFormAnswers->bindParam(':id', $id, PDO::PARAM_INT);
            $stmtDeleteTeamFormAnswers->execute();

            // Dann die Antwort selbst löschen
            $stmt = $this->db->prepare("DELETE FROM Answer WHERE ID = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $result = $stmt->execute();

            // Transaktion abschließen
            $this->db->commit();

            return $result;
        } catch (PDOException $e) {
            // Rollback bei Fehler
            $this->db->rollBack();
            error_log("Error in AnswerModel::delete: " . $e->getMessage());
            return false;
        }
    }

    public function deleteByQuestionId($questionId): bool {
        try {
            // Transaktion starten
            $this->db->beginTransaction();

            // Zuerst alle TeamFormAnswer-Einträge löschen, die auf Antworten dieser Frage verweisen
            $stmtDeleteTeamFormAnswers = $this->db->prepare(
                "DELETE FROM TeamFormAnswer 
                 WHERE answer_ID IN (
                     SELECT ID FROM Answer WHERE Question_ID = :question_id
                 )"
            );
            $stmtDeleteTeamFormAnswers->bindParam(':question_id', $questionId, PDO::PARAM_INT);
            $stmtDeleteTeamFormAnswers->execute();

            // Dann alle Antworten zu dieser Frage löschen
            $stmt = $this->db->prepare("DELETE FROM Answer WHERE Question_ID = :question_id");
            $stmt->bindParam(':question_id', $questionId, PDO::PARAM_INT);
            $result = $stmt->execute();

            // Transaktion abschließen
            $this->db->commit();

            return $result;
        } catch (PDOException $e) {
            // Rollback bei Fehler
            $this->db->rollBack();
            error_log("Error in AnswerModel::deleteByQuestionId: " . $e->getMessage());
            return false;
        }
    }

    public function countCorrectAnswers($questionId): int {
        try {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) FROM Answer 
                 WHERE Question_ID = :question_id AND IsCorrect = 1"
            );
            $stmt->bindParam(':question_id', $questionId, PDO::PARAM_INT);
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error in AnswerModel::countCorrectAnswers: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Prüft, ob eine Antwort korrekt ist.
     *
     * @param int $answerId ID der Antwort
     * @return bool True wenn korrekt, false sonst
     */
    public function isCorrectAnswer($answerId): bool {
        try {
            $stmt = $this->db->prepare(
                "SELECT IsCorrect FROM Answer WHERE ID = :id"
            );
            $stmt->bindParam(':id', $answerId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchColumn();
            return (bool)$result;
        } catch (PDOException $e) {
            error_log("Error in AnswerModel::isCorrectAnswer: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Holt alle korrekten Antworten zu einer Frage.
     *
     * @param int $questionId ID der Frage
     * @return array Liste der korrekten Antworten
     */
    public function getCorrectAnswersByQuestion($questionId): array {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM Answer 
                 WHERE Question_ID = :question_id AND IsCorrect = 1 
                 ORDER BY ID"
            );
            $stmt->bindParam(':question_id', $questionId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in AnswerModel::getCorrectAnswersByQuestion: " . $e->getMessage());
            return [];
        }
    }
}