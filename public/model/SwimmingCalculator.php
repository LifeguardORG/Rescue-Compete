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
        $fractionStr = number_format($fraction, 4, '.', '');
        $fractionDigits = substr($fractionStr, 2);
        return sprintf("%02d:%02d:%02d.%s", $hours, $minutes, $secs, $fractionDigits);
    }

    /**
     * Berechnet die Schwimm-Punkte basierend auf Zeiten und Konfiguration.
     */
    public static function calculateSwimmingPoints(array $results, array $config, array $expectedStaffeln): array
    {
        $shareSwimming = isset($config['SHARE_SWIMMING']) ? (float)$config['SHARE_SWIMMING'] : 50.0;
        $totalPoints = isset($config['TOTAL_POINTS']) ? (float)$config['TOTAL_POINTS'] : 12000.0;
        $deductionIntervalMs = isset($config['DEDUCTION_INTERVAL_MS']) ? (float)$config['DEDUCTION_INTERVAL_MS'] : 100.0;
        $pointsDeduction = isset($config['POINTS_DEDUCTION']) ? (float)$config['POINTS_DEDUCTION'] : 1.0;

        $totalSwimmingPoints = $totalPoints * ($shareSwimming / 100);
        $numStaffeln = count($expectedStaffeln);

        if ($numStaffeln <= 0) {
            return $results;
        }

        $staffelMaximalPoints = $totalSwimmingPoints / $numStaffeln;

        // Minimale Zeiten pro Staffel und Wertung ermitteln
        $minTimes = [];
        foreach ($results as $wertung => $wertungData) {
            $minTimes[$wertung] = [];

            foreach ($wertungData['Teams'] as $teamData) {
                foreach ($teamData as $staffelName => $data) {
                    if (is_array($data) && !empty($data[0])) {
                        $swimSeconds = self::timeStringToSeconds($data[0]);
                        $swimMs = (int) floor($swimSeconds * 1000);

                        if (!isset($minTimes[$wertung][$staffelName]) || $swimMs < $minTimes[$wertung][$staffelName]) {
                            $minTimes[$wertung][$staffelName] = $swimMs;
                        }
                    }
                }
            }
        }

        // Punkteberechnung
        foreach ($results as $wertung => &$wertungData) {
            foreach ($wertungData['Teams'] as $team => &$teamData) {
                $totalStaffelScore = 0;

                foreach ($teamData as $staffelName => &$data) {
                    if (!is_array($data) || empty($data[0])) {
                        if (is_array($data)) {
                            $data[3] = null;
                        }
                        continue;
                    }

                    $overallMs = (int)($data[4] ?? 0);
                    $minMs = $minTimes[$wertung][$staffelName] ?? 0;
                    $difference = $overallMs - $minMs;
                    $intervals = floor($difference / $deductionIntervalMs);
                    $staffelPoints = max($staffelMaximalPoints - ($intervals * $pointsDeduction), 0);

                    $data[3] = (int)$staffelPoints;
                    $totalStaffelScore += $data[3];
                }
                $teamData['TotalStaffelScore'] = $totalStaffelScore;
            }
        }

        // Teams nach Gesamtpunkten sortieren
        foreach ($results as $wertung => &$wertungData) {
            uasort($wertungData['Teams'], function($a, $b) {
                $scoreA = $a['TotalStaffelScore'] ?? 0;
                $scoreB = $b['TotalStaffelScore'] ?? 0;
                return $scoreB <=> $scoreA;
            });
        }

        return $results;
    }
}