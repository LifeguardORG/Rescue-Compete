<?php
require_once '../db/DbConnection.php';
require_once '../model/StationModel.php';
require_once '../model/StationWeightModel.php';
require_once '../controller/StationWeightController.php';

use Station\StationModel;
use Station\StationWeightModel;
use Station\Controller\StationWeightController;

if (!isset($conn)) {
    die("<script>alert('Datenbankverbindung nicht verfügbar.');</script>");
}

$stationModel = new StationModel($conn);
$weightModel = new StationWeightModel($conn);
$controller = new StationWeightController($stationModel, $weightModel);

$data = $controller->processRequest();
$stations = $data['stations'];
$message = $data['message'];

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
    <link rel="stylesheet" href="../css/Navbar.css">
    <link rel="stylesheet" href="../css/Sidebar.css">
    <link rel="stylesheet" href="../css/InputStyling.css">
    <link rel="stylesheet" href="../css/UserInputViewStyling.css">
    <link rel="stylesheet" href="../css/StationWeightsStyling.css">
</head>
<body>
<?php include '../php_assets/Navbar.php'; ?>

<div class="container">
    <?php include '../php_assets/Sidebar.php'; ?>

    <!-- Hauptinhalt -->
    <div class="main-content vertical">
        <h2 class="main-title"><?php echo htmlspecialchars($pageTitle); ?></h2>

        <!-- Statusmeldungen -->
        <?php if (!empty($message)): ?>
            <div class="message-box <?php echo strpos($message, 'erfolgreich') !== false ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="data-container">
            <div class="info-box">
                <h4>Hinweise zur Stationsgewichtung:</h4>
                <ul>
                    <li>Die Gewichtung bestimmt, wie stark eine Station in die Gesamtwertung eingeht</li>
                    <li>Ein höherer Wert bedeutet eine stärkere Gewichtung. Die Standardgewichtung ist 100%</li>
                    <li>Das Maximum sind 200%</li>
                    <li>Die Summe aller Punktzahlen bleibt trotzdem konstant, da die Gewichtungen automatisch normalisiert werden</li>
                </ul>
            </div>

            <form method="POST">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th>Station</th>
                        <th>Nummer</th>
                        <th>Gewichtung (%)</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($stations)): ?>
                        <tr>
                            <td colspan="3">Keine Stationen gefunden.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($stations as $station): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($station['name']); ?></td>
                                <td><?php echo htmlspecialchars($station['Nr']); ?></td>
                                <td>
                                    <div class="weight-controls">
                                        <button type="button"
                                                class="weight-button decrease-weight"
                                                data-station="<?php echo htmlspecialchars($station['ID']); ?>">
                                            –
                                        </button>
                                        <input type="number"
                                               name="weights[<?php echo htmlspecialchars($station['ID']); ?>]"
                                               class="weight-input"
                                               value="<?php echo htmlspecialchars($station['weight']); ?>"
                                               min="0"
                                               step="10"
                                               required
                                               id="weight-<?php echo htmlspecialchars($station['ID']); ?>">
                                        <button type="button"
                                                class="weight-button increase-weight"
                                                data-station="<?php echo htmlspecialchars($station['ID']); ?>">
                                            +
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>

                <div class="form-actions">
                    <button type="submit" name="update_weights" class="btn primary-btn">Gewichtungen speichern</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="../js/StationWeightInputScript.js"></script>

<?php include '../php_assets/Footer.php'; ?>

</body>
</html>