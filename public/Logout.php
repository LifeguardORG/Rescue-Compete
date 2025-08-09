<?php
require_once __DIR__ . '/CookieMonster.php';

// Sichere Session-Einstellungen BEVOR session_start()
initializeSecureSession();

// Session starten
session_start();

// Anzeigen aller auftretenden Fehler
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Aufrufen der Funktion zum Ausloggen
logout();

/**
 * Loggt den aktuell angemeldeten Benutzer aus und löscht die Session.
 */
function logout() {
    // Session-Array leeren
    session_unset();

    // Cookie für die Session entfernen
    removeSessionCookie();

    // Session löschen
    session_destroy();

    // Absolute URL für die Weiterleitung
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = htmlspecialchars($_SERVER["HTTP_HOST"]);
    $baseUrl = $protocol . $host . rtrim(dirname(dirname(htmlspecialchars($_SERVER["PHP_SELF"]))), "/\\");

    // Weiterleiten zur Login-Seite mit Erfolgsmeldung
    $redirectUrl = "$baseUrl/view/Login.php";
    header("Location: $redirectUrl");
    exit; // Wichtig: Script-Ausführung beenden nach dem Weiterleiten
}