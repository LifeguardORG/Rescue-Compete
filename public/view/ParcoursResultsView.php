<?php

// Seiten-Titel festlegen
$pageTitle = "Parcours Ergebnisse";

// Erforderliche Dateien einbinden
require_once  '../db/DbConnection.php';
require_once  '../model/ResultModel.php';
require_once  '../model/ResultConfigurationModel.php';
require_once  '../controller/ParcoursResultsController.php';

use Model\ResultModel;
use Model\ResultConfigurationModel;
use Controllers\ParcoursResultsController;

// Überprüfen, ob eine Datenbankverbindung besteht
if (!isset($conn)) {
    die("<script>alert('Datenbankverbindung nicht verfügbar.');</script>");
}

// Instanziiere Model und Controller
$model = new ResultModel($conn);
$configModel = new ResultConfigurationModel();
$controller = new ParcoursResultsController($model, $configModel);

// Den Request verarbeiten und die Ergebnisse abrufen
$data = $controller->processRequest();
extract($data); // Jetzt stehen $wertungDetails, $stationIDs und $weights zur Verfügung
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RescueCompete</title>
    <link rel="icon" type="image/x-icon" href="../assets/images/logos/ww-favicon.ico">
    <link rel="stylesheet" href="../css/Navbar.css">
    <link rel="stylesheet" href="../css/ResultStyling.css">

    <!-- PDF-Export-Skripte und Styles einbinden -->
    <?php include __DIR__ . '/../php_assets/pdfExport/PdfExportLibs.php'; ?>
</head>
<body>
<?php include __DIR__ . '/../php_assets/Navbar.php'; ?>

<div class="container">
    <!-- Wrapper, der die Legenden und das Ergebnis enthält -->
    <div class="wrapper">
        <!-- Legenden-Bereich -->
        <div class="legend-container">
            <!-- Legende zur Darstellung der angezeigten Werte -->
            <div class="legend">
                <h3>Legende</h3>
                <ul>
                    <li>1. Wert: erreichte Punkte</li>
                    <li>2. Wert: normierte Punkte</li>
                </ul>
            </div>
            <!-- Berechnungsinformationen für jede Station -->
            <div class="legend">
                <h3>Berechnungen</h3>
                <table>
                    <?php foreach ($stationIDs as $stationName):
                        // Gewichtung der Station ermitteln, Standard: 100%
                        $stationWeight = isset($weights[$stationName]) ? (int)$weights[$stationName] : 100;
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($stationName); ?>:</td>
                            <td><?php echo htmlspecialchars($stationWeight); ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <div style="margin-top: 8px;">
                    Die Maximal-Summe aller Stationen zusammen ist immer gleich.
                </div>
            </div>

            <!-- Button für den Export -->
            <?php include __DIR__ . '/../php_assets/pdfExport/ExportButton.php'; ?>
        </div>
        <!-- Ergebnisbereich -->
        <div class="results-content">
            <?php if (empty($wertungDetails)): ?>
                <p>Keine Daten verfügbar.</p>
            <?php else: ?>
                <?php foreach ($wertungDetails as $wertungsklasse => $details): ?>
                    <div class="results-section">
                        <h2>Wertung: <?php echo htmlspecialchars($wertungsklasse); ?></h2>
                        <table class="results-table">
                            <thead>
                            <tr>
                                <th class="team-header">Mannschaft</th>
                                <?php foreach ($stationIDs as $stationName): ?>
                                    <th class="station-header"><?php echo htmlspecialchars($stationName); ?></th>
                                <?php endforeach; ?>
                                <th class="points-header">Gesamtpunkte</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($details['Teams'] as $teamName => $stations): ?>
                                <tr>
                                    <td class="team-name"><?php echo htmlspecialchars($teamName); ?></td>
                                    <?php
                                    // Summe der adjustierten Punkte für das Team initialisieren
                                    $totalPoints = 0;
                                    foreach ($stationIDs as $stationName):
                                        if (isset($stations[$stationName]['original']) && is_array($stations[$stationName])) {
                                            $original = $stations[$stationName]['original'];
                                            $adjusted = $stations[$stationName]['adjusted'];
                                            echo "<td class=\"station-points\">" . htmlspecialchars($original) . "<br><strong>" . htmlspecialchars($adjusted) . "</strong></td>";
                                            $totalPoints += is_numeric($adjusted) ? $adjusted : 0;
                                        } else {
                                            echo "<td class=\"station-points\">-</td>";
                                        }
                                    endforeach;
                                    ?>
                                    <td class="total-points"><?php echo htmlspecialchars($totalPoints); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../php_assets/Footer.php'; ?>

</body>
</html>