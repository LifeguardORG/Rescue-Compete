-- Migration: Wertungs-abhängige Stationsgewichtung
--
-- Hintergrund: Bisher waren Stationsgewichte global pro Station (Tabelle
-- StationWeight). Künftig ist die Gewichtung Wertungs-abhängig: pro Wertung
-- ergeben die zugeordneten Stationen zusammen exakt 100 % des Parcours-Anteils.
-- Dafür bekommt die Zuordnungstabelle WertungStation eine Gewichtsspalte.
--
-- Kein Backfill: weight = 0 bedeutet „unbestimmt". Berechnung und UI fallen
-- dann auf eine Gleichverteilung (equalSplit, Rest vorne aufgefüllt) zurück.
-- Erst nach dem Speichern auf der Gewichtungsseite gelten die gesetzten Werte.
--
-- Idempotent (ADD COLUMN IF NOT EXISTS, MariaDB) — mehrfaches Einspielen schadet nicht.

ALTER TABLE `WertungStation` ADD COLUMN IF NOT EXISTS `weight` int(11) NOT NULL DEFAULT 0;
