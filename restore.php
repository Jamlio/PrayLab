<?php
$configFile = __DIR__ . '/config.json';
$data = json_decode(file_get_contents('php://input'), true);

if (is_array($data)) {
    file_put_contents($configFile, json_encode($data, JSON_PRETTY_PRINT));
    http_response_code(200);
} else {
    http_response_code(400);
}
?>