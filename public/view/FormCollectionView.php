<?php
require_once '../db/DbConnection.php';
require_once '../model/FormCollectionModel.php';
require_once '../controller/FormCollectionController.php';
require_once '../php_assets/CustomAlertBox.php';

use FormCollection\FormCollectionModel;
use FormCollection\FormCollectionController;

// Session-Check
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Weiterleitung, wenn Berechtigungen nicht Korrekt sind
$allowedAccountTypes = ['Wettkampfleitung', 'Admin'];
if(!isset($_SESSION["acc_typ"]) || !in_array($_SESSION["acc_typ"], $allowedAccountTypes)){
    header("Location: ../index.php");
    exit;
}

if (!isset($conn)) {
    die("Datenbankverbindung nicht verfügbar.");
}

// Controller instanziieren und Request verarbeiten
$controller = new FormCollectionController($conn);
$controller->handleRequest();

// Falls es ein AJAX-Request war, ist die Verarbeitung bereits abgeschlossen
// und die Funktion wird nicht mehr erreicht (wegen exit in sendJsonResponse)

// Daten für die View
$collections = $controller->collections;
$questionPools = $controller->questionPools;
$stations = $controller->stations;
$message = $controller->message;
$messageType = $controller->messageType;
$currentCollection = $controller->currentCollection;
$selectedQuestions = $controller->selectedQuestions;
$collectionTokens = $controller->collectionTokens;
$performanceStats = $controller->performanceStats;
$teamProgress = $controller->teamProgress;
$validationErrors = $controller->validationErrors;

// Aktuelle Ansicht bestimmen
$currentView = $_GET['view'] ?? 'overview';
$collectionId = isset($_GET['collection_id']) ? intval($_GET['collection_id']) : null;

$pageTitle = "Formular Verwaltung";
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
    <!-- QR-Code-Bibliothek -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head>
<body>
<!-- Navbar -->
<?php include '../php_assets/Navbar.php'; ?>

<div class="container">
    <!-- Sidebar -->
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
                    onclick="showTab('create')">Neue Formular-Gruppe</button>
            <button class="tab-button <?php echo $currentView === 'qrcodes' ? 'active' : ''; ?>"
                    data-tab="qrcodes"
                    onclick="showTab('qrcodes')">QR-Codes</button>
            <button class="tab-button <?php echo $currentView === 'performance' ? 'active' : ''; ?>"
                    data-tab="performance"
                    onclick="showTab('performance')">Statistiken</button>
        </div>

        <!-- Statusmeldungen -->
        <?php if (!empty($message)): ?>
            <div class="message-box <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Tab: Übersicht -->
        <div id="overview" class="tab-content <?php echo $currentView === 'overview' ? 'active' : ''; ?>">
            <div class="data-container">
                <div class="actions-bar">
                    <button class="btn primary-btn" onclick="showTab('create')">
                        Neue Formular-Gruppe erstellen
                    </button>
                    <button class="btn" onclick="processExpiredForms()">
                        Abgelaufene Formulare verarbeiten
                    </button>
                </div>

                <?php if (empty($collections)): ?>
                    <div class="no-data">
                        <p>Keine Formular-Gruppen vorhanden.</p>
                        <p><a href="#" onclick="showTab('create')">Erstellen Sie Ihre erste Formular-Gruppe</a></p>
                    </div>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Station</th>
                            <th>Fragen</th>
                            <th>Formulare</th>
                            <th>Abgeschlossen</th>
                            <th>Zeitlimit</th>
                            <th>Erstellt</th>
                            <th>Aktionen</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($collections as $collection): ?>
                            <tr>
                                <td class="collection-name-cell">
                                    <strong><?php echo htmlspecialchars($collection['name']); ?></strong>
                                    <?php if (!empty($collection['description'])): ?>
                                        <br><div class="collection-description"><?php echo htmlspecialchars($collection['description']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($collection['stationName'] ?? '-'); ?></td>
                                <td class="numeric-cell"><?php echo intval($collection['totalQuestions']); ?></td>
                                <td class="numeric-cell"><?php echo intval($collection['formsCount']); ?></td>
                                <td class="numeric-cell">
                                    <?php echo intval($collection['completedForms']); ?>/<?php echo intval($collection['totalInstances']); ?>
                                    <?php if ($collection['totalInstances'] > 0): ?>
                                        <br><small>(<?php echo $collection['completionRate']; ?>%)</small>
                                    <?php endif; ?>
                                </td>
                                <td class="numeric-cell">
                                    <?php echo gmdate("i:s", $collection['timeLimit']); ?> min
                                </td>
                                <td><?php echo date('d.m.Y', strtotime($collection['createdAt'])); ?></td>
                                <td class="action-cell">
                                    <div class="button-group">
                                        <button class="btn small" onclick="viewCollection(<?php echo $collection['ID']; ?>)">
                                            Details
                                        </button>
                                        <button class="btn small" onclick="viewTokens(<?php echo $collection['ID']; ?>)">
                                            QR-Codes
                                        </button>
                                        <button class="btn warning-btn small" onclick="confirmDeleteCollection(<?php echo $collection['ID']; ?>, '<?php echo addslashes($collection['name']); ?>')">
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

        <!-- Tab: Neue Formular-Gruppe -->
        <div id="create" class="tab-content <?php echo $currentView === 'create' ? 'active' : ''; ?>">
            <div class="data-container">
                <h3>Neue Formular-Gruppe erstellen</h3>

                <form method="POST" id="createCollectionForm">
                    <input type="hidden" name="action" value="create_collection">

                    <div class="form-group">
                        <label for="name">Name der Formular-Gruppe *</label>
                        <input type="text" id="name" name="name" required
                               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                               placeholder="z.B. Erste-Hilfe-Quiz"
                               class="<?php echo $controller->hasValidationError('name') ? 'error' : ''; ?>">
                        <?php if ($controller->hasValidationError('name')): ?>
                            <div class="validation-message show"><?php echo htmlspecialchars($controller->getValidationError('name')); ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="description">Beschreibung</label>
                        <textarea id="description" name="description" rows="3" maxlength="200"
                                  placeholder="Optionale Beschreibung der Formular-Gruppe (max. 200 Zeichen)"
                                  class="<?php echo $controller->hasValidationError('description') ? 'error' : ''; ?>"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                        <div class="character-counter">
                            <span id="description-counter">0</span>/200 Zeichen
                        </div>
                        <?php if ($controller->hasValidationError('description')): ?>
                            <div class="validation-message show"><?php echo htmlspecialchars($controller->getValidationError('description')); ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="station_id">Station</label>
                        <select id="station_id" name="station_id">
                            <option value="">Keine Station zuordnen</option>
                            <?php foreach ($stations as $station): ?>
                                <option value="<?php echo $station['ID']; ?>"
                                    <?php echo (isset($_POST['station_id']) && $_POST['station_id'] == $station['ID']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($station['name']); ?> (Nr. <?php echo $station['Nr']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="time_limit">Zeitlimit pro Formular (Sekunden) *</label>
                        <input type="number" id="time_limit" name="time_limit"
                               min="10" max="1800" value="<?php echo $_POST['time_limit'] ?? '180'; ?>" required
                               class="<?php echo $controller->hasValidationError('timeLimit') ? 'error' : ''; ?>">
                        <small>Empfohlen: 180 Sekunden (3 Minuten)</small>
                        <?php if ($controller->hasValidationError('timeLimit')): ?>
                            <div class="validation-message show"><?php echo htmlspecialchars($controller->getValidationError('timeLimit')); ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="forms_count">Anzahl der Formulare *</label>
                        <input type="number" id="forms_count" name="forms_count"
                               min="1" max="20" value="<?php echo $_POST['forms_count'] ?? '4'; ?>" required
                               class="<?php echo $controller->hasValidationError('formsCount') ? 'error' : ''; ?>">
                        <small>Anzahl verschiedener Formulare, die aus den Fragen erstellt werden</small>
                        <?php if ($controller->hasValidationError('formsCount')): ?>
                            <div class="validation-message show"><?php echo htmlspecialchars($controller->getValidationError('formsCount')); ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="question_pool">Fragenpool auswählen *</label>
                        <select id="question_pool" name="question_pool" required
                                class="<?php echo $controller->hasValidationError('totalQuestions') ? 'error' : ''; ?>">
                            <option value="">Bitte wählen...</option>
                            <?php foreach ($questionPools as $pool): ?>
                                <option value="<?php echo $pool['ID']; ?>"
                                    <?php echo (isset($_POST['question_pool']) && $_POST['question_pool'] == $pool['ID']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($pool['Name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($controller->hasValidationError('totalQuestions')): ?>
                            <div class="validation-message show"><?php echo htmlspecialchars($controller->getValidationError('totalQuestions')); ?></div>
                        <?php endif; ?>
                    </div>

                    <div id="questionsContainer" style="display: none;">
                        <div class="form-group">
                            <label>Fragen auswählen *</label>
                            <div class="question-selection">
                                <button type="button" class="btn secondary-btn" onclick="selectAllQuestions()">
                                    Alle auswählen
                                </button>
                                <button type="button" class="btn secondary-btn" onclick="deselectAllQuestions()">
                                    Alle abwählen
                                </button>
                            </div>
                            <div id="questionsList" class="questions-list">
                                <!-- Dynamisch geladen via JavaScript -->
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn primary-btn">Formular-Gruppe erstellen</button>
                        <button type="button" class="btn" onclick="showTab('overview')">Abbrechen</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tab: QR-Codes -->
        <div id="qrcodes" class="tab-content <?php echo $currentView === 'qrcodes' ? 'active' : ''; ?>">
            <div class="data-container">
                <h3>QR-Codes verwalten</h3>

                <div class="collection-selector">
                    <label for="qr_collection_select">Formular-Gruppe auswählen:</label>
                    <select id="qr_collection_select" onchange="loadCollectionTokens()">
                        <option value="">Bitte wählen...</option>
                        <?php foreach ($collections as $collection): ?>
                            <option value="<?php echo $collection['ID']; ?>"
                                <?php echo ($collectionId == $collection['ID']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($collection['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div id="tokensContainer">
                    <?php if (!empty($collectionTokens)): ?>
                        <div class="qr-codes-grid">
                            <?php foreach ($collectionTokens as $index => $token): ?>
                                <div class="qr-code-item">
                                    <h4><?php echo htmlspecialchars($token['collectionName']); ?> - Formular <?php echo $token['formNumber']; ?></h4>
                                    <div class="qr-code-container" id="qrcode-<?php echo $index; ?>"
                                         data-url="<?php echo htmlspecialchars($token['qrCodeUrl']); ?>"></div>
                                    <div class="qr-code-info">
                                        <p><strong>Token:</strong> <?php echo htmlspecialchars($token['token']); ?></p>
                                        <p><strong>URL:</strong> <a href="<?php echo htmlspecialchars($token['qrCodeUrl']); ?>" target="_blank">
                                                <?php echo htmlspecialchars($token['qrCodeUrl']); ?>
                                            </a></p>
                                        <button class="btn download-btn" onclick="downloadQrCode(<?php echo $index; ?>, '<?php echo addslashes($token['collectionName'] . '_Form_' . $token['formNumber']); ?>')">
                                            QR-Code herunterladen
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-data">
                            <p>Keine QR-Codes verfügbar. Wählen Sie eine Formular-Gruppe aus.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Tab: Statistiken -->
        <div id="performance" class="tab-content <?php echo $currentView === 'performance' ? 'active' : ''; ?>">
            <div class="data-container">
                <h3>Performance-Statistiken</h3>

                <!-- Formular-Gruppen-Performance -->
                <?php if (!empty($performanceStats)): ?>
                    <h4>Formular-Gruppen-Performance</h4>
                    <table class="data-table">
                        <thead>
                        <tr>
                            <th>Formular-Gruppe</th>
                            <th>Formulare</th>
                            <th>Fragen</th>
                            <th>Teams</th>
                            <th>Abgeschlossen</th>
                            <th>Durchschnitt</th>
                            <th>Completion Rate</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($performanceStats as $stat): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($stat['collectionName']); ?></td>
                                <td class="numeric-cell"><?php echo $stat['formsCount']; ?></td>
                                <td class="numeric-cell"><?php echo $stat['totalQuestions']; ?></td>
                                <td class="numeric-cell"><?php echo $stat['teamsAssigned']; ?></td>
                                <td class="numeric-cell"><?php echo $stat['completedInstances']; ?>/<?php echo $stat['totalInstances']; ?></td>
                                <td class="numeric-cell"><?php echo round($stat['averageScore'], 1); ?> Punkte</td>
                                <td class="numeric-cell"><?php echo $stat['completionRate']; ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

                <!-- Team-Fortschritt -->
                <?php if (!empty($teamProgress)): ?>
                    <h4>Team-Fortschritt</h4>
                    <table class="data-table">
                        <thead>
                        <tr>
                            <th>Team</th>
                            <th>Kreisverband</th>
                            <th>Formular-Gruppe</th>
                            <th>Formulare</th>
                            <th>Abgeschlossen</th>
                            <th>Punkte</th>
                            <th>Fortschritt</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($teamProgress as $progress): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($progress['Teamname']); ?></td>
                                <td><?php echo htmlspecialchars($progress['Kreisverband']); ?></td>
                                <td><?php echo htmlspecialchars($progress['collectionName']); ?></td>
                                <td class="numeric-cell"><?php echo $progress['totalForms']; ?></td>
                                <td class="numeric-cell"><?php echo $progress['completedForms']; ?></td>
                                <td class="numeric-cell"><?php echo $progress['totalPoints']; ?></td>
                                <td class="numeric-cell"><?php echo $progress['completionPercentage']; ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

                <?php if (empty($performanceStats) && empty($teamProgress)): ?>
                    <div class="no-data">
                        <p>Keine Statistiken verfügbar.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
<?php
// Formular-Gruppen-Löschung bestätigen
echo CustomAlertBox::renderSimpleConfirm(
    "confirmDeleteCollectionModal",
    "Formular-Gruppe löschen",
    "Möchten Sie diese Formular-Gruppe wirklich löschen? Alle zugehörigen Formulare, Antworten und QR-Codes werden ebenfalls gelöscht.",
    "deleteCollection()",
    "closeModal('confirmDeleteCollectionModal')"
);

// Erfolgs-/Fehlermeldungen
if (!empty($message)):
    $alertType = ($messageType === 'success') ? 'Erfolg' :
        (($messageType === 'error') ? 'Fehler' : 'Hinweis');
    echo CustomAlertBox::renderSimpleAlert(
        "messageAlert",
        $alertType,
        $message
    );
    echo '<script>document.getElementById("messageAlert").classList.add("active");</script>';
endif;
?>

<!-- JavaScript einbinden -->
<script src="../js/FormCollectionViewScript.js"></script>

<!-- Tab-Initialisierung sicherstellen -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Sicherstellen, dass der korrekte Tab angezeigt wird
        const currentView = '<?php echo $currentView; ?>';
        showTab(currentView);

        // Character Counter initialisieren - mit Verzögerung für Tab-Switching
        setTimeout(function() {
            initializeCharacterCounter();
        }, 100);

        // Character Counter auch bei Tab-Wechsel neu initialisieren
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', function() {
                setTimeout(function() {
                    initializeCharacterCounter();
                }, 100);
            });
        });
    });
</script>

<?php include '../php_assets/Footer.php'; ?>

</body>
</html>