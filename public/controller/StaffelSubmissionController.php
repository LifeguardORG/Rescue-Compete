<?php

namespace Controllers;

use Model\StaffelSubmissionModel;

require_once '../db/DbConnection.php';
require_once '../model/StaffelSubmissionModel.php';

// Hole die globale Datenbankverbindung
$connection = $GLOBALS['conn'] ?? null;
if (!$connection) {
    die("Datenbankverbindung nicht verfügbar.");
}

// Debug-Funktion zur einheitlichen Protokollierung
function debug_log($message, $data = null) {
    $log = "[DEBUG] " . $message;
    if ($data !== null) {
        $log .= ": " . print_r($data, true);
    }
    error_log($log);
}

debug_log("Controller wurde geladen");

$pageTitle = "Eingabe der Schwimm-Ergebnisse";

class StaffelSubmissionController {
    private StaffelSubmissionModel $model;

    // Konstruktor: Initialisiert das Model
    public function __construct($db) {
        $this->model = new StaffelSubmissionModel($db);
        debug_log("Controller initialisiert");
    }

    // Zeigt die Liste aller Staffeln an
    public function listStaffeln() {
        debug_log("Methode listStaffeln aufgerufen");
        $staffeln = $this->model->getAllStaffeln();
        debug_log("Geladene Staffeln", $staffeln);
        include '../view/StaffelList.php';
        exit;
    }

    // Zeigt die Eingabeseite für eine bestimmte Staffel an
    public function inputStaffel() {
        debug_log("Methode inputStaffel aufgerufen", $_GET);
        $staffelID = isset($_GET['staffel']) ? (int)$_GET['staffel'] : 0;
        debug_log("Staffel-ID", $staffelID);

        if ($staffelID <= 0) {
            debug_log("Fehler: Ungültige Staffel-ID", $staffelID);
            $this->displayError("Ungültige Staffel-ID");
            return;
        }

        // Staffel-Datensatz laden, um den Staffelnamen zu erhalten
        $staffel = $this->model->getStaffelById($staffelID);
        debug_log("Geladene Staffel-Daten", $staffel);

        if (!$staffel) {
            debug_log("Fehler: Staffel nicht gefunden", $staffelID);
            $this->displayError("Staffel nicht gefunden");
            return;
        }

        $staffelName = $staffel['name'];
        $teams = $this->model->getTeams();
        $submittedTeams = $this->model->getSubmittedTeams($staffelID);

        // Lade bereits vorhandene Ergebnisse
        $existingResults = $this->model->getStaffelResults($staffelID);

        debug_log("Staffelname", $staffelName);
        debug_log("Anzahl geladener Teams", count($teams));
        debug_log("Bereits eingetragene Teams", $submittedTeams);
        debug_log("Vorhandene Ergebnisse", $existingResults);

        include '../view/StaffelSubmission.php';
        exit;
    }

    // Speichert die eingegebenen Schwimm-Ergebnisse
    public function saveStaffelResults() {
        debug_log("Methode saveStaffelResults aufgerufen", $_SERVER['REQUEST_METHOD']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            debug_log("Fehler: Ungültige Anfrage-Methode", $_SERVER['REQUEST_METHOD']);
            $this->displayError("Ungültige Anfrage-Methode");
            return;
        }

        $staffelID = isset($_POST['staffelID']) ? (int)$_POST['staffelID'] : 0;
        debug_log("Zu speichernde Staffel-ID", $staffelID);

        if ($staffelID <= 0) {
            debug_log("Fehler: Ungültige Staffel-ID", $staffelID);
            $this->displayError("Ungültige Staffel-ID");
            return;
        }

        $results = $_POST['results'] ?? [];
        debug_log("Empfangene Ergebnisse", $results);

        // Überprüfen, ob mindestens ein Ergebnis eingegeben wurde
        $hasResults = false;
        foreach ($results as $teamID => $teamResults) {
            debug_log("Prüfe Team-Ergebnisse", ['teamID' => $teamID, 'data' => $teamResults]);
            foreach ($teamResults as $key => $value) {
                if (!empty(trim($value))) {
                    $hasResults = true;
                    debug_log("Gültiges Ergebnis gefunden", ['teamID' => $teamID, 'key' => $key, 'value' => $value]);
                    break 2;
                }
            }
        }

        if (!$hasResults) {
            debug_log("Fehler: Keine Ergebnisse eingegeben");
            header("Location: ../controller/StaffelSubmissionController.php?action=input&staffel=" . $staffelID . "&status=failure&message=Keine Ergebnisse eingegeben");
            exit;
        }

        // Prüfen auf Strafzeiten ohne geschwommene Zeit
        foreach ($results as $teamID => $teamResults) {
            $schwimmzeit = trim($teamResults['geschwommene_zeit'] ?? '');
            $strafzeit = trim($teamResults['strafzeit'] ?? '');

            if (empty($schwimmzeit) && !empty($strafzeit)) {
                debug_log("Fehler: Strafzeit ohne geschwommene Zeit", [
                    'teamID' => $teamID,
                    'strafzeit' => $strafzeit
                ]);
                header("Location: ../controller/StaffelSubmissionController.php?action=input&staffel=" . $staffelID . "&status=failure&message=Für mindestens ein Team wurde eine Strafzeit ohne geschwommene Zeit eingegeben");
                exit;
            }
        }

        // Ergebnisse speichern
        debug_log("Speichere Ergebnisse");
        $success = $this->model->saveResults($staffelID, $results);
        debug_log("Speichervorgang abgeschlossen", ['erfolg' => $success ? 'Ja' : 'Nein']);

        if ($success) {
            header("Location: ../controller/StaffelSubmissionController.php?action=input&staffel=" . $staffelID . "&status=success");
        } else {
            header("Location: ../controller/StaffelSubmissionController.php?action=input&staffel=" . $staffelID . "&status=failure&message=" . urlencode("Die Ergebnisse konnten nicht gespeichert werden. Bitte überprüfen Sie das Format der eingegebenen Zeiten."));
        }
        exit;
    }

    // Hilfsmethode zur Anzeige von Fehlermeldungen
    private function displayError($message) {
        debug_log("Fehleranzeige", $message);
        error_log($message);

        // Bessere Fehlerbehandlung - Weiterleitung zur Staffel-Liste mit Fehlermeldung
        header("Location: ../controller/StaffelSubmissionController.php?action=list&status=failure&message=" . urlencode($message));
        exit;
    }
}

// Routing basierend auf dem GET-Parameter 'action'
$action = $_GET['action'] ?? 'list';
debug_log("Routing für Action", $action);

$controller = new StaffelSubmissionController($connection);

switch ($action) {
    case 'input':
        $controller->inputStaffel();
        break;
    case 'save':
        $controller->saveStaffelResults();
        break;
    default: // or 'list'
        $controller->listStaffeln();
        break;
}