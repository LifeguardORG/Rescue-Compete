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
        // 1. Station-Gewichte aktualisieren
        $this->updateWeightsIfNeeded();

        // 2. Konfiguration laden
        $config = $this->loadConfig();

        // 3. Adjustierte Parcours-Ergebnisse berechnen
        $wertungDetails = $this->model->getAdjustedParcoursResults($config);

        // 4. Station-Namen sammeln
        $stationNames = $this->collectStationNames($wertungDetails);

        return [
            'wertungDetails' => $wertungDetails,
            'stationIDs'     => $stationNames,
            'weights'        => $config['WEIGHTS'] ?? [],
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
     * Aktualisiert Station-Gewichte falls nötig.
     */
    private function updateWeightsIfNeeded(): void
    {
        $stationNames = $this->configModel->getDbStationNames();
        $config = $this->configModel->getConfig();

        if (empty($config)) {
            $config = [
                'SHARE_PARCOURS' => 50,
                'TOTAL_POINTS'   => 12000,
                'WEIGHTS'        => array_fill_keys($stationNames, 100)
            ];
        } else {
            if (!isset($config['WEIGHTS']) || !is_array($config['WEIGHTS'])) {
                $config['WEIGHTS'] = array_fill_keys($stationNames, 100);
            }
        }

        $configStationNames = array_keys($config['WEIGHTS']);
        sort($configStationNames);
        sort($stationNames);

        if ($configStationNames !== $stationNames) {
            $config['WEIGHTS'] = array_fill_keys($stationNames, 100);
            $this->configModel->updateConfig($config);
        }
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