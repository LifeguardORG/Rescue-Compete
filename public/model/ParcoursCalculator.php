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
     * @param array $parcoursData     Raw parcours data
     * @param array $config           Configuration array with SHARE_PARCOURS, TOTAL_POINTS
     * @param array $weightsByWertung Map: wertungName => (stationName => prozent). Die
     *                                Stationsgewichte sind Wertungs-abhängig und ergeben
     *                                pro Wertung 100 (Fallback Gleichverteilung im Model).
     * @return array Adjusted parcours data with calculated points
     */
    public static function calculateAdjustedPoints(array $parcoursData, array $config, array $weightsByWertung = []): array
    {
        // Konfigurationswerte aus der Datenbank verwenden
        $shareParcours = isset($config['SHARE_PARCOURS']) ? (float)$config['SHARE_PARCOURS'] / 100 : 0.5;
        $totalPoints = isset($config['TOTAL_POINTS']) ? (float)$config['TOTAL_POINTS'] : 12000.0;
        $totalParcoursPoints = $totalPoints * $shareParcours;

        if (getenv('APP_DEBUG')) {
            error_log("DEBUG ParcoursCalculator - Config:");
            error_log("  SHARE_PARCOURS from config: " . ($config['SHARE_PARCOURS'] ?? 'not set'));
            error_log("  shareParcours calculated: $shareParcours");
            error_log("  totalPoints: $totalPoints");
            error_log("  totalParcoursPoints: $totalParcoursPoints");
        }

        // Der Parcours-Topf wird PRO WERTUNG nur auf deren zugeordnete Stationen
        // verteilt. Die Gewichte je Station kommen Wertungs-abhängig aus
        // $weightsByWertung; sumWeights ist deren Summe (= 100 bei gültiger Aufteilung).
        foreach ($parcoursData as $wertung => &$wertungData) {
            if (!isset($wertungData['Teams'])) {
                continue;
            }

            // Wertungs-abhängige Stationsgewichte. Fallback 100 pro vorhandener Station,
            // falls (unerwartet) keine Map übergeben wurde.
            $weights = $weightsByWertung[$wertung] ?? [];

            // Stationsmenge dieser Wertung (Union der Stations-Keys ihrer Teams).
            $wertungStationNames = self::collectWertungStationNames($wertungData['Teams']);

            // Summe der Gewichte nur über die dieser Wertung zugeordneten Stationen.
            $sumWeights = 0;
            foreach ($wertungStationNames as $stationName) {
                $sumWeights += isset($weights[$stationName]) ? (float)$weights[$stationName] : 100;
            }

            if (getenv('APP_DEBUG')) {
                error_log("  Wertung '$wertung' sumWeights: $sumWeights");
            }

            foreach ($wertungData['Teams'] as $teamName => &$teamData) {
                foreach ($teamData as $stationName => &$stationEntries) {
                    if ($stationName === 'gesamtpunkte') {
                        continue;
                    }

                    $stationEntries = self::calculateStationPoints(
                        $stationEntries,
                        $stationName,
                        $weights,
                        $sumWeights,
                        $totalParcoursPoints
                    );
                }
                unset($stationEntries);
            }
            unset($teamData);
        }
        unset($wertungData);

        return $parcoursData;
    }

    /**
     * Sammelt die eindeutigen Station-Namen innerhalb einer einzelnen Wertung.
     *
     * @param array $teams Team-Daten einer Wertung ($wertungData['Teams']).
     * @return array Liste der eindeutigen Station-Namen dieser Wertung.
     */
    private static function collectWertungStationNames(array $teams): array
    {
        $stationNames = [];
        foreach ($teams as $teamName => $results) {
            foreach (array_keys($results) as $key) {
                if ($key !== 'gesamtpunkte') {
                    $stationNames[$key] = true;
                }
            }
        }
        return array_keys($stationNames);
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