<?php

namespace Station\Controller;

use Station\StationModel;
use Station\StationWeightModel;

/**
 * Controller für die Verwaltung der Stationsgewichtungen.
 */
class StationWeightController {
    private StationModel $stationModel;
    private StationWeightModel $weightModel;
    public string $message = "";
    public array $modalData = [];
    public string $redirectUrl = "StationWeightView.php";

    /**
     * Konstruktor: Initialisiert die benötigten Modelle.
     *
     * @param StationModel $stationModel Das Stationsmodell
     * @param StationWeightModel $weightModel Das Gewichtungsmodell
     */
    public function __construct(StationModel $stationModel, StationWeightModel $weightModel) {
        $this->stationModel = $stationModel;
        $this->weightModel = $weightModel;
    }

    /**
     * Verarbeitet Anfragen zum Aktualisieren von Stationsgewichtungen.
     *
     * @return array Daten für die View
     */
    public function processRequest(): array {
        // Initialisiere die Gewichtungen, falls noch keine existieren
        $this->weightModel->initializeWeights();

        // Verarbeite das Update-Formular
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_weights'])) {
            $this->handleUpdateWeights();
        }

        // Hole alle Stationen mit ihren Gewichtungen
        $stations = $this->getStationsWithWeights();

        return [
            'stations' => $stations,
            'message' => $this->message
        ];
    }

    /**
     * Verarbeitet die Aktualisierung der Stationsgewichtungen.
     */
    private function handleUpdateWeights() {
        if (!isset($_POST['weights']) || !is_array($_POST['weights'])) {
            $this->message = "Fehlerhafte Anfrage: Keine Gewichtungen übermittelt.";
            return;
        }

        $weights = $_POST['weights'];
        $stationIds = array_keys($weights);
        $success = true;

        // Validiere die Gewichtungen
        foreach ($weights as $id => $weight) {
            if (!is_numeric($weight) || $weight < 0) {
                $this->message = "Ungültige Gewichtung für Station ID $id. Gewichtungen müssen positive Zahlen sein.";
                return;
            }
        }

        // Aktualisiere die Gewichtungen in der Datenbank
        foreach ($weights as $id => $weight) {
            $result = $this->weightModel->setWeight((int)$id, (int)$weight);
            if (!$result) {
                $success = false;
            }
        }

        $this->message = $success
            ? "Stationsgewichtungen erfolgreich aktualisiert."
            : "Fehler beim Aktualisieren der Gewichtungen.";
    }

    /**
     * Holt alle Stationen mit ihren aktuellen Gewichtungen.
     *
     * @return array Liste der Stationen mit Gewichtungen
     */
    private function getStationsWithWeights(): array {
        $stations = $this->stationModel->read();
        $weights = $this->weightModel->read();

        // Erstelle ein Lookup-Array für die Gewichtungen
        $weightLookup = [];
        if ($weights) {
            foreach ($weights as $weight) {
                $weightLookup[$weight['station_ID']] = $weight['weight'];
            }
        }

        // Füge die Gewichtungen zu den Stationsdaten hinzu
        if ($stations) {
            foreach ($stations as &$station) {
                $station['weight'] = $weightLookup[$station['ID']] ?? 100; // Standardgewichtung 100
            }
        }

        return $stations ?: [];
    }
}
