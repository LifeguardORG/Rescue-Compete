<?php

namespace Model;

/**
 * Hilfsklasse für Parcours-Berechnungen.
 * Zentralisiert die Logik für die Berechnung adjustierter Parcours-Punkte.
 */
class ParcoursCalculator
{
    /**
     * Berechnet adjustierte Parcours-Punkte basierend auf Gewichtung und Konfiguration.
     *
     * @param array $parcoursData Raw parcours data
     * @param array $config Configuration array with WEIGHTS, SHARE_PARCOURS, TOTAL_POINTS
     * @return array Adjusted parcours data with calculated points
     */
    public static function calculateAdjustedPoints(array $parcoursData, array $config): array
    {
        $stationNames = self::collectStationNames($parcoursData);

        // Konfigurationswerte aus der Datenbank verwenden
        $shareParcours = isset($config['SHARE_PARCOURS']) ? (float)$config['SHARE_PARCOURS'] / 100 : 0.5;
        $totalPoints = isset($config['TOTAL_POINTS']) ? (float)$config['TOTAL_POINTS'] : 12000.0;
        $totalParcoursPoints = $totalPoints * $shareParcours;
        $weights = $config['WEIGHTS'] ?? [];

        // DEBUG: Konfigurationswerte ausgeben
        error_log("DEBUG ParcoursCalculator - Config:");
        error_log("  SHARE_PARCOURS from config: " . ($config['SHARE_PARCOURS'] ?? 'not set'));
        error_log("  shareParcours calculated: $shareParcours");
        error_log("  totalPoints: $totalPoints");
        error_log("  totalParcoursPoints: $totalParcoursPoints");

        // Summe aller Gewichte berechnen
        $sumWeights = 0;
        foreach ($stationNames as $stationName) {
            $weight = isset($weights[$stationName]) ? (float)$weights[$stationName] : 100;
            $sumWeights += $weight;
        }

        error_log("  sumWeights: $sumWeights");

        // Für jede Wertung und jedes Team: adjustierte Punkte berechnen
        foreach ($parcoursData as $wertung => &$wertungData) {
            if (isset($wertungData['Teams'])) {
                foreach ($wertungData['Teams'] as $teamName => &$teamData) {
                    foreach ($teamData as $stationName => &$stationEntries) {
                        if ($stationName === 'gesamtpunkte') {
                            continue;
                        }

                        $calculatedPoints = self::calculateStationPoints(
                            $stationEntries,
                            $stationName,
                            $weights,
                            $sumWeights,
                            $totalParcoursPoints
                        );

                        $stationEntries = $calculatedPoints;
                    }
                }
            }
        }

        return $parcoursData;
    }

    /**
     * Berechnet die Punkte für eine einzelne Station.
     *
     * @param mixed $stationEntries Die Einträge für diese Station
     * @param string $stationName Name der Station
     * @param array $weights Gewichtungen
     * @param float $sumWeights Summe aller Gewichte
     * @param float $totalParcoursPoints Gesamt verfügbare Parcours-Punkte
     * @return array Array mit 'original' und 'adjusted' Punkten
     */
    private static function calculateStationPoints(
        $stationEntries,
        string $stationName,
        array $weights,
        float $sumWeights,
        float $totalParcoursPoints
    ): array {
        $sumPoints = 0;
        $sumMaxPoints = 0;

        if (is_array($stationEntries)) {
            foreach ($stationEntries as $entry) {
                if (isset($entry['points']) && isset($entry['maxPoints'])) {
                    $entryPoints = (float)$entry['points'];
                    $entryMaxPoints = (float)$entry['maxPoints'];
                    $sumPoints += $entryPoints;
                    $sumMaxPoints += $entryMaxPoints;
                }
            }
        }

        // Falls kein Maximalwert vorhanden, setze beide Werte auf null
        if ($sumMaxPoints <= 0) {
            return [
                'original' => null,
                'adjusted' => null
            ];
        }

        $originalPoints = (int)$sumPoints;
        $stationWeight = isset($weights[$stationName]) ? (float)$weights[$stationName] : 100;
        $allocatedPoints = ($sumWeights > 0) ? (($stationWeight / $sumWeights) * $totalParcoursPoints) : 0;
        $ratio = $sumPoints / $sumMaxPoints;
        $adjustedPoints = round($ratio * $allocatedPoints);

        return [
            'original' => $originalPoints,
            'adjusted' => $adjustedPoints
        ];
    }

    /**
     * Sammelt alle eindeutigen Station-Namen aus den Parcours-Daten.
     *
     * @param array $parcoursData Die Parcours-Daten
     * @return array Liste der eindeutigen Station-Namen
     */
    private static function collectStationNames(array $parcoursData): array
    {
        $stationNames = [];
        foreach ($parcoursData as $wertung => $data) {
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

    /**
     * Extrahiert die adjustierten Punkte für die Verwendung in CompleteResults.
     *
     * @param array $adjustedParcoursData Adjustierte Parcours-Daten
     * @return array Vereinfachtes Array mit adjustierten Punkten pro Team/Station
     */
    public static function extractAdjustedPointsForComplete(array $adjustedParcoursData): array
    {
        $result = [];

        foreach ($adjustedParcoursData as $wertung => $wertungData) {
            if (isset($wertungData['Teams'])) {
                foreach ($wertungData['Teams'] as $teamName => $teamData) {
                    foreach ($teamData as $stationName => $stationData) {
                        if ($stationName === 'gesamtpunkte') {
                            continue;
                        }

                        if (isset($stationData['adjusted'])) {
                            $result[$wertung][$teamName][$stationName] = (int)$stationData['adjusted'];
                        } else {
                            $result[$wertung][$teamName][$stationName] = null;
                        }
                    }
                }
            }
        }

        return $result;
    }
}