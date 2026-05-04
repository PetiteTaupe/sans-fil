<?php
$stderr = fopen('php://stderr', 'w');

function getAlerts($data) {
    $alerts = [];

    $temp = $data['temp1'];
    $hum = $data['humidity'];
    $bat = $data['battery_voltage'];

    if ($temp < 12) {
            $alerts[] = "Température trop basse ($temp °C)";
        } elseif ($temp > 30) {
            $alerts[] = "Température trop élevée ($temp °C)";
        }

    if ($hum < 30) {
            $alerts[] = "Air trop sec ($hum %)";
        } elseif ($hum > 70) {
            $alerts[] = "Air trop humide ($hum %)";
        }

    if ($bat < 3100) {
            $alerts[] = "Batterie critique ($bat mV)";
        } elseif ($bat < 3200) {
            $alerts[] = "Batterie faible ($bat mV)";
        }

    return $alerts;
}

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
$alerts = getAlerts($data);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Labo sans fil</title>
    <meta http-equiv="refresh" content="10">
</head>
<body>
    <h1>Webhook</h1>
    <h2>Données capteurs</h2>

    <p>Température: <?= $data['temp1']?> °C</p>
    <p>Humidité: <?= $data['humidity']?> %</p>
    <p>Batterie: <?= $data['battery_voltage']?> mV</p>

    <?php if (!empty($alerts)): ?>
        <h2>Alertes</h2>
        <ul>
            <?php foreach ($alerts as $alert): ?>
                <li><?= $alert ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    
</body>
</html>