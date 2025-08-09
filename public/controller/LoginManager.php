<?php
require_once '../db/DbConnection.php';
require_once '../model/UserModel.php';
require_once '../CookieMonster.php';

use Station\UserModel;

// WICHTIG: Sichere Session-Einstellungen BEVOR session_start()
initializeSecureSession();

// Jetzt die Session starten
session_start();

// Debug: Session-Status prüfen
error_log("Session-Status: " . session_status());
error_log("Session-ID: " . session_id());

// Prüfe die Datenbankverbindung
if (!isset($conn) || !($conn instanceof PDO)) {
    error_log("Datenbankverbindung nicht verfügbar");
    die("<script>alert('Datenbankverbindung nicht verfügbar.'); window.location.href='../view/Login.php?f=999';</script>");
}

// Initialisiere das UserModel
$model = new UserModel($conn);

// Absolute URLs für die Weiterleitung
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = htmlspecialchars($_SERVER["HTTP_HOST"]);
$baseUrl = $protocol . $host . rtrim(dirname(htmlspecialchars($_SERVER["PHP_SELF"]), 2), "/\\");

// QR-Code-Weiterleitung prüfen
$hasQrRedirect = !empty($_POST['redirect_qrcode']);
$qrCode = $hasQrRedirect ? $_POST['redirect_qrcode'] : '';

// Standardmäßig auf Login-Seite zurückleiten
$redirectPath = "$baseUrl/view/Login.php";
$errorFlag = "1"; // Standardfehler: Fehlende Eingabe

// Formularwerte validieren und verarbeiten
$username = isset($_POST['username']) ? trim($_POST['username']) : "";
$password = isset($_POST['password']) ? trim($_POST['password']) : "";

// Debug: Login-Versuch protokollieren
error_log("Login-Versuch für Benutzer: " . $username);

// Prüfe, ob Benutzername und Passwort ausgefüllt sind
if (!empty($username) && !empty($password)) {
    // Hole Benutzerdaten aus der Datenbank
    $user = $model->bootlegRead($username);

    if ($user) {
        error_log("Benutzer gefunden: " . print_r($user, true));

        // Nutzer gefunden, Passwort überprüfen
        $salt = "Zehn zahme Ziegen zogen zehn Zentner Zucker zum Zoo";
        $algo = "md5";
        $pw_hash = hash_hmac($algo, $password, $salt);

        if ($pw_hash === $user["passwordHash"]) {
            // Passwort korrekt - Login erfolgreich
            error_log("Login erfolgreich für Benutzer: " . $username);

            // Session-Daten setzen
            $_SESSION["id"] = $user["ID"];
            $_SESSION["login"] = "ok";
            $_SESSION["username"] = $username;

            // Benutzertyp in Session speichern, wenn verfügbar
            if (isset($user["acc_typ"])) {
                $_SESSION["acc_typ"] = $user["acc_typ"];
            }

            // Debug: Session-Daten nach dem Setzen prüfen
            error_log("Session-Daten gesetzt: " . print_r($_SESSION, true));

            // Session-Cookie mit verlängerter Lebensdauer erstellen
            $cookieResult = createSessionCookie();
            if (!$cookieResult) {
                error_log("Warnung: Session-Cookie konnte nicht gesetzt werden");
            }

            // Bei QR-Code-Weiterleitung direkt zum FormRedirect gehen
            if ($hasQrRedirect) {
                $redirectPath = "$baseUrl/view/FormRedirect.php?code=" . urlencode($qrCode);
                $errorFlag = null; // Kein Fehler bei erfolgreichem Login
            } else {
                // Auf die Startseite weiterleiten
                $redirectPath = "$baseUrl/index.php";
                $errorFlag = null; // Kein Fehler bei erfolgreichem Login
            }
        } else {
            // Passwort falsch
            error_log("Falsches Passwort für Benutzer: " . $username);
            $errorFlag = "3"; // Fehlercode: Falsches Passwort
        }
    } else {
        // Nutzer nicht gefunden
        error_log("Benutzer nicht gefunden: " . $username);
        $errorFlag = "2"; // Fehlercode: Benutzer nicht gefunden
    }
} else {
    error_log("Leere Eingabe: Username='" . $username . "', Password='" . (empty($password) ? 'leer' : 'gefüllt') . "'");
}

// Füge Fehlercode zur Weiterleitung hinzu, falls vorhanden
if ($errorFlag !== null) {
    // Bei QR-Code-Weiterleitung, diesen zum Login zurückgeben
    if ($hasQrRedirect && strpos($redirectPath, "Login.php") !== false) {
        $redirectPath .= "?f=" . $errorFlag . "&redirect=form&code=" . urlencode($qrCode);
    } else {
        $redirectPath .= "?f=" . $errorFlag;
    }
}

// Debug: Weiterleitung protokollieren
error_log("Weiterleitung zu: " . $redirectPath);

// Weiterleitung durchführen
header("Location: $redirectPath");
exit; // Wichtig: Beendet die Skriptausführung nach der Weiterleitung