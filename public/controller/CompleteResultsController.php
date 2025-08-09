<?php

namespace Controllers;

use Model\ResultModel;
use Model\ResultConfigurationModel;
use Model\ParcoursCalculator;

// ParcoursCalculator einbinden
require_once __DIR__ . '/../model/ParcoursCalculator.php';

class CompleteResultsController {
    private ResultModel $model;
    private ResultConfigurationModel $configModel;

    public function __construct(ResultModel $model, ResultConfigurationModel $configModel) {
        $this->model = $model;
        $this->configModel = $configModel;
    }

    /**
     * Verarbeitet den Request und kombiniert Schwimm- und Parcours-Ergebnisse.
     */
    public function processRequest(): array {
        // 1. Grunddaten laden
        $swimmingResults = $this->model->getSwimmingWertungenWithDetails();
        $staffelNames = $this->model->getExpectedStaffeln();
        $stationNames = $this->model->getExpectedStations();

        if (empty($staffelNames)) {
            $staffelNames = range(1, 3);
        }

        // 2. Konfiguration laden
        $config = $this->loadConfig();

        // 3. Adjustierte Parcours-Punkte berechnen
        $adjustedParcoursData = $this->model->getAdjustedParcoursResults($config);
        $extractedParcoursPoints = ParcoursCalculator::extractAdjustedPointsForComplete($adjustedParcoursData);

        // 4. Ergebnisse kombinieren
        $combinedResults = $this->combineResults($swimmingResults, $extractedParcoursPoints, $staffelNames, $stationNames);

        return [
            'combinedResults' => $combinedResults,
            'staffelNames'    => $staffelNames,
            'stationNames'    => $stationNames,
            'config'          => $config,
        ];
    }

    /**
     * Lädt die Konfiguration mit Fallback-Werten.
     */
    private function loadConfig(): array
    {
        $config = $this->configModel->getConfig();

        $configDefaults = [
            'SHARE_SWIMMING' => 50,
            'SHARE_PARCOURS' => 50,
            'TOTAL_POINTS'   => 12000,
        ];

        foreach ($configDefaults as $key => $value) {
            if (!isset($config[$key])) {
                $config[$key] = $value;
            }
        }

        return $config;
    }

    /**
     * Kombiniert Schwimm- und Parcours-Ergebnisse.
     */
    private function combineResults(
        array $swimmingResults,
        array $extractedParcoursPoints,
        array $staffelNames,
        array $stationNames
    ): array {
        $combinedResults = [];

        // Schwimm-Ergebnisse verarbeiten
        foreach ($swimmingResults as $wertung => $data) {
            if (!isset($combinedResults[$wertung])) {
                $combinedResults[$wertung] = ['Teams' => []];
            }
            if (isset($data['Teams'])) {
                foreach ($data['Teams'] as $teamName => $teamData) {
                    if (!isset($combinedResults[$wertung]['Teams'][$teamName])) {
                        $combinedResults[$wertung]['Teams'][$teamName] = [
                            'swimming' => [],
                            'parcours' => [],
                            'total'    => 0
                        ];
                    }

                    $swimTotal = 0;

                    // Staffel-Punkte verarbeiten
                    foreach ($staffelNames as $staffelName) {
                        if (isset($teamData[$staffelName])) {
                            $points = isset($teamData[$staffelName][3]) ? (int)$teamData[$staffelName][3] : 0;
                            $combinedResults[$wertung]['Teams'][$teamName]['swimming'][$staffelName] = $points;
                            $swimTotal += $points;
                        } else {
                            $combinedResults[$wertung]['Teams'][$teamName]['swimming'][$staffelName] = null;
                        }
                    }

                    // Gesamtwert bevorzugen wenn vorhanden
                    if (isset($teamData['TotalStaffelScore'])) {
                        $swimTotal = (int)$teamData['TotalStaffelScore'];
                    }
                    $combinedResults[$wertung]['Teams'][$teamName]['total'] += $swimTotal;
                }
            }
        }

        // Parcours-Ergebnisse verarbeiten
        foreach ($extractedParcoursPoints as $wertung => $wertungData) {
            if (!isset($combinedResults[$wertung])) {
                $combinedResults[$wertung] = ['Teams' => []];
            }

            foreach ($wertungData as $teamName => $teamStations) {
                if (!isset($combinedResults[$wertung]['Teams'][$teamName])) {
                    $combinedResults[$wertung]['Teams'][$teamName] = [
                        'swimming' => [],
                        'parcours' => [],
                        'total'    => 0
                    ];
                }

                $parcoursTotal = 0;

                // Station-Punkte verarbeiten
                foreach ($stationNames as $stationName) {
                    if (isset($teamStations[$stationName])) {
                        $adjustedPoints = (int)$teamStations[$stationName];
                        $combinedResults[$wertung]['Teams'][$teamName]['parcours'][$stationName] = $adjustedPoints;
                        $parcoursTotal += $adjustedPoints;
                    } else {
                        $combinedResults[$wertung]['Teams'][$teamName]['parcours'][$stationName] = null;
                    }
                }
                $combinedResults[$wertung]['Teams'][$teamName]['total'] += $parcoursTotal;
            }
        }

        // Teams nach Gesamtpunkten sortieren
        foreach ($combinedResults as $wertung => &$data) {
            if (isset($data['Teams'])) {
                uasort($data['Teams'], function($a, $b) {
                    return $b['total'] <=> $a['total'];
                });
            }
        }
        unset($data);

        return $combinedResults;
    }
}