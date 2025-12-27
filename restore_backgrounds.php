<?php
$configFile = __DIR__ . '/config.json';
$config = json_decode(file_get_contents($configFile), true);
$data = json_decode(file_get_contents('php://input'), true);

if (is_array($data)) {
    $config['prayer_backgrounds'] = $data;
    file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT));
    http_response_code(200);
} else {
    http_response_code(400);
}
?>