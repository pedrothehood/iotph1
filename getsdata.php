<?php
// Erstelle php rest service
// Ergebnis im json-format zurückgeben

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

// API-Key prüfen
if ($api_key !== $api_key_value) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized: Invalid API key']);
    exit;
}

// sensor_id aus GET-Parameter einlesen
$sensor_id = $_GET['sensor_id'] ?? null;

try {
    // DB-Verbindung herstellen
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Daten aus Tabelle Sensor abrufen
    $sensor_data = [];
    if ($sensor_id) {
        $stmt = $pdo->prepare("SELECT * FROM Sensor WHERE sensorid = ?");
        $stmt->execute([$sensor_id]);
        $sensor_data = $stmt->fetchAll();
    } else {
        $stmt = $pdo->query("SELECT * FROM Sensor");
        $sensor_data = $stmt->fetchAll();
    }
    
    // Daten aus Tabelle Sensorid abrufen
    $sensorid_data = [];
    if ($sensor_id) {
        $stmt = $pdo->prepare("SELECT * FROM Sensorid WHERE sensorid = ?");
        $stmt->execute([$sensor_id]);
        $sensorid_data = $stmt->fetchAll();
    } else {
        $stmt = $pdo->query("SELECT * FROM Sensorid");
        $sensorid_data = $stmt->fetchAll();
    }
    
    // Ergebnis als JSON zurückgeben
    header('Content-Type: application/json');
    echo json_encode([
        'Sensor' => $sensor_data,
        'Sensorid' => $sensorid_data
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
