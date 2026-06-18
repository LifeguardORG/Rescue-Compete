-- Migration: Zuordnung von Stationen zu Wertungsklassen
--
-- Hintergrund: Bisher galt implizit „jede Wertung umfasst alle Stationen".
-- Künftig sollen Stationen gezielt einzelnen Wertungen zugeordnet werden;
-- der Parcours-Punktetopf einer Wertung wird nur auf deren zugeordnete
-- Stationen verteilt. Dafür wird eine n:m-Verknüpfungstabelle eingeführt
-- (analog zu WertungStaffel).
--
-- Kein Backfill: Eine Wertung ohne Zuordnung bekommt bewusst keine
-- Parcours-Punkte und keine Stationsspalten. Bestehende Wettkämpfe zeigen
-- erst nach expliziter Zuordnung wieder Parcours-Punkte. Die Ergebniseingabe
-- an den Stationen bleibt davon unberührt (zeigt weiterhin alle Teams).

CREATE TABLE IF NOT EXISTS `WertungStation` (
  `wertung_ID` int(11) NOT NULL,
  `station_ID` int(11) NOT NULL,
  PRIMARY KEY (`wertung_ID`,`station_ID`),
  KEY `station_ID` (`station_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
