<?php

$pageTitle = "Verwaltung der Ergebnis-Berechnung";

// Den Controller einbinden und ausführen
require_once '../controller/ResultConfigurationController.php';

// Controller instanziieren und ausführen
$controller = new ResultConfigController();
$config = $controller->getConfig();

// Berechnete Werte für die Anzeige
$totalPoints = $config['TOTAL_POINTS'] ?? 12000;
$swimmingShare = $config['SHARE_SWIMMING'] ?? 50;
$parcoursShare = $config['SHARE_PARCOURS'] ?? 50;
$swimmingPoints = ($totalPoints * $swimmingShare) / 100;
$parcoursPoints = ($totalPoints * $parcoursShare) / 100;

// Alle erforderlichen Daten für die View sind jetzt verfügbar
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
    <link rel="stylesheet" href="../css/ResultConfigurationStyling.css">
</head>
<body>
<?php include '../php_assets/Navbar.php'; ?>

<div class="container">
    <?php include '../php_assets/Sidebar.php'; ?>

    <!-- Hauptinhalt -->
    <div class="main-content vertical">
        <h2 class="main-title"><?php echo htmlspecialchars($pageTitle); ?></h2>

        <!-- Statusmeldungen -->
        <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
            <div class="message-box success">
                Konfiguration wurde erfolgreich gespeichert.
            </div>
        <?php endif; ?>

        <div class="data-container">

            <form method="POST" action="../controller/ResultConfigurationController.php?action=update" id="configForm">
                <!-- Gesamtberechnungs-Konfiguration -->
                <div class="config-form-section">
                    <h3>Gesamtberechnungs-Konfiguration</h3>

                    <!-- Legende für die Ergebnis-Berechnung -->
                    <div class="info-box">
                        <h4>Hinweise zur Ergebnis-Berechnung:</h4>
                        <ul>
                            <li>Die Gesamtpunktzahl definiert die maximal erreichbaren Punkte im Wettkampf und wird zwischen Schwimm- und Parcours-Bereich aufgeteilt</li>
                            <li>Ändern Sie einen Prozentanteil → der andere Anteil passt sich automatisch an (Summe bleibt 100%) → Einzelpunkte werden neu berechnet → Gesamtpunkte bleiben konstant</li>
                            <li>Ändern Sie Schwimm- oder Parcours-Punkte → Gesamtpunkte werden neu berechnet → Prozentanteile passen sich entsprechend an</li>
                            <li>Ändern Sie die Gesamtpunktzahl → Prozentanteile bleiben konstant → Einzelpunkte werden proportional neu verteilt</li>
                        </ul>
                    </div>

                    <!-- Neue Punkte-Verteilungs-Ansicht -->
                    <div class="total-calculation-grid">
                        <!-- Schwimm-Spalte -->
                        <div class="calculation-column">
                            <h4>Schwimm Anteil</h4>
                            <input type="number"
                                   class="points-input"
                                   id="swimmingPoints"
                                   value="<?php echo $swimmingPoints; ?>"
                                   min="0"
                                   step="1">
                            <div>
                                <input type="number"
                                       class="percentage-input"
                                       id="SHARE_SWIMMING"
                                       name="SHARE_SWIMMING"
                                       value="<?php echo $swimmingShare; ?>"
                                       min="0"
                                       max="100"
                                       step="0.1"
                                       required>
                                <span class="percentage-label">%</span>
                            </div>
                        </div>

                        <!-- Parcours-Spalte -->
                        <div class="calculation-column">
                            <h4>Parcours Anteil</h4>
                            <input type="number"
                                   class="points-input"
                                   id="parcoursPoints"
                                   value="<?php echo $parcoursPoints; ?>"
                                   min="0"
                                   step="1">
                            <div>
                                <input type="number"
                                       class="percentage-input"
                                       id="SHARE_PARCOURS"
                                       name="SHARE_PARCOURS"
                                       value="<?php echo $parcoursShare; ?>"
                                       min="0"
                                       max="100"
                                       step="0.1"
                                       required>
                                <span class="percentage-label">%</span>
                            </div>
                        </div>

                        <!-- Gesamt-Spalte -->
                        <div class="calculation-column total-column">
                            <h4>Gesamt</h4>
                            <input type="number"
                                   class="points-input"
                                   id="TOTAL_POINTS"
                                   name="TOTAL_POINTS"
                                   value="<?php echo $totalPoints; ?>"
                                   min="1"
                                   step="1"
                                   required>
                            <div style="margin-top: 11px; margin-bottom: 11px;">
                                <strong>100.0 %</strong>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn primary-btn">Gesamtberechnung speichern</button>
                    </div>
                </div>

                <!-- Schwimm-Ergebnisse Konfiguration -->
                <div class="config-form-section">
                    <h3>Schwimm-Ergebnisse Konfiguration</h3>

                    <!-- Info-Box für Schwimmpunkt-Berechnung -->
                    <div class="info-box">
                        <h4>Berechnung der Schwimmpunkte:</h4>
                        <ul>
                            <li>Maximalpunkte pro Staffel: Innerhalb einer Wertung wird für jede Staffel die schnellste Schwimmzeit (ohne Strafsekunden) ermittelt. Diese Mannschaft erhält die Maximalpunkte (Schwimm Anteil ÷ Staffelanzahl)</li>
                            <li>Alle anderen Mannschaften in der Staffel werden an dieser Bestzeit gemessen</li>
                            <li>Für jedes Abzugsintervall (in Millisekunden) über der Bestzeit werden Punkte abgezogen</li>
                            <li>Bei 100ms Intervall und 1 Punkt Abzug verliert eine Mannschaft mit 2,5 Sekunden Rückstand 25 Punkte (2500ms ÷ 100ms = 25 Intervalle)</li>
                        </ul>
                    </div>

                    <div class="config-fields-grid">
                        <div class="form-group">
                            <label for="DEDUCTION_INTERVAL_MS">Abzugsintervall in ms</label>
                            <input type="number"
                                   id="DEDUCTION_INTERVAL_MS"
                                   name="DEDUCTION_INTERVAL_MS"
                                   value="<?php echo htmlspecialchars($config['DEDUCTION_INTERVAL_MS'] ?? '100'); ?>"
                                   min="1"
                                   max="1000"
                                   required>
                        </div>
                        <div class="form-group">
                            <label for="POINTS_DEDUCTION">Punktabzug pro Intervall</label>
                            <input type="number"
                                   id="POINTS_DEDUCTION"
                                   name="POINTS_DEDUCTION"
                                   value="<?php echo htmlspecialchars($config['POINTS_DEDUCTION'] ?? '1'); ?>"
                                   min="1"
                                   max="100"
                                   required>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn primary-btn">Schwimmkonfiguration speichern</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../php_assets/Footer.php'; ?>

<script src="../js/ResultConfiguration.js"></script>
</body>
</html>s