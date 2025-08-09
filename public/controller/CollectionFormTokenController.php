<?php

namespace FormCollection;

use FormCollection\CollectionFormTokenModel;
use PDO;
use PDOException;

/**
 * Controller-Klasse für die Verwaltung von CollectionFormTokens
 * Verarbeitet HTTP-Requests für Token-Management
 */
class CollectionFormTokenController
{
    private CollectionFormTokenModel $model;
    private PDO $db;

    // Public Properties für View-Zugriff
    public array $tokens = [];
    public string $message = '';
    public string $messageType = 'info';
    public ?array $currentToken = null;
    public array $usageStats = [];

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->model = new CollectionFormTokenModel($db);
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
        $this->tokens = $this->model->readToken() ?? [];
    }

    /**
     * Verarbeitet POST-Requests
     */
    private function handlePostRequest(): void
    {
        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'create_token':
                $this->handleCreateToken();
                break;

            case 'update_token':
                $this->handleUpdateToken();
                break;

            case 'delete_token':
                $this->handleDeleteToken();
                break;

            case 'regenerate_tokens':
                $this->handleRegenerateTokens();
                break;

            case 'resolve_token':
                $this->handleResolveToken();
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
            case 'view_token':
                $this->handleViewToken();
                break;

            case 'get_tokens_by_collection':
                $this->handleGetTokensByCollection();
                break;

            case 'get_usage_stats':
                $this->handleGetUsageStats();
                break;

            case 'download_qr_codes':
                $this->handleDownloadQrCodes();
                break;

            default:
                // Keine spezielle Aktion - Standard-Daten sind bereits geladen
                break;
        }
    }

    /**
     * Erstellt einen neuen Token
     */
    private function handleCreateToken(): void
    {
        try {
            // Input-Validierung
            $requiredFields = ['collection_id', 'form_number'];
            foreach ($requiredFields as $field) {
                if (empty($_POST[$field])) {
                    $this->message = "Feld '{$field}' ist erforderlich.";
                    $this->messageType = 'error';
                    return;
                }
            }

            $collectionId = intval($_POST['collection_id']);
            $formNumber = intval($_POST['form_number']);

            // Validierung
            if ($collectionId <= 0 || $formNumber <= 0) {
                $this->message = 'Ungültige Collection-ID oder Formularnummer.';
                $this->messageType = 'error';
                return;
            }

            // Token erstellen
            $token = $this->model->createToken($collectionId, $formNumber);

            if ($token) {
                $this->message = "Token erfolgreich erstellt: {$token}";
                $this->messageType = 'success';
                $this->loadBaseData(); // Daten neu laden
            } else {
                $this->message = 'Fehler beim Erstellen des Tokens.';
                $this->messageType = 'error';
            }

        } catch (\Exception $e) {
            error_log("Error in CollectionFormTokenController::handleCreateToken: " . $e->getMessage());
            $this->message = 'Ein unerwarteter Fehler ist aufgetreten.';
            $this->messageType = 'error';
        }
    }

    /**
     * Aktualisiert einen bestehenden Token
     */
    private function handleUpdateToken(): void
    {
        try {
            $tokenId = intval($_POST['token_id'] ?? 0);

            if ($tokenId <= 0) {
                $this->message = 'Ungültige Token-ID.';
                $this->messageType = 'error';
                return;
            }

            $tokenData = [
                'collection_ID' => intval($_POST['collection_id']),
                'formNumber' => intval($_POST['form_number']),
                'token' => trim($_POST['token'] ?? '')
            ];

            // Validierung
            if ($tokenData['collection_ID'] <= 0 || $tokenData['formNumber'] <= 0 || empty($tokenData['token'])) {
                $this->message = 'Alle Felder sind erforderlich und müssen gültige Werte enthalten.';
                $this->messageType = 'error';
                return;
            }

            // Token aktualisieren
            if ($this->model->updateToken($tokenId, $tokenData)) {
                $this->message = 'Token erfolgreich aktualisiert.';
                $this->messageType = 'success';
                $this->loadBaseData(); // Daten neu laden
            } else {
                $this->message = 'Fehler beim Aktualisieren des Tokens.';
                $this->messageType = 'error';
            }

        } catch (\Exception $e) {
            error_log("Error in CollectionFormTokenController::handleUpdateToken: " . $e->getMessage());
            $this->message = 'Ein unerwarteter Fehler ist aufgetreten.';
            $this->messageType = 'error';
        }
    }

    /**
     * Löscht einen Token
     */
    private function handleDeleteToken(): void
    {
        try {
            $tokenId = intval($_POST['token_id'] ?? 0);
            $confirmDelete = $_POST['confirm_delete'] ?? '';

            if ($tokenId <= 0) {
                $this->message = 'Ungültige Token-ID.';
                $this->messageType = 'error';
                return;
            }

            if ($confirmDelete !== '1') {
                $this->message = 'Löschung nicht bestätigt.';
                $this->messageType = 'error';
                return;
            }

            // Token löschen
            if ($this->model->deleteToken($tokenId)) {
                $this->message = 'Token erfolgreich gelöscht.';
                $this->messageType = 'success';
                $this->loadBaseData(); // Daten neu laden
            } else {
                $this->message = 'Fehler beim Löschen des Tokens.';
                $this->messageType = 'error';
            }

        } catch (\Exception $e) {
            error_log("Error in CollectionFormTokenController::handleDeleteToken: " . $e->getMessage());
            $this->message = 'Ein unerwarteter Fehler ist aufgetreten.';
            $this->messageType = 'error';
        }
    }

    /**
     * Regeneriert alle Tokens für eine Collection
     */
    private function handleRegenerateTokens(): void
    {
        try {
            $collectionId = intval($_POST['collection_id'] ?? 0);
            $formsCount = intval($_POST['forms_count'] ?? 0);

            if ($collectionId <= 0 || $formsCount <= 0) {
                $this->message = 'Ungültige Collection-ID oder Anzahl der Formulare.';
                $this->messageType = 'error';
                return;
            }

            // Tokens regenerieren
            if ($this->model->regenerateTokensForCollection($collectionId, $formsCount)) {
                $this->message = "Alle Tokens für Collection erfolgreich regeneriert ({$formsCount} Tokens).";
                $this->messageType = 'success';
                $this->loadBaseData(); // Daten neu laden
            } else {
                $this->message = 'Fehler beim Regenerieren der Tokens.';
                $this->messageType = 'error';
            }

        } catch (\Exception $e) {
            error_log("Error in CollectionFormTokenController::handleRegenerateTokens: " . $e->getMessage());
            $this->message = 'Ein unerwarteter Fehler ist aufgetreten.';
            $this->messageType = 'error';
        }
    }

    /**
     * Löst einen Token auf (für API-Calls)
     */
    private function handleResolveToken(): void
    {
        try {
            $tokenString = trim($_POST['token'] ?? '');

            if (empty($tokenString)) {
                $this->sendJsonResponse(false, 'Token-String ist erforderlich.');
                return;
            }

            $tokenInfo = $this->model->resolveToken($tokenString);

            if ($tokenInfo) {
                $this->sendJsonResponse(true, 'Token erfolgreich aufgelöst.', [
                    'tokenInfo' => $tokenInfo
                ]);
            } else {
                $this->sendJsonResponse(false, 'Token nicht gefunden oder ungültig.');
            }

        } catch (\Exception $e) {
            error_log("Error in CollectionFormTokenController::handleResolveToken: " . $e->getMessage());
            $this->sendJsonResponse(false, 'Fehler beim Auflösen des Tokens.');
        }
    }

    /**
     * Zeigt Token-Details an
     */
    private function handleViewToken(): void
    {
        try {
            $tokenId = intval($_GET['token_id'] ?? 0);

            if ($tokenId <= 0) {
                $this->message = 'Ungültige Token-ID.';
                $this->messageType = 'error';
                return;
            }

            $this->currentToken = $this->model->readToken($tokenId);

            if (!$this->currentToken) {
                $this->message = 'Token nicht gefunden.';
                $this->messageType = 'error';
                return;
            }

        } catch (\Exception $e) {
            error_log("Error in CollectionFormTokenController::handleViewToken: " . $e->getMessage());
            $this->message = 'Fehler beim Laden der Token-Details.';
            $this->messageType = 'error';
        }
    }

    /**
     * Holt alle Tokens für eine Collection (AJAX)
     */
    private function handleGetTokensByCollection(): void
    {
        try {
            $collectionId = intval($_GET['collection_id'] ?? 0);

            if ($collectionId <= 0) {
                $this->sendJsonResponse(false, 'Ungültige Collection-ID.');
                return;
            }

            $tokens = $this->model->getTokensByCollection($collectionId);

            $this->sendJsonResponse(true, 'Tokens erfolgreich geladen.', [
                'tokens' => $tokens,
                'count' => count($tokens)
            ]);

        } catch (\Exception $e) {
            error_log("Error in CollectionFormTokenController::handleGetTokensByCollection: " . $e->getMessage());
            $this->sendJsonResponse(false, 'Fehler beim Laden der Tokens.');
        }
    }

    /**
     * Holt Token-Nutzungsstatistiken (AJAX)
     */
    private function handleGetUsageStats(): void
    {
        try {
            $collectionId = isset($_GET['collection_id']) ? intval($_GET['collection_id']) : null;

            $this->usageStats = $this->model->getTokenUsageStats($collectionId);

            $this->sendJsonResponse(true, 'Statistiken erfolgreich geladen.', [
                'stats' => $this->usageStats,
                'count' => count($this->usageStats)
            ]);

        } catch (\Exception $e) {
            error_log("Error in CollectionFormTokenController::handleGetUsageStats: " . $e->getMessage());
            $this->sendJsonResponse(false, 'Fehler beim Laden der Statistiken.');
        }
    }

    /**
     * Bereitet QR-Code-Download vor
     */
    private function handleDownloadQrCodes(): void
    {
        try {
            $collectionId = intval($_GET['collection_id'] ?? 0);

            if ($collectionId <= 0) {
                $this->message = 'Ungültige Collection-ID.';
                $this->messageType = 'error';
                return;
            }

            // Basis-URL für QR-Codes
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $baseUrl = $protocol . '://' . $host;

            $tokensWithUrls = $this->model->getTokensWithUrls($collectionId, $baseUrl);

            if (empty($tokensWithUrls)) {
                $this->message = 'Keine Tokens für diese Collection gefunden.';
                $this->messageType = 'error';
                return;
            }

            // Für QR-Code-Generierung in der View bereitstellen
            $this->tokens = $tokensWithUrls;
            $this->message = 'QR-Codes bereit zum Download.';
            $this->messageType = 'success';

        } catch (\Exception $e) {
            error_log("Error in CollectionFormTokenController::handleDownloadQrCodes: " . $e->getMessage());
            $this->message = 'Fehler beim Vorbereiten der QR-Codes.';
            $this->messageType = 'error';
        }
    }

    /**
     * Validiert Token-Input-Daten
     *
     * @param array $data Input-Daten
     * @param array $requiredFields Erforderliche Felder
     * @return bool True wenn alle Validierungen bestanden
     */
    private function validateTokenInput(array $data, array $requiredFields): bool
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
     * Sanitisiert String-Eingaben
     *
     * @param string $input Zu sanitisierende Eingabe
     * @return string Sanitisierte Eingabe
     */
    private function sanitizeString(string $input): string
    {
        return trim(filter_var($input, FILTER_SANITIZE_STRING));
    }

    /**
     * Validiert und sanitisiert Integer-Eingaben
     *
     * @param mixed $input Zu validierende Eingabe
     * @return int Sanitisierte Integer-Eingabe
     */
    private function sanitizeInt($input): int
    {
        return filter_var($input, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 0]
        ]) ?: 0;
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
     * Holt verfügbare Collections für Token-Erstellung
     *
     * @return array Array mit Collection-Daten
     */
    public function getAvailableCollections(): array
    {
        try {
            $stmt = $this->db->query("SELECT ID, name, formsCount FROM FormCollection ORDER BY name");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in CollectionFormTokenController::getAvailableCollections: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Prüft ob ein Token bereits verwendet wird
     *
     * @param string $tokenString Token-String
     * @return array Nutzungsinformationen
     */
    public function checkTokenUsage(string $tokenString): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT COUNT(tfi.ID) as usageCount,
                        COUNT(CASE WHEN tfi.completed = 1 THEN 1 END) as completedCount
                 FROM CollectionFormToken cft
                 LEFT JOIN TeamFormInstance tfi ON cft.collection_ID = tfi.collection_ID 
                                                AND cft.formNumber = tfi.formNumber
                 WHERE cft.token = :token"
            );
            $stmt->execute([':token' => $tokenString]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['usageCount' => 0, 'completedCount' => 0];
        } catch (PDOException $e) {
            error_log("Error in CollectionFormTokenController::checkTokenUsage: " . $e->getMessage());
            return ['usageCount' => 0, 'completedCount' => 0];
        }
    }

    /**
     * Generiert eine QR-Code-URL für einen Token
     *
     * @param string $tokenString Token-String
     * @param string $baseUrl Basis-URL (optional)
     * @return string QR-Code-URL
     */
    public function generateQrCodeUrl(string $tokenString, string $baseUrl = ''): string
    {
        if (empty($baseUrl)) {
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $baseUrl = $protocol . '://' . $host;
        }

        return $baseUrl . '/view/FormRedirect.php?code=' . urlencode($tokenString);
    }
}
