# RescueCompete

**Digitale Wettkampfsoftware für Wasserwacht-Wettbewerbe**

RescueCompete ist eine speziell entwickelte Web-Anwendung zur digitalen Verwaltung und Auswertung von Wasserwacht-Wettkämpfen. Die Software wurde im Rahmen eines Designprojekts an der Technischen Hochschule Lübeck entwickelt und bereits erfolgreich bei Landes- und Bundeswettbewerben eingesetzt.

## Funktionsumfang

- **Digitale Ergebniseingabe** für Schwimm- und Parcours-Disziplinen
- **Automatische Berechnung** und Auswertung der Wettkampfergebnisse
- **Mannschafts- und Stationsverwaltung** mit flexibler Konfiguration
- **Quiz-System** für Wartepunkte mit Timer-Funktionalität
- **Benutzer- und Rechteverwaltung** mit rollenbasiertem Zugang
- **Responsive Design** für alle Endgeräte
- **QR-Code Integration** für sichere Formular-Links

## Technische Details

### Systemanforderungen
- Webserver mit PHP-Unterstützung
- MariaDB/MySQL Datenbank
- Docker-Umgebung (empfohlen)

### Architektur
- **Backend**: PHP mit MVC-Architektur
- **Frontend**: HTML, CSS, JavaScript
- **Datenbank**: MariaDB mit umfangreichen Views und Stored Procedures
- **Session-Management**: PHP Sessions mit benutzerdefinierten Rollen

### Datenbankstruktur
Die Anwendung nutzt eine komplexe Datenbankstruktur mit folgenden Hauptentitäten:
- Mannschaften und Wertungsklassen
- Stationen und Protokolle
- Staffeln für Schwimmwettbewerbe
- Formular-Kollektionen mit dynamischen Quizfragen
- Benutzer mit rollenbasierter Rechteverwaltung

## Installation

1. Repository klonen
```bash
git clone [repository-url]
cd rescuecompete
```

2. Docker-Umgebung starten
```bash
docker-compose up -d
```

3. Webserver konfigurieren und auf `index.php` verweisen

## Benutzerrollen

### Administrator
- Vollzugriff auf alle Funktionen
- Wettkampf- und Website-Verwaltung
- Benutzerverwaltung

### Wettkampfleitung
- Ergebniseingabe und -verwaltung
- Mannschafts- und Stationsverwaltung
- Auswertungen und Berichte

### Schiedsrichter
- Ergebniseingabe für zugewiesene Stationen
- Zugriff auf relevante Formulare

### Teilnehmer
- Zugang zu Quiz-Formularen
- Einsicht in Wettkampfinformationen

## Entwicklungsteam

**Jonas Richter** - Projektmanager und Entwickler  
Stellvertretender technischer Leiter der Wasserwacht Thüringen

**Sven Meiburg** - Entwickler

**Prof. Dr. Monique Janneck** - Projektbetreuung  
Technische Hochschule Lübeck

## Praxiseinsätze

Die Software wurde bereits erfolgreich eingesetzt bei:
- **Sachsen/Thüringen-Meisterschaften 2025** - Vollständig digitaler Wettkampf
- **Bundesmeisterschaften 2025** - Hybride Lösung mit MS Forms Integration

## Open Source & Verfügbarkeit

RescueCompete wird als **Open-Source-Projekt** veröffentlicht. Jede Wasserwacht-Gliederung kann die Software kostenlos nutzen, selbst hosten und weiterentwickeln.

### Hosted Service
Für Organisationen ohne eigene technische Infrastruktur wird ein gehosteter Service unter **rescue-compete.de** angeboten.

## Support

**Fehler melden oder technische Unterstützung:**  
E-Mail: jonas-richter@email.de

**Projektanfragen und Implementierung:**  
Hilfe bei der Einrichtung von Wettkämpfen und Schulungen sind auf Anfrage kostenlos verfügbar.

## Lizenz

Dieses Projekt wird ohne Gewährleistung bereitgestellt. Die Nutzung erfolgt auf eigene Verantwortung.
Mehr ist in den Nutzungsrichtlinien nachzulesen.

## Haftungsausschluss

Der Support erfolgt im Rahmen verfügbarer Ressourcen. Bei kritischen Fehlern während Wettkämpfen stehen wir nach Möglichkeit zur Verfügung, können aber keine 24/7-Betreuung garantieren.

---

*Entwickelt mit Unterstützung der Technischen Hochschule Lübeck im Rahmen eines Designprojekts für die Wasserwacht des Deutschen Roten Kreuzes.*