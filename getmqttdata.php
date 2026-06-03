<?php
// Header für JSON-Ausgabe setzen
header('Content-Type: application/json; charset=utf-8');

// Konfiguration einlesen
$config = require __DIR__ . '/../config.php';

$host    = $config['db_host'];
$db      = $config['db_name'];
$user    = $config['db_user'];
$pass    = $config['db_pass'];
$api_key_value = $config['api_key'];
$charset = 'utf8mb4';

// API-Key aus GET-Parameter einlesen
$api_key = $_GET['api_key'] ?? '';

// 1. API-Key Prüfung
if (empty($api_key) || $api_key !== $api_key_value) {
    http_response_code(401);
    echo json_encode(['error' => 'Ungültiger oder fehlender API-Key.']);
    exit;
}

// 2. Erforderliche Parameter prüfen
$sensorid = $_GET['sensorid'] ?? '';
if (empty($sensorid)) {
    http_response_code(400);
    echo json_encode(['error' => 'Der Parameter "sensorid" ist erforderlich.']);
    exit;
}

// Optionaler Parameter
$variant = $_GET['variant'] ?? null;

// 3. Datenbankverbindung herstellen (PDO)
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // 4. SQL-Abfrage dynamisch aufbauen
    $sql = "SELECT * FROM mqttconn WHERE sensorid = :sensorid";
    $params = ['sensorid' => $sensorid];
    
    // Falls 'variant' mitgeliefert wurde, SQL erweitern
    if ($variant !== null) {
        $sql .= " AND variant = :variant";
        $params['variant'] = $variant;
    }
    
    // 5. Query ausführen
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    // Holt alle Datensätze. Wenn nichts gefunden wird, ist $data ein leeres Array []
    $data = $stmt->fetchAll();
    
    // 6. Daten als JSON ausgeben (liefert [] bei 0 Treffern)
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    // Internen SQL-Fehler aus Sicherheitsgründen maskieren
    http_response_code(500);
    echo json_encode(['error' => 'Datenbankfehler aufgetreten.']);
}
?>