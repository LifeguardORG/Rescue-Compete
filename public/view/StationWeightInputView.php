<?php
require_once __DIR__ . '/../php_assets/RequireLogin.php';
requireLogin();

require_once '../db/DbConnection.php';
require_once '../model/StationModel.php';
require_once '../controller/StationWeightController.php';

use Station\StationModel;
use Station\Controller\StationWeightController;

// Zugriff auf Wettkampfleitung/Admin beschränken (wie die Stations-Verwaltung)
$allowedAccountTypes = ['Wettkampfleitung', 'Admin'];
if (!isset($_SESSION["acc_typ"]) || !in_array($_SESSION["acc_typ"], $allowedAccountTypes)) {
    header("Location: ../index.php");
    exit;
}

if (!isset($conn)) {
    require __DIR__ . '/../php_assets/DbErrorPage.php'; die();
}

$stationModel = new StationModel($conn);
$controller = new StationWeightController($stationModel);

$data = $controller->processRequest();
$wertungen = $data['wertungen'];
$message = $data['message'];

// Erfolgsmeldung aus der Session (nach erfolgreichem Speichern)
$successMessage = "";
if (isset($_SESSION['success_message'])) {
    $successMessage = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// Optional vorausgewählte Wertung (z. B. nach dem Speichern via Redirect)
$selectedWertung = isset($_GET['wertung']) ? (int)$_GET['wertung'] : 0;

$pageTitle = "Verwaltung der Stationsgewichtungen";
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RescueCompete - <?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="icon" type="image/x-icon" href="../assets/images/logos/ww-favicon.ico">
    <link rel="stylesheet" href="../css/Colors.css">
    <link rel="stylesheet" href="../css/GlobalLayout.css">
    <link rel="stylesheet" href="../css/Navbar.css">
    <link rel="stylesheet" href="../css/Sidebar.css">
    <link rel="stylesheet" href="../css/Footer.css">
    <link rel="stylesheet" href="../css/Components.css">
    <link rel="stylesheet" href="../css/UserInputViewStyling.css">
    <link rel="stylesheet" href="../css/StationWeightsStyling.css">
    <!-- Checkbox-/Zuweisungs-Styles wie in der Stations-Zuordnung -->
    <link rel="stylesheet" href="../css/ScoringInputViewStyling.css">
</head>
<body class="has-navbar">
<?php include '../php_assets/Navbar.php'; ?>

<div class="container">
    <?php include '../php_assets/Sidebar.php'; ?>

    <!-- Hauptinhalt -->
    <div class="main-content vertical">

        <!-- Statusmeldungen -->
        <?php if (!empty($successMessage)): ?>
            <div class="message-box success"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>
        <?php if (!empty($message)): ?>
            <div class="message-box error"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="data-container">
            <div class="info-box">
                <h4>Hinweise zur Stationsgewichtung:</h4>
                <ul>
                    <li>Die Gewichtung ist <strong>pro Wertung</strong>: Wählen Sie zuerst eine Wertung aus.</li>
                    <li>Alle Stationen einer Wertung ergeben zusammen <strong>genau 100 %</strong> des Parcours-Anteils.</li>
                    <li>Ändern Sie einen Wert, passen sich die <strong>nicht gesperrten</strong> Stationen automatisch an. Mit dem Schloss fixieren Sie einen Wert.</li>
                    <li>Stationen werden einer Wertung im Tab „Wertung zuordnen“ der Stations-Verwaltung zugewiesen.</li>
                </ul>
            </div>

            <div class="form-group">
                <label for="weightWertung">Wertung *</label>
                <select id="weightWertung" onchange="loadStationWeights()">
                    <option value="" disabled <?php echo $selectedWertung ? '' : 'selected'; ?> hidden>Bitte auswählen</option>
                    <?php foreach ($wertungen as $wertung): ?>
                        <option value="<?php echo htmlspecialchars($wertung['ID']); ?>"
                            <?php echo ((int)$wertung['ID'] === $selectedWertung) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($wertung['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (empty($wertungen)): ?>
                    <small>Keine Wertungen vorhanden. Bitte legen Sie zuerst eine Wertung an.</small>
                <?php else: ?>
                    <small>Wählen Sie die Wertung, deren Stationsgewichte Sie bearbeiten möchten.</small>
                <?php endif; ?>
            </div>

            <form method="POST" id="weightForm">
                <input type="hidden" name="wertung" id="weightFormWertung" value="<?php echo $selectedWertung ?: ''; ?>">

                <div id="weightContainer" style="display: none;">
                    <table class="data-table">
                        <thead>
                        <tr>
                            <th>Station</th>
                            <th>Nr</th>
                            <th>Gewichtung (%)</th>
                            <th>Sperren</th>
                        </tr>
                        </thead>
                        <tbody id="weightStationList">
                            <!-- Dynamisch geladen -->
                        </tbody>
                        <tfoot>
                        <tr>
                            <th colspan="2" style="text-align:right;">Summe</th>
                            <th><span id="weightSum">0</span> %</th>
                            <th></th>
                        </tr>
                        </tfoot>
                    </table>

                    <p id="weightHint" class="info-text"></p>

                    <div class="form-actions">
                        <button type="submit" name="save_weights" value="1" class="btn primary-btn">Gewichtungen speichern</button>
                    </div>
                </div>

                <div id="weightEmpty" class="no-data" style="display: none;">
                    <p>Dieser Wertung sind keine Stationen zugeordnet.</p>
                    <p>Ordnen Sie Stationen im Tab „Wertung zuordnen“ der <a href="StationInputView.php?view=assign">Stations-Verwaltung</a> zu.</p>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="../js/StationWeightInputScript.js"></script>

<?php include '../php_assets/Footer.php'; ?>

</body>
</html>
