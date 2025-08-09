<?php
// Verbindung zur Datenbank über Umgebungsvariablen
$servername = getenv('MYSQL_SERVER') ?: 'localhost';
$username   = getenv('MYSQL_USER') ?: 'webapp';  // Geändert von MYSQL_USERNAME
$password   = getenv('MYSQL_PASSWORD') ?: '05N3tl9MZFAZ';  // Geändert von MYSQL_USERPASS
$dbname     = getenv('MYSQL_DATABASE') ?: 'webappdb';

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password, $options);
    $GLOBALS['conn'] = $conn;
} catch (PDOException $e) {
    echo "Verbindungsfehler: " . $e->getMessage();
    exit;
}