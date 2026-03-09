<?php
require_once '../db/DbConnection.php';
require_once '../model/QuizPoolModel.php';
require_once '../controller/QuestionPoolInputController.php';
require_once '../php_assets/CustomAlertBox.php';

use QuestionPool\QuizPoolModel;
use QuestionPool\QuestionPoolInputController;

if (!isset($conn)) {
    require __DIR__ . '/../php_assets/DbErrorPage.php'; die();
}

// Model und Controller instanziieren
$model = new QuizPoolModel($conn);
$controller = new QuestionPoolInputController($model);
$controller->handleRequest();

// Daten für die View
$message = $controller->message;
$pools = $model->read();

// Formularwerte (ggf. aus vorherigem POST)
$name = isset($_POST['name']) ? trim($_POST['name']) : "";

$pageTitle = "Verwaltung der Fragenpools";
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RescueCompete - Fragenpools</title>
    <link rel="icon" type="image/x-icon" href="../assets/images/logos/ww-favicon.ico">
    <link rel="stylesheet" href="../css/Colors.css">
    <link rel="stylesheet" href="../css/GlobalLayout.css">
    <link rel="stylesheet" href="../css/Navbar.css">
    <link rel="stylesheet" href="../css/Sidebar.css">
    <link rel="stylesheet" href="../css/Footer.css">
    <link rel="stylesheet" href="../css/Components.css">
</head>
<body class="has-navbar">
<!-- Navbar -->
<?php include '../php_assets/Navbar.php'; ?>

<div class="container">
    <!-- Sidebar import -->
    <?php include '../php_assets/Sidebar.php'; ?>

    <!-- Fragenpool hinzufügen / aktualisieren -->
    <section class="form-section">
        <h2>Neuen Fragenpool hinzufügen</h2>
        <?php if (!empty($message)): ?>
            <!-- Nachricht wird später als CustomAlertBox angezeigt -->
        <?php endif; ?>
        <form id="pool-form" method="POST">
            <div class="form-group">
                <label for="name">Name des Fragenpools:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" placeholder="Name des Fragenpools" required>
            </div>
            <button type="submit" name="add_pool" class="btn">Fragenpool speichern</button>
        </form>
    </section>

    <!-- Bestehende Fragenpools anzeigen -->
    <section class="info-section">
        <h2>Bestehende Fragenpools</h2>
        <table>
            <thead>
            <tr>
                <th width="50%">Name</th>
                <th width="25%">Anzahl Fragen</th>
                <th width="25%">Aktionen</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($pools)): ?>
                <?php foreach ($pools as $pool): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($pool['Name']); ?></td>
                        <td><?php echo htmlspecialchars($model->getQuestionCount($pool['ID'])); ?></td>
                        <td>
                            <form method="POST" class="delete-form">
                                <input type="hidden" name="delete_id" value="<?php echo htmlspecialchars($pool['ID']); ?>">
                                <input type="hidden" name="delete_pool" value="1">
                                <button type="submit" class="btn">Löschen</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3">Keine Fragenpools gefunden.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </section>
</div>

<?php

echo CustomAlertBox::renderSimpleConfirm(
    "confirmDeleteModal",
    "Löschen bestätigen",
    "Möchten Sie diesen Fragenpool und alle zugehörigen Fragen wirklich löschen?",
    "if(deleteForm){ deleteForm.submit(); }",
    "document.getElementById('confirmDeleteModal').classList.remove('active');"
);

if (!empty($message)):
    $alertType = strpos($message, 'erfolgreich') !== false ? 'Erfolg' : 'Hinweis';
    echo CustomAlertBox::renderSimpleAlert(
        "messageAlert",
        $alertType,
        $message
    );
    echo '<script>document.getElementById("messageAlert").classList.add("active");</script>';
endif;
?>

<script>
    let deleteForm = null;

    document.addEventListener('DOMContentLoaded', function() {
        const deleteForms = document.querySelectorAll('.delete-form');
        deleteForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                deleteForm = this;
                const modal = document.getElementById('confirmDeleteModal');
                if (modal) {
                    modal.classList.add('active');
                }
            });
        });
    });
</script>

</body>
</html>