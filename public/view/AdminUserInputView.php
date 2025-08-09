<?php
require_once '../db/DbConnection.php';
require_once '../model/UserModel.php';
require_once '../controller/AdminUserInputController.php';
require_once '../php_assets/CustomAlertBox.php';

use AdminUser\AdminUserInputController;
use Station\UserModel;

// Session-Check
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Prüfen, ob der Benutzer angemeldet ist und Admin-Rechte hat
if (!isset($_SESSION['id']) || !isset($_SESSION['login']) || $_SESSION['login'] !== 'ok' || !AdminUserInputController::hasAdminPermissions()) {
    header("Location: ../index.php");
    exit;
}

if (!isset($conn)) {
    die("Datenbankverbindung nicht verfügbar.");
}

// Benachrichtigungen aus Session abrufen
$sessionMessage = "";
$sessionMessageType = "";
if (isset($_SESSION['notification_message'])) {
    $sessionMessage = $_SESSION['notification_message'];
    $sessionMessageType = $_SESSION['notification_type'] ?? 'info';
    unset($_SESSION['notification_message'], $_SESSION['notification_type']);
}

// Model und Controller instanziieren
$model = new UserModel($conn);
$controller = new AdminUserInputController($model);
$controller->handleRequest();

// Daten für die View
$modalData = $controller->modalData;
$message = $controller->message;
$messageType = $controller->messageType;

// Session-Nachrichten haben Priorität vor Controller-Nachrichten
if (!empty($sessionMessage)) {
    $message = $sessionMessage;
    $messageType = $sessionMessageType;
}

$adminUsers = $model->readAdminUsers(); // Nur Admin-Benutzer laden

// Aktuelle Ansicht bestimmen
$currentView = $_GET['view'] ?? 'overview';

$pageTitle = "Admin-Accounts Verwaltung";
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RescueCompete - <?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="icon" type="image/x-icon" href="../assets/images/logos/ww-favicon.ico">
    <link rel="stylesheet" href="../css/Colors.css">
    <link rel="stylesheet" href="../css/Navbar.css">
    <link rel="stylesheet" href="../css/Sidebar.css">
    <link rel="stylesheet" href="../css/InputStyling.css">
    <link rel="stylesheet" href="../css/FormCollectionViewStyling.css">
    <link rel="stylesheet" href="../css/AdminUserInputViewStyling.css">
</head>
<body>
<!-- Navbar -->
<?php include '../php_assets/Navbar.php'; ?>

<div class="container">
    <!-- Sidebar -->
    <?php include '../php_assets/Sidebar.php'; ?>

    <!-- Hauptinhalt -->
    <div class="main-content vertical">
        <h2 class="main-title"><?php echo htmlspecialchars($pageTitle); ?></h2>

        <!-- Navigation Tabs -->
        <div class="tab-navigation">
            <button class="tab-button <?php echo $currentView === 'overview' ? 'active' : ''; ?>"
                    data-tab="overview"
                    onclick="showTab('overview')">Übersicht</button>
            <button class="tab-button <?php echo $currentView === 'create' ? 'active' : ''; ?>"
                    data-tab="create"
                    onclick="showTab('create')">Neuen Admin erstellen</button>
        </div>

        <!-- Tab: Übersicht -->
        <div id="overview" class="tab-content <?php echo $currentView === 'overview' ? 'active' : ''; ?>">
            <div class="data-container">
                <div class="actions-bar">
                    <button class="btn primary-btn" onclick="showTab('create')">
                        Neuen Admin erstellen
                    </button>
                    <a href="UserInputView.php" class="btn secondary-btn">
                        Zur normalen Benutzerverwaltung
                    </a>
                </div>

                <?php if (empty($adminUsers)): ?>
                    <div class="no-data">
                        <p>Keine Admin-Accounts vorhanden.</p>
                        <p><a href="#" onclick="showTab('create')">Erstellen Sie den ersten Admin-Account</a></p>
                    </div>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                        <tr>
                            <th>Benutzername</th>
                            <th>Neues Passwort</th>
                            <th>Account-Typ</th>
                            <th>Erstellungsdatum</th>
                            <th>Aktionen</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($adminUsers as $user): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                    <?php if ($user['ID'] == $_SESSION['id']): ?>
                                        <br><small class="current-user-indicator">Sie sind angemeldet</small>
                                    <?php endif; ?>
                                </td>
                                <td class="password-update-cell">
                                    <div class="password-update-container">
                                        <input type="password"
                                               class="password-input"
                                               id="password_<?php echo $user['ID']; ?>"
                                               placeholder="Neues Passwort eingeben"
                                               minlength="8">
                                        <button type="button"
                                                class="btn update-password-btn small"
                                                onclick="updateUserPassword(<?php echo $user['ID']; ?>)">
                                            Aktualisieren
                                        </button>
                                    </div>
                                </td>
                                <td class="type-cell">
                                    <span class="status-badge admin"><?php echo htmlspecialchars($user['acc_typ']); ?></span>
                                </td>
                                <td class="date-cell">
                                    <!-- Hier könnte das Erstellungsdatum stehen, falls verfügbar -->
                                    -
                                </td>
                                <td class="action-cell">
                                    <div class="button-group">
                                        <?php if ($user['ID'] != $_SESSION['id']): ?>
                                            <button class="btn warning-btn small"
                                                    onclick="confirmDeleteAdmin(<?php echo $user['ID']; ?>, '<?php echo addslashes($user['username']); ?>')">
                                                Löschen
                                            </button>
                                        <?php else: ?>
                                            <span class="btn small disabled">Eigener Account</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tab: Neuen Admin erstellen -->
        <div id="create" class="tab-content <?php echo $currentView === 'create' ? 'active' : ''; ?>">
            <div class="data-container">
                <h3>Neuen Admin-Account erstellen</h3>

                <form method="POST" id="createAdminForm">
                    <input type="hidden" name="action" value="create_admin">

                    <div class="form-group">
                        <label for="username">Benutzername *</label>
                        <input type="text" id="username" name="username" required
                               placeholder="z.B. admin.mueller">
                        <small>Der Benutzername sollte eindeutig und leicht zu merken sein.</small>
                    </div>

                    <div class="form-group">
                        <label for="password">Passwort *</label>
                        <input type="password" id="password" name="password" required
                               placeholder="Sicheres Passwort eingeben">
                        <small>Verwenden Sie ein starkes Passwort mit mindestens 8 Zeichen.</small>
                    </div>

                    <div class="form-group">
                        <label for="password_confirm">Passwort bestätigen *</label>
                        <input type="password" id="password_confirm" name="password_confirm" required
                               placeholder="Passwort wiederholen">
                        <div class="validation-message" id="password-mismatch">
                            Die Passwörter stimmen nicht überein.
                        </div>
                    </div>

                    <div class="info-box">
                        <h4>Hinweise zur Admin-Account-Erstellung:</h4>
                        <ul>
                            <li>Admin-Accounts haben vollständige Berechtigung über die gesamte Anwendung</li>
                            <li>Admins können andere Admin-Accounts erstellen und löschen</li>
                            <li>Admins können alle anderen Benutzertypen verwalten</li>
                            <li>Sie können Ihren eigenen Account nicht löschen</li>
                        </ul>
                    </div>

                    <div class="form-actions">
                        <button type="submit" name="add_admin_user" class="btn primary-btn">Admin-Account erstellen</button>
                        <button type="button" class="btn" onclick="showTab('overview')">Abbrechen</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
<?php
// Admin-Löschung bestätigen
echo CustomAlertBox::renderSimpleConfirm(
    "confirmDeleteAdminModal",
    "Admin-Account löschen",
    "Möchten Sie diesen Admin-Account wirklich löschen? Diese Aktion kann nicht rückgängig gemacht werden.",
    "deleteAdmin()",
    "closeModal('confirmDeleteAdminModal')"
);

// Duplikat-Modal
if (!empty($modalData)):
    $alert = new CustomAlertBox("confirmDuplicateAdmin");
    $alert->setTitle("Duplikat gefunden");
    $alert->setMessage("Ein Admin-Account mit diesem Namen existiert bereits. Möchten Sie diesen aktualisieren?");
    $alert->setData([
        'username' => $modalData['username'] ?? "",
        'passwordHash' => $modalData['passwordHash'] ?? "",
        'acc_typ' => $modalData['acc_typ'] ?? "",
        'duplicate_id' => $modalData['duplicate_id'] ?? "",
        'confirm_update' => "1",
        'add_admin_user' => "1"
    ]);
    $alert->addButton("Ja", "", "btn primary-btn", "submit");
    $alert->addButton("Nein", "document.getElementById('confirmDuplicateAdmin').classList.remove('active');");
    echo $alert->render();
endif;

// Erfolgs-/Fehlermeldungen
if (!empty($message)):
    $alertType = ($messageType === 'success') ? 'Erfolg' :
        (($messageType === 'error') ? 'Fehler' : 'Hinweis');
    echo CustomAlertBox::renderSimpleAlert(
        "messageAlert",
        $alertType,
        $message
    );
    echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            setTimeout(function() {
                const messageAlert = document.getElementById("messageAlert");
                if (messageAlert) {
                    messageAlert.classList.add("active");
                }
            }, 100);
        });
    </script>';
endif;
?>

<!-- JavaScript einbinden -->
<script src="../js/AdminUserInputViewScript.js"></script>

<!-- Tab-Initialisierung sicherstellen -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Sicherstellen, dass der korrekte Tab angezeigt wird
        const currentView = '<?php echo $currentView; ?>';
        showTab(currentView);

        // Duplikat-Modal anzeigen, falls vorhanden
        <?php if (!empty($modalData)): ?>
        setTimeout(function() {
            const duplicateModal = document.getElementById('confirmDuplicateAdmin');
            if (duplicateModal) {
                duplicateModal.classList.add('active');
            }
        }, 100);
        <?php endif; ?>
    });
</script>

<?php include '../php_assets/Footer.php'; ?>

</body>
</html>