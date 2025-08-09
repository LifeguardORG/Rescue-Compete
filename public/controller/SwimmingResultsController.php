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
        // Sammle alle eindeutigen Staffel-IDs aus den Ergebnissen
        $staffelIDs = $this->collectStaffelIDs($wertungDetails);

        return [
            'wertungDetails' => $wertungDetails,
            'staffelIDs'     => $staffelIDs,
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

    /**
     * Sammelt alle eindeutigen Staffel-IDs aus den gruppierten Schwimm-Ergebnissen.
     * Dabei werden Sonderschlüssel wie 'TotalStaffelScore' ausgeschlossen.
     *
     * @param array $wertungDetails Das Array mit den Schwimm-Ergebnissen, gruppiert nach Wertungsklassen.
     * @return array Eindeutige, sortierte Staffel-IDs.
     */
    private function collectStaffelIDs(array $wertungDetails): array {
        $staffelNames = [];
        foreach ($wertungDetails as $wertungsklasse => $details) {
            if (isset($details['Teams'])) {
                foreach ($details['Teams'] as $teamName => $results) {
                    // Füge die Schlüssel (Staffel-Namen) der Team-Ergebnisse hinzu.
                    $staffelNames = array_merge($staffelNames, array_keys($results));
                }
            }
        }
        // Entferne Duplikate und Sonderschlüssel (z.B. 'TotalStaffelScore')
        $staffelNames = array_filter(array_unique($staffelNames), function($key) {
            return $key !== 'TotalStaffelScore';
        });
        sort($staffelNames);
        return $staffelNames;
    }
}
