<?php
namespace FormCollection;

use PDO;
use PDOException;

class FormCollectionModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Prüft, ob ein Collection-Name bereits existiert
     *
     * @param string $name Name der Collection
     * @param int|null $excludeId Optional: ID zum Ausschließen bei Updates
     * @return bool True wenn Name existiert, false wenn nicht
     */
    public function checkNameExists(string $name, ?int $excludeId = null): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM FormCollection WHERE name = :name";
            $params = [':name' => trim($name)];

            if ($excludeId !== null) {
                $sql .= " AND ID != :excludeId";
                $params[':excludeId'] = $excludeId;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error in FormCollectionModel::checkNameExists: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Validiert Collection-Daten
     *
     * @param array $collectionData Collection-Daten zum Validieren
     * @param int|null $excludeId Optional: ID zum Ausschließen bei Updates
     * @return array Array mit Validierungsfehlern (leer wenn alles okay)
     */
    public function validateCollectionData(array $collectionData, ?int $excludeId = null): array
    {
        $errors = [];

        // Name validieren
        $name = trim($collectionData['name'] ?? '');
        if (empty($name)) {
            $errors['name'] = 'Der Name der Formular-Gruppe ist erforderlich.';
        } elseif (strlen($name) < 3) {
            $errors['name'] = 'Der Name muss mindestens 3 Zeichen lang sein.';
        } elseif (strlen($name) > 255) {
            $errors['name'] = 'Der Name darf maximal 255 Zeichen lang sein.';
        } elseif ($this->checkNameExists($name, $excludeId)) {
            $errors['name'] = 'Eine Formular-Gruppe mit diesem Namen existiert bereits.';
        }

        // Beschreibung validieren
        $description = trim($collectionData['description'] ?? '');
        if (strlen($description) > 200) {
            $errors['description'] = 'Die Beschreibung darf maximal 200 Zeichen lang sein.';
        }

        // Zeitlimit validieren
        $timeLimit = intval($collectionData['timeLimit'] ?? 0);
        if ($timeLimit < 10) {
            $errors['timeLimit'] = 'Das Zeitlimit muss mindestens 10 Sekunden betragen.';
        } elseif ($timeLimit > 1800) {
            $errors['timeLimit'] = 'Das Zeitlimit darf maximal 30 Minuten (1800 Sekunden) betragen.';
        }

        // Anzahl Fragen validieren
        $totalQuestions = intval($collectionData['totalQuestions'] ?? 0);
        if ($totalQuestions < 1) {
            $errors['totalQuestions'] = 'Mindestens eine Frage muss ausgewählt werden.';
        }

        // Anzahl Formulare validieren
        $formsCount = intval($collectionData['formsCount'] ?? 0);
        if ($formsCount < 1) {
            $errors['formsCount'] = 'Die Anzahl der Formulare muss mindestens 1 betragen.';
        } elseif ($formsCount > 20) {
            $errors['formsCount'] = 'Die Anzahl der Formulare darf maximal 20 betragen.';
        } elseif ($totalQuestions < $formsCount) {
            $errors['formsCount'] = "Sie müssen mindestens {$formsCount} Fragen auswählen (mindestens eine pro Formular).";
        }

        return $errors;
    }

    /**
     * Erstellt eine neue FormCollection mit automatischer Token-Generierung
     *
     * @param array $collectionData Array mit 'name', 'description', 'timeLimit', 'totalQuestions', 'formsCount', 'station_ID'
     * @param array $questionIds Array mit Question-IDs die der Collection zugewiesen werden sollen
     * @return int|null Die ID der neuen Collection oder null bei Fehler
     */
    public function createCollection(array $collectionData, array $questionIds = []): ?int
    {
        try {
            $this->db->beginTransaction();

            // Validierung vor dem Erstellen
            $errors = $this->validateCollectionData($collectionData);
            if (!empty($errors)) {
                $this->db->rollBack();
                return null;
            }

            // FormCollection erstellen
            $stmt = $this->db->prepare(
                "INSERT INTO FormCollection (name, description, timeLimit, totalQuestions, formsCount, station_ID) 
                 VALUES (:name, :description, :timeLimit, :totalQuestions, :formsCount, :stationId)"
            );

            $stmt->execute([
                ':name' => trim($collectionData['name']),
                ':description' => trim($collectionData['description'] ?? ''),
                ':timeLimit' => $collectionData['timeLimit'] ?? 180,
                ':totalQuestions' => $collectionData['totalQuestions'],
                ':formsCount' => $collectionData['formsCount'],
                ':stationId' => $collectionData['station_ID'] ?? null
            ]);

            $collectionId = $this->db->lastInsertId();

            // Fragen zur Collection hinzufügen
            if (!empty($questionIds)) {
                $this->assignQuestionsToCollection($collectionId, $questionIds);
            }

            // QR-Code-Tokens für alle Formulare generieren
            $this->generateTokensForCollection($collectionId, $collectionData['formsCount']);

            $this->db->commit();
            return $collectionId;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error in FormCollectionModel::createCollection: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Liest eine oder mehrere FormCollections aus der Datenbank
     *
     * @param int|null $collectionId Optional: ID einer spezifischen Collection
     * @return array|null Collection-Daten oder null bei Fehler
     */
    public function readCollection(?int $collectionId = null): ?array
    {
        try {
            if ($collectionId === null) {
                // Alle Collections mit Statistiken über View abrufen
                $stmt = $this->db->query("SELECT * FROM CollectionOverview ORDER BY createdAt DESC");
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                // Spezifische Collection mit Details
                $stmt = $this->db->prepare(
                    "SELECT fc.*, s.name as stationName,
                            COUNT(DISTINCT cq.question_ID) as assignedQuestions
                     FROM FormCollection fc
                     LEFT JOIN Station s ON fc.station_ID = s.ID
                     LEFT JOIN CollectionQuestion cq ON fc.ID = cq.collection_ID
                     WHERE fc.ID = :collectionId
                     GROUP BY fc.ID"
                );
                $stmt->execute([':collectionId' => $collectionId]);
                return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
            }
        } catch (PDOException $e) {
            error_log("Error in FormCollectionModel::readCollection: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Löscht eine FormCollection komplett mit allen abhängigen Daten
     * Nutzt die Stored Procedure für sichere Löschung
     *
     * @param int $collectionId ID der zu löschenden Collection
     * @return bool True bei Erfolg, false bei Fehler
     */
    public function deleteCollection(int $collectionId): bool
    {
        try {
            $stmt = $this->db->prepare("CALL deleteFormCollection(:collectionId)");
            $stmt->execute([':collectionId' => $collectionId]);

            // Ergebnis der Stored Procedure abrufen
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result !== false;
        } catch (PDOException $e) {
            error_log("Error in FormCollectionModel::deleteCollection: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Weist Fragen einer Collection zu
     *
     * @param int $collectionId ID der Collection
     * @param array $questionIds Array mit Question-IDs
     * @return bool True bei Erfolg, false bei Fehler
     */
    public function assignQuestionsToCollection(int $collectionId, array $questionIds): bool
    {
        try {
            // Erst alle bestehenden Zuweisungen löschen
            $stmt = $this->db->prepare("DELETE FROM CollectionQuestion WHERE collection_ID = :collectionId");
            $stmt->execute([':collectionId' => $collectionId]);

            // Neue Zuweisungen erstellen
            $stmt = $this->db->prepare(
                "INSERT INTO CollectionQuestion (collection_ID, question_ID) VALUES (:collectionId, :questionId)"
            );

            foreach ($questionIds as $questionId) {
                $stmt->execute([
                    ':collectionId' => $collectionId,
                    ':questionId' => $questionId
                ]);
            }

            return true;
        } catch (PDOException $e) {
            error_log("Error in FormCollectionModel::assignQuestionsToCollection: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Holt alle Fragen einer Collection
     *
     * @param int $collectionId ID der Collection
     * @return array Array mit Question-Daten
     */
    public function getCollectionQuestions(int $collectionId): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT q.*, qp.Name as poolName
                 FROM CollectionQuestion cq
                 JOIN Question q ON cq.question_ID = q.ID
                 JOIN QuestionPool qp ON q.QuestionPool_ID = qp.ID
                 WHERE cq.collection_ID = :collectionId
                 ORDER BY q.ID"
            );
            $stmt->execute([':collectionId' => $collectionId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in FormCollectionModel::getCollectionQuestions: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Generiert QR-Code-Tokens für alle Formulare einer Collection
     *
     * @param int $collectionId ID der Collection
     * @param int $formsCount Anzahl der Formulare
     * @return bool True bei Erfolg, false bei Fehler
     */
    public function generateTokensForCollection(int $collectionId, int $formsCount): bool
    {
        try {
            // Erst alle bestehenden Tokens löschen
            $stmt = $this->db->prepare("DELETE FROM CollectionFormToken WHERE collection_ID = :collectionId");
            $stmt->execute([':collectionId' => $collectionId]);

            // Neue Tokens für jedes Formular generieren
            $stmt = $this->db->prepare(
                "INSERT INTO CollectionFormToken (collection_ID, formNumber, token) VALUES (:collectionId, :formNumber, :token)"
            );

            for ($formNumber = 1; $formNumber <= $formsCount; $formNumber++) {
                $token = $this->generateUniqueToken($collectionId, $formNumber);
                $stmt->execute([
                    ':collectionId' => $collectionId,
                    ':formNumber' => $formNumber,
                    ':token' => $token
                ]);
            }

            return true;
        } catch (PDOException $e) {
            error_log("Error in FormCollectionModel::generateTokensForCollection: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generiert ein eindeutiges Token für ein Formular
     *
     * @param int $collectionId ID der Collection
     * @param int $formNumber Nummer des Formulars
     * @return string 12-stelliger Token
     */
    private function generateUniqueToken(int $collectionId, int $formNumber): string
    {
        do {
            $token = substr(md5($collectionId . $formNumber . time() . rand()), 0, 12);
        } while ($this->tokenExists($token));

        return $token;
    }

    /**
     * Prüft, ob ein Token bereits existiert
     *
     * @param string $token Zu prüfender Token
     * @return bool True wenn Token existiert, false wenn nicht
     */
    public function tokenExists(string $token): bool
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM CollectionFormToken WHERE token = :token");
            $stmt->execute([':token' => $token]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error in FormCollectionModel::tokenExists: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Holt alle Tokens einer Collection mit QR-Code-URLs
     *
     * @param int $collectionId ID der Collection
     * @param string $baseUrl Basis-URL für QR-Codes
     * @return array Array mit Token-Daten und URLs
     */
    public function getCollectionTokens(int $collectionId, string $baseUrl = ''): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT cft.*, fc.name as collectionName
                 FROM CollectionFormToken cft
                 JOIN FormCollection fc ON cft.collection_ID = fc.ID
                 WHERE cft.collection_ID = :collectionId
                 ORDER BY cft.formNumber"
            );
            $stmt->execute([':collectionId' => $collectionId]);
            $tokens = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // QR-Code-URLs hinzufügen
            foreach ($tokens as &$token) {
                $token['qrCodeUrl'] = $baseUrl . '/view/FormRedirect.php?code=' . $token['token'];
            }

            return $tokens;
        } catch (PDOException $e) {
            error_log("Error in FormCollectionModel::getCollectionTokens: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Holt alle verfügbaren Stationen für Collection-Zuweisungen
     *
     * @return array Array mit Station-Daten
     */
    public function getAvailableStations(): array
    {
        try {
            $stmt = $this->db->query("SELECT * FROM Station ORDER BY name");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in FormCollectionModel::getAvailableStations: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Holt alle verfügbaren QuestionPools für Collection-Erstellung
     *
     * @return array Array mit QuestionPool-Daten
     */
    public function getAvailableQuestionPools(): array
    {
        try {
            $stmt = $this->db->query("SELECT * FROM QuestionPool ORDER BY Name");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in FormCollectionModel::getAvailableQuestionPools: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Holt alle Fragen eines QuestionPools
     *
     * @param int $poolId ID des QuestionPools
     * @return array Array mit Question-Daten
     */
    public function getQuestionsByPool(int $poolId): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM Question WHERE QuestionPool_ID = :poolId ORDER BY ID");
            $stmt->execute([':poolId' => $poolId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in FormCollectionModel::getQuestionsByPool: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Holt Collection-Performance-Statistiken
     *
     * @param int|null $collectionId Optional: Spezifische Collection
     * @return array Performance-Daten
     */
    public function getCollectionPerformance(?int $collectionId = null): array
    {
        try {
            if ($collectionId === null) {
                $stmt = $this->db->query("SELECT * FROM CollectionPerformance ORDER BY collectionName");
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $stmt = $this->db->prepare("SELECT * FROM CollectionPerformance WHERE collectionId = :collectionId");
                $stmt->execute([':collectionId' => $collectionId]);
                return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
            }
        } catch (PDOException $e) {
            error_log("Error in FormCollectionModel::getCollectionPerformance: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Holt Team-Collection-Progress (nur für bereits erstellte Instances)
     *
     * @return array Team-Progress-Daten
     */
    public function getTeamCollectionProgress(): array
    {
        try {
            $stmt = $this->db->query("SELECT * FROM TeamCollectionProgress ORDER BY Teamname, collectionName");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in FormCollectionModel::getTeamCollectionProgress: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Prüft abgelaufene Timer und schließt entsprechende Instances ab
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
                if ($this->completeFormInstance($instance['ID'])) {
                    $stats['expired']++;
                } else {
                    $stats['errors']++;
                }
            }

            return $stats;
        } catch (PDOException $e) {
            error_log("Error in FormCollectionModel::processExpiredInstances: " . $e->getMessage());
            return ['processed' => 0, 'expired' => 0, 'errors' => 1];
        }
    }

    /**
     * Schließt eine TeamFormInstance ab und berechnet die Punkte
     *
     * @param int $instanceId ID der Instance
     * @return bool True bei Erfolg, false bei Fehler
     */
    public function completeFormInstance(int $instanceId): bool
    {
        try {
            $stmt = $this->db->prepare("CALL autoSubmitExpiredForm(:instanceId)");
            $stmt->execute([':instanceId' => $instanceId]);
            return true;
        } catch (PDOException $e) {
            error_log("Error in FormCollectionModel::completeFormInstance: " . $e->getMessage());
            return false;
        }
    }
}