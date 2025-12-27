<?php
header('Content-Type: application/json');
$config = json_decode(file_get_contents(__DIR__ . '/config.json'), true);

// Your existing weather fetching code here:
$lat = $config['script_lat'];
$lon = $config['script_lon'];
$owmApiKey = $config['script_weather_api'];
$weatherUrl = "https://api.openweathermap.org/data/2.5/weather?lat={$lat}&lon={$lon}&units=metric&appid={$owmApiKey}";

$weatherResponse = file_get_contents($weatherUrl);
if ($weatherResponse === false) {
    echo json_encode(['error' => 'Failed to fetch weather data']);
    exit;
}

$weatherData = json_decode($weatherResponse, true);

$weatherDetails = [
    'temp' => $weatherData['main']['temp'] ?? 'N/A',
    'description' => ucfirst($weatherData['weather'][0]['description'] ?? 'N/A'),
    'humidity' => $weatherData['main']['humidity'] ?? 'N/A',
    'wind_speed' => $weatherData['wind']['speed'] ?? 'N/A',
    'icon' => $weatherData['weather'][0]['icon'] ?? null,
];

echo json_encode($weatherDetails);