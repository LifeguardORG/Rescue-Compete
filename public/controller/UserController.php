<?php
namespace Nutzer;

use Station\UserModel;

class UserController {
    private UserModel $model;
    public string $message = "";
    public string $messageType = "info";
    public array $modalData = [];
    public string $redirectUrl = "UserInputView.php";

    public function __construct(UserModel $model) {
        $this->model = $model;
    }

    public function handleRequest() {
        // Passwort-Update
        if (isset($_POST['update_password'])) {
            $this->handlePasswordUpdate();
            return;
        }

        // Löschen eines Nutzers
        if (isset($_POST['delete_user'])) {
            $deleteId = intval($_POST['delete_id']);

            // Prüfen, ob es sich um einen Admin-Account handelt
            $userToDelete = $this->model->read($deleteId);
            if ($userToDelete && $userToDelete['acc_typ'] === 'Admin') {
                $this->message = "Admin-Accounts können nur über die Admin-Verwaltung gelöscht werden.";
                $this->messageType = "error";
                return;
            }

            if ($this->model->delete($deleteId)) {
                $_SESSION['notification_message'] = "Benutzer wurde erfolgreich gelöscht.";
                $_SESSION['notification_type'] = "success";
                header("Location: " . $this->redirectUrl);
                exit;
            } else {
                $this->message = "Fehler beim Löschen des Nutzers.";
                $this->messageType = "error";
            }
        }

        // Hinzufügen oder Aktualisieren eines Nutzers
        if (isset($_POST['add_user'])) {
            $username     = trim($_POST['username']);
            $password     = trim($_POST['password']);
            $passwordConfirm = trim($_POST['password_confirm']);
            $acc_typ      = trim($_POST['acc_typ']);

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

            if (strlen($username) > 32) {
                $this->message = "Benutzername darf maximal 32 Zeichen lang sein.";
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

            if (empty($acc_typ)) {
                $this->message = "Account-Typ ist erforderlich.";
                $this->messageType = "error";
                return;
            }

            // Sicherheitsprüfung: Verhindere Admin-Erstellung über normale Benutzerverwaltung
            if ($acc_typ === 'Admin') {
                $this->message = "Admin-Accounts können nur über die Admin-Verwaltung erstellt werden.";
                $this->messageType = "error";
                return;
            }

            // Spezifische Validierung für Teilnehmer
            if ($acc_typ === 'Teilnehmer' && (!isset($_POST['team_number']) || empty($_POST['team_number']))) {
                $this->message = "Teilnehmer müssen einem Team zugeordnet werden.";
                $this->messageType = "error";
                return;
            }

            $passwordHash = hash_hmac("md5", $password, "Zehn zahme Ziegen zogen zehn Zentner Zucker zum Zoo");

            // Mannschaft_ID verarbeiten
            $mannschaft_ID = "";
            if(array_key_exists("team_number", $_POST) && !empty($_POST['team_number'])) {
                $mannschaft_ID = trim($_POST['team_number']);
            }

            $confirmUpdate = isset($_POST['confirm_update']) && $_POST['confirm_update'] == "1";
            $providedDuplicateId = isset($_POST['duplicate_id']) ? intval($_POST['duplicate_id']) : null;

            $entry = [
                'username'      => $username,
                'passwordHash'  => $passwordHash,
                'acc_typ' => $acc_typ,
                'mannschaft_ID' => $mannschaft_ID,
                'station_ID' => null
            ];

            $result = $this->model->addOrUpdateUser($entry, $confirmUpdate, $providedDuplicateId);

            if ($result['status'] === 'duplicate') {
                // Daten für das modale Fenster speichern
                $this->modalData = [
                    'message'      => $result['message'],
                    'duplicate_id' => $result['duplicate_id'],
                    'username'     => $username,
                    'passwordHash'  => $passwordHash,
                    'acc_typ' => $acc_typ,
                    'mannschaft_ID' => $mannschaft_ID
                ];
            } elseif ($result['status'] === 'created') {
                $_SESSION['notification_message'] = "Benutzer '{$username}' wurde erfolgreich erstellt.";
                $_SESSION['notification_type'] = "success";
                header("Location: " . $this->redirectUrl . "?view=overview");
                exit;
            } elseif ($result['status'] === 'updated') {
                $_SESSION['notification_message'] = "Benutzer '{$username}' wurde erfolgreich aktualisiert.";
                $_SESSION['notification_type'] = "success";
                header("Location: " . $this->redirectUrl . "?view=overview");
                exit;
            } else {
                $this->message = isset($result['message']) ? $result['message'] : "Ein unbekannter Fehler ist aufgetreten.";
                $this->messageType = "error";
            }
        }
    }

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

        // Sicherheitsprüfung: Verhindere Admin-Passwort-Änderung über normale Verwaltung
        if ($existingUser['acc_typ'] === 'Admin') {
            echo json_encode(['success' => false, 'message' => 'Admin-Accounts können nur über die Admin-Verwaltung verwaltet werden.']);
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
}