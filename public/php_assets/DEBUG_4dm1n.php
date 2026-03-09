<?php
/**
 * Skript zum Zurücksetzen des Admin-Passworts
 *
 * SICHERHEITSHINWEIS: Lösche dieses Skript nach dem Ausführen!
 */

// Fehlerberichterstattung für Entwicklung
ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set('display_startup_errors', 1);

// Datenbankverbindung herstellen
require_once '../db/DbConnection.php';

// Prüfe, ob die Datenbankverbindung erfolgreich war
if (!isset($conn) || !($conn instanceof PDO)) {
    die("Fehler: Datenbankverbindung konnte nicht hergestellt werden.");
}

// Benutzername für den Admin-Account
$username = "admin";
$newPassword = "admin";
$accountType = "Wettkampfleitung"; // Account-Typ auf "Wettkampfleitung" gesetzt

// Passwort mit dem gleichen Salt und Algorithmus hashen, der in der LoginManager.php verwendet wird
$salt = "Zehn zahme Ziegen zogen zehn Zentner Zucker zum Zoo";
$algo = "md5";
$passwordHash = hash_hmac($algo, $newPassword, $salt);

// Ausgabe für Debugging
echo "<h2>Admin-Passwort zurücksetzen</h2>";

try {
    // Direktes SQL-Statement verwenden, um Probleme mit der updateUser-Methode zu umgehen
    $stmt = $conn->prepare("SELECT ID FROM User WHERE username = :username LIMIT 1");
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $userId = $user['ID'];

        // Passwort und Account-Typ direkt in der Datenbank aktualisieren
        $updateStmt = $conn->prepare("UPDATE User SET passwordHash = :passwordHash, acc_typ = :accountType WHERE ID = :id");
        $updateStmt->bindParam(':passwordHash', $passwordHash, PDO::PARAM_STR);
        $updateStmt->bindParam(':accountType', $accountType, PDO::PARAM_STR);
        $updateStmt->bindParam(':id', $userId, PDO::PARAM_INT);

        if ($updateStmt->execute()) {
            echo "<p style='color:green;'>Das Passwort für den Benutzer <strong>$username</strong> wurde erfolgreich auf <strong>$newPassword</strong> zurückgesetzt!</p>";
            echo "<p style='color:green;'>Der Account-Typ wurde auf <strong>$accountType</strong> gesetzt.</p>";

            // Zeige die Anmeldedaten an
            echo "<p>Du kannst dich jetzt mit folgenden Daten anmelden:</p>";
            echo "<ul>";
            echo "<li>Benutzername: <strong>$username</strong></li>";
            echo "<li>Passwort: <strong>$newPassword</strong></li>";
            echo "<li>Account-Typ: <strong>$accountType</strong></li>";
            echo "</ul>";

            echo "<p><a href='../view/Login.php'>Zum Login</a></p>";
            echo "<p><strong>WICHTIG:</strong> Lösche diese Datei nach dem Zurücksetzen des Passworts aus Sicherheitsgründen!</p>";
        } else {
            echo "<p style='color:red;'>Fehler beim Aktualisieren des Passworts. SQL-Fehler: " . $updateStmt->errorInfo()[2] . "</p>";

            // Debug-Informationen anzeigen
            echo "<h3>Debug-Informationen:</h3>";
            echo "<pre>";
            echo "User-ID: " . $userId . "\n";
            echo "Passwort-Hash: " . $passwordHash . "\n";
            echo "Account-Typ: " . $accountType . "\n";
            echo "SQL-Statement: UPDATE User SET passwordHash = '$passwordHash', acc_typ = '$accountType' WHERE ID = $userId";
            echo "</pre>";
        }
    } else {
        echo "<p style='color:red;'>Der Benutzer <strong>$username</strong> existiert nicht in der Datenbank.</p>";

        echo "<h3>Neuen Admin-Account erstellen?</h3>";
        echo "<form method='post'>";
        echo "<input type='hidden' name='create_admin' value='1'>";
        echo "<button type='submit'>Ja, neuen Admin-Account erstellen</button>";
        echo "</form>";

        if (isset($_POST['create_admin'])) {
            $insertStmt = $conn->prepare("INSERT INTO User (username, passwordHash, acc_typ) VALUES (:username, :passwordHash, :accountType)");
            $insertStmt->bindParam(':username', $username, PDO::PARAM_STR);
            $insertStmt->bindParam(':passwordHash', $passwordHash, PDO::PARAM_STR);
            $insertStmt->bindParam(':accountType', $accountType, PDO::PARAM_STR);

            if ($insertStmt->execute()) {
                $newUserId = $conn->lastInsertId();
                echo "<p style='color:green;'>Neuer Admin-Account mit der ID <strong>$newUserId</strong> erfolgreich erstellt!</p>";
                echo "<p>Du kannst dich jetzt mit folgenden Daten anmelden:</p>";
                echo "<ul>";
                echo "<li>Benutzername: <strong>$username</strong></li>";
                echo "<li>Passwort: <strong>$newPassword</strong></li>";
                echo "<li>Account-Typ: <strong>$accountType</strong></li>";
                echo "</ul>";
                echo "<p><a href='../view/Login.php'>Zum Login</a></p>";
            } else {
                echo "<p style='color:red;'>Fehler beim Erstellen des Admin-Accounts. SQL-Fehler: " . $insertStmt->errorInfo()[2] . "</p>";
            }
        }
    }
} catch (PDOException $e) {
    echo "<p style='color:red;'>Datenbankfehler: " . $e->getMessage() . "</p>";

    // Hilfreiche Hinweise für häufige Probleme
    echo "<h3>Häufige Probleme:</h3>";
    echo "<ul>";
    echo "<li>Überprüfe, ob die Tabelle 'User' die korrekte Struktur hat (Spalte 'passwordHash' und 'acc_typ').</li>";
    echo "<li>Überprüfe, ob dein Datenbankbenutzer Schreibrechte hat.</li>";
    echo "<li>Stelle sicher, dass der Admin-Benutzer existiert (SELECT * FROM User WHERE username = 'admin').</li>";
    echo "</ul>";
}

// Dump der User-Tabellen-Struktur für Debug-Zwecke, wenn das Problem weiter besteht
if (isset($_GET['show_structure'])) {
    try {
        echo "<h3>Struktur der User-Tabelle:</h3>";
        $tableStmt = $conn->query("DESCRIBE User");
        echo "<table border='1'>";
        echo "<tr><th>Feld</th><th>Typ</th><th>Null</th><th>Schlüssel</th><th>Standard</th><th>Extra</th></tr>";
        while ($row = $tableStmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            foreach ($row as $key => $value) {
                echo "<td>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";

        echo "<h3>Vorhandene Benutzer:</h3>";
        $usersStmt = $conn->query("SELECT ID, username, acc_typ FROM User LIMIT 10");
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Benutzername</th><th>Account-Typ</th></tr>";
        while ($row = $usersStmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['ID']) . "</td>";
            echo "<td>" . htmlspecialchars($row['username']) . "</td>";
            echo "<td>" . htmlspecialchars($row['acc_typ']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } catch (PDOException $e) {
        echo "<p style='color:red;'>Fehler beim Abrufen der Tabellenstruktur: " . $e->getMessage() . "</p>";
    }
}

echo "<p><a href='?show_structure=1'>Tabellenstruktur anzeigen</a> (für Debug-Zwecke)</p>";
