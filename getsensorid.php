<?php
// Konfiguration laden
$config = require __DIR__ . '/../config.php';

$servername = $config['db_host'];
$dbname     = $config['db_name'];
$username   = $config['db_user'];
$password   = $config['db_pass'];

try {
    // PDO Verbindung herstellen
    $dsn = "mysql:host=$servername;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Wirft Fehler bei Problemen
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Ergebnisse direkt als assoziatives Array
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Echte Prepared Statements nutzen
    ];
    
    $pdo = new PDO($dsn, $username, $password, $options);

    // Sensor-ID aus GET holen
    $sensorid = $_GET['sensorid'] ?? "";

    // SQL Query vorbereiten
    $baseSql = "SELECT sensorid, bezeichnung, standort, owner, techtyp, showrownr, showcode, 
                amountrows, delaymin, status, sleep, serveractive, delfromday, code, value1, 
                value2, value3, alertminval1, alertminval1txt, alertmaxval1, alertmaxval1txt, 
                alertminval2, alertminval2txt, alertmaxval2, alertmaxval2txt, alertminval3, 
                alertminval3txt, alertmaxval3, alertmaxval3txt, alertemail1, alertemail2, 
                alertemail3, ts FROM Sensorid";

    if ($sensorid !== "") {
        // Query mit Platzhalter (?) für Sicherheit
        $stmt = $pdo->prepare($baseSql . " WHERE sensorid = ?");
        $stmt->execute([$sensorid]);
    } else {
        // Query für alle Sensoren
        $stmt = $pdo->query($baseSql);
    }

    // Daten abrufen
    $sensor_data = $stmt->fetchAll();

    // Header setzen und JSON ausgeben
    header('Content-type: application/json; charset=utf-8');
    
    // Falls keine Daten gefunden wurden, leeres Array statt null liefern
    echo json_encode(['sensorinfo' => $sensor_data ?: []]);

} catch (PDOException $e) {
    // Im Fehlerfall eine JSON-Fehlermeldung senden
    header('Content-type: application/json', true, 500);
    echo json_encode(['error' => 'Datenbankfehler: ' . $e->getMessage()]);
    exit;
}
?>
