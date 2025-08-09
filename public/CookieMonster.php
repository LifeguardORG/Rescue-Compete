<?php

/**
 * Initialisiert sichere Session-Einstellungen
 * MUSS vor session_start() aufgerufen werden
 */
function initializeSecureSession() {
    // Session-Parameter setzen BEVOR session_start() aufgerufen wird
    session_set_cookie_params([
        'lifetime' => 86400, // 24 Stunden
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Strict'
    ]);

    // Session-Lifetime im PHP setzen
    ini_set('session.gc_maxlifetime', 86400);
}

/**
 * Erstellt einen Cookie für die aktuelle Session mit längerem Ablauf
 * Wird NACH erfolgreichem Login aufgerufen
 */
function createSessionCookie() {
    // Prüfe, ob Session bereits gestartet ist
    if (session_status() !== PHP_SESSION_ACTIVE) {
        error_log("Warnung: createSessionCookie() aufgerufen, aber Session ist nicht aktiv");
        return false;
    }

    // Lebensdauer des Cookies auf 24 Stunden setzen
    $lifetime = time() + 86400; // 24 Stunden in Sekunden

    // Setze den Session-Cookie mit HttpOnly-Flag für mehr Sicherheit
    $result = setcookie(session_name(), session_id(), [
        'expires' => $lifetime,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Strict'
    ]);

    if (!$result) {
        error_log("Fehler beim Setzen des Session-Cookies");
        return false;
    }

    error_log("Session-Cookie erfolgreich gesetzt für Session: " . session_id());
    return true;
}

/**
 * Entfernt den Cookie einer Session und löscht die Session-Daten
 */
function removeSessionCookie() {
    // Session-Daten löschen
    $_SESSION = [];

    // Cookie löschen, wenn er existiert
    if (isset($_COOKIE[session_name()])) {
        // Setze den Cookie mit einem abgelaufenen Datum
        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => '/',
        ]);
    }
}

/**
 * Prüft, ob die aktuelle Session gültig ist
 * Kann verwendet werden, um zu prüfen, ob der Nutzer eingeloggt ist
 *
 * @return bool True wenn die Session gültig ist, andernfalls false
 */
function isValidSession(): bool
{
    // Prüfe, ob die notwendigen Session-Variablen gesetzt sind
    if (!isset($_SESSION["login"]) || $_SESSION["login"] !== "ok") {
        return false;
    }

    return true;
}