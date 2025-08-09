<?php

namespace FormCollection;

use FormCollection\TeamFormInstanceModel;
use PDO;
use PDOException;

/**
 * Controller-Klasse für die Verwaltung von TeamFormInstances
 * Verarbeitet HTTP-Requests für TeamFormInstance-Management
 */
class TeamFormInstanceController
{
    private TeamFormInstanceModel $model;
    private PDO $db;

    // Public Properties für View-Zugriff
    public array $instances = [];
    public string $message = '';
    public string $messageType = 'info';
    public ?array $currentInstance = null;
    public array $instanceAnswers = [];
    public array $assignedQuestions = [];
    public array $statistics = [];

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->model = new TeamFormInstanceModel($db);
    }

    /**
     * Hauptmethode zur Verarbeitung aller Requests
     */
    public function handleRequest(): void
    {
        // Standard-Daten laden
        $this->loadBaseData();

        // POST-Requests verarbeiten
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePostRequest();
        }

        // GET-Requests verarbeiten
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->handleGetRequest();
        }
    }

    /**
     * Lädt grundlegende Daten für die View
     */
    private function loadBaseData(): void
    {
        $this->instances = $this->model->readInstance() ?? [];
    }

    /**
     * Verarbeitet POST-Requests
     */
    private function handlePostRequest(): void
    {
        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'create_instance':
                $this->handleCreateInstance();
                break;

            case 'update_instance':
                $this->handleUpdateInstance();
                break;

            case 'delete_instance':
                $this->handleDeleteInstance();
                break;

            case 'start_timer':
                $this->handleStartTimer();
                break;

            case 'complete_instance':
                $this->handleCompleteInstance();
                break;

            case 'save_answer':
                $this->handleSaveAnswer();
                break;

            case 'process_expired':
                $this->handleProcessExpired();
                break;

            default:
                $this->message = 'Unbekannte Aktion.';
                $this->messageType = 'error';
        }
    }

    /**
     * Verarbeitet GET-Requests
     */
    private function handleGetRequest(): void
    {
        $action = $_GET['action'] ?? '';

        switch ($action) {
            case 'view_instance':
                $this->handleViewInstance();
                break;

            case 'get_instances_by_team':
                $this->handleGetInstancesByTeam();
                break;

            case 'get_instances_by_collection':
                $this->handleGetInstancesByCollection();
                break;

            case 'get_assigned_questions':
                $this->handleGetAssignedQuestions();
                break;

            case 'get_remaining_time':
                $this->handleGetRemainingTime();
                break;

            case 'get_statistics':
                $this->handleGetStatistics();
                break;

            case 'check_instance_status':
                $this->handleCheckInstanceStatus();
                break;

            default:
                // Keine spezielle Aktion - Standard-Daten sind bereits geladen
                break;
        }
    }

    /**
     * Erstellt eine neue TeamFormInstance
     */
    private function handleCreateInstance(): void
    {
        try {
            // Input-Validierung
            $requiredFields = ['team_id', 'collection_id', 'form_number'];
            if (!$this->validateInput($_POST, $requiredFields)) {
                return;
            }

            $teamId = intval($_POST['team_id']);
            $collectionId = intval($_POST['collection_id']);
            $formNumber = intval($_POST['form_number']);

            // Validierung
            if ($teamId <= 0 || $collectionId <= 0 || $formNumber <= 0) {
                $this->message = 'Ungültige Team-ID, Collection-ID oder Formularnummer.';
                $this->messageType = 'error';
                return;
            }

            // Prüfen ob Instance bereits existiert
            $existingInstance = $this->model->getInstanceByTeamAndForm($teamId, $collectionId, $formNumber);
            if ($existingInstance) {
                $this->message = 'Instance existiert bereits für dieses Team und Formular.';
                $this->messageType = 'error';
                return;
            }

            // Instance mit Stored Procedure erstellen
            $result = $this->model->createInstanceWithProcedure($teamId, $collectionId, $formNumber);

            if ($result && isset($result['created_instance_id'])) {
                $this->message = "Instance erfolgreich erstellt (ID: {$result['created_instance_id']}).";
                $this->messageType = 'success';
                $this->loadBaseData(); // Daten neu laden
            } else {
                $this->message = 'Fehler beim Erstellen der Instance.';
                $this->messageType = 'error';
            }

        } catch (\Exception $e) {
            error_log("Error in TeamFormInstanceController::handleCreateInstance: " . $e->getMessage());
            $this->message = 'Ein unerwarteter Fehler ist aufgetreten.';
            $this->messageType = 'error';
        }
    }

    /**
     * Aktualisiert eine bestehende TeamFormInstance
     */
    private function handleUpdateInstance(): void
    {
        try {
            $instanceId = intval($_POST['instance_id'] ?? 0);

            if ($instanceId <= 0) {
                $this->message = 'Ungültige Instance-ID.';
                $this->messageType = 'error';
                return;
            }

            // Zu aktualisierende Daten zusammenstellen
            $updateData = [];
            $allowedFields = ['completed', 'points', 'startTime', 'completionDate'];

            foreach ($allowedFields as $field) {
                if (isset($_POST[$field])) {
                    $updateData[$field] = $_POST[$field];
                }
            }

            if (empty($updateData)) {
                $this->message = 'Keine Daten zum Aktualisieren angegeben.';
                $this->messageType = 'error';
                return;
            }

            // Instance aktualisieren
            if ($this->model->updateInstance($instanceId, $updateData)) {
                $this->message = 'Instance erfolgreich aktualisiert.';
                $this->messageType = 'success';
                $this->loadBaseData(); // Daten neu laden
            } else {
                $this->message = 'Fehler beim Aktualisieren der Instance.';
                $this->messageType = 'error';
            }

        } catch (\Exception $e) {
            error_log("Error in TeamFormInstanceController::handleUpdateInstance: " . $e->getMessage());
            $this->message = 'Ein unerwarteter Fehler ist aufgetreten.';
            $this->messageType = 'error';
        }
    }

    /**
     * Löscht eine TeamFormInstance
     */
    private function handleDeleteInstance(): void
    {
        try {
            $instanceId = intval($_POST['instance_id'] ?? 0);
            $confirmDelete = $_POST['confirm_delete'] ?? '';

            if ($instanceId <= 0) {
                $this->message = 'Ungültige Instance-ID.';
                $this->messageType = 'error';
                return;
            }

            if ($confirmDelete !== '1') {
                $this->message = 'Löschung nicht bestätigt.';
                $this->messageType = 'error';
                return;
            }

            // Instance löschen
            if ($this->model->deleteInstance($instanceId)) {
                $this->message = 'Instance erfolgreich gelöscht.';
                $this->messageType = 'success';
                $this->loadBaseData(); // Daten neu laden
            } else {
                $this->message = 'Fehler beim Löschen der Instance.';
                $this->messageType = 'error';
            }

        } catch (\Exception $e) {
            error_log("Error in TeamFormInstanceController::handleDeleteInstance: " . $e->getMessage());
            $this->message = 'Ein unerwarteter Fehler ist aufgetreten.';
            $this->messageType = 'error';
        }
    }

    /**
     * Startet den Timer für eine Instance
     */
    private function handleStartTimer(): void
    {
        try {
            $instanceId = intval($_POST['instance_id'] ?? 0);

            if ($instanceId <= 0) {
                $this->message = 'Ungültige Instance-ID.';
                $this->messageType = 'error';
                return;
            }

            // Timer starten
            if ($this->model->startTimer($instanceId)) {
                $this->message = 'Timer erfolgreich gestartet.';
                $this->messageType = 'success';

                // Für AJAX-Requests: JSON-Response senden
                if (!empty($_POST['ajax'])) {
                    $this->sendJsonResponse(true, 'Timer gestartet', [
                        'instanceId' => $instanceId,
                        'startTime' => date('Y-m-d H:i:s')
                    ]);
                    return;
                }
            } else {
                $this->message = 'Fehler beim Starten des Timers oder Timer bereits gestartet.';
                $this->messageType = 'error';

                if (!empty($_POST['ajax'])) {
                    $this->sendJsonResponse(false, $this->message);
                    return;
                }
            }

        } catch (\Exception $e) {
            error_log("Error in TeamFormInstanceController::handleStartTimer: " . $e->getMessage());
            $this->message = 'Ein unerwarteter Fehler ist aufgetreten.';
            $this->messageType = 'error';

            if (!empty($_POST['ajax'])) {
                $this->sendJsonResponse(false, $this->message);
                return;
            }
        }
    }

    /**
     * Schließt eine Instance ab
     */
    private function handleCompleteInstance(): void
    {
        try {
            $instanceId = intval($_POST['instance_id'] ?? 0);

            if ($instanceId <= 0) {
                $this->message = 'Ungültige Instance-ID.';
                $this->messageType = 'error';
                return;
            }

            // Instance abschließen
            if ($this->model->completeInstance($instanceId)) {
                $this->message = 'Instance erfolgreich abgeschlossen.';
                $this->messageType = 'success';

                if (!empty($_POST['ajax'])) {
                    $this->sendJsonResponse(true, 'Instance abgeschlossen', [
                        'instanceId' => $instanceId,
                        'completionDate' => date('Y-m-d H:i:s')
                    ]);
                    return;
                }
            } else {
                $this->message = 'Fehler beim Abschließen der Instance.';
                $this->messageType = 'error';

                if (!empty($_POST['ajax'])) {
                    $this->sendJsonResponse(false, $this->message);
                    return;
                }
            }

        } catch (\Exception $e) {
            error_log("Error in TeamFormInstanceController::handleCompleteInstance: " . $e->getMessage());
            $this->message = 'Ein unerwarteter Fehler ist aufgetreten.';
            $this->messageType = 'error';

            if (!empty($_POST['ajax'])) {
                $this->sendJsonResponse(false, $this->message);
                return;
            }
        }
    }

    /**
     * Speichert eine Antwort für eine Instance
     */
    private function handleSaveAnswer(): void
    {
        try {
            $requiredFields = ['instance_id', 'question_id', 'answer_id'];
            if (!$this->validateInput($_POST, $requiredFields)) {
                if (!empty($_POST['ajax'])) {
                    $this->sendJsonResponse(false, $this->message);
                    return;
                }
                return;
            }

            $instanceId = intval($_POST['instance_id']);
            $questionId = intval($_POST['question_id']);
            $answerId = intval($_POST['answer_id']);

            // Antwort speichern
            if ($this->model->saveAnswer($instanceId, $questionId, $answerId)) {
                $this->message = 'Antwort erfolgreich gespeichert.';
                $this->messageType = 'success';

                if (!empty($_POST['ajax'])) {
                    $this->sendJsonResponse(true, 'Antwort gespeichert', [
                        'instanceId' => $instanceId,
                        'questionId' => $questionId,
                        'answerId' => $answerId
                    ]);
                    return;
                }
            } else {
                $this->message = 'Fehler beim Speichern der Antwort.';
                $this->messageType = 'error';

                if (!empty($_POST['ajax'])) {
                    $this->sendJsonResponse(false, $this->message);
                    return;
                }
            }

        } catch (\Exception $e) {
            error_log("Error in TeamFormInstanceController::handleSaveAnswer: " . $e->getMessage());
            $this->message = 'Ein unerwarteter Fehler ist aufgetreten.';
            $this->messageType = 'error';

            if (!empty($_POST['ajax'])) {
                $this->sendJsonResponse(false, $this->message);
                return;
            }
        }
    }

    /**
     * Verarbeitet abgelaufene Instances
     */
    private function handleProcessExpired(): void
    {
        try {
            $stats = $this->model->processExpiredInstances();

            if ($stats['errors'] > 0) {
                $this->message = "Verarbeitung abgeschlossen. {$stats['expired']} Formulare abgeschlossen, {$stats['errors']} Fehler.";
                $this->messageType = 'error';
            } else {
                $this->message = "Erfolgreich {$stats['expired']} von {$stats['processed']} abgelaufenen Formularen verarbeitet.";
                $this->messageType = 'success';
            }

            $this->loadBaseData(); // Daten neu laden

        } catch (\Exception $e) {
            error_log("Error in TeamFormInstanceController::handleProcessExpired: " . $e->getMessage());
            $this->message = 'Fehler beim Verarbeiten abgelaufener Formulare.';
            $this->messageType = 'error';
        }
    }

    /**
     * Zeigt Instance-Details an
     */
    private function handleViewInstance(): void
    {
        try {
            $instanceId = intval($_GET['instance_id'] ?? 0);

            if ($instanceId <= 0) {
                $this->message = 'Ungültige Instance-ID.';
                $this->messageType = 'error';
                return;
            }

            $this->currentInstance = $this->model->readInstance($instanceId);

            if (!$this->currentInstance) {
                $this->message = 'Instance nicht gefunden.';
                $this->messageType = 'error';
                return;
            }

            // Zusätzliche Daten laden
            $this->instanceAnswers = $this->model->getAnswersByInstance($instanceId);
            $this->assignedQuestions = $this->model->getAssignedQuestions($instanceId);

        } catch (\Exception $e) {
            error_log("Error in TeamFormInstanceController::handleViewInstance: " . $e->getMessage());
            $this->message = 'Fehler beim Laden der Instance-Details.';
            $this->messageType = 'error';
        }
    }

    /**
     * Holt alle Instances für ein Team (AJAX)
     */
    private function handleGetInstancesByTeam(): void
    {
        try {
            $teamId = intval($_GET['team_id'] ?? 0);

            if ($teamId <= 0) {
                $this->sendJsonResponse(false, 'Ungültige Team-ID.');
                return;
            }

            $instances = $this->model->getInstancesByTeam($teamId);

            $this->sendJsonResponse(true, 'Instances erfolgreich geladen.', [
                'instances' => $instances,
                'count' => count($instances)
            ]);

        } catch (\Exception $e) {
            error_log("Error in TeamFormInstanceController::handleGetInstancesByTeam: " . $e->getMessage());
            $this->sendJsonResponse(false, 'Fehler beim Laden der Instances.');
        }
    }

    /**
     * Holt alle Instances für eine Collection (AJAX)
     */
    private function handleGetInstancesByCollection(): void
    {
        try {
            $collectionId = intval($_GET['collection_id'] ?? 0);

            if ($collectionId <= 0) {
                $this->sendJsonResponse(false, 'Ungültige Collection-ID.');
                return;
            }

            $instances = $this->model->getInstancesByCollection($collectionId);

            $this->sendJsonResponse(true, 'Instances erfolgreich geladen.', [
                'instances' => $instances,
                'count' => count($instances)
            ]);

        } catch (\Exception $e) {
            error_log("Error in TeamFormInstanceController::handleGetInstancesByCollection: " . $e->getMessage());
            $this->sendJsonResponse(false, 'Fehler beim Laden der Instances.');
        }
    }

    /**
     * Holt zugewiesene Fragen für eine Instance (AJAX)
     */
    private function handleGetAssignedQuestions(): void
    {
        try {
            $instanceId = intval($_GET['instance_id'] ?? 0);

            if ($instanceId <= 0) {
                $this->sendJsonResponse(false, 'Ungültige Instance-ID.');
                return;
            }

            $questions = $this->model->getAssignedQuestions($instanceId);

            $this->sendJsonResponse(true, 'Fragen erfolgreich geladen.', [
                'questions' => $questions,
                'count' => count($questions)
            ]);

        } catch (\Exception $e) {
            error_log("Error in TeamFormInstanceController::handleGetAssignedQuestions: " . $e->getMessage());
            $this->sendJsonResponse(false, 'Fehler beim Laden der Fragen.');
        }
    }

    /**
     * Holt verbleibende Zeit für eine Instance (AJAX)
     */
    private function handleGetRemainingTime(): void
    {
        try {
            $instanceId = intval($_GET['instance_id'] ?? 0);

            if ($instanceId <= 0) {
                $this->sendJsonResponse(false, 'Ungültige Instance-ID.');
                return;
            }

            $remainingTime = $this->model->getRemainingTime($instanceId);

            if ($remainingTime !== null) {
                $this->sendJsonResponse(true, 'Verbleibende Zeit ermittelt.', [
                    'remainingTime' => $remainingTime,
                    'formattedTime' => gmdate("i:s", $remainingTime),
                    'expired' => $remainingTime <= 0
                ]);
            } else {
                $this->sendJsonResponse(false, 'Verbleibende Zeit konnte nicht ermittelt werden.');
            }

        } catch (\Exception $e) {
            error_log("Error in TeamFormInstanceController::handleGetRemainingTime: " . $e->getMessage());
            $this->sendJsonResponse(false, 'Fehler beim Ermitteln der verbleibenden Zeit.');
        }
    }

    /**
     * Holt Statistiken für Instances (AJAX)
     */
    private function handleGetStatistics(): void
    {
        try {
            $filters = [];

            // Filter aus GET-Parametern extrahieren
            if (!empty($_GET['team_id'])) {
                $filters['team_id'] = intval($_GET['team_id']);
            }
            if (!empty($_GET['collection_id'])) {
                $filters['collection_id'] = intval($_GET['collection_id']);
            }
            if (isset($_GET['completed'])) {
                $filters['completed'] = intval($_GET['completed']);
            }

            $this->statistics = $this->model->getInstanceStatistics($filters);

            $this->sendJsonResponse(true, 'Statistiken erfolgreich geladen.', [
                'statistics' => $this->statistics
            ]);

        } catch (\Exception $e) {
            error_log("Error in TeamFormInstanceController::handleGetStatistics: " . $e->getMessage());
            $this->sendJsonResponse(false, 'Fehler beim Laden der Statistiken.');
        }
    }

    /**
     * Prüft den Status einer Instance (AJAX)
     */
    private function handleCheckInstanceStatus(): void
    {
        try {
            $instanceId = intval($_GET['instance_id'] ?? 0);

            if ($instanceId <= 0) {
                $this->sendJsonResponse(false, 'Ungültige Instance-ID.');
                return;
            }

            $instance = $this->model->readInstance($instanceId);

            if (!$instance) {
                $this->sendJsonResponse(false, 'Instance nicht gefunden.');
                return;
            }

            $remainingTime = $this->model->getRemainingTime($instanceId);
            $isReady = $this->model->isInstanceReadyToStart($instanceId);

            $this->sendJsonResponse(true, 'Instance-Status ermittelt.', [
                'instanceId' => $instanceId,
                'completed' => $instance['completed'],
                'points' => $instance['points'],
                'startTime' => $instance['startTime'],
                'completionDate' => $instance['completionDate'],
                'remainingTime' => $remainingTime,
                'isReady' => $isReady,
                'status' => $instance['completed'] ? 'completed' :
                    ($instance['startTime'] ? 'running' : 'ready')
            ]);

        } catch (\Exception $e) {
            error_log("Error in TeamFormInstanceController::handleCheckInstanceStatus: " . $e->getMessage());
            $this->sendJsonResponse(false, 'Fehler beim Prüfen des Instance-Status.');
        }
    }

    /**
     * Validiert Input-Daten
     *
     * @param array $data Input-Daten
     * @param array $requiredFields Erforderliche Felder
     * @return bool True wenn alle Validierungen bestanden
     */
    private function validateInput(array $data, array $requiredFields): bool
    {
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === '')) {
                $this->message = "Feld '{$field}' ist erforderlich.";
                $this->messageType = 'error';
                return false;
            }
        }
        return true;
    }

    /**
     * Sendet JSON-Response für AJAX-Requests
     *
     * @param bool $success Erfolg-Status
     * @param string $message Nachricht
     * @param array $data Zusätzliche Daten
     */
    private function sendJsonResponse(bool $success, string $message, array $data = []): void
    {
        header('Content-Type: application/json');
        echo json_encode([
                'success' => $success,
                'message' => $message,
                'data' => $data
            ] + $data);
        exit;
    }

    /**
     * Holt verfügbare Teams für Instance-Erstellung
     *
     * @return array Array mit Team-Daten
     */
    public function getAvailableTeams(): array
    {
        try {
            $stmt = $this->db->query("SELECT ID, Teamname, Kreisverband FROM Mannschaft ORDER BY Teamname");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in TeamFormInstanceController::getAvailableTeams: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Holt verfügbare Collections für Instance-Erstellung
     *
     * @return array Array mit Collection-Daten
     */
    public function getAvailableCollections(): array
    {
        try {
            $stmt = $this->db->query("SELECT ID, name, formsCount FROM FormCollection ORDER BY name");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in TeamFormInstanceController::getAvailableCollections: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Holt Dashboard-Statistiken für die Übersicht
     *
     * @return array Dashboard-Statistiken
     */
    public function getDashboardStats(): array
    {
        try {
            $stats = [];

            // Gesamtstatistiken
            $stmt = $this->db->query(
                "SELECT 
                    COUNT(*) as totalInstances,
                    COUNT(CASE WHEN completed = 1 THEN 1 END) as completedInstances,
                    COUNT(CASE WHEN startTime IS NOT NULL AND completed = 0 THEN 1 END) as runningInstances,
                    COUNT(CASE WHEN startTime IS NULL AND completed = 0 THEN 1 END) as pendingInstances
                 FROM TeamFormInstance"
            );
            $stats['overview'] = $stmt->fetch(PDO::FETCH_ASSOC);

            // Collections-Statistiken
            $stmt = $this->db->query(
                "SELECT fc.name, COUNT(tfi.ID) as instanceCount,
                        COUNT(CASE WHEN tfi.completed = 1 THEN 1 END) as completedCount
                 FROM FormCollection fc
                 LEFT JOIN TeamFormInstance tfi ON fc.ID = tfi.collection_ID
                 GROUP BY fc.ID, fc.name
                 ORDER BY fc.name"
            );
            $stats['collections'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $stats;
        } catch (PDOException $e) {
            error_log("Error in TeamFormInstanceController::getDashboardStats: " . $e->getMessage());
            return [];
        }
    }
}