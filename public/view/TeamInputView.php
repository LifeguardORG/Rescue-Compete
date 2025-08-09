<?php
require_once '../db/DbConnection.php';
require_once '../model/TeamModel.php';
require_once '../controller/MannschaftController.php';
require_once '../php_assets/CustomAlertBox.php';

use Mannschaft\TeamModel;
use Station\Controller\MannschaftController;

// Session-Check
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Berechtigungsprüfung
$allowedAccountTypes = ['Wettkampfleitung', 'Admin'];
if(!isset($_SESSION["acc_typ"]) || !in_array($_SESSION["acc_typ"], $allowedAccountTypes)){
    header("Location: ../index.php");
    exit;
}

// Datenbankverbindung prüfen
if (!isset($conn)) {
    die("<script>alert('Datenbankverbindung nicht verfügbar.');</script>");
}

// Model und Controller initialisieren
$model = new TeamModel($conn);
$controller = new MannschaftController($model);
$controller->handleRequest();

// Daten aus Controller abrufen
$duplicateData = $controller->duplicateData;
$errorData = $controller->errorData;
$successData = $controller->successData;
$teams = $model->getAllMannschaften();

// POST-Daten für Formular-Prefill (nur bei Fehlern)
$teamname = "";
$kreisverband = "";
$landesverband = "";

if (!empty($errorData) && isset($_POST['teamname'])) {
    $teamname = trim($_POST['teamname']);
    $kreisverband = trim($_POST['kreisverband']);
    $landesverband = trim($_POST['landesverband']);
}

// Aktuelle Ansicht bestimmen
$currentView = $_GET['view'] ?? 'overview';

// Success-Messages aus URL-Parametern
$successMessage = '';
if (isset($_GET['created'])) {
    $successMessage = 'Mannschaft erfolgreich erstellt.';
} elseif (isset($_GET['updated'])) {
    $successMessage = 'Mannschaft erfolgreich aktualisiert.';
} elseif (isset($_GET['deleted'])) {
    $successMessage = 'Mannschaft erfolgreich gelöscht.';
}

$pageTitle = "Verwaltung der Mannschaften";
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="icon" type="image/x-icon" href="../assets/images/logos/ww-favicon.ico">
    <link rel="stylesheet" href="../css/Colors.css">
    <link rel="stylesheet" href="../css/Navbar.css">
    <link rel="stylesheet" href="../css/Sidebar.css">
    <link rel="stylesheet" href="../css/InputStyling.css">
    <link rel="stylesheet" href="../css/TeamInputStyling.css">
</head>
<body>

<?php include '../php_assets/Navbar.php'; ?>

<div class="container">
    <?php include '../php_assets/Sidebar.php'; ?>

    <div class="main-content vertical">
        <h2><?php echo htmlspecialchars($pageTitle); ?></h2>

        <!-- Navigation Tabs -->
        <div class="tab-navigation">
            <button class="tab-button <?php echo $currentView === 'overview' ? 'active' : ''; ?>"
                    data-tab="overview"
                    onclick="showTab('overview')">Übersicht</button>
            <button class="tab-button <?php echo $currentView === 'create' ? 'active' : ''; ?>"
                    data-tab="create"
                    onclick="showTab('create')">Mannschaft erstellen</button>
        </div>

        <!-- Tab: Übersicht -->
        <div id="overview" class="tab-content <?php echo $currentView === 'overview' ? 'active' : ''; ?>">
            <div class="data-container">
                <div class="actions-bar">
                    <button class="btn primary-btn" onclick="showTab('create')">
                        Neue Mannschaft erstellen
                    </button>
                </div>

                <?php if (empty($teams)): ?>
                    <div class="no-data">
                        <p>Keine Mannschaften vorhanden.</p>
                        <p><a href="#" onclick="showTab('create')">Erstellen Sie Ihre erste Mannschaft</a></p>
                    </div>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Mannschafts-Name</th>
                            <th>Kreisverband</th>
                            <th>Landesverband</th>
                            <th>Aktionen</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($teams as $team): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($team['ID'] ?? 'N/A'); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($team['Teamname'] ?? "nicht gefunden"); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($team['Kreisverband'] ?? "nicht gefunden"); ?></td>
                                <td><?php echo htmlspecialchars($team['Landesverband'] ?? "nicht gefunden"); ?></td>
                                <td class="action-cell">
                                    <div class="button-group">
                                        <button class="btn warning-btn small"
                                                onclick="confirmDeleteTeam(<?php echo intval($team['ID']); ?>, '<?php echo addslashes(htmlspecialchars($team['Teamname'])); ?>')">
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

        <!-- Tab: Neue Mannschaft -->
        <div id="create" class="tab-content <?php echo $currentView === 'create' ? 'active' : ''; ?>">
            <div class="data-container">
                <h3>Neue Mannschaft erstellen</h3>

                <form method="POST" id="createTeamForm">
                    <input type="hidden" name="add_team" value="1">

                    <div class="form-group">
                        <label for="teamname">Mannschafts-Name *</label>
                        <input type="text" id="teamname" name="teamname" required
                               maxlength="100"
                               placeholder="z.B. THW Lübeck, DRK München"
                               value="<?= htmlspecialchars($teamname); ?>">
                        <small>Geben Sie einen eindeutigen Namen für die Mannschaft ein (max. 100 Zeichen)</small>
                    </div>

                    <div class="form-group">
                        <label for="kreisverband">Kreisverband *</label>
                        <input type="text" id="kreisverband" name="kreisverband" required
                               maxlength="32"
                               placeholder="z.B. Lübeck, München"
                               value="<?= htmlspecialchars($kreisverband); ?>">
                        <small>Der zugehörige Kreisverband der Mannschaft (max. 32 Zeichen)</small>
                    </div>

                    <div class="form-group">
                        <label for="landesverband">Landesverband *</label>
                        <input type="text" id="landesverband" name="landesverband" required
                               maxlength="32"
                               placeholder="z.B. Schleswig-Holstein, Bayern"
                               value="<?= htmlspecialchars($landesverband); ?>">
                        <small>Der zugehörige Landesverband der Mannschaft (max. 32 Zeichen)</small>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn primary-btn">Mannschaft erstellen</button>
                        <button type="button" class="btn" onclick="clearForm()">Formular leeren</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Success-Alert bei erfolgreichem Vorgang
if (!empty($successMessage)):
    echo CustomAlertBox::renderSuccessAlert(
        "successAlert",
        "Erfolgreich",
        $successMessage
    );
endif;

// Error-Alert bei Fehlern
if (!empty($errorData)):
    echo CustomAlertBox::renderErrorAlert(
        "errorAlert",
        $errorData['title'],
        $errorData['message']
    );
endif;

// Duplikat-Bestätigung
if (!empty($duplicateData)):
    echo CustomAlertBox::renderDuplicateConfirm(
        "confirmDuplicateTeam",
        $duplicateData,
        $duplicateData['existing_kreisverband'],
        $duplicateData['existing_landesverband']
    );
endif;

// Modal für Lösch-Bestätigung
echo CustomAlertBox::renderSimpleConfirm(
    "confirmDeleteModal",
    "Löschen bestätigen",
    "Möchten Sie diese Mannschaft und alle dazugehörigen Daten wirklich löschen?",
    "deleteTeam()",
    "closeModal('confirmDeleteModal')"
);
?>

<script src="../js/TeamInputScript.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const currentView = '<?php echo $currentView; ?>';
        showTab(currentView);

        // Success-Alert anzeigen
        <?php if (!empty($successMessage)): ?>
        showModal('successAlert');
        clearForm();
        <?php endif; ?>

        // Error-Alert anzeigen
        <?php if (!empty($errorData)): ?>
        showModal('errorAlert');
        <?php endif; ?>

        // Duplikat-Alert anzeigen
        <?php if (!empty($duplicateData)): ?>
        showModal('confirmDuplicateTeam');
        <?php endif; ?>
    });
</script>

<?php include '../php_assets/Footer.php'; ?>

</body>
</html>