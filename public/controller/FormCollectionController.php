<?php

namespace FormCollection;

use FormCollection\FormCollectionModel;
use PDO;
use PDOException;
use Exception;

class FormCollectionController
{
    private FormCollectionModel $model;
    private PDO $db;

    // Public Properties für View-Zugriff
    public array $collections = [];
    public array $questionPools = [];
    public array $stations = [];
    public string $message = '';
    public string $messageType = 'info';
    public ?array $currentCollection = null;
    public array $selectedQuestions = [];
    public array $collectionTokens = [];
    public array $performanceStats = [];
    public array $teamProgress = [];
    public array $validationErrors = [];

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->model = new FormCollectionModel($db);
    }

    /**
     * Hauptmethod zur Verarbeitung aller Requests
     */
    public function handleRequest(): void
    {
        // AJAX-Requests zuerst behandeln (bevor HTML-Output beginnt)
        if ($this->isAjaxRequest()) {
            $this->handleAjaxRequest();
            return; // Wichtig: Stoppt weitere Verarbeitung
        }

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
     * Prüft ob es sich um einen AJAX-Request handelt
     */
    private function isAjaxRequest(): bool
    {
        return (isset($_GET['ajax']) && $_GET['ajax'] === '1') ||
            (isset($_POST['ajax']) && $_POST['ajax'] === '1') ||
            (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    }

    /**
     * Behandelt AJAX-Requests
     */
    private function handleAjaxRequest(): void
    {
        try {
            $action = $_GET['action'] ?? $_POST['action'] ?? '';

            switch ($action) {
                case 'load_questions':
                    $this->handleLoadQuestionsAjax();
                    break;

                case 'check_name':
                    $this->handleCheckNameAjax();
                    break;

                default:
                    $this->sendJsonResponse(false, 'Unbekannte AJAX-Aktion.');
            }
        } catch (Exception $e) {
            error_log("Error in FormCollectionController::handleAjaxRequest: " . $e->getMessage());
            $this->sendJsonResponse(false, 'Ein unerwarteter Fehler ist aufgetreten.');
        }
    }

    /**
     * Lädt grundlegende Daten für die View
     */
    private function loadBaseData(): void
    {
        $this->collections = $this->model->readCollection() ?? [];
        $this->questionPools = $this->model->getAvailableQuestionPools();
        $this->stations = $this->model->getAvailableStations();
        $this->performanceStats = $this->model->getCollectionPerformance();
        $this->teamProgress = $this->model->getTeamCollectionProgress();
    }

    /**
     * Verarbeitet POST-Requests
     */
    private function handlePostRequest(): void
    {
        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'create_collection':
                $this->handleCreateCollection();
                break;

            case 'delete_collection':
                $this->handleDeleteCollection();
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
            case 'view_collection':
                $this->handleViewCollection();
                break;

            case 'view_tokens':
                $this->handleViewTokens();
                break;

            default:
                // Keine spezielle Aktion - Standard-Daten sind bereits geladen
                break;
        }
    }

    /**
     * Erstellt eine neue FormCollection
     */
    private function handleCreateCollection(): void
    {
        try {
            // Input-Validierung
            $requiredFields = ['name', 'question_pool', 'forms_count', 'time_limit'];
            foreach ($requiredFields as $field) {
                if (empty($_POST[$field])) {
                    $this->message = "Feld '{$field}' ist erforderlich.";
                    $this->messageType = 'error';
                    return;
                }
            }

            // Fragen-IDs validieren
            if (empty($_POST['question_ids']) || !is_array($_POST['question_ids'])) {
                $this->message = 'Bitte wählen Sie mindestens eine Frage aus.';
                $this->messageType = 'error';
                return;
            }

            $questionIds = array_map('intval', $_POST['question_ids']);
            $formsCount = intval($_POST['forms_count']);

            // Collection-Daten zusammenstellen
            $collectionData = [
                'name' => trim($_POST['name']),
                'description' => trim($_POST['description'] ?? ''),
                'timeLimit' => intval($_POST['time_limit']),
                'totalQuestions' => count($questionIds),
                'formsCount' => $formsCount,
                'station_ID' => !empty($_POST['station_id']) ? intval($_POST['station_id']) : null
            ];

            // Model-Validierung durchführen
            $this->validationErrors = $this->model->validateCollectionData($collectionData);

            if (!empty($this->validationErrors)) {
                $this->message = 'Bitte korrigieren Sie die Eingabefehler.';
                $this->messageType = 'error';
                return;
            }

            // Collection erstellen
            $collectionId = $this->model->createCollection($collectionData, $questionIds);

            if ($collectionId) {
                $this->message = "Formular-Gruppe '{$collectionData['name']}' wurde erfolgreich erstellt.";
                $this->messageType = 'success';

                // Daten neu laden
                $this->loadBaseData();

                // Validierungsfehler zurücksetzen nach erfolgreichem Erstellen
                $this->validationErrors = [];
            } else {
                $this->message = 'Fehler beim Erstellen der Formular-Gruppe.';
                $this->messageType = 'error';
            }

        } catch (Exception $e) {
            error_log("Error in FormCollectionController::handleCreateCollection: " . $e->getMessage());
            $this->message = 'Ein unerwarteter Fehler ist aufgetreten.';
            $this->messageType = 'error';
        }
    }

    /**
     * Löscht eine FormCollection
     */
    private function handleDeleteCollection(): void
    {
        try {
            $collectionId = intval($_POST['collection_id'] ?? 0);
            $confirmDelete = $_POST['confirm_delete'] ?? '';

            if ($collectionId <= 0) {
                $this->message = 'Ungültige Formular-Gruppen-ID.';
                $this->messageType = 'error';
                return;
            }

            if ($confirmDelete !== '1') {
                $this->message = 'Löschung nicht bestätigt.';
                $this->messageType = 'error';
                return;
            }

            // Collection-Name für Meldung abrufen
            $collection = $this->model->readCollection($collectionId);
            $collectionName = $collection['name'] ?? 'Unbekannt';

            // Löschen
            if ($this->model->deleteCollection($collectionId)) {
                $this->message = "Formular-Gruppe '{$collectionName}' wurde erfolgreich gelöscht.";
                $this->messageType = 'success';

                // Daten neu laden
                $this->loadBaseData();
            } else {
                $this->message = 'Fehler beim Löschen der Formular-Gruppe.';
                $this->messageType = 'error';
            }

        } catch (Exception $e) {
            error_log("Error in FormCollectionController::handleDeleteCollection: " . $e->getMessage());
            $this->message = 'Ein unerwarteter Fehler ist aufgetreten.';
            $this->messageType = 'error';
        }
    }

    /**
     * Verarbeitet abgelaufene Formulare
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

        } catch (Exception $e) {
            error_log("Error in FormCollectionController::handleProcessExpired: " . $e->getMessage());
            $this->message = 'Fehler beim Verarbeiten abgelaufener Formulare.';
            $this->messageType = 'error';
        }
    }

    /**
     * Zeigt Collection-Details an
     */
    private function handleViewCollection(): void
    {
        try {
            $collectionId = intval($_GET['collection_id'] ?? 0);

            if ($collectionId <= 0) {
                $this->message = 'Ungültige Formular-Gruppen-ID.';
                $this->messageType = 'error';
                return;
            }

            $this->currentCollection = $this->model->readCollection($collectionId);

            if (!$this->currentCollection) {
                $this->message = 'Formular-Gruppe nicht gefunden.';
                $this->messageType = 'error';
                return;
            }

            $this->selectedQuestions = $this->model->getCollectionQuestions($collectionId);

        } catch (Exception $e) {
            error_log("Error in FormCollectionController::handleViewCollection: " . $e->getMessage());
            $this->message = 'Fehler beim Laden der Formular-Gruppen-Details.';
            $this->messageType = 'error';
        }
    }

    /**
     * Zeigt QR-Code-Tokens einer Collection an
     */
    private function handleViewTokens(): void
    {
        try {
            $collectionId = intval($_GET['collection_id'] ?? 0);

            if ($collectionId <= 0) {
                $this->message = 'Ungültige Formular-Gruppen-ID.';
                $this->messageType = 'error';
                return;
            }

            // Basis-URL für QR-Codes
            $baseUrl = 'http://' . $_SERVER['HTTP_HOST'];

            $this->collectionTokens = $this->model->getCollectionTokens($collectionId, $baseUrl);

        } catch (Exception $e) {
            error_log("Error in FormCollectionController::handleViewTokens: " . $e->getMessage());
            $this->message = 'Fehler beim Laden der QR-Code-Tokens.';
            $this->messageType = 'error';
        }
    }

    /**
     * Lädt Fragen für AJAX-Request und gibt JSON zurück
     */
    private function handleLoadQuestionsAjax(): void
    {
        try {
            $poolId = intval($_GET['pool_id'] ?? 0);

            if ($poolId <= 0) {
                $this->sendJsonResponse(false, 'Ungültige Pool-ID.');
                return;
            }

            // Model-Methode aufrufen
            $questions = $this->model->getQuestionsByPool($poolId);

            $this->sendJsonResponse(true, 'Fragen erfolgreich geladen.', [
                'questions' => $questions,
                'count' => count($questions)
            ]);

        } catch (Exception $e) {
            error_log("Error in FormCollectionController::handleLoadQuestionsAjax: " . $e->getMessage());
            $this->sendJsonResponse(false, 'Fehler beim Laden der Fragen.');
        }
    }

    /**
     * Prüft Namen-Duplikate für AJAX-Request
     */
    private function handleCheckNameAjax(): void
    {
        try {
            $name = trim($_GET['name'] ?? '');
            $excludeId = isset($_GET['exclude_id']) ? intval($_GET['exclude_id']) : null;

            if (empty($name)) {
                $this->sendJsonResponse(true, 'Kein Name angegeben.', ['exists' => false]);
                return;
            }

            $exists = $this->model->checkNameExists($name, $excludeId);

            $this->sendJsonResponse(true, $exists ? 'Name bereits vorhanden.' : 'Name verfügbar.', [
                'exists' => $exists,
                'name' => $name
            ]);

        } catch (Exception $e) {
            error_log("Error in FormCollectionController::handleCheckNameAjax: " . $e->getMessage());
            $this->sendJsonResponse(false, 'Fehler beim Prüfen des Namens.');
        }
    }

    /**
     * Sendet JSON-Response für AJAX-Requests
     */
    private function sendJsonResponse(bool $success, string $message, array $data = []): void
    {
        // Sicherstellen, dass keine vorherige Ausgabe erfolgt ist
        if (ob_get_level()) {
            ob_clean();
        }

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

        $response = [
            'success' => $success,
            'message' => $message
        ];

        if (!empty($data)) {
            $response = array_merge($response, $data);
        }

        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Hilfsfunktion zum Validieren von Array-Eingaben
     */
    private function validateArrayInput(array $data, array $requiredFields): bool
    {
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === '')) {
                return false;
            }
        }
        return true;
    }

    /**
     * Sanitisiert String-Eingaben
     */
    private function sanitizeString(string $input): string
    {
        return trim(filter_var($input, FILTER_SANITIZE_STRING));
    }

    /**
     * Validiert und sanitisiert Integer-Eingaben
     */
    private function sanitizeInt($input): int
    {
        return filter_var($input, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 0]
        ]) ?: 0;
    }

    /**
     * Gibt Validierungsfehler für ein bestimmtes Feld zurück
     */
    public function getValidationError(string $field): string
    {
        return $this->validationErrors[$field] ?? '';
    }

    /**
     * Prüft ob ein bestimmtes Feld einen Validierungsfehler hat
     */
    public function hasValidationError(string $field): bool
    {
        return isset($this->validationErrors[$field]);
    }
}