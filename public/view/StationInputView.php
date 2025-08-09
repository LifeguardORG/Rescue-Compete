<?php

require_once '../db/DbConnection.php';
require_once '../model/StationModel.php';
require_once '../controller/StationInputController.php';
require_once '../php_assets/CustomAlertBox.php';

use Station\StationModel;
use Station\StationInputController;

// Session-Check
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Weiterleitung, wenn Berechtigungen nicht korrekt sind
$allowedAccountTypes = ['Wettkampfleitung', 'Admin'];
if(!isset($_SESSION["acc_typ"]) || !in_array($_SESSION["acc_typ"], $allowedAccountTypes)){
    header("Location: ../index.php");
    exit;
}

// Überprüfen, ob die Datenbankverbindung ($conn) verfügbar ist
if (!isset($conn)) {
    die("<script>alert('Datenbankverbindung nicht verfügbar.');</script>");
}

// Instanzierung des Models und des Controllers
$model = new StationModel($conn);
$controller = new StationInputController($model);
$controller->handleRequest();

// Ergebnisse aus dem Controller abrufen
$modalData = $controller->modalData;
$message = $controller->message;
$stationen = $model->read();

// Werte aus dem POST-Request (falls vorhanden)
$name = isset($_POST['name']) ? trim($_POST['name']) : "";
$nr = isset($_POST['Nr']) ? trim($_POST['Nr']) : "";

// Aktuelle Ansicht bestimmen
$currentView = $_GET['view'] ?? 'overview';

// Seiten-Titel
$pageTitle = "Verwaltung der Stationen";
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Dynamischer Seitentitel -->
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="icon" type="image/x-icon" href="../assets/images/logos/ww-favicon.ico">
    <!-- CSS-Dateien einbinden -->
    <link rel="stylesheet" href="../css/Colors.css">
    <link rel="stylesheet" href="../css/Navbar.css">
    <link rel="stylesheet" href="../css/Sidebar.css">
    <link rel="stylesheet" href="../css/InputStyling.css">
    <link rel="stylesheet" href="../css/StationInputStyling.css">
</head>
<body>

<!-- Navbar wird eingebunden -->
<?php include '../php_assets/Navbar.php'; ?>

<div class="container">
    <!-- Sidebar wird eingebunden -->
    <?php include '../php_assets/Sidebar.php'; ?>

    <!-- Hauptinhalt -->
    <div class="main-content vertical">
        <h2><?php echo htmlspecialchars($pageTitle); ?></h2>

        <!-- Navigation Tabs -->
        <div class="tab-navigation">
            <button class="tab-button <?php echo $currentView === 'overview' ? 'active' : ''; ?>"
                    data-tab="overview"
                    onclick="showTab('overview')">Übersicht</button>
            <button class="tab-button <?php echo $currentView === 'create' ? 'active' : ''; ?>"
                    data-tab="create"
                    onclick="showTab('create')">Neue Station</button>
        </div>

        <!-- Statusmeldungen -->
        <?php if (!empty($message)): ?>
            <div class="message-box error">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Tab: Übersicht -->
        <div id="overview" class="tab-content <?php echo $currentView === 'overview' ? 'active' : ''; ?>">
            <div class="data-container">
                <div class="actions-bar">
                    <button class="btn primary-btn" onclick="showTab('create')">
                        Neue Station erstellen
                    </button>
                </div>

                <?php if (empty($stationen)): ?>
                    <div class="no-data">
                        <p>Keine Stationen vorhanden.</p>
                        <p><a href="#" onclick="showTab('create')">Erstellen Sie Ihre erste Station</a></p>
                    </div>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Stationsnummer</th>
                            <th>Aktionen</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($stationen as $station): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($station['name'] ?? "nicht gefunden"); ?></strong>
                                </td>
                                <td class="numeric-cell"><?php echo htmlspecialchars($station['Nr'] ?? "nicht gefunden"); ?></td>
                                <td class="action-cell">
                                    <div class="button-group">
                                        <button class="btn warning-btn small"
                                                onclick="confirmDeleteStation(<?php echo htmlspecialchars($station['ID']); ?>, '<?php echo addslashes($station['name']); ?>')">
                                            Löschen
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tab: Neue Station -->
        <div id="create" class="tab-content <?php echo $currentView === 'create' ? 'active' : ''; ?>">
            <div class="data-container">
                <h3>Neue Station erstellen</h3>

                <form method="POST" id="createStationForm">
                    <input type="hidden" name="add_station" value="1">

                    <div class="form-group">
                        <label for="name">Stationsname *</label>
                        <input type="text" id="name" name="name" required
                               placeholder="z.B. Erste Hilfe, Transport"
                               value="<?= htmlspecialchars($name); ?>">
                        <small>Geben Sie einen eindeutigen Namen für die Station ein</small>
                    </div>

                    <div class="form-group">
                        <label for="Nr">Stationsnummer *</label>
                        <input type="text" id="Nr" name="Nr" required
                               placeholder="z.B. 1, 2, 3"
                               value="<?= htmlspecialchars($nr); ?>">
                        <small>Eine Nummer zur Identifizierung der Stationen</small>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn primary-btn">Station erstellen</button>
                        <button type="button" class="btn" onclick="showTab('overview')">Abbrechen</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Modals für Alerts
if (!empty($modalData)):
    if (isset($modalData['duplicateName'])):
        echo CustomAlertBox::renderSimpleAlert(
            "duplicateNameAlert",
            "Station existiert bereits",
            "Eine Station mit diesem Namen existiert bereits."
        );
    endif;

    if (isset($modalData['duplicateNumber'])):
        $number = $modalData['number'] ?? 'unbekannt';
        echo CustomAlertBox::renderSimpleAlert(
            "duplicateNumberAlert",
            "Stationsnummer bereits vergeben",
            "Die Stationsnummer {$number} ist bereits vergeben. Bitte wählen Sie eine andere Nummer."
        );
    endif;
endif;

echo CustomAlertBox::renderSimpleConfirm(
    "confirmDeleteModal",
    "Löschen bestätigen",
    "Möchten Sie diese Station und alle dazugehörigen Protokolle mit den jeweiligen Werten wirklich löschen?",
    "deleteStation()",
    "closeModal('confirmDeleteModal')"
);
?>

<!-- JavaScript einbinden -->
<script src="../js/StationInputScript.js"></script>

<!-- Tab-Initialisierung sicherstellen -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Sicherstellen, dass der korrekte Tab angezeigt wird
        const currentView = '<?php echo $currentView; ?>';
        showTab(currentView);
    });
</script>

<?php include '../php_assets/Footer.php'; ?>

</body>
</html>