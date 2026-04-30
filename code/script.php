<?php
$stderr = fopen('php://stderr', 'w');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);

    $decoded = $data['uplink_message']['decoded_payload']['data'];

    fwrite($stderr, "Decoded payload: " . print_r($decoded, true) . "\n");

    // stocker
    file_put_contents('data.json', json_encode($decoded));

    exit;
}

$data = json_decode(@file_get_contents('data.json'), true);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Labo sans fil</title>
    <meta http-equiv="refresh" content="10">
</head>
<body>
    <h1>Données capteurs</h1>

    <p>Température: <?= $data['temp1']?> °C</p>
    <p>Humidité: <?= $data['humidity']?> %</p>
    <p>Batterie: <?= $data['battery_voltage']?> mV</p>

</body>
</html>