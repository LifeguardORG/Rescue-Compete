-- Migration: Snapshot des Zeitlimits auf TeamFormInstance
--
-- Hintergrund: Damit eine Änderung des Zeitlimits einer FormCollection
-- nur für neue Quiz-Starts wirksam wird (und nicht laufende oder bereits
-- abgeschlossene Instanzen rückwirkend beeinflusst), erhält jede
-- TeamFormInstance einen eigenen `timeLimit`-Snapshot, der beim Timer-Start
-- aus der FormCollection kopiert wird.

ALTER TABLE `TeamFormInstance`
  ADD COLUMN `timeLimit` INT NULL DEFAULT NULL
  COMMENT 'Snapshot bei Timer-Start; NULL = nutze FormCollection.timeLimit'
  AFTER `startTime`;

-- Backfill: Für alle bereits gestarteten (laufenden oder abgeschlossenen)
-- Instanzen den aktuellen Collection-Wert als Snapshot übernehmen.
-- Ohne diesen Schritt würden Altinstanzen den NULL-Fallback auf
-- fc.timeLimit nutzen und doch von einer späteren Zeitlimit-Änderung
-- erfasst werden — was die zentrale Anforderung verletzen würde.
UPDATE `TeamFormInstance` tfi
JOIN `FormCollection` fc ON tfi.collection_ID = fc.ID
SET tfi.timeLimit = fc.timeLimit
WHERE tfi.startTime IS NOT NULL AND tfi.timeLimit IS NULL;
