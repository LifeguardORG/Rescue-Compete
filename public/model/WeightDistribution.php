<?php

namespace Model;

/**
 * Hilfsklasse für die Verteilung von Stationsgewichten innerhalb einer Wertung.
 *
 * Pro Wertung ergeben die zugeordneten Stationen zusammen exakt 100 % des
 * Parcours-Anteils. Diese Klasse zentralisiert zwei reine Funktionen, die
 * 1:1 auch im JavaScript (StationWeightInputScript.js) implementiert sind,
 * damit Anzeige und Berechnung garantiert dieselben Zahlen liefern.
 *
 * WICHTIG: Die Reihenfolge der Stationen muss überall einheitlich sein
 * (Station.Nr, name), sonst landet der „+1"-Rest der Gleichverteilung an
 * unterschiedlichen Stellen.
 */
class WeightDistribution
{
    /**
     * Gleichverteilung von 100 % auf $n Stationen, ganzzahlig, mit vorne
     * aufgefülltem Rest.
     *
     * Beispiele: equalSplit(5) = [20,20,20,20,20]; equalSplit(3) = [34,33,33].
     *
     * @param int $n Anzahl der Stationen.
     * @return int[] Liste der Prozentwerte (Summe == 100, bzw. [] bei $n <= 0).
     */
    public static function equalSplit(int $n): array
    {
        if ($n <= 0) {
            return [];
        }

        $base = intdiv(100, $n);
        $rest = 100 - $base * $n;

        $result = [];
        for ($i = 0; $i < $n; $i++) {
            $result[] = $i < $rest ? $base + 1 : $base;
        }
        return $result;
    }

    /**
     * Liefert die wirksamen Prozente für eine Stationsliste.
     *
     * Ergeben die gespeicherten Werte exakt 100, werden sie unverändert
     * übernommen (0 ist ein gültiges Gewicht). Andernfalls – z. B. wenn noch
     * nichts gespeichert wurde (alle 0) oder sich der Stationssatz geändert
     * hat – wird auf eine Gleichverteilung zurückgefallen.
     *
     * @param int[] $storedWeightsInOrder Gespeicherte Gewichte in fester Reihenfolge.
     * @return int[] Wirksame Prozente (Summe == 100, sofern $storedWeightsInOrder nicht leer).
     */
    public static function effective(array $storedWeightsInOrder): array
    {
        $n = count($storedWeightsInOrder);
        if ($n === 0) {
            return [];
        }

        $values = array_map('intval', array_values($storedWeightsInOrder));
        if (array_sum($values) === 100) {
            return $values;
        }

        return self::equalSplit($n);
    }
}
