<?php
// StaffelList.php
// Erwartet wird: $staffeln als Array aus der Tabelle Staffel

$pageTitle = "Schwimmen - Staffel Ergebnisse";

// DbConnection einbinden und die globale Verbindung übernehmen
require_once '../db/DbConnection.php';
if (!isset($GLOBALS['conn'])) {
    die("Datenbankverbindung nicht verfügbar.");
} else {
    $conn = $GLOBALS['conn'];
}

require_once '../model/StaffelSubmissionModel.php';
require_once '../controller/StaffelSubmissionController.php';

use Controllers\StaffelSubmissionController;
use Model\StaffelSubmissionModel;

// Controller initialisieren und Staffelliste abrufen
$model = new StaffelSubmissionModel($conn);
$controller = new StaffelSubmissionController($conn);
$staffeln = $model->getAllStaffeln();

// Falls $staffeln nicht gesetzt oder kein Array ist, initialisieren wir es als leeres Array.
if (!isset($staffeln)) {
    $staffeln = [];
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
            <h1>Staffeln</h1>
            <?php if (empty($staffeln)): ?>
                <p>Keine Staffeln gefunden.</p>
            <?php else: ?>
                <ul class="station-list">
                    <?php foreach ($staffeln as $staffel): ?>
                        <li class="station-item">
                            <div class="station-header">
                        <span class="station-name">
                            <?php echo htmlspecialchars($staffel['name'] ?: "Staffel " . $staffel['ID']); ?>
                        </span>
                                <a href="../controller/StaffelSubmissionController.php?action=input&staffel=<?php echo htmlspecialchars($staffel['ID']); ?>"
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
