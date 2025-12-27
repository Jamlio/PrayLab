<?php
$configFile = __DIR__ . '/config.json';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $config = json_decode(file_get_contents($configFile), true);

    // Update only selected fields
    $config['script_setup'] = true;
    $config['script_title'] = $_POST['script_title'] ?? $config['script_title'];
    $config['script_weather_api'] = $_POST['script_weather_api'] ?? $config['script_weather_api'];
    $config['script_lat'] = isset($_POST['script_lat']) ? round((float)$_POST['script_lat'], 4) : $config['script_lat'];
    $config['script_lon'] = isset($_POST['script_lon']) ? round((float)$_POST['script_lon'], 4) : $config['script_lon'];
    $config['script_timezone'] = $_POST['script_timezone'] ?? $config['script_timezone'];
    $config['script_country'] = $_POST['script_country'] ?? $config['script_country'];
    $config['prayer_backgrounds']['default'] = $_POST['background_default'] ?? $config['prayer_backgrounds']['default'];

    file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT));
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Initial Setup</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>

<body class="bg-gray-950 text-white min-h-screen flex items-center justify-center">
    <div class="bg-gray-900 p-6 rounded-xl w-full max-w-md space-y-6">
        <!-- Welcome -->
        <div class="text-center space-y-2">
            <h1 class="text-3xl font-bold text-blue-400">🛠️ Setup Display Manager</h1>
            <p class="text-sm text-gray-400">Complete the steps below to initialize your system.</p>
        </div>

        <!-- Progress Checklist -->
        <ul class="space-y-2 text-sm text-gray-300">
            <li id="stepTitle" class="flex items-center gap-2"><i class="ph ph-circle text-gray-500"></i> Display Title
            </li>
            <li id="stepWeather" class="flex items-center gap-2"><i class="ph ph-circle text-gray-500"></i> Weather API
                Key</li>
            <li id="stepLat" step="0.0001" class="flex items-center gap-2"><i class="ph ph-circle text-gray-500"></i> Location Lat.
            </li>
            <li id="stepLon" step="0.0001" class="flex items-center gap-2"><i class="ph ph-circle text-gray-500"></i> Location Lon.
            </li>
            <li id="stepTimezone" class="flex items-center gap-2"><i class="ph ph-circle text-gray-500"></i> Timezone
            </li>
            <li id="stepCountry" class="flex items-center gap-2"><i class="ph ph-circle text-gray-500"></i> Country
            </li>
        </ul>

        <!-- Setup Form -->
        <form method="POST" class="space-y-4" id="setupForm">
            <input type="text" name="script_title" placeholder="Display Title" class="input w-full" required>
            <input type="text" name="script_weather_api" placeholder="Weather API Key" class="input w-full" required>
            <input type="text" name="script_lat" placeholder="Location Lat." class="input w-full" required>
            <input type="text" name="script_lon" placeholder="Location Lon." class="input w-full" required>
            <input type="text" name="script_timezone" placeholder="Timezone" class="input w-full" required>
            <input type="text" name="script_country" placeholder="Country Code (ex. LT, UK, FR)" class="input w-full" required>
            <input type="text" name="background_default" placeholder="Default Background" class="input w-full" required>

            <button type="submit" class="bg-blue-500 hover:bg-blue-600 w-full py-2 rounded text-white font-semibold">🚀
                Complete Setup</button>
        </form>

        <!-- Test API Button -->
        <button onclick="testAPI()"
            class="bg-gray-700 hover:bg-gray-600 w-full py-2 rounded text-white font-semibold">🔍 Test Weather
            API</button>

        <!-- API Test Result -->
        <div id="apiResult" class="text-sm text-center text-gray-400 mt-2"></div>
    </div>

    <style>
    .input {
        padding: 0.75rem;
        border-radius: 0.5rem;
        background-color: #1f2937;
        border: 1px solid #374151;
        width: 100%;
        color: white;
    }

    .completed i {
        color: #22c55e;
    }
    </style>

    <script>
    const form = document.getElementById('setupForm');
    const steps = {
        script_title: document.getElementById('stepTitle'),
        script_weather_api: document.getElementById('stepWeather'),
        script_lat: document.getElementById('stepLat'),
        script_lon: document.getElementById('stepLon'),
        script_timezone: document.getElementById('stepTimezone'),
        script_country: document.getElementById('stepCountry')
    };

    form.querySelectorAll('input').forEach(input => {
        input.addEventListener('input', () => {
            const step = steps[input.name];
            if (step) {
                if (input.value.trim()) {
                    step.classList.add('completed');
                    step.querySelector('i').className = 'ph ph-check-circle text-green-400';
                } else {
                    step.classList.remove('completed');
                    step.querySelector('i').className = 'ph ph-circle text-gray-500';
                }
            }
        });
    });

    function testAPI() {
        const key = form.script_weather_api.value.trim();
        if (!key) {
            document.getElementById('apiResult').textContent = "⚠️ Please enter a Weather API key first.";
            return;
        }

        const lat = form.script_lat.value.trim();
        const lon = form.script_lon.value.trim();
        fetch(`https://api.openweathermap.org/data/2.5/weather?lat=${lat}&lon=${lon}&appid=${key}`)
            .then(res => res.ok ? res.json() : Promise.reject())
            .then(() => {
                document.getElementById('apiResult').textContent = "✅ Weather API key looks valid!";
            })
            .catch(() => {
                document.getElementById('apiResult').textContent = "❌ Invalid Weather API key or connection error.";
            });
    }
    </script>
</body>

</html>