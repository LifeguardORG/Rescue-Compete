<?php
namespace Controllers;

use Model\ResultModel;

class SwimmingResultsController {
    /**
     * @var ResultModel Das Model, das die Schwimm-Ergebnisse bereitstellt.
     */
    private ResultModel $model;

    /**
     * Konstruktor: Initialisiert den Controller mit dem übergebenen Model.
     *
     * @param ResultModel $model Das Model zur Abfrage der Schwimm-Ergebnisse.
     */
    public function __construct(ResultModel $model) {
        $this->model = $model;
    }

    /**
     * Verarbeitet den Request und liefert die gruppierten Schwimm-Ergebnisse sowie die
     * eindeutigen Staffel-IDs zurück.
     *
     * @return array Enthält die Schlüssel 'wertungDetails' (gruppierte Schwimm-Ergebnisse)
     *               und 'staffelIDs' (eindeutige, sortierte Staffel-IDs).
     */
    public function processRequest(): array {
        // Initialisiere die PHP-Fehlerausgabe und Logging
        $this->initializeErrorLogging();
        // Hole die Schwimm-Ergebnisse aus dem Model
        $wertungDetails = $this->model->getSwimmingWertungenWithDetails();
        // Je Wertung die zugeordneten Staffeln (für die Spaltenköpfe) – gleiche Quelle
        // wie die Punkteberechnung, damit Spalten und Divisor konsistent sind.
        $staffelIDsByWertung = $this->model->getStaffelnByWertungMap();

        return [
            'wertungDetails'      => $wertungDetails,
            'staffelIDsByWertung' => $staffelIDsByWertung,
        ];
    }

    /**
     * Konfiguriert die PHP-Fehlerausgabe und das Logging.
     */
    private function initializeErrorLogging() {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        ini_set('log_errors', 1);
        ini_set('error_log', __DIR__ . '/../logs/php_error.log');
    }
}
