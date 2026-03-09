<?php
require_once '../db/DbConnection.php';
require_once '../model/TeamFormRelationModel.php';
require_once '../model/MannschaftModel.php';
require_once '../model/FormManagementModel.php';
require_once '../model/StationModel.php';
require_once '../php_assets/CustomAlertBox.php';

use TeamForm\TeamFormRelationModel;
use Mannschaft\MannschaftModel;
use QuestionForm\FormManagementModel;
use Station\StationModel;

// Überprüfen, ob eine Datenbankverbindung besteht
if (!isset($conn)) {
    require __DIR__ . '/../php_assets/DbErrorPage.php'; die();
}

// Modelle instanziieren
$teamFormModel = new TeamFormRelationModel($conn);
$mannschaftModel = new MannschaftModel($conn);
$formModel = new FormManagementModel($conn);
$stationModel = new StationModel($conn);

// Filter-Parameter
$selectedTeamId = isset($_GET['team_id']) ? intval($_GET['team_id']) : 0;
$selectedStationId = isset($_GET['station_id']) ? intval($_GET['station_id']) : 0;
$selectedFormTitle = isset($_GET['form_title']) ? trim($_GET['form_title']) : '';

// Dropdown-Daten laden
$teams = $mannschaftModel->read();
$stations = $stationModel->read();

// Alle eindeutigen Formular-Titel abrufen
try {
    $stmt = $conn->query("SELECT DISTINCT Titel FROM QuestionForm ORDER BY Titel");
    $formTitles = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $formTitles = [];
}

// Daten gemäß Filter laden
$results = [];
$statistics = [];

if ($selectedTeamId > 0) {
    // Wenn Team ausgewählt: Alle Formulare für dieses Team
    $teamData = $mannschaftModel->read($selectedTeamId);
    $teamForms = $teamFormModel->getFormsByTeam($selectedTeamId);

// Erweiterte Daten für jedes Formular abrufen
    $results = [];
    foreach ($teamForms as $form) {
        $formId = $form['form_ID'];
        $form['question_count'] = $formModel->getFormQuestionCount($formId);
        $results[] = $form;
    }

    // Statistiken berechnen
    $totalForms = count($results);
    $completedForms = 0;
    $totalPoints = 0;
    $maxPossiblePoints = 0;

    foreach ($results as $form) {
        $maxPossiblePoints += $form['question_count'];
        if ($form['completed'] == 1) {
            $completedForms++;
            $totalPoints += $form['points'];
        }
    }

    $statistics = [
        'title' => 'Mannschaft: ' . ($teamData['Teamname'] ?? 'Unbekannt'),
        'subtitle' => 'Kreisverband: ' . ($teamData['Kreisverband'] ?? ''),
        'total_forms' => $totalForms,
        'completed_forms' => $completedForms,
        'total_points' => $totalPoints,
        'max_possible_points' => $maxPossiblePoints,
        'completion_percentage' => $totalForms > 0 ? round(($completedForms / $totalForms) * 100, 1) : 0,
        'points_percentage' => $maxPossiblePoints > 0 ? round(($totalPoints / $maxPossiblePoints) * 100, 1) : 0
    ];

} elseif ($selectedStationId > 0) {
    // Wenn Station ausgewählt: Alle Formulare für diese Station
    $stationData = $stationModel->read($selectedStationId);

    // Formulare für diese Station abrufen
    try {
        $stmt = $conn->prepare(
            "SELECT qf.ID, qf.Titel, qf.Station_ID, s.name AS station_name
             FROM QuestionForm qf
             JOIN Station s ON qf.Station_ID = s.ID
             WHERE qf.Station_ID = :station_id"
        );
        $stmt->bindParam(':station_id', $selectedStationId, PDO::PARAM_INT);
        $stmt->execute();
        $stationForms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $stationForms = [];
    }

    // Für jedes Formular die Mannschaften und den Status abrufen
    $results = [];
    foreach ($stationForms as $form) {
        $formId = $form['ID'];

        try {
            $stmt = $conn->prepare(
                "SELECT tf.*, m.Teamname, m.Kreisverband
                 FROM TeamForm tf
                 JOIN Mannschaft m ON tf.team_ID = m.ID
                 WHERE tf.form_ID = :form_id
                 ORDER BY m.Teamname"
            );
            $stmt->bindParam(':form_id', $formId, PDO::PARAM_INT);
            $stmt->execute();
            $teamForms = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Anzahl der Fragen für dieses Formular
            $questionCount = $formModel->getFormQuestionCount($formId);

            // Daten für jedes TeamForm erweitern
            foreach ($teamForms as $teamForm) {
                $teamForm['form_title'] = $form['Titel'];
                $teamForm['station_name'] = $form['station_name'];
                $teamForm['question_count'] = $questionCount;
                $results[] = $teamForm;
            }
        } catch (PDOException $e) {
            // Fehler ignorieren und fortfahren
        }
    }

    // Statistiken berechnen
    $totalTeams = count($teams);
    $totalForms = count($stationForms);
    $totalAssignments = count($results);
    $completedForms = 0;
    $totalPoints = 0;
    $maxPossiblePoints = 0;

    foreach ($results as $form) {
        $maxPossiblePoints += $form['question_count'];
        if ($form['completed'] == 1) {
            $completedForms++;
            $totalPoints += $form['points'];
        }
    }

    $statistics = [
        'title' => 'Station: ' . ($stationData['name'] ?? 'Unbekannt'),
        'subtitle' => 'Formulare: ' . $totalForms,
        'total_teams' => $totalTeams,
        'total_assignments' => $totalAssignments,
        'completed_forms' => $completedForms,
        'total_points' => $totalPoints,
        'max_possible_points' => $maxPossiblePoints,
        'completion_percentage' => $totalAssignments > 0 ? round(($completedForms / $totalAssignments) * 100, 1) : 0,
        'average_points' => $completedForms > 0 ? round($totalPoints / $completedForms, 1) : 0
    ];

} elseif (!empty($selectedFormTitle)) {
    // Wenn Formular-Titel ausgewählt: Alle Formulare mit diesem Titel

    // Formulare mit diesem Titel abrufen
    try {
        $stmt = $conn->prepare(
            "SELECT qf.ID, qf.Titel, qf.Station_ID, s.name AS station_name
             FROM QuestionForm qf
             JOIN Station s ON qf.Station_ID = s.ID
             WHERE qf.Titel = :title"
        );
        $stmt->bindParam(':title', $selectedFormTitle, PDO::PARAM_STR);
        $stmt->execute();
        $titleForms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $titleForms = [];
    }

    // Für jedes Formular die Mannschaften und den Status abrufen
    $results = [];
    foreach ($titleForms as $form) {
        $formId = $form['ID'];

        try {
            $stmt = $conn->prepare(
                "SELECT tf.*, m.Teamname, m.Kreisverband
                 FROM TeamForm tf
                 JOIN Mannschaft m ON tf.team_ID = m.ID
                 WHERE tf.form_ID = :form_id
                 ORDER BY m.Teamname"
            );
            $stmt->bindParam(':form_id', $formId, PDO::PARAM_INT);
            $stmt->execute();
            $teamForms = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Anzahl der Fragen für dieses Formular
            $questionCount = $formModel->getFormQuestionCount($formId);

            // Daten für jedes TeamForm erweitern
            foreach ($teamForms as $teamForm) {
                $teamForm['form_title'] = $form['Titel'];
                $teamForm['station_name'] = $form['station_name'];
                $teamForm['question_count'] = $questionCount;
                $results[] = $teamForm;
            }
        } catch (PDOException $e) {
            // Fehler ignorieren und fortfahren
        }
    }

    // Statistiken berechnen
    $totalTeams = count($teams);
    $totalForms = count($titleForms);
    $totalAssignments = count($results);
    $completedForms = 0;
    $totalPoints = 0;
    $maxPossiblePoints = 0;

    foreach ($results as $form) {
        $maxPossiblePoints += $form['question_count'];
        if ($form['completed'] == 1) {
            $completedForms++;
            $totalPoints += $form['points'];
        }
    }

    $statistics = [
        'title' => 'Formular: ' . $selectedFormTitle,
        'subtitle' => 'Stationen: ' . count(array_unique(array_column($titleForms, 'station_name'))),
        'total_teams' => $totalTeams,
        'total_assignments' => $totalAssignments,
        'completed_forms' => $completedForms,
        'total_points' => $totalPoints,
        'max_possible_points' => $maxPossiblePoints,
        'completion_percentage' => $totalAssignments > 0 ? round(($completedForms / $totalAssignments) * 100, 1) : 0,
        'average_points' => $completedForms > 0 ? round($totalPoints / $completedForms, 1) : 0
    ];
}

$pageTitle = "Formular-Auswertung";
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
    <link rel="stylesheet" href="../css/FormResultStyling.css">
</head>
<body class="has-navbar">
<!-- Navbar -->
<?php include '../php_assets/Navbar.php'; ?>

<div class="container">
    <!-- Sidebar import -->
    <?php include '../php_assets/Sidebar.php'; ?>

    <section class="main-content">

        <div class="filter-container">
            <form method="GET" id="filterForm">
                <div class="filter-item">
                    <label for="team_id">Mannschaft:</label>
                    <select id="team_id" name="team_id" onchange="document.getElementById('filterForm').submit();">
                        <option value="0">-- Alle Mannschaften --</option>
                        <?php foreach ($teams as $team): ?>
                            <option value="<?php echo $team['ID']; ?>"
                                <?php echo $selectedTeamId == $team['ID'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($team['Teamname'] . ' (' . $team['Kreisverband'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-item">
                    <label for="station_id">Station:</label>
                    <select id="station_id" name="station_id" onchange="document.getElementById('filterForm').submit();">
                        <option value="0">-- Alle Stationen --</option>
                        <?php foreach ($stations as $station): ?>
                            <option value="<?php echo $station['ID']; ?>"
                                <?php echo $selectedStationId == $station['ID'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($station['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-item">
                    <label for="form_title">Formular:</label>
                    <select id="form_title" name="form_title" onchange="document.getElementById('filterForm').submit();">
                        <option value="">-- Alle Formulare --</option>
                        <?php foreach ($formTitles as $title): ?>
                            <option value="<?php echo htmlspecialchars($title); ?>"
                                <?php echo $selectedFormTitle === $title ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-actions">
                    <button type="button" class="btn secondary-btn" onclick="resetFilters()">
                        Filter zurücksetzen
                    </button>
                </div>
            </form>
        </div>

        <?php if (!empty($statistics)): ?>
            <div class="statistics-container">
                <h3><?php echo htmlspecialchars($statistics['title']); ?></h3>
                <p class="subtitle"><?php echo htmlspecialchars($statistics['subtitle']); ?></p>

                <div class="statistic-cards">
                    <?php if ($selectedTeamId > 0): ?>
                        <!-- Statistiken für Mannschaft -->
                        <div class="statistic-card">
                            <div class="statistic-title">Formulare ausgefüllt</div>
                            <div class="statistic-value">
                                <?php echo $statistics['completed_forms']; ?> / <?php echo $statistics['total_forms']; ?>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $statistics['completion_percentage']; ?>%"></div>
                            </div>
                            <div class="statistic-subtext"><?php echo $statistics['completion_percentage']; ?>%</div>
                        </div>

                        <div class="statistic-card">
                            <div class="statistic-title">Punkte erreicht</div>
                            <div class="statistic-value">
                                <?php echo $statistics['total_points']; ?> / <?php echo $statistics['max_possible_points']; ?>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $statistics['points_percentage']; ?>%"></div>
                            </div>
                            <div class="statistic-subtext"><?php echo $statistics['points_percentage']; ?>%</div>
                        </div>
                    <?php else: ?>
                        <!-- Statistiken für Station oder Formular -->
                        <div class="statistic-card">
                            <div class="statistic-title">Formulare ausgefüllt</div>
                            <div class="statistic-value">
                                <?php echo $statistics['completed_forms']; ?> / <?php echo $statistics['total_assignments']; ?>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $statistics['completion_percentage']; ?>%"></div>
                            </div>
                            <div class="statistic-subtext"><?php echo $statistics['completion_percentage']; ?>%</div>
                        </div>

                        <div class="statistic-card">
                            <div class="statistic-title">Durchschnittliche Punkte</div>
                            <div class="statistic-value">
                                <?php echo $statistics['average_points']; ?>
                            </div>
                            <div class="statistic-subtext">pro ausgefülltem Formular</div>
                        </div>

                        <div class="statistic-card">
                            <div class="statistic-title">Gesamtpunkte</div>
                            <div class="statistic-value">
                                <?php echo $statistics['total_points']; ?> / <?php echo $statistics['max_possible_points']; ?>
                            </div>
                            <div class="statistic-subtext">
                                <?php echo $statistics['max_possible_points'] > 0 ?
                                    round(($statistics['total_points'] / $statistics['max_possible_points']) * 100, 1) : 0; ?>%
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="results-container">
            <?php if (!empty($results)): ?>
                <table class="results-table">
                    <thead>
                    <tr>
                        <?php if ($selectedTeamId == 0): ?>
                            <th width="18%">Mannschaft</th>
                            <th width="18%">Formular</th>
                            <th width="14%">Station</th>
                            <th width="8%">Fragen</th>
                            <th width="12%">Status</th>
                            <th width="15%">Punkte</th>
                            <th width="15%">Abschlussdatum</th>
                        <?php else: ?>
                            <th width="22%">Formular</th>
                            <th width="18%">Station</th>
                            <th width="10%">Fragen</th>
                            <th width="14%">Status</th>
                            <th width="18%">Punkte</th>
                            <th width="18%">Abschlussdatum</th>
                        <?php endif; ?>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($results as $form): ?>
                        <tr>
                            <?php if ($selectedTeamId == 0): ?>
                                <td><?php echo htmlspecialchars($form['Teamname'] ?? $form['Teamname']); ?>
                                    (<?php echo htmlspecialchars($form['Kreisverband']); ?>)</td>
                            <?php endif; ?>
                            <td><?php echo htmlspecialchars($form['Titel'] ?? $form['form_title'] ?? 'Kein Titel'); ?></td>
                            <td><?php echo htmlspecialchars($form['station_name']); ?></td>
                            <td class="numeric-cell"><?php echo $form['question_count']; ?></td>
                            <td class="status-cell">
                                <?php if ($form['completed'] == 1): ?>
                                    <span class="status-indicator completed">Abgeschlossen</span>
                                <?php else: ?>
                                    <span class="status-indicator pending">Offen</span>
                                <?php endif; ?>
                            </td>
                            <td class="numeric-cell">
                                <?php if ($form['completed'] == 1): ?>
                                    <?php echo $form['points']; ?> / <?php echo $form['question_count']; ?>
                                    (<?php echo $form['question_count'] > 0 ?
                                        round(($form['points'] / $form['question_count']) * 100) : 0; ?>%)
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo $form['completed'] == 1 && !empty($form['completion_date']) ?
                                    date('d.m.Y H:i', strtotime($form['completion_date'])) : '-'; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-results">
                    <p>Keine Ergebnisse für die ausgewählten Filter.</p>
                    <p>Bitte wählen Sie andere Filterkriterien oder <a href="TeamFormRelationView.php">erstellen Sie Formulare</a>.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<script>
    function resetFilters() {
        // Alle Filter zurücksetzen
        document.getElementById('team_id').value = '0';
        document.getElementById('station_id').value = '0';
        document.getElementById('form_title').value = '';

        // Formular absenden
        document.getElementById('filterForm').submit();
    }

    // Vermeidet, dass mehrere Filter gleichzeitig aktiv sind
    document.addEventListener('DOMContentLoaded', function() {
        const teamSelect = document.getElementById('team_id');
        const stationSelect = document.getElementById('station_id');
        const formSelect = document.getElementById('form_title');

        teamSelect.addEventListener('change', function() {
            if (this.value !== '0') {
                stationSelect.value = '0';
                formSelect.value = '';
            }
        });

        stationSelect.addEventListener('change', function() {
            if (this.value !== '0') {
                teamSelect.value = '0';
                formSelect.value = '';
            }
        });

        formSelect.addEventListener('change', function() {
            if (this.value !== '') {
                teamSelect.value = '0';
                stationSelect.value = '0';
            }
        });
    });
</script>

</body>
</html>
