<?php
// Starte die Session für Login-Seite
session_start();

// Absolute URLs für die Weiterleitung
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = htmlspecialchars($_SERVER["HTTP_HOST"]);
$baseUrl = $protocol . $host . rtrim(dirname(htmlspecialchars($_SERVER["PHP_SELF"]), 2), "/\\");

// QR-Code aus der URL-Weiterleitung erfassen
if (isset($_GET['redirect']) && $_GET['redirect'] === 'form' && isset($_GET['code'])) {
    $_SESSION['redirect_code'] = $_GET['code'];
}

// Leite den Nutzer zur Index.php weiter, wenn er bereits eingeloggt ist
if(isset($_SESSION["login"]) && $_SESSION["login"] === "ok"){
    // Falls ein redirect_code in der Session existiert, leite zum entsprechenden Formular weiter
    if (isset($_SESSION['redirect_code'])) {
        $code = $_SESSION['redirect_code'];
        // Code aus Session entfernen um Weiterleitungsschleifen zu verhindern
        unset($_SESSION['redirect_code']);
        header("location: FormRedirect.php?code=" . urlencode($code));
        exit;
    }

    header("location: $baseUrl/index.php");
    exit;
}

$pageTitle = "Login";

// Fehlermeldung basierend auf dem Parameter anzeigen
$errorMessage = '';
if (isset($_GET['f'])) {
    switch ($_GET['f']) {
        case '1':
            $errorMessage = 'Bitte geben Sie Benutzername und Passwort ein.';
            break;
        case '2':
            $errorMessage = 'Benutzer nicht gefunden.';
            break;
        case '3':
            $errorMessage = 'Falsches Passwort.';
            break;
        case '999':
            $errorMessage = 'Datenbankverbindung nicht verfügbar.';
            break;
        default:
            $errorMessage = 'Ein Fehler ist aufgetreten. Bitte versuchen Sie es erneut.';
    }
}

// Weiterleitung nach Login anpassen - verstecktes Feld für QR-Code-Weiterleitung
$loginAction = '../controller/LoginManager.php';
if (isset($_SESSION['redirect_code'])) {
    // Wenn ein QR-Code in der Session ist, URL-Parameter hinzufügen
    $loginAction .= '?redirect_after=form';
}
?>

<!DOCTYPE html>
<html lang="de">
<!-- Imports von css Dateien, js Scripts und der Navbar als auch PHP Dateien mit nötigen Methoden -->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RescueCompete - <?php echo $pageTitle; ?></title>
    <link rel="icon" type="image/x-icon" href="../assets/images/logos/ww-favicon.ico">
    <link rel="stylesheet" href="../css/Colors.css">
    <link rel="stylesheet" href="../css/InputStyling.css">
    <link rel="stylesheet" href="../css/Login.css">
    <link rel="stylesheet" href="../css/PasswordVisibility.css">
    <script src="../js/LoginErrors.js"></script>
    <script src="../js/PasswordVisibility.js"></script>
</head>
<body>

<!-- Login Formular -->
<div id="body_container">
    <form class="form-section" method="post" action="<?php echo $loginAction; ?>" id="login_form">
        <h2>Wettkampf-Software Login</h2>

        <!-- Container für Fehlermeldungen -->
        <div id="error-message">
            <?php if (!empty($errorMessage)): ?>
                <div class="error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
            <?php endif; ?>

            <?php if (isset($_SESSION['redirect_code'])): ?>
                <div class="notice-message" style="color: #008ccd; margin-bottom: 15px;">
                    Bitte melde dich an, um zum Formular zu gelangen.
                </div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="username">Benutzername:</label>
            <input type="text" class="valid" id="username" name="username" placeholder="Benutzername eingeben" required>
        </div>

        <div class="form-group">
            <label for="password">Passwort:</label>
            <div class="password-container">
                <input type="password" class="valid" id="password" name="password" placeholder="Passwort eingeben" required>
                <button type="button" id="toggle-password" class="toggle-password" title="Passwort anzeigen">
                    <span id="toggle-icon" class="eye-icon eye-closed"></span>
                </button>
            </div>
        </div>

        <?php if (isset($_SESSION['redirect_code'])): ?>
            <!-- Versteckte Felder für die Weiterleitung -->
            <input type="hidden" name="redirect_qrcode" value="<?php echo htmlspecialchars($_SESSION['redirect_code']); ?>">
        <?php endif; ?>

        <div class="button-group">
            <input type="submit" value="Einloggen">
        </div>
    </form>
</div>

</body>
</html>