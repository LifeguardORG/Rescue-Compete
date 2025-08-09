<?php

namespace Controllers;

use Model\StationSubmissionModel;

require_once '../db/DbConnection.php';
require_once '../model/StationSubmissionModel.php';

// Hole die globale Datenbankverbindung aus dem globalen Scope
$connection = $GLOBALS['conn'] ?? null;
if (!$connection) {
    die("Datenbankverbindung nicht verfügbar.");
}

$pageTitle = "Eingabe der Parcours-Ergebnisse";

class StationSubmissionController {
    private StationSubmissionModel $model;

    // Konstruktor: Initialisiert das Model mit der Datenbankverbindung
    public function __construct($db) {
        $this->model = new StationSubmissionModel($db);
    }

    // Zeigt die Liste aller Stationen an
    public function listStations() {
        $stations = $this->model->getAllStations();
        include '../view/StationList.php';
        exit; // Verhindert, dass der Rest des Skripts erneut ausgeführt wird
    }

    // Zeigt die Eingabeseite für eine bestimmte Station an
    public function inputStation() {
        $stationID = isset($_GET['station']) ? (int)$_GET['station'] : 0;
        if ($stationID <= 0) {
            die("Ungültige Station.");
        }
        // Lade den Stationsdatensatz (inkl. Name)
        $stationData = $this->model->getStationByID($stationID);

        // Lade die Protokolle (Ergebnisvorlagen) für die Station und die Mannschaften
        $protocols = $this->model->getProtocolsByStation($stationID);
        $teams = $this->model->getTeams();
        $submittedTeams = $this->model->getSubmittedTeams($stationID);

        // Übergabe des Stationsnamens statt nur der Nummer
        include '../view/StationSubmission.php';
        exit;
    }


    // Speichert die eingegebenen Ergebnisse einer Station
    public function saveStationResults() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Lese die Station-ID, Mannschaft und die Ergebnisse aus den POST-Daten
            $stationID = isset($_POST['stationID']) ? (int)$_POST['stationID'] : 0;
            $teamID = $_POST['teamID'] ?? null;
            $resultsInput = $_POST['results'] ?? [];

            // Wenn keine Ergebnisse eingegeben wurden, trotzdem Erfolg zurückgeben
            if (empty($resultsInput)) {
                header("Location: ../controller/StationSubmissionController.php?action=input&station=" . $stationID . "&status=success");
                exit;
            }

            // Zur Vereinheitlichung packen wir die Ergebnisse unter den teamID-Schlüssel
            $results = [$teamID => $resultsInput];

            $success = $this->model->saveResults($results);

            // Weiterleiten je nach Erfolg: Bleibe auf der gleichen Seite, anstatt zurück zur Liste zu gehen.
            if ($success) {
                header("Location: ../controller/StationSubmissionController.php?action=input&station=" . $stationID . "&status=success");
            } else {
                header("Location: ../controller/StationSubmissionController.php?action=input&station=" . $stationID . "&status=failure");
            }
            exit;
        }
    }
}

// Routing: Falls die Datei direkt aufgerufen wird, wird anhand des GET-Parameters die entsprechende Aktion ausgeführt.
$action = $_GET['action'] ?? 'list';
$controller = new StationSubmissionController($connection);

switch ($action) {
    case 'input':
        $controller->inputStation();
        break;
    case 'save':
        $controller->saveStationResults();
        break;
    case 'list':
    default:
        $controller->listStations();
        break;
}