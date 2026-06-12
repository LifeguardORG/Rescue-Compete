<?php

namespace Model;

/**
 * Hilfsklasse für Schwimm-Berechnungen.
 * Zentralisiert die Logik für die Berechnung der Schwimm-Punkte.
 */
class SwimmingCalculator
{
    /**
     * Konvertiert einen Zeit-String im Format "hh:mm:ss.nnnn" in Sekunden (float).
     */
    public static function timeStringToSeconds(string $timeStr): float
    {
        $parts = explode(":", $timeStr);
        if (count($parts) < 3) {
            return 0.0;
        }
        $hours = (float)$parts[0];
        $minutes = (float)$parts[1];
        $secParts = explode(".", $parts[2]);
        $seconds = (float)$secParts[0];
        $fraction = isset($secParts[1]) ? ((float)("0." . $secParts[1])) : 0.0;
        return $hours * 3600 + $minutes * 60 + $seconds + $fraction;
    }

    /**
     * Formatiert eine Sekunden-Zahl (float) in das Format "hh:mm:ss.nnnn".
     */
    public static function formatTimeWithMilliseconds(float $seconds): string
    {
        $hours = (int) floor($seconds / 3600);
        $minutes = floor(fmod($seconds, 3600) / 60);
        $secs = floor(fmod($seconds, 60));
        $fraction = $seconds - floor($seconds);
        $fractionStr = number_format($fraction, 4, ".", "");
        $fractionDigits = substr($fractionStr, 2);
        return sprintf("%02d:%02d:%02d.%s", $hours, $minutes, $secs, $fractionDigits);
    }

    /**
     * Berechnet die Schwimm-Punkte basierend auf Zeiten und Konfiguration.
     *
     * @param array $results    Schwimm-Ergebnisse, gruppiert nach Wertung.
     * @param array $config     Berechnungs-Konfiguration.
     * @param array $staffelMap Map: wertungName => string[] (zugeordnete Staffelnamen).
     *                          Der Schwimm-Punktetopf wird PRO WERTUNG durch die Anzahl
     *                          DEREN zugeordneter Staffeln geteilt.
     */
    public static function calculateSwimmingPoints(array $results, array $config, array $staffelMap): array
    {
        $shareSwimming = isset($config["SHARE_SWIMMING"]) ? (float)$config["SHARE_SWIMMING"] : 50.0;
        $totalPoints = isset($config["TOTAL_POINTS"]) ? (float)$config["TOTAL_POINTS"] : 12000.0;
        $deductionIntervalMs = isset($config["DEDUCTION_INTERVAL_MS"]) ? (float)$config["DEDUCTION_INTERVAL_MS"] : 100.0;
        $pointsDeduction = isset($config["POINTS_DEDUCTION"]) ? (float)$config["POINTS_DEDUCTION"] : 1.0;

        $totalSwimmingPoints = $totalPoints * ($shareSwimming / 100);

        // Maximalpunkte pro Staffel je Wertung = Schwimm-Topf / Anzahl der dieser
        // Wertung zugeordneten Staffeln. Wertungen ohne Zuordnung bleiben unberücksichtigt.
        $staffelMaximalPointsByWertung = [];
        foreach ($staffelMap as $wertung => $staffeln) {
            $numStaffeln = count($staffeln);
            if ($numStaffeln > 0) {
                $staffelMaximalPointsByWertung[$wertung] = $totalSwimmingPoints / $numStaffeln;
            }
        }

        // Minimale Zeiten pro Staffel und Wertung ermitteln.
        // WICHTIG: Als Wertungsmaßstab die GESAMTZEIT (Schwimmzeit + Strafzeit, data[4])
        // verwenden – konsistent mit der Punkteberechnung weiter unten. Würde hier die
        // reine Schwimmzeit (data[0]) benutzt, bekäme selbst das schnellste Team keine
        // vollen Punkte, sobald es eine Strafzeit hat (Maßstab-Inkonsistenz).
        $minTimes = [];
        foreach ($results as $wertung => $wertungData) {
            $minTimes[$wertung] = [];

            foreach ($wertungData["Teams"] as $teamData) {
                foreach ($teamData as $staffelName => $data) {
                    // data[0] (Schwimmzeit) dient nur als "Team ist angetreten"-Indikator.
                    if (is_array($data) && !empty($data[0])) {
                        $overallMs = (int)($data[4] ?? 0);

                        // Schutz vor 0-Zeiten (fehlerhaft eingetragene "00:00:00.0000"),
                        // die sonst zur "schnellsten Zeit" werden und die Wertung sabotieren.
                        if ($overallMs <= 0) {
                            continue;
                        }

                        if (!isset($minTimes[$wertung][$staffelName]) || $overallMs < $minTimes[$wertung][$staffelName]) {
                            $minTimes[$wertung][$staffelName] = $overallMs;
                        }
                    }
                }
            }
        }

        // Punkteberechnung
        foreach ($results as $wertung => &$wertungData) {
            // Wertung ohne zugeordnete Staffeln: keine Schwimmpunkte.
            $staffelMaximalPoints = $staffelMaximalPointsByWertung[$wertung] ?? 0;

            foreach ($wertungData["Teams"] as $team => &$teamData) {
                $totalStaffelScore = 0;

                foreach ($teamData as $staffelName => &$data) {
                    if (!is_array($data) || empty($data[0])) {
                        if (is_array($data)) {
                            $data[3] = null;
                        }
                        continue;
                    }

                    $overallMs = (int)($data[4] ?? 0);

                    // Konsistent mit Min-Zeit-Loop: 0-Einträge nicht werten,
                    // sonst lieferte das Team Punkte oberhalb des Maximums.
                    if ($overallMs <= 0 || !isset($minTimes[$wertung][$staffelName])) {
                        $data[3] = null;
                        continue;
                    }

                    $minMs = $minTimes[$wertung][$staffelName];
                    $difference = $overallMs - $minMs;
                    $intervals = floor($difference / $deductionIntervalMs);
                    $staffelPoints = max($staffelMaximalPoints - ($intervals * $pointsDeduction), 0);

                    $data[3] = (int)$staffelPoints;
                    $totalStaffelScore += $data[3];
                }
                $teamData["TotalStaffelScore"] = $totalStaffelScore;
            }
        }

        // Teams nach Gesamtpunkten sortieren
        foreach ($results as $wertung => &$wertungData) {
            uasort($wertungData["Teams"], function($a, $b) {
                $scoreA = $a["TotalStaffelScore"] ?? 0;
                $scoreB = $b["TotalStaffelScore"] ?? 0;
                return $scoreB <=> $scoreA;
            });
        }

        return $results;
    }
}
