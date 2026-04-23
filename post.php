<?php
$config = require __DIR__ . '/../config.php';

$servername    = $config['db_host'];
$dbname        = $config['db_name'];
$username      = $config['db_user'];
$password      = $config['db_pass'];
$api_key_value = $config['api_key'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $api_key = test_input($_POST["api_key"] ?? "");

    if ($api_key === $api_key_value) {
        $sensorid = test_input($_POST["sensorid"] ?? "");
        $value1   = test_input($_POST["value1"] ?? "");
        $value2   = test_input($_POST["value2"] ?? "");
        $value3   = test_input($_POST["value3"] ?? "");

        $dsn = "mysql:host=$servername;dbname=$dbname;charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        try {
            $pdo = new PDO($dsn, $username, $password, $options);

            // 1. Daten einfügen
            $sql = "INSERT INTO Sensor (sensorid, value1, value2, value3) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$sensorid, $value1, $value2, $value3]);
            echo "New record created successfully\n";

            // 2. Alte Daten löschen (älter als 7 Tage)
            $sql_delete = "DELETE FROM Sensor WHERE reading_time < DATE_SUB(NOW(), INTERVAL 7 DAY)";
            $deletedRows = $pdo->exec($sql_delete);
            echo "Records older 7 Days deleted: " . $deletedRows;

        } catch (\PDOException $e) {
            echo "Database Error: " . $e->getMessage();
        }
    } else {
        echo "Wrong API Key provided.";
    }
} else {
    echo "No data posted with HTTP POST.";
}

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>
