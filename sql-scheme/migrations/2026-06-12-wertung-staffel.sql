-- Migration: Zuordnung von Staffeln zu Wertungsklassen
--
-- Hintergrund: Bisher galt implizit „jede Wertung umfasst alle Staffeln".
-- Künftig sollen Staffeln gezielt einzelnen Wertungen zugeordnet werden;
-- der Schwimm-Punktetopf einer Wertung wird nur auf deren zugeordnete
-- Staffeln verteilt. Dafür wird eine n:m-Verknüpfungstabelle eingeführt
-- (analog zu MannschaftWertung).
--
-- Kein Backfill: Eine Wertung ohne Zuordnung bekommt bewusst keine
-- Schwimmpunkte und keine Staffelspalten. Bestehende Wettkämpfe zeigen
-- erst nach expliziter Zuordnung wieder Schwimmpunkte.

CREATE TABLE IF NOT EXISTS `WertungStaffel` (
  `wertung_ID` int(11) NOT NULL,
  `staffel_ID` int(11) NOT NULL,
  PRIMARY KEY (`wertung_ID`,`staffel_ID`),
  KEY `staffel_ID` (`staffel_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
