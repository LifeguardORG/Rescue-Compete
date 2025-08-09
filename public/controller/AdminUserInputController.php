<?php
namespace AdminUser;

use Station\UserModel;

/**
 * Controller für die Verwaltung von Admin-Benutzern
 * Nur Admin-Benutzer dürfen auf diese Funktionalität zugreifen
 */
class AdminUserInputController {
    private UserModel $model;
    public string $message = "";
    public string $messageType = "info";
    public array $modalData = [];
    public string $redirectUrl = "AdminUserInputView.php";

    public function __construct(UserModel $model) {
        $this->model = $model;
    }

    public function handleRequest() {
        // Passwort-Update
        if (isset($_POST['update_password'])) {
            $this->handlePasswordUpdate();
            return;
        }

        // Löschen eines Admin-Nutzers
        if (isset($_POST['delete_admin_user'])) {
            $deleteId = intval($_POST['delete_id']);

            // Verhindere Selbstlöschung
            if ($deleteId == $_SESSION['id']) {
                $this->message = "Sie können sich nicht selbst löschen.";
                $this->messageType = "error";
                return;
            }

            // Benutzer-Details für Benachrichtigung holen
            $userToDelete = $this->model->read($deleteId);
            $username = $userToDelete ? $userToDelete['username'] : 'unbekannt';

            if ($this->model->delete($deleteId)) {
                $_SESSION['notification_message'] = "Admin-Account '{$username}' wurde erfolgreich gelöscht.";
                $_SESSION['notification_type'] = "success";
                header("Location: " . $this->redirectUrl);
                exit;
            } else {
                $this->message = "Fehler beim Löschen des Admin-Nutzers.";
                $this->messageType = "error";
            }
        }

        // Hinzufügen oder Aktualisieren eines Admin-Nutzers
        if (isset($_POST['add_admin_user'])) {
            $username = trim($_POST['username']);
            $password = trim($_POST['password']);
            $passwordConfirm = trim($_POST['password_confirm']);
            $acc_typ = "Admin"; // Fest auf Admin gesetzt

            // Validierung der Eingaben
            if (empty($username)) {
                $this->message = "Benutzername ist erforderlich.";
                $this->messageType = "error";
                return;
            }

            if (strlen($username) < 3) {
                $this->message = "Benutzername muss mindestens 3 Zeichen lang sein.";
                $this->messageType = "error";
                return;
            }

            if (empty($password)) {
                $this->message = "Passwort ist erforderlich.";
                $this->messageType = "error";
                return;
            }

            if (strlen($password) < 8) {
                $this->message = "Passwort muss mindestens 8 Zeichen lang sein.";
                $this->messageType = "error";
                return;
            }

            if ($password !== $passwordConfirm) {
                $this->message = "Passwörter stimmen nicht überein.";
                $this->messageType = "error";
                return;
            }

            $passwordHash = hash_hmac("md5", $password, "Zehn zahme Ziegen zogen zehn Zentner Zucker zum Zoo");

            $confirmUpdate = isset($_POST['confirm_update']) && $_POST['confirm_update'] == "1";
            $providedDuplicateId = isset($_POST['duplicate_id']) ? intval($_POST['duplicate_id']) : null;

            $entry = [
                'username' => $username,
                'passwordHash' => $passwordHash,
                'acc_typ' => $acc_typ,
                'mannschaft_ID' => null, // Admins haben keine Mannschaft
                'station_ID' => null     // Admins haben keine Station
            ];

            $result = $this->model->addOrUpdateUser($entry, $confirmUpdate, $providedDuplicateId);

            if ($result['status'] === 'duplicate') {
                // Daten für das modale Fenster speichern
                $this->modalData = [
                    'message' => $result['message'],
                    'duplicate_id' => $result['duplicate_id'],
                    'username' => $username,
                    'passwordHash' => $passwordHash,
                    'acc_typ' => $acc_typ
                ];
            } elseif ($result['status'] === 'created') {
                $_SESSION['notification_message'] = "Admin-Account '{$username}' wurde erfolgreich erstellt.";
                $_SESSION['notification_type'] = "success";
                header("Location: " . $this->redirectUrl . "?view=overview");
                exit;
            } elseif ($result['status'] === 'updated') {
                $_SESSION['notification_message'] = "Admin-Account '{$username}' wurde erfolgreich aktualisiert.";
                $_SESSION['notification_type'] = "success";
                header("Location: " . $this->redirectUrl . "?view=overview");
                exit;
            } else {
                $this->message = isset($result['message']) ? $result['message'] : "Ein unbekannter Fehler ist aufgetreten.";
                $this->messageType = "error";
            }
        }
    }

    /**
     * Behandelt die Passwort-Aktualisierung
     */
    private function handlePasswordUpdate() {
        $userId = intval($_POST['user_id']);
        $newPassword = trim($_POST['new_password']);

        // Validierung
        if (empty($newPassword)) {
            echo json_encode(['success' => false, 'message' => 'Passwort darf nicht leer sein.']);
            exit;
        }

        if (strlen($newPassword) < 8) {
            echo json_encode(['success' => false, 'message' => 'Passwort muss mindestens 8 Zeichen lang sein.']);
            exit;
        }

        // Überprüfen, ob Benutzer existiert
        $existingUser = $this->model->read($userId);
        if (!$existingUser) {
            echo json_encode(['success' => false, 'message' => 'Benutzer nicht gefunden.']);
            exit;
        }

        // Überprüfen, ob es sich um einen Admin handelt
        if ($existingUser['acc_typ'] !== 'Admin') {
            echo json_encode(['success' => false, 'message' => 'Nur Admin-Accounts können hier verwaltet werden.']);
            exit;
        }

        // Neues Passwort hashen
        $newPasswordHash = hash_hmac("md5", $newPassword, "Zehn zahme Ziegen zogen zehn Zentner Zucker zum Zoo");

        // Passwort aktualisieren
        $success = $this->model->updatePassword($userId, $newPasswordHash);

        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Passwort erfolgreich aktualisiert.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Fehler beim Aktualisieren des Passworts.']);
        }
        exit;
    }

    /**
     * Prüft, ob der aktuelle Benutzer Admin-Rechte hat
     *
     * @return bool True wenn Admin, sonst false
     */
    public static function hasAdminPermissions(): bool {
        return isset($_SESSION['acc_typ']) && $_SESSION['acc_typ'] === 'Admin';
    }
}