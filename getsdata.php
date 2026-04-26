<?php
// getsdata.php

// Konfiguration laden
$config = require __DIR__ . '/../config.php';

$host = $config['db_host'];
$db = $config['db_name'];
$user = $config['db_user'];
$pass = $config['db_pass'];
$charset = 'utf8mb4';
$api_key_value = $config['api_key'];

// Datenbankverbindung herstellen
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Datenbankverbindungsfehler']);
    exit;
}

// API-Key überprüfen
$api_key = $_GET['api_key'] ?? '';
if ($api_key !== $api_key_value) {
    http_response_code(401);
    echo json_encode(['error' => 'Ungültiger API-Key']);
    exit;
}

// Sensor-ID aus GET-Parametern lesen
$sensor_id = $_GET['sensor_id'] ?? null;

try {
    // Daten aus sensor-Tabelle abrufen
    $sensor_data = [];
    if ($sensor_id) {
        $stmt = $pdo->prepare("SELECT * FROM sensor WHERE sensorid = ?");
        $stmt->execute([$sensor_id]);
        $sensor_data = $stmt->fetchAll();
    } else {
        $stmt = $pdo->query("SELECT * FROM sensor");
        $sensor_data = $stmt->fetchAll();
    }
    
    // Daten aus sensorid-Tabelle abrufen
    $sensorid_data = [];
    if ($sensor_id) {
        $stmt = $pdo->prepare("SELECT * FROM sensorid WHERE sensorid = ?");
        $stmt->execute([$sensor_id]);
        $sensorid_data = $stmt->fetchAll();
    } else {
        $stmt = $pdo->query("SELECT * FROM sensorid");
        $sensorid_data = $stmt->fetchAll();
    }
    
    // Ergebnis zurückgeben
    echo json_encode([
        'sensor' => $sensor_data,
        'sensorid' => $sensorid_data
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Datenbankabfragefehler']);
}
?>
