<?php

namespace FormCollection;

use PDO;
use PDOException;

/**
 * Model-Klasse für die Verwaltung von TeamFormInstances
 * Verwaltet die individuellen Formular-Instanzen für Teams
 */
class TeamFormInstanceModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Erstellt eine neue TeamFormInstance
     *
     * @param array $instanceData Array mit team_ID, collection_ID, formNumber
     * @return int|null ID der neuen Instance oder null bei Fehler
     */
    public function createInstance(array $instanceData): ?int
    {
        try {
            // Prüfen ob Instance bereits existiert
            $existingInstance = $this->getInstanceByTeamAndForm(
                $instanceData['team_ID'],
                $instanceData['collection_ID'],
                $instanceData['formNumber']
            );

            if ($existingInstance) {
                return $existingInstance['ID'];
            }

            $stmt = $this->db->prepare(
                "INSERT INTO TeamFormInstance (team_ID, collection_ID, formNumber, assignedQuestions) 
                 VALUES (:teamId, :collectionId, :formNumber, :assignedQuestions)"
            );

            $stmt->execute([
                ':teamId' => $instanceData['team_ID'],
                ':collectionId' => $instanceData['collection_ID'],
                ':formNumber' => $instanceData['formNumber'],
                ':assignedQuestions' => $instanceData['assignedQuestions'] ?? '[]'
            ]);

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error in TeamFormInstanceModel::createInstance: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Liest eine oder mehrere TeamFormInstances aus der Datenbank
     *
     * @param int|null $instanceId Optional: ID einer spezifischen Instance
     * @return array|null Instance-Daten oder null bei Fehler
     */
    public function readInstance(?int $instanceId = null): ?array
    {
        try {
            if ($instanceId === null) {
                // Alle Instances mit Team- und Collection-Informationen über View
                $stmt = $this->db->query("SELECT * FROM TeamFormStatistics ORDER BY Teamname, collectionName, formNumber");
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                // Spezifische Instance mit Details
                $stmt = $this->db->prepare(
                    "SELECT tfi.*, fc.name as collectionName, fc.timeLimit, fc.totalQuestions,
                            m.Teamname, m.Kreisverband, s.name as stationName
                     FROM TeamFormInstance tfi
                     JOIN FormCollection fc ON tfi.collection_ID = fc.ID
                     JOIN Mannschaft m ON tfi.team_ID = m.ID
                     LEFT JOIN Station s ON fc.station_ID = s.ID
                     WHERE tfi.ID = :instanceId"
                );
                $stmt->execute([':instanceId' => $instanceId]);
                return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
            }
        } catch (PDOException $e) {
            error_log("Error in TeamFormInstanceModel::readInstance: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Aktualisiert eine bestehende TeamFormInstance
     *
     * @param int $instanceId ID der zu aktualisierenden Instance
     * @param array $instanceData Neue Instance-Daten
     * @return bool True bei Erfolg, false bei Fehler
     */
    public function updateInstance(int $instanceId, array $instanceData): bool
    {
        try {
            $fields = [];
            $params = [':instanceId' => $instanceId];

            // Dynamisch zu aktualisierende Felder bestimmen
            $allowedFields = ['completed', 'points', 'startTime', 'completionDate', 'assignedQuestions'];

            foreach ($allowedFields as $field) {
                if (array_key_exists($field, $instanceData)) {
                    $fields[] = "{$field} = :{$field}";
                    $params[":{$field}"] = $instanceData[$field];
                }
            }

            if (empty($fields)) {
                return false; // Keine Felder zum Aktualisieren
            }

            $sql = "UPDATE TeamFormInstance SET " . implode(', ', $fields) . " WHERE ID = :instanceId";
            $stmt = $this->db->prepare($sql);

            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Error in TeamFormInstanceModel::updateInstance: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Löscht eine TeamFormInstance und alle zugehörigen Antworten
     *
     * @param int $instanceId ID der zu löschenden Instance
     * @return bool True bei Erfolg, false bei Fehler
     */
    public function deleteInstance(int $instanceId): bool
    {
        try {
            $this->db->beginTransaction();

            // Zuerst alle Antworten löschen (CASCADE sollte das automatisch machen)
            $stmt = $this->db->prepare("DELETE FROM TeamFormAnswer WHERE teamFormInstance_ID = :instanceId");
            $stmt->execute([':instanceId' => $instanceId]);

            // Dann die Instance selbst löschen
            $stmt = $this->db->prepare("DELETE FROM TeamFormInstance WHERE ID = :instanceId");
            $result = $stmt->execute([':instanceId' => $instanceId]);

            $this->db->commit();
            return $result;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error in TeamFormInstanceModel::deleteInstance: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Holt eine Instance anhand von Team, Collection und Formularnummer
     *
     * @param int $teamId ID des Teams
     * @param int $collectionId ID der Collection
     * @param int $formNumber Nummer des Formulars
     * @return array|null Instance-Daten oder null wenn nicht gefunden
     */
    public function getInstanceByTeamAndForm(int $teamId, int $collectionId, int $formNumber): ?array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT tfi.*, fc.name as collectionName, fc.timeLimit,
                        m.Teamname, m.Kreisverband
                 FROM TeamFormInstance tfi
                 JOIN FormCollection fc ON tfi.collection_ID = fc.ID
                 JOIN Mannschaft m ON tfi.team_ID = m.ID
                 WHERE tfi.team_ID = :teamId AND tfi.collection_ID = :collectionId AND tfi.formNumber = :formNumber"
            );
            $stmt->execute([
                ':teamId' => $teamId,
                ':collectionId' => $collectionId,
                ':formNumber' => $formNumber
            ]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("Error in TeamFormInstanceModel::getInstanceByTeamAndForm: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Holt eine Instance anhand des Tokens
     *
     * @param string $token Token der Instance
     * @return array|null Instance-Daten oder null wenn nicht gefunden
     */
    public function getInstanceByToken(string $token): ?array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT tfi.*, fc.name as collectionName, fc.timeLimit, fc.totalQuestions,
                        m.Teamname, m.Kreisverband, s.name as stationName
                 FROM TeamFormInstance tfi
                 JOIN FormCollection fc ON tfi.collection_ID = fc.ID
                 JOIN Mannschaft m ON tfi.team_ID = m.ID
                 LEFT JOIN Station s ON fc.station_ID = s.ID
                 WHERE tfi.token = :token"
            );
            $stmt->execute([':token' => $token]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("Error in TeamFormInstanceModel::getInstanceByToken: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Holt alle Instances für ein bestimmtes Team
     *
     * @param int $teamId ID des Teams
     * @return array Array mit Instance-Daten
     */
    public function getInstancesByTeam(int $teamId): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT tfi.*, fc.name as collectionName, fc.timeLimit,
                        s.name as stationName
                 FROM TeamFormInstance tfi
                 JOIN FormCollection fc ON tfi.collection_ID = fc.ID
                 LEFT JOIN Station s ON fc.station_ID = s.ID
                 WHERE tfi.team_ID = :teamId
                 ORDER BY fc.name, tfi.formNumber"
            );
            $stmt->execute([':teamId' => $teamId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in TeamFormInstanceModel::getInstancesByTeam: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Holt alle Instances für eine bestimmte Collection
     *
     * @param int $collectionId ID der Collection
     * @return array Array mit Instance-Daten
     */
    public function getInstancesByCollection(int $collectionId): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT tfi.*, m.Teamname, m.Kreisverband
                 FROM TeamFormInstance tfi
                 JOIN Mannschaft m ON tfi.team_ID = m.ID
                 WHERE tfi.collection_ID = :collectionId
                 ORDER BY m.Teamname, tfi.formNumber"
            );
            $stmt->execute([':collectionId' => $collectionId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in TeamFormInstanceModel::getInstancesByCollection: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Startet den Timer für eine Instance
     *
     * @param int $instanceId ID der Instance
     * @return bool True bei Erfolg, false bei Fehler
     */
    public function startTimer(int $instanceId): bool
    {
        try {
            $stmt = $this->db->prepare(
                "UPDATE TeamFormInstance SET startTime = NOW() 
                 WHERE ID = :instanceId AND startTime IS NULL"
            );
            $stmt->execute([':instanceId' => $instanceId]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error in TeamFormInstanceModel::startTimer: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Schließt eine Instance ab und berechnet die Punkte
     *
     * @param int $instanceId ID der Instance
     * @return bool True bei Erfolg, false bei Fehler
     */
    public function completeInstance(int $instanceId): bool
    {
        try {
            // Stored Procedure verwenden für sichere Abschließung
            $stmt = $this->db->prepare("CALL autoSubmitExpiredForm(:instanceId)");
            $stmt->execute([':instanceId' => $instanceId]);
            return true;
        } catch (PDOException $e) {
            error_log("Error in TeamFormInstanceModel::completeInstance: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Speichert eine Antwort für eine Instance
     *
     * @param int $instanceId ID der Instance
     * @param int $questionId ID der Frage
     * @param int $answerId ID der Antwort
     * @return bool True bei Erfolg, false bei Fehler
     */
    public function saveAnswer(int $instanceId, int $questionId, int $answerId): bool
    {
        try {
            // Stored Procedure verwenden für sichere Antwort-Speicherung
            $stmt = $this->db->prepare("CALL saveFormAnswer(:instanceId, :questionId, :answerId)");
            $stmt->execute([
                ':instanceId' => $instanceId,
                ':questionId' => $questionId,
                ':answerId' => $answerId
            ]);
            return true;
        } catch (PDOException $e) {
            error_log("Error in TeamFormInstanceModel::saveAnswer: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Holt alle Antworten für eine Instance
     *
     * @param int $instanceId ID der Instance
     * @return array Array mit Antwort-Daten
     */
    public function getAnswersByInstance(int $instanceId): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT tfa.*, q.Text as questionText, a.Text as answerText, a.IsCorrect
                 FROM TeamFormAnswer tfa
                 JOIN Question q ON tfa.question_ID = q.ID
                 JOIN Answer a ON tfa.answer_ID = a.ID
                 WHERE tfa.teamFormInstance_ID = :instanceId
                 ORDER BY tfa.question_ID"
            );
            $stmt->execute([':instanceId' => $instanceId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in TeamFormInstanceModel::getAnswersByInstance: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Holt die zugewiesenen Fragen für eine Instance
     *
     * @param int $instanceId ID der Instance
     * @return array Array mit Fragen-Daten
     */
    public function getAssignedQuestions(int $instanceId): array
    {
        try {
            $instance = $this->readInstance($instanceId);
            if (!$instance || empty($instance['assignedQuestions'])) {
                return [];
            }

            $questionIds = json_decode($instance['assignedQuestions'], true);
            if (!is_array($questionIds) || empty($questionIds)) {
                return [];
            }

            // Fragen mit Antworten laden
            $placeholders = str_repeat('?,', count($questionIds) - 1) . '?';
            $stmt = $this->db->prepare(
                "SELECT q.*, qp.Name as poolName
                 FROM Question q
                 JOIN QuestionPool qp ON q.QuestionPool_ID = qp.ID
                 WHERE q.ID IN ({$placeholders})
                 ORDER BY FIELD(q.ID, " . implode(',', $questionIds) . ")"
            );
            $stmt->execute($questionIds);
            $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Antworten für jede Frage laden
            foreach ($questions as &$question) {
                $stmt = $this->db->prepare(
                    "SELECT * FROM Answer WHERE Question_ID = :questionId ORDER BY ID"
                );
                $stmt->execute([':questionId' => $question['ID']]);
                $question['answers'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            return $questions;
        } catch (PDOException $e) {
            error_log("Error in TeamFormInstanceModel::getAssignedQuestions: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Prüft abgelaufene Instances und schließt sie ab
     *
     * @return array Statistik der verarbeiteten Instances
     */
    public function processExpiredInstances(): array
    {
        try {
            $stats = ['processed' => 0, 'expired' => 0, 'errors' => 0];

            // Abgelaufene Instances finden
            $stmt = $this->db->prepare(
                "SELECT tfi.ID, tfi.startTime, fc.timeLimit
                 FROM TeamFormInstance tfi
                 JOIN FormCollection fc ON tfi.collection_ID = fc.ID
                 WHERE tfi.completed = 0 
                   AND tfi.startTime IS NOT NULL
                   AND TIMESTAMPDIFF(SECOND, tfi.startTime, NOW()) > fc.timeLimit"
            );
            $stmt->execute();
            $expiredInstances = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stats['processed'] = count($expiredInstances);

            foreach ($expiredInstances as $instance) {
                if ($this->completeInstance($instance['ID'])) {
                    $stats['expired']++;
                } else {
                    $stats['errors']++;
                }
            }

            return $stats;
        } catch (PDOException $e) {
            error_log("Error in TeamFormInstanceModel::processExpiredInstances: " . $e->getMessage());
            return ['processed' => 0, 'expired' => 0, 'errors' => 1];
        }
    }

    /**
     * Holt Statistiken für Instances
     *
     * @param array $filters Optional: Filter-Parameter
     * @return array Statistik-Daten
     */
    public function getInstanceStatistics(array $filters = []): array
    {
        try {
            $whereConditions = [];
            $params = [];

            // Filter anwenden
            if (!empty($filters['team_id'])) {
                $whereConditions[] = "tfi.team_ID = :teamId";
                $params[':teamId'] = $filters['team_id'];
            }

            if (!empty($filters['collection_id'])) {
                $whereConditions[] = "tfi.collection_ID = :collectionId";
                $params[':collectionId'] = $filters['collection_id'];
            }

            if (!empty($filters['completed'])) {
                $whereConditions[] = "tfi.completed = :completed";
                $params[':completed'] = $filters['completed'];
            }

            $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

            $stmt = $this->db->prepare(
                "SELECT 
                    COUNT(*) as totalInstances,
                    COUNT(CASE WHEN tfi.completed = 1 THEN 1 END) as completedInstances,
                    COUNT(CASE WHEN tfi.startTime IS NOT NULL AND tfi.completed = 0 THEN 1 END) as runningInstances,
                    AVG(CASE WHEN tfi.completed = 1 THEN tfi.points END) as averagePoints,
                    MAX(CASE WHEN tfi.completed = 1 THEN tfi.points END) as maxPoints,
                    MIN(CASE WHEN tfi.completed = 1 THEN tfi.points END) as minPoints
                 FROM TeamFormInstance tfi
                 JOIN FormCollection fc ON tfi.collection_ID = fc.ID
                 JOIN Mannschaft m ON tfi.team_ID = m.ID
                 {$whereClause}"
            );
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log("Error in TeamFormInstanceModel::getInstanceStatistics: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Erstellt eine neue Instance mit der Stored Procedure
     *
     * @param int $teamId ID des Teams
     * @param int $collectionId ID der Collection
     * @param int $formNumber Nummer des Formulars
     * @return array|null Ergebnis der Stored Procedure oder null bei Fehler
     */
    public function createInstanceWithProcedure(int $teamId, int $collectionId, int $formNumber): ?array
    {
        try {
            $stmt = $this->db->prepare("CALL createTeamFormInstance(:teamId, :collectionId, :formNumber)");
            $stmt->execute([
                ':teamId' => $teamId,
                ':collectionId' => $collectionId,
                ':formNumber' => $formNumber
            ]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Error in TeamFormInstanceModel::createInstanceWithProcedure: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Prüft ob eine Instance bereit zum Starten ist
     *
     * @param int $instanceId ID der Instance
     * @return bool True wenn bereit, false wenn nicht
     */
    public function isInstanceReadyToStart(int $instanceId): bool
    {
        try {
            $instance = $this->readInstance($instanceId);

            if (!$instance) {
                return false;
            }

            // Instance ist bereit wenn sie nicht abgeschlossen ist und ein gültiges assignedQuestions Array hat
            $assignedQuestions = json_decode($instance['assignedQuestions'] ?? '[]', true);

            return $instance['completed'] == 0 && is_array($assignedQuestions) && !empty($assignedQuestions);
        } catch (PDOException $e) {
            error_log("Error in TeamFormInstanceModel::isInstanceReadyToStart: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Berechnet die verbleibende Zeit für eine Instance
     *
     * @param int $instanceId ID der Instance
     * @return int|null Verbleibende Zeit in Sekunden oder null wenn nicht berechenbar
     */
    public function getRemainingTime(int $instanceId): ?int
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT tfi.startTime, fc.timeLimit,
                        TIMESTAMPDIFF(SECOND, tfi.startTime, NOW()) as elapsedSeconds
                 FROM TeamFormInstance tfi
                 JOIN FormCollection fc ON tfi.collection_ID = fc.ID
                 WHERE tfi.ID = :instanceId AND tfi.completed = 0 AND tfi.startTime IS NOT NULL"
            );
            $stmt->execute([':instanceId' => $instanceId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$result) {
                return null;
            }

            $remainingTime = $result['timeLimit'] - $result['elapsedSeconds'];
            return max(0, $remainingTime);
        } catch (PDOException $e) {
            error_log("Error in TeamFormInstanceModel::getRemainingTime: " . $e->getMessage());
            return null;
        }
    }
}