<?php
require_once '../db/DbConnection.php';
require_once '../model/MannschaftModel.php';
require_once '../controller/MannschaftController.php';
require_once '../php_assets/CustomAlertBox.php';

use Mannschaft\MannschaftModel;
use Station\Controller\MannschaftController;

// Überprüfe, ob eine Datenbankverbindung besteht.
if (!isset($conn)) {
    require __DIR__ . '/../php_assets/DbErrorPage.php'; die();
}

// Instanziiere Modell und Controller.
$model = new MannschaftModel($conn);
$controller = new MannschaftController($model);
$controller->handleRequest();

// Daten für die Anzeige in der View.
$duplicateData = $controller->duplicateData;  // Verwende hier den Wert aus dem Controller
$message = $controller->message;
$teams = $model->getAllMannschaften();

// Werte aus dem POST-Request (falls vorhanden).
$teamname = isset($_POST['teamname']) ? trim($_POST['teamname']) : "";
$kreisverband = isset($_POST['kreisverband']) ? trim($_POST['kreisverband']) : "";
$landesverband = isset($_POST['landesverband']) ? trim($_POST['landesverband']) : "";

$pageTitle = "Verwaltung der Mannschaften";
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RescueCompete</title>
    <link rel="icon" type="image/x-icon" href="../assets/images/logos/ww-favicon.ico">
    <!-- Gemeinsame Styles für alle Eingabeseiten -->
    <link rel="stylesheet" href="../css/Colors.css">
    <link rel="stylesheet" href="../css/GlobalLayout.css">
    <link rel="stylesheet" href="../css/Navbar.css">
    <link rel="stylesheet" href="../css/Sidebar.css">
    <link rel="stylesheet" href="../css/Footer.css">
    <link rel="stylesheet" href="../css/Components.css">
</head>
<body class="has-navbar">
<!-- Navbar einbinden -->
<?php include '../php_assets/Navbar.php'; ?>

<div class="container">
    <!-- Sidebar import -->
    <?php include '../php_assets/Sidebar.php'; ?>

    <section class="form-section">
        <h2>Neue Mannschaft hinzufügen</h2>
        <?php if ($message): ?>
            <p style="color:red;"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="teamname">Mannschafts-Name</label>
                <input type="text" id="teamname" name="teamname" placeholder="Mannschafts-Name" required
                       value="<?php echo htmlspecialchars($teamname); ?>">
            </div>
            <div class="form-group">
                <label for="kreisverband">Kreisverband</label>
                <input type="text" id="kreisverband" name="kreisverband" placeholder="Kreisverband" required
                       value="<?php echo htmlspecialchars($kreisverband); ?>">
            </div>
            <div class="form-group">
                <label for="landesverband">Landesverband</label>
                <input type="text" id="landesverband" name="landesverband" placeholder="Landesverband" required
                       value="<?php echo htmlspecialchars($landesverband); ?>">
            </div>
            <button type="submit" name="add_team" class="btn">Mannschaft hinzufügen</button>
        </form>
    </section>

    <section class="info-section">
        <h2>Bestehende Mannschaften</h2>
        <table>
            <thead>
            <tr>
                <th>Mannschafts-Name</th>
                <th>Kreisverband</th>
                <th>Landesverband</th>
                <th>Aktionen</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($teams)): ?>
                <?php foreach ($teams as $team): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($team['Teamname'] ?? "nicht gefunden"); ?></td>
                        <td><?php echo htmlspecialchars($team['Kreisverband'] ?? "nicht gefunden"); ?></td>
                        <td><?php echo htmlspecialchars($team['Landesverband'] ?? "nicht gefunden"); ?></td>
                        <td>
                            <form method="POST" class="delete-form">
                                <input type="hidden" name="delete_id" value="<?php echo htmlspecialchars($team['ID']); ?>">
                                <input type="hidden" name="delete_team" value="1">
                                <button type="submit" class="btn">Löschen</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">Keine Mannschaften gefunden.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </section>
</div>

<?php
    // ------  Eingabe Duplikate - AlertBox ---------
    // Falls Duplikatdaten vorliegen, wird ein modales Bestätigungsfenster via AlertBox angezeigt.
    if (!empty($duplicateData)):
        $alert = new CustomAlertBox("confirmDuplicateTeam");
        $alert->setTitle("Duplikat gefunden");
        $alert->setMessage("Eine Mannschaft mit diesem Namen im Kreisverband existiert bereits. Möchtest du diese aktualisieren?");
        // Setze die versteckten Felder, die im Formular benötigt werden.
        $alert->setData([
            'teamname'     => $duplicateData['teamname'] ?? "",
            'kreisverband' => $duplicateData['kreisverband'] ?? "",
            'landesverband'=> $duplicateData['landesverband'] ?? "",
            'duplicate_id' => $duplicateData['duplicate_id'] ?? "",
            'confirm_update' => "1",
            'add_team'     => "1"
        ]);
        // Button "Ja" als Submit-Button
        $alert->addButton("Ja", "", "btn", "submit");
        // Button "Nein", der das Modal schließt
        $alert->addButton("Nein", "document.getElementById('confirmDuplicateTeam').classList.remove('active');", "btn", "button");
        echo $alert->render();
    endif;

    // ------ Löschen - AlertBox ---------
    echo CustomAlertBox::renderSimpleConfirm(
        "confirmDeleteModal",
        "Löschen bestätigen",
        "Möchten Sie diese Mannschaft und alle dazugehörigen Punkte und Zeiten wirklich löschen?",
        "window.confirmDelete();", // Hier wurde der Aufruf geändert
        "document.getElementById('confirmDeleteModal').classList.remove('active');"
    );

?>

<script src="../js/MannschaftInputScript.js"></script>
</body>
</html>
