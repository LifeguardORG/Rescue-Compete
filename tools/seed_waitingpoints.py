#!/usr/bin/env python3
"""Generate SQL to seed Waitingpoint questions for THSM 2026.

Source: THSM_2026_Waitingpoint.pdf
Target: webappdb (MariaDB) on rescue-compete server.

Pools (already in DB):
  5  Geschichte                          (15 Q)
  6  Naturschutz                         (8  Q)
  7  Gesundheit und Medizin              (8  Q)
  8  Erste-Hilfe und Sanitaetsdienst     (15 Q)
  9  Wasserwacht und Rettungsschwimmen   (15 Q)
  10 Sport                               (5  Q)

Output: stdout — pipe to file.
"""

# Each entry: (question_text, [(answer_text, is_correct), ...])
# Correct answer per PDF (bold). Geschichte F10 clarified: A) 3-mal.
# Wasserwacht F5 typo "einesVerunglückten" corrected to "eines Verunglückten".

GESCHICHTE = [
    ("In welchem Jahr wurde das „Internationale Komitee der Hilfsgesellschaften für die Verwundetenpflege“ (das heutige IKRK) gegründet?",
     [("1859", False), ("1863", True), ("1864", False), ("1871", False)]),
    ("Welches Ereignis war der direkte Auslöser für Henry Dunant, sein Buch „Eine Erinnerung an Solferino“ zu schreiben?",
     [("Der Krimkrieg", False),
      ("Der Deutsch-Französische Krieg", False),
      ("Der Sardinische Krieg (auch Zweiter Italienischer Unabhängigkeitskrieg genannt)", True),
      ("Der Amerikanische Bürgerkrieg", False)]),
    ("Welches der folgenden Schutzzeichen wurde als letztes offiziell durch die Zusatzprotokolle zu den Genfer Abkommen anerkannt?",
     [("Der Rote Löwe mit Roter Sonne", False),
      ("Der Rote Halbmond", False),
      ("Der Rote Kristall", True),
      ("Der Rote Davidstern", False)]),
    ("Wer war die treibende Kraft hinter der Gründung der „Liga der Rotkreuz-Gesellschaften“ (heute IFRK) im Jahr 1919?",
     [("Henry Dunant", False),
      ("Gustave Moynier", False),
      ("Henry P. Davison", True),
      ("Clara Barton", False)]),
    ("Wie viele Genfer Abkommen bilden heute den Kern des humanitären Völkerrechts?",
     [("Zwei", False), ("Drei", False), ("Vier", True), ("Fünf", False)]),
    ("Welcher Grundsatz der Rotkreuz-Bewegung besagt, dass die Bewegung keine Seite in Konflikten bevorzugen oder an politischen, rassischen, religiösen oder ideologischen Auseinandersetzungen teilnehmen darf?",
     [("Unparteilichkeit", False),
      ("Neutralität", True),
      ("Unabhängigkeit", False),
      ("Freiwilligkeit", False)]),
    ("Das DRK ist die nationale Rotkreuz-Gesellschaft in Deutschland. In welcher Rechtsform ist der DRK-Bundesverband e.V. organisiert?",
     [("Gemeinnützige GmbH", False),
      ("Körperschaft des öffentlichen Rechts", False),
      ("Eingetragener Verein (mit Sonderstellung durch das DRK-Gesetz)", True),
      ("Stiftung bürgerlichen Rechts", False)]),
    ("Welches Organ des DRK ist für die strategische Ausrichtung und die Wahl des Präsidiums zuständig?",
     [("Der Länderrat", False),
      ("Die Bundesversammlung", True),
      ("Der Bundesleitungsausschuss", False),
      ("Der Aufsichtsrat", False)]),
    ("Wo befindet sich der Hauptsitz des Internationalen Komitees vom Roten Kreuz (IKRK)?",
     [("Paris", False), ("Genf", True), ("Brüssel", False), ("Wien", False)]),
    ("Wie oft erhielt die Internationale Rotkreuz-Bewegung (bzw. das IKRK) bisher den Friedensnobelpreis?",
     [("3-mal (1917, 1944 und 1963)", True),
      ("5-mal", False),
      ("0-mal (nur Henry Dunant persönlich)", False),
      ("1-mal", False)]),
    ("Was unterscheidet das IKRK wesentlich von der IFRK (Föderation)?",
     [("Das IKRK arbeitet nur in Friedenszeiten.", False),
      ("Die IFRK ist eine staatliche Organisation, das IKRK privat.", False),
      ("Das IKRK hat ein spezifisches Mandat als neutrale Vermittlerin in bewaffneten Konflikten.", True),
      ("Die IFRK ist nur für Europa zuständig.", False)]),
    ("Wer war die erste Frau im Gründungskomitee des Roten Kreuzes (1863)?",
     [("Florence Nightingale", False),
      ("Clara Barton", False),
      ("Keine (das „Komitee der Fünf“ bestand ausschließlich aus Männern)", True),
      ("Elsa Brändström", False)]),
    ("Welcher Begriff beschreibt die besondere Rolle der Nationalen Gesellschaften als Unterstützer der staatlichen Behörden im humanitären Bereich?",
     [("Subsidiäre Einheit", False),
      ("Auxiliar-Status", True),
      ("Souveräne Partner", False),
      ("Exekutiv-Organe", False)]),
    ("Das DRK-Gesetz regelt die Aufgaben des Roten Kreuzes in Deutschland. Welches Recht wird dem DRK darin unter anderem zugesprochen?",
     [("Das Recht zur Steuererhebung", False),
      ("Die Verwendung des Schutzzeichens (Rotes Kreuz auf weißem Grund)", True),
      ("Die alleinige Durchführung des Rettungsdienstes", False),
      ("Die Leitung des Katastrophenschutzes des Bundes", False)]),
    ("Wie nennt man die Gesamtheit aller nationalen Gesellschaften, des IKRK und der IFRK?",
     [("Weltweites Rotes Kreuz", False),
      ("Internationale Rotkreuz- und Rothalbmond-Bewegung", True),
      ("Globaler Humanitärer Verbund", False),
      ("Union der Genfer Konventionen", False)]),
]

NATURSCHUTZ = [
    ("Was passiert primär bei der sogenannten „Eutrophierung“ eines Sees?",
     [("Das Wasser wird durch zu viel Kalk zu hart.", False),
      ("Durch Überdüngung (Nitrate/Phosphate) wachsen zu viele Algen, die beim Absterben dem Wasser Sauerstoff entziehen.", True),
      ("Der See trocknet aufgrund steigender Temperaturen langsam aus.", False),
      ("Fische vermehren sich unkontrolliert und fressen alle Wasserpflanzen auf.", False)]),
    ("Welcher pH-Wert gilt für die meisten heimischen Fischarten (wie Forelle oder Karpfen) als idealer Lebensbereich?",
     [("Stark sauer (pH 3,0 bis 4,5)", False),
      ("Extrem alkalisch (pH 9,5 bis 11,0)", False),
      ("Neutral bis schwach basisch (pH 6,5 bis 8,5)", True),
      ("Der pH-Wert spielt für Fische keine Rolle, solange genug Nahrung da ist.", False)]),
    ("Warum ist „Totholz“ (umgestürzte Bäume) in und an Gewässern ein wichtiger Bestandteil des Naturschutzes?",
     [("Es dient als Brennstoffreserve für Wanderer.", False),
      ("Es bietet Versteckmöglichkeiten für Fische und ist Lebensraum für Insektenlarven.", True),
      ("Es verhindert, dass das Wasser zu schnell fließt und verdunstet.", False),
      ("Es filtert Mikroplastik aus dem fließenden Wasser.", False)]),
    ("Wie wirkt sich eine dauerhafte Erhöhung der Wassertemperatur auf den Sauerstoffgehalt eines Flusses aus?",
     [("Warmes Wasser kann mehr Sauerstoff speichern als kaltes Wasser.", False),
      ("Warmes Wasser kann weniger gelösten Sauerstoff speichern (Löslichkeit sinkt).", True),
      ("Die Temperatur hat keinen Einfluss auf den Sauerstoff, nur auf die Fließgeschwindigkeit.", False),
      ("Nur bei Frost sinkt der Sauerstoffgehalt auf Null.", False)]),
    ("Was ist das Ziel einer sogenannten „Renaturierung“ eines Bachlaufs?",
     [("Den Bach in ein betoniertes Bett zu legen, um Hochwasser schneller abzuleiten.", False),
      ("Den Bachlauf zu begradigen, damit Schiffe besser fahren können.", False),
      ("Den Bach in seinen ursprünglichen, kurvigen Zustand zurückzuführen und Uferzonen zu beleben.", True),
      ("Den Bach komplett unter die Erde zu verlegen (Verrohrung).", False)]),
    ("Welche Fischart gilt als „Indikatorart“ für besonders sauberes, sauerstoffreiches und kühles Wasser?",
     [("Der Spiegelkarpfen", False),
      ("Die Bachforelle", True),
      ("Der Wels", False),
      ("Der Goldfisch", False)]),
    ("Was versteht man unter der „Durchgängigkeit“ eines Gewässers?",
     [("Dass das Wasser klar genug ist, um bis zum Grund zu sehen.", False),
      ("Dass Boote ohne Hindernisse von der Quelle bis zur Mündung fahren können.", False),
      ("Dass Fische und Kleinstlebewesen Barrieren (wie Wehre) durch Fischtreppen o.ä. überwinden können.", True),
      ("Dass Regenwasser ungefiltert in den Fluss geleitet wird.", False)]),
    ("Welchen Einfluss haben Waschmittelrückstände (Phosphate) in Gewässern, wenn sie nicht ausreichend geklärt werden?",
     [("Sie machen das Wasser so weich, dass Fische nicht mehr schwimmen können.", False),
      ("Sie färben das Wasser dauerhaft weiß.", False),
      ("Sie wirken als Dünger und beschleunigen das Algenwachstum massiv.", True),
      ("Sie verhindern die Eisbildung im Winter.", False)]),
]

GESUNDHEIT = [
    ("Welche anatomische Struktur verhindert, dass Nahrung während des Schluckvorgangs in die Luftröhre gelangt?",
     [("Das Gaumensegel", False),
      ("Der Kehldeckel (Epiglottis)", True),
      ("Die Speiseröhre", False),
      ("Die Stimmbänder", False)]),
    ("In welcher Herzkammer beginnt der Körperkreislauf (großer Kreislauf), um das Blut in die Aorta zu pumpen?",
     [("Rechter Vorhof", False),
      ("Rechte Herzkammer", False),
      ("Linker Vorhof", False),
      ("Linke Herzkammer", True)]),
    ("Wo findet der eigentliche Gasaustausch in der Lunge statt?",
     [("In der Luftröhre (Trachea)", False),
      ("In den Bronchien", False),
      ("In den Lungenbläschen (Alveolen)", True),
      ("Im Zwerchfell", False)]),
    ("Welches Blutgefäß bildet eine Ausnahme und führt (obwohl es eine Vene ist) sauerstoffreiches Blut?",
     [("Die Pfortader", False),
      ("Die Lungenvene", True),
      ("Die Hohlvene", False),
      ("Die Drosselvene", False)]),
    ("Wo im menschlichen Körper befindet sich der kleinste Knochen, der sogenannte „Steigbügel“?",
     [("In der Nasenwurzel", False),
      ("Im Handgelenk", False),
      ("Im Mittelohr", True),
      ("Im Sprunggelenk", False)]),
    ("Welches Organ fungiert sowohl als exokrine Drüse (Verdauungssaft) als auch als endokrine Drüse (Hormone wie Insulin)?",
     [("Die Leber", False),
      ("Die Milz", False),
      ("Die Bauchspeicheldrüse (Pankreas)", True),
      ("Die Gallenblase", False)]),
    ("Wie heißt die oberste Schicht der Haut, die als eigentliche Schutzbarriere dient?",
     [("Epidermis (Oberhaut)", True),
      ("Dermis (Lederhaut)", False),
      ("Subkutis (Unterhaut)", False),
      ("Faszien", False)]),
    ("Welcher Teil des Gehirns ist primär für die Koordination von Bewegungen und das Gleichgewicht zuständig?",
     [("Das Großhirn", False),
      ("Das Kleinhirn (Cerebellum)", True),
      ("Der Balken", False),
      ("Die Hypophyse", False)]),
]

ERSTE_HILFE = [
    ("Welches Leitsymptom unterscheidet einen Spannungspneumothorax primär von einem einfachen Pneumothorax?",
     [("Plötzlich auftretende Atemnot", False),
      ("Stechender Schmerz in der Brust", False),
      ("Obere Einflussstauung (gestaute Halsvenen) und Schockzeichen", True),
      ("Hustenreiz mit blutigem Auswurf", False)]),
    ("Im Rahmen des ABCDE-Schemas steht das „D“ für „Disability“. Was wird hierbei primär mit der GCS (Glasgow Coma Scale) überprüft?",
     [("Die Durchblutung der Extremitäten", False),
      ("Der neurologische Status (Augenöffnung, verbale und motorische Reaktion)", True),
      ("Die Dehydration des Patienten", False),
      ("Die Druckdolenz des Abdomens", False)]),
    ("Bei einer Reanimation wird ein AED eingesetzt. Welche der folgenden Herzrhythmusstörungen ist eine klassische Indikation für eine Defibrillation?",
     [("Asystolie (Nulllinie)", False),
      ("Pulslose ventrikuläre Tachykardie (pVT)", True),
      ("Pulslose elektrische Aktivität (PEA)", False),
      ("Sinusrhythmus bei 60 Schlägen/Minute", False)]),
    ("Was versteht man unter dem Begriff „Volumensubstitutionstherapie“ bei einem hämorrhagischen Schock?",
     [("Die Gabe von CO2 über eine Maske", False),
      ("Das Absaugen von Blut aus der Lunge", False),
      ("Der intravenöse Ersatz von Flüssigkeitsverlusten (z. B. mit isotonischen Elektrolytlösungen)", True),
      ("Die Gabe von blutdrucksenkenden Medikamenten", False)]),
    ("Bei einem Massenanfall von Verletzten (MANV) erfolgt die Sichtung (Triage). Welche Farbe erhält ein Patient, der unmittelbar lebensgefährlich verletzt ist und sofortige Behandlung benötigt?",
     [("Blau", False), ("Rot", True), ("Gelb", False), ("Grün", False)]),
    ("Welches Medikament (oft als Spray) wird im Sanitätsdienst häufig bei einem akuten Koronarsyndrom (Herzinfarkt-Verdacht) zur Vorlastsenkung eingesetzt, sofern der Blutdruck stabil ist?",
     [("Adrenalin", False),
      ("Glyceroltrinitat (Nitro-Spray)", True),
      ("Diazepam", False),
      ("Heparin", False)]),
    ("Ein Patient weist ein „Schildkrötenphänomen“ (Einziehen des Kopfes) und eine paradoxe Atmung auf. Worauf deutet dies hin?",
     [("Schlaganfall", False),
      ("Akutes Abdomen", False),
      ("Schwere respiratorische Insuffizienz / Verlegung der Atemwege", True),
      ("Beckenfraktur", False)]),
    ("Was ist das erste, was man beim Auffinden einer leblosen Person tun sollte?",
     [("Den Puls am Handgelenk suchen.", False),
      ("Die Unfallstelle absichern und die Person ansprechen/anfassen (Bewusstsein prüfen).", True),
      ("Sofort mit der Beatmung beginnen.", False),
      ("Die Person in die stabile Seitenlage bringen.", False)]),
    ("Wie tief sollte das Brustbein bei einem Erwachsenen während der Herzdruckmassage eingedrückt werden?",
     [("1 bis 2 cm", False),
      ("Mindestens 10 cm", False),
      ("Etwa 5 bis 6 cm", True),
      ("Gar nicht, nur sanftes Drücken reicht.", False)]),
    ("Worauf deutet eine „schmerzlose“ blasse Haut, kalter Schweiß und ein schneller, kaum tastbarer Puls hin?",
     [("Einen Sonnenstich", False),
      ("Einen Schockzustand (z. B. durch Blutverlust)", True),
      ("Einen epileptischen Anfall", False),
      ("Eine leichte Unterzuckerung", False)]),
    ("In welche Position bringt man einen Patienten mit Atemnot (z. B. bei Asthma oder Herzproblemen)?",
     [("Flach auf den Rücken, Beine hoch.", False),
      ("Oberkörper hochlagern (z.B. sitzende Position).", True),
      ("Stabile Seitenlage.", False),
      ("Flach mit erhöhten Oberkörper", False)]),
    ("Wie erkennt man einen Schlaganfall mit dem „FAST“-Test? Wofür steht das „S“?",
     [("Schwindel (Check, ob der Patient schwankt)", False),
      ("Speech / Sprache (Prüfen, ob der Patient lallt oder Sätze nicht nachsprechen kann)", True),
      ("Schmerz (Prüfen, ob der Kopf wehtut)", False),
      ("Sicht (Prüfen, ob der Patient Doppelbilder sieht)", False)]),
    ("Was sollte man tun, wenn sich eine Person an Essen verschluckt hat und nicht mehr husten oder atmen kann?",
     [("Viel Wasser zu trinken geben.", False),
      ("5 Schläge auf den Rücken, bei Erfolglosigkeit den Heimlich-Handgriff (Oberbauchkompression).", True),
      ("Die Person kräftig schütteln.", False),
      ("Sofort mit der Wiederbelebung beginnen, solange sie noch wach ist.", False)]),
    ("Welche Maßnahme ist bei einer Nasenblutung richtig?",
     [("Den Kopf weit in den Nacken legen.", False),
      ("Den Kopf leicht nach vorne beugen und eine kalte Kompresse in den Nacken legen.", True),
      ("Die Nase mit Watte fest zustopfen.", False),
      ("Den Patienten flach auf den Boden legen.", False)]),
    ("Worauf muss man bei der stabilen Seitenlage besonders achten?",
     [("Dass die Beine überkreut liegen.", False),
      ("Dass der Mund der tiefste Punkt ist und der Hals überstreckt wird (Atemwege frei).", True),
      ("Dass der Patient eine Decke über dem Kopf hat.", False),
      ("Dass der Patient Sicher auf der Decke liegt.", False)]),
]

WASSERWACHT = [
    ("Was versteht man beim Ertrinkungsvorgang unter dem „Laryngospasmus“?",
     [("Eine Lähmung der Atemmuskulatur durch Kälte.", False),
      ("Ein reflexartiger Verschluss der Stimmritzen, der das Eindringen von Wasser in die Lunge verhindert (trockenes Ertrinken).", True),
      ("Ein unkontrolliertes Zucken der Speiseröhre.", False),
      ("Das Platzen der Lungenbläschen durch hohen Druck.", False)]),
    ("Welches physikalische Gesetz erklärt, warum die Lunge eines Tauchers beim Aufstieg ohne Ausatmen reißen kann?",
     [("Das Archimedische Prinzip", False),
      ("Das Gesetz von Boyle-Mariotte (Druck-Volumen-Gesetz)", True),
      ("Das Dalton-Gesetz", False),
      ("Die Newtonsche Mechanik", False)]),
    ("Wo schwimmt man mit dem größten Kraftaufwand gegen den Strom?",
     [("In Ufernähe auf der Innenseite einer Kurve.", False),
      ("In der Nähe des Ufers.", False),
      ("In der Mitte des Stromes.", True)]),
    ("Was ist das Hauptrisiko beim sogenannten „Hyperventilieren“ vor dem Streckentauchen?",
     [("Man bekommt einen Krampf im Fuß.", False),
      ("Das Hinauszögern des Atemreizes bei gleichzeitig sinkendem Sauerstoffgehalt (Gefahr des Schwimmbad-Blackouts).", True),
      ("Das Wasser wird in der Lunge zu heiß.", False),
      ("Die Ohren fangen an zu bluten.", False)]),
    ("Welche Stelle eines Ruderbootes mit Spiegelheck eignet sich am besten zur Übernahme eines Verunglückten ins Boot?",
     [("Die Steuerbordseite.", False),
      ("Die Backbordseite.", False),
      ("Das Heck des Bootes.", True),
      ("Der Bug des Boots.", False)]),
    ("Wie reagiert der menschliche Körper beim „Tauchreflex“, wenn das Gesicht mit kaltem Wasser in Berührung kommt?",
     [("Die Herzfrequenz sinkt (Bradykardie) und die Blutgefäße in den Extremitäten verengen sich (Zentralisation).", True),
      ("Der Puls rast und die Haut wird rot.", False),
      ("Man bekommt sofort einen unkontrollierbaren Hustenreiz.", False),
      ("Die Verdauung wird massiv angeregt.", False)]),
    ("Bei einer Unterkühlung (Hypothermie) im Stadium II (Abwehrphase, 34–30 °C) zittert der Patient nicht mehr. Warum ist aktives Aufwärmen der Extremitäten hier gefährlich?",
     [("Weil die Haut verbrennen könnte.", False),
      ("Gefahr des „Afterdrop“ (Rückstrom von kaltem Schalenblut zum warmen Kern führt zum Herzstillstand).", True),
      ("Weil der Patient dann zu viel Durst bekommt.", False),
      ("Weil das Zittern lebensnotwendig ist.", False)]),
    ("Was ist die Hauptaufgabe eines „Leinenführers“ bei einem Einsatz mit Rettungstauchern?",
     [("Er hält die Ausrüstung sauber.", False),
      ("Er führt die Kommunikation über Signale und überwacht die Sicherheit und Position des Tauchers.", True),
      ("Er entscheidet, welche Fische gefangen werden.", False),
      ("Er steuert das Motorrettungsboot.", False)]),
    ("Die Hauptgefahrenzone bei einem Wehr",
     [("liegt hauptsächlich vor dem Wehr", False),
      ("ist nicht vorhanden", False),
      ("liegt hinter dem Wehr (Wasserwalzen, einschließlich schwimmfähiger Gegenstände)", True)]),
    ("Was bedeutet die Flagge „Gelb-Rot“ (halbiert) an einem Wasserrettungsturm?",
     [("Bewachter Strandabschnitt / Wasserrettung im Dienst.", True),
      ("Badeverbot wegen Sturm.", False),
      ("Tauchereinsatz im Bereich.", False),
      ("Bootsrennen findet statt.", False)]),
    ("Wie lautet die Faustregel für den Druckausgleich beim Tieftauchen?",
     [("Nur einmal ganz unten ausgleichen.", False),
      ("Kontinuierlich und rechtzeitig schon auf den ersten Metern druckausgleichen.", True),
      ("Man sollte den Druckausgleich durch kräftiges Schnäuzen erzwingen.", False),
      ("Im Süßwasser ist kein Druckausgleich nötig.", False)]),
    ("Bei der Herz-Lungen-Wiederbelebung eines Ertrunkenen gibt es eine Besonderheit im Vergleich zum normalen Herzstillstand. Welche?",
     [("Man darf nur drücken, nicht beatmen.", False),
      ("Man beginnt mit 5 Initialbeatmungen, da Sauerstoffmangel die primäre Ursache ist.", True),
      ("Man muss den Patienten erst auf den Kopf stellen, damit das Wasser rausläuft.", False),
      ("Man darf keinen Defibrillator (AED) benutzen.", False)]),
    ("Was versteht man in der Wasserwacht unter einem „Fließwasserretter“ (Swiftwater Rescue)?",
     [("Ein Rettungsschwimmer, der besonders schnell schwimmt.", False),
      ("Eine spezialisierte Kraft für Rettung aus stark strömenden Gewässern, Hochwasser und Wildwasser.", True),
      ("Jemand, der im Schwimmbad die Rutschen bewacht.", False),
      ("Ein Techniker, der die Wasserqualität misst.", False)]),
    ("Welche Gefahr besteht beim Springen in unbekanntes, trübes Wasser („Kopfsprung“)?",
     [("Die Badehose könnte verloren gehen.", False),
      ("Schwere Halswirbelsäulenverletzungen durch Aufprall auf Hindernisse oder den Grund.", True),
      ("Das Wasser könnte zu sauer sein.", False),
      ("Man verliert die Orientierung, wo oben und unten ist.", False)]),
    ("Was ist die „Zehner-Regel“ (oder 10:10-Regel) beim Rettungswachdienst?",
     [("Ein Bereich sollte in 10 Sekunden gescannt werden und eine Hilfeleistung in 10 Minuten abgeschlossen sein.", True),
      ("Man darf nur 10 Minuten in der Sonne sitzen.", False),
      ("Pro 10 Schwimmer wird ein Rettungsschwimmer benötigt.", False),
      ("Nach 10 Stunden Dienst hat man 10 Stunden frei.", False)]),
]

SPORT = [
    ("Welcher physikalische Effekt sorgt beim Schwimmen für den Auftrieb des Körpers?",
     [("Bernoulli-Effekt", False),
      ("Magnus-Effekt", False),
      ("Archimedisches Prinzip", True),
      ("Venturi-Effekt", False)]),
    ("Wie wird die Trainingsmethode genannt, bei der sich intensive Belastungsphasen mit kurzen Pausen abwechseln?",
     [("Dauermethode", False),
      ("Intervallmethode", True),
      ("Wettkampfmethode", False),
      ("Regenerationsmethode", False)]),
    ("Was ist die maximale Anzahl an Delphin-Beinschlägen, die nach dem Start und jeder Wende unter Wasser beim Kraulen erlaubt sind?",
     [("Maximal 5 Schläge", False),
      ("Es gibt keine Begrenzung der Anzahl", False),
      ("Unbegrenzt, solange die 15-Meter-Marke nicht überschritten wird", True),
      ("Genau 2 Schläge", False)]),
    ("Welches Vitamin ist besonders wichtig für den Knochenstoffwechsel und wird durch Sonnenlicht synthetisiert?",
     [("Vitamin C", False),
      ("Vitamin B12", False),
      ("Vitamin D", True),
      ("Vitamin K", False)]),
    ("Wie nennt man die Zunahme des Muskelquerschnitts infolge von Krafttraining?",
     [("Atrophie", False),
      ("Hypertrophie", True),
      ("Hyperplasie", False),
      ("Kapillarisierung", False)]),
]

POOLS = [
    (5,  "Geschichte",                        GESCHICHTE,  15),
    (6,  "Naturschutz",                       NATURSCHUTZ, 8),
    (7,  "Gesundheit und Medizin",            GESUNDHEIT,  8),
    (8,  "Erste-Hilfe und Sanitätsdienst",    ERSTE_HILFE, 15),
    (9,  "Wasserwacht und Rettungsschwimmen", WASSERWACHT, 15),
    (10, "Sport",                             SPORT,       5),
]


def sql_escape(s: str) -> str:
    # Single quote escape for MariaDB. Backslashes also need doubling.
    return s.replace("\\", "\\\\").replace("'", "''")


def main() -> None:
    out = []
    out.append("-- THSM 2026 Waitingpoints — generated, do not edit by hand")
    out.append("SET NAMES utf8mb4;")
    out.append("START TRANSACTION;")
    out.append("")

    total_q = 0
    total_a = 0
    for pool_id, pool_name, questions, expected_count in POOLS:
        assert len(questions) == expected_count, \
            f"{pool_name}: expected {expected_count}, got {len(questions)}"
        out.append(f"-- Pool {pool_id}: {pool_name} ({len(questions)} Fragen)")
        for q_text, answers in questions:
            assert len(answers) >= 2, f"too few answers: {q_text!r}"
            n_correct = sum(1 for _, c in answers if c)
            assert n_correct == 1, f"need exactly 1 correct in: {q_text!r} (got {n_correct})"
            out.append(
                f"INSERT INTO Question (QuestionPool_ID, Text) "
                f"VALUES ({pool_id}, '{sql_escape(q_text)}');"
            )
            out.append("SET @qid = LAST_INSERT_ID();")
            values = ",\n  ".join(
                f"(@qid, '{sql_escape(a_text)}', {1 if is_corr else 0})"
                for a_text, is_corr in answers
            )
            out.append(f"INSERT INTO Answer (Question_ID, Text, IsCorrect) VALUES\n  {values};")
            out.append("")
            total_q += 1
            total_a += len(answers)

    out.append("COMMIT;")
    out.append(f"-- Summary: {total_q} questions, {total_a} answers inserted.")
    print("\n".join(out))


if __name__ == "__main__":
    main()
