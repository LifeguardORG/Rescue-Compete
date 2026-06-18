<?php

namespace Controllers;

use Model\ResultModel;
use Model\ResultConfigurationModel;
use Model\ParcoursCalculator;

// ParcoursCalculator einbinden
require_once __DIR__ . '/../model/ParcoursCalculator.php';

/**
 * Controller für Parcours-Ergebnisse.
 */
class ParcoursResultsController {
    private ResultModel $model;
    private ResultConfigurationModel $configModel;

    public function __construct(ResultModel $model, ResultConfigurationModel $configModel) {
        $this->model = $model;
        $this->configModel = $configModel;
    }

    /**
     * Verarbeitet den Request und liefert die Parcours-Ergebnisse.
     */
    public function processRequest(): array {
        // 1. Konfiguration laden (Shares/Gesamtpunkte). Die Gewichte sind jetzt
        //    Wertungs-abhängig und werden direkt im Model ermittelt.
        $config = $this->loadConfig();

        // 2. Adjustierte Parcours-Ergebnisse berechnen
        $wertungDetails = $this->model->getAdjustedParcoursResults($config);

        // 3. Station-Namen sammeln (globale Union – Fallback/Allgemein)
        $stationNames = $this->collectStationNames($wertungDetails);

        // 4. Je Wertung deren zugeordnete Stationen (für die Spaltenköpfe pro Wertung) –
        //    gleiche Quelle wie die Punkteberechnung, damit Spalten und Topf konsistent sind.
        $stationNamesByWertung = $this->model->getStationsByWertungMap();

        // 5. Wertungs-abhängige Stationsgewichte (Prozent, Summe 100) für die Anzeige.
        $weightsByWertung = $this->model->getWeightsByWertungMap();

        return [
            'wertungDetails'        => $wertungDetails,
            'stationIDs'            => $stationNames,
            'stationNamesByWertung' => $stationNamesByWertung,
            'weightsByWertung'      => $weightsByWertung,
        ];
    }

    /**
     * Lädt die Konfiguration mit Fallback-Werten.
     */
    private function loadConfig(): array
    {
        $config = $this->configModel->getConfig();
        $stationNames = $this->configModel->getDbStationNames();

        if (empty($config)) {
            $config = [
                'SHARE_PARCOURS' => 50,
                'TOTAL_POINTS'   => 12000,
                'WEIGHTS'        => array_fill_keys($stationNames, 100)
            ];
            $this->configModel->updateConfig($config);
        } else {
            if (!isset($config['WEIGHTS']) || !is_array($config['WEIGHTS'])) {
                $config['WEIGHTS'] = array_fill_keys($stationNames, 100);
            }
        }

        return $config;
    }

    /**
     * Sammelt Station-Namen aus den Ergebnissen.
     */
    private function collectStationNames(array $wertungDetails): array
    {
        $stationNames = [];
        foreach ($wertungDetails as $wertung => $data) {
            if (isset($data['Teams'])) {
                foreach ($data['Teams'] as $teamName => $results) {
                    foreach (array_keys($results) as $key) {
                        if ($key !== 'gesamtpunkte') {
                            $stationNames[] = $key;
                        }
                    }
                }
            }
        }
        $stationNames = array_unique($stationNames);
        sort($stationNames);
        return $stationNames;
    }
}