<?php

$pageTitle = "Ergebniseingabe - Stations Ergebnisse";

// DbConnection einbinden und die globale Verbindung übernehmen
require_once '../db/DbConnection.php';

// Auf die globale Verbindungsvariable zugreifen
$conn = $GLOBALS['conn'] ?? null;

if (!isset($conn)) {
    die("Datenbankverbindung nicht verfügbar.");
}

require_once '../model/StationSubmissionModel.php';
require_once '../controller/StationSubmissionController.php';

use Controllers\StationSubmissionController;
use Model\StationSubmissionModel;

// Controller initialisieren und Stationsliste abrufen
$model = new StationSubmissionModel($conn);
$controller = new StationSubmissionController($conn);
$stations = $model->getAllStations();

// Falls $stations nicht gesetzt oder kein Array ist, initialisieren wir es als leeres Array.
if (!isset($stations)) {
    $stations = [];
}

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RescueCompete</title>
    <link rel="icon" type="image/x-icon" href="../assets/images/logos/ww-favicon.ico">
    <link rel="stylesheet" href="../css/Navbar.css">
    <link rel="stylesheet" href="../css/StationSubmissionStyling.css">
</head>
<body>
<?php include '../php_assets/Navbar.php'; ?>
<div class="container">
    <div class="wrapper">
        <div class="submission-form">
            <h1>Stationen</h1>
            <?php if (empty($stations)): ?>
                <p>Keine Stationen gefunden.</p>
            <?php else: ?>
                <ul class="station-list">
                    <?php foreach ($stations as $station): ?>
                        <li class="station-item">
                            <div class="station-header">
                            <span class="station-name">
                                <?php echo htmlspecialchars($station['name'] ?: "Station " . $station['Nr']); ?>
                            </span>
                                <a href="../controller/StationSubmissionController.php?action=input&station=<?php echo htmlspecialchars($station['ID']); ?>"
                                   class="station-button">
                                    Ergebnisse eintragen
                                </a>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../php_assets/Footer.php'; ?>

</body>
</html>
