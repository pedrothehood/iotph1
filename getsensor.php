<?php
$config = require __DIR__ . '/../config.php';

$host    = $config['db_host'];
$db      = $config['db_name'];
$user    = $config['db_user'];
$pass    = $config['db_pass'];
$charset = 'utf8mb4';

// 1. Debug-Ausgabe wie gewünscht
var_dump($_GET);

// PDO Verbindungsaufbau
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Parameter holen
$sensorid = $_GET['sensorid'] ?? "";
$amountrows = (int)($_GET['amountrows'] ?? 240);

// 1. Abfrage: Sensordaten
if ($sensorid !== "") {
    $stmt = $pdo->prepare("SELECT id, sensorid, value1, value2, value3, reading_time FROM Sensor WHERE sensorid = ? ORDER BY reading_time DESC LIMIT ?");
    $stmt->execute([$sensorid, $amountrows]);
} else {
    $stmt = $pdo->prepare("SELECT id, sensorid, value1, value2, value3, reading_time FROM Sensor ORDER BY reading_time DESC LIMIT ?");
    $stmt->execute([$amountrows]);
}
$sensor_data = $stmt->fetchAll();

// Header setzen
header('Content-type: application/json');

// Wenn keine Daten da sind, verhält sich das Skript wie das Original
if (!$sensor_data) {
    $sensor_data = null; 
}

if ($sensor_data !== null) {
    // 2. Abfrage: Sensorinfo
    if ($sensorid !== "") {
        $stmt2 = $pdo->prepare("SELECT sensorid, bezeichnung, standort, owner, techtyp, showrownr, showcode, amountrows, delaymin, status, code, value1, value2, value3, ts FROM Sensorid WHERE sensorid = ?");
        $stmt2->execute([$sensorid]);
    } else {
        $stmt2 = $pdo->query("SELECT sensorid, bezeichnung, standort, owner, techtyp, showrownr, showcode, amountrows, delaymin, status, code, value1, value2, value3, ts FROM Sensorid");
    }
    $data2 = $stmt2->fetch() ?: null;

    // Erzeuge exakt die Struktur deines Beispiels
    // Dein altes Skript erzeugte: {"data": {"sensordaten": [...], "sensorinfo": {...}}}
    $final_structure = [
        "data" => [
            "sensordaten" => $sensor_data,
            "sensorinfo"  => $data2
        ]
    ];

    echo json_encode($final_structure);
}
?>

