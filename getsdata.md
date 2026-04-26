#Spec getsdata.php

Erstelle php rest service
ergebnis im json-format zurückgeben
die konfiguration wie folgt  einlesen:
$config = require __DIR__ . '/../config.php';


$host    = $config['db_host'];
$db      = $config['db_name'];
$user    = $config['db_user'];
$pass    = $config['db_pass'];
$api_key_value = $config['api_key'];
$charset = 'utf8mb4';

Prüfe den api-key aus api_key_value mit dem als Parameter übergebenen api_key und gebe einen Fehler aus, falls diese nicht übereinstimmen und brich das Programm ab. Der API-Key selber darf im Code nirgends stehen!
Die Tabellen sensor und die tabellen sensorid werden eingelesen,
für beide Tabellen gilt: falls variable ”sensor_id” (mit get einlesen) vorhanden ist, dann
im select nach sensorid = sensorid suchen
select auf tabelle sensor und tabelle sensorid mit variable sensor_id, falls vorhanden
beide Tabellen-Inhalte als json zurückgeben
