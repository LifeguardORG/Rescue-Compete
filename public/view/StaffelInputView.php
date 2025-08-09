<?php

require_once '../db/DbConnection.php';
require_once '../model/StaffelModel.php';
require_once '../controller/StaffelInputController.php';
require_once '../php_assets/CustomAlertBox.php';

use Staffel\StaffelInputController;
use Staffel\StaffelModel;

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
$model = new StaffelModel($conn);
$controller = new StaffelInputController($model);
$controller->handleRequest();

// Ergebnisse aus dem Controller abrufen
$modalData = $controller->modalData;
$message = $controller->message;
$staffeln = $model->read();

// Werte aus dem POST-Request (falls vorhanden)
$name = isset($_POST['name']) ? trim($_POST['name']) : "";

// Aktuelle Ansicht bestimmen
$currentView = $_GET['view'] ?? 'overview';

// Seiten-Titel
$pageTitle = "Verwaltung der Staffeln";
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
    <link rel="stylesheet" href="../css/StaffelInputStyling.css">
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
                    onclick="showTab('create')">Neue Staffel</button>
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
                        Neue Staffel erstellen
                    </button>
                </div>

                <?php if (empty($staffeln)): ?>
                    <div class="no-data">
                        <p>Keine Staffeln vorhanden.</p>
                        <p><a href="#" onclick="showTab('create')">Erstellen Sie Ihre erste Staffel</a></p>
                    </div>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Aktionen</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($staffeln as $staffel): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($staffel['name'] ?? "nicht gefunden"); ?></strong>
                                </td>
                                <td class="action-cell">
                                    <div class="button-group">
                                        <button class="btn warning-btn small"
                                                onclick="confirmDeleteStaffel(<?php echo htmlspecialchars($staffel['ID']); ?>, '<?php echo addslashes($staffel['name']); ?>')">
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

        <!-- Tab: Neue Staffel -->
        <div id="create" class="tab-content <?php echo $currentView === 'create' ? 'active' : ''; ?>">
            <div class="data-container">
                <h3>Neue Staffel erstellen</h3>

                <form method="POST" id="createStaffelForm">
                    <input type="hidden" name="add_Staffel" value="1">

                    <div class="form-group">
                        <label for="name">Staffelname *</label>
                        <input type="text" id="name" name="name" required
                               placeholder="z.B. Jugend A, Damen, Herren"
                               value="<?= htmlspecialchars($name); ?>">
                        <small>Geben Sie einen eindeutigen Namen für die Staffel ein</small>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn primary-btn">Staffel erstellen</button>
                        <button type="button" class="btn" onclick="showTab('overview')">Abbrechen</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Modals für Alerts
if (!empty($modalData) && isset($modalData['duplicate'])):
    echo CustomAlertBox::renderSimpleAlert(
        "duplicateStaffelAlert",
        "Staffel existiert bereits",
        "Eine Staffel mit diesem Namen existiert bereits."
    );
endif;

echo CustomAlertBox::renderSimpleConfirm(
    "confirmDeleteModal",
    "Löschen bestätigen",
    "Möchten Sie diese Staffel mit den enthaltenen Zeiten wirklich löschen?",
    "deleteStaffel()",
    "closeModal('confirmDeleteModal')"
);
?>

<!-- JavaScript einbinden -->
<script src="../js/StaffelInputScript.js"></script>

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