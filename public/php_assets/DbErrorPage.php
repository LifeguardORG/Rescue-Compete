<?php
http_response_code(503);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Datenbankfehler</title>
    <link rel="stylesheet" href="../css/Colors.css">
    <link rel="stylesheet" href="../css/GlobalLayout.css">
    <link rel="stylesheet" href="../css/Components.css">
</head>
<body>
    <div class="modal active">
        <div class="modal-content">
            <h2>Datenbankfehler</h2>
            <p>Die Datenbankverbindung ist nicht verfügbar. Bitte versuchen Sie es später erneut.</p>
            <a href="../index.php" class="btn">Zur Startseite</a>
        </div>
    </div>
</body>
</html>
