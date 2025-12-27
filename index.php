<?php
$configFile = __DIR__ . '/config.json';

if (!file_exists($configFile)) {
    $defaultConfig = [
        "script_setup" => false,
        "mode" => "prayer",

        "team_a" => "",
        "team_b" => "",
        "team_a_logo" => "",
        "team_b_logo" => "",
        "match_time" => "",
        "stadium" => "",
        "score_a" => 0,
        "score_b" => 0,
        "status" => "",

        "livestream" => "",

        "announcement_title" => "",
        "announcement_message" => "",
        "announcement_footer" => "",

        "script_title" => "",
        "script_weather_api" => "",
        "script_lat" => "",
        "script_lon" => "",
        "script_timezone" => "",
        "script_country" => "",
        "script_version" => "1.2.5",
        "script_updated_at" => "August 18",

        "prayer_backgrounds" => [
            "Fajr" => "",
            "Sunrise" => "",
            "Dhuhr" => "",
            "Asr" => "",
            "Maghrib" => "",
            "Isha" => "",
            "default" => ""
        ]
    ];

    file_put_contents($configFile, json_encode($defaultConfig, JSON_PRETTY_PRINT));
}
$config = json_decode(file_get_contents(__DIR__ . '/config.json'), true);

// Checking if the setup is complete
if (!isset($config['script_setup']) || $config['script_setup'] !== true) {
    include __DIR__ . '/setup.php';
    exit;
}

// IslamicFinder API endpoint
$country = "LT";
$zipcode = "0"; // If unknown, keep 0 or nearest valid
$lat = $config['script_lat'];
$lon = $config['script_lon'];
$timezone = $config['script_timezone'];

$url = "https://www.islamicfinder.us/index.php/api/prayer_times?country={$country}&zipcode={$zipcode}&latitude={$lat}&longitude={$lon}&timezone={$timezone}";

// Fetch prayer times with cURL and local cache fallback
function fetch_prayer_times_with_cache($url) {
    $cacheDir = __DIR__ . '/cache';
    if (!is_dir($cacheDir)) @mkdir($cacheDir, 0755, true);
    $cacheFile = $cacheDir . '/prayer_times.json';

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_USERAGENT, 'PrayLab/1.2 (+https://example.local)');
    $res = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_errno($ch);
    curl_close($ch);

    if ($err || $http >= 400 || !$res) {
        // Try cache
        if (file_exists($cacheFile)) {
            return file_get_contents($cacheFile);
        }
        return false;
    }

    // Save successful response to cache (best effort)
    @file_put_contents($cacheFile, $res);
    return $res;
}

// Ensure consistent timezone for server-side parsing
date_default_timezone_set($config['script_timezone'] ?? 'UTC');

$response = fetch_prayer_times_with_cache($url);

if ($response === false) {
    http_response_code(503); // Service Unavailable
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>API Error</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-gray-950 text-white flex items-center justify-center min-h-screen">
        <div class="bg-gray-900 p-6 rounded-xl shadow-lg text-center max-w-md space-y-4">
            <h1 class="text-2xl font-bold text-red-400">🚨 API Connection Error</h1>
            <p class="text-sm text-gray-300">We couldn't connect to the external API. This may be due to:</p>
            <ul class="text-sm text-gray-400 list-disc list-inside text-left">
                <li>Network issues</li>
                <li>API service downtime</li>
                <li>Invalid API key or endpoint</li>
            </ul>
            <p class="text-sm text-gray-400">Please try again later or check your configuration.</p>
            <a href="index.php" class="inline-block bg-red-500 hover:bg-red-600 px-4 py-2 rounded text-white text-sm font-semibold">🔄 Retry</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Decode JSON
$data = json_decode($response, true);
if (!isset($data['success']) || !$data['success']) {
    die("Error: API returned no results");
}

// Extract prayer times
$results = $data['results'];
$prayer_times = [];

// List of prayer keys you want
$wanted_prayers = [
    "Fajr" => "Fajr",
    "Sunrise" => "Duha",
    "Dhuhr" => "Dhuhr",
    "Asr" => "Asr",
    "Maghrib" => "Maghrib",
    "Isha" => "Isha"
];

foreach ($wanted_prayers as $key => $apiName) {
    if (isset($results[$apiName])) {
        // Clean up %am% / %pm% markers
        $clean_time = str_replace(['%am%', '%pm%'], ['am', 'pm'], strtolower($results[$apiName]));

        // Convert to 24-hour format
        $time_24h = date("H:i", strtotime($clean_time));

        $prayer_times[$key] = $time_24h;
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Prayer Times</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
    /* Custom scrollbar for dark theme */
    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    ::-webkit-scrollbar-thumb {
        background: #4b5563;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-track {
        background: #1f2937;
    }

    body {
        /* Prevent text selection highlighting in fullscreen */
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
    }
    </style>
    <script>
    // Simulate data loading completion after prayer times are fetched
    window.addEventListener('load', function() {
        document.getElementById('loading-screen').classList.add('hidden');
        document.getElementById('main-content').classList.remove('hidden');
    });
    </script>
</head>

<body class="bg-gray-900 text-gray-100 min-h-screen flex flex-col bg-cover bg-center bg-no-repeat" style="
  transition: background-image 0.5s ease-in-out;
  ">

    <!-- Loading Screen -->
    <div id="loading-screen" class="fixed inset-0 flex flex-col items-center justify-center bg-gray-900 z-50">
        <div class="animate-spin rounded-full h-24 w-24 border-t-4 border-indigo-500 border-solid"></div>
        <p class="mt-6 text-gray-300 text-lg">Loading prayer times...</p>
    </div>

    <div id="main-content" class="hidden">
        <!-- Header -->
        <header class="flex justify-between items-center p-6 border-b border-white/10">
            <div>
                <h1 class="text-2xl font-bold"><?= $config['script_title'] ?></h1>
                <p class="text-gray-400 text-sm">Prayer Times • By Legendukas • <span id="hijriDate"
                        class="text-gray-400"></span></p>
            </div>
            <div class="text-right">
                <div id="clock" class="text-2xl font-bold"></div>
                <div id="date" class="text-gray-400 text-sm"></div>
            </div>
        </header>

        <!-- Main -->
        <main class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-6 p-6">

            <section id="rotatingSection"
                class="md:col-span-2 bg-black/20 backdrop-blur-sm rounded-xl p-10 border border-white/20 flex flex-col justify-center items-center select-text shadow-lg">
            </section>

            <script>
            const config = <?php echo json_encode($config); ?>;

            let currentMode = config.mode; // store initial mode
            let currentScreen = 0;
            let timeoutId = null;
            const sectionEl = document.getElementById('rotatingSection');

            function getSoccerHTML(newConfig) {
                const scoreA = Number(newConfig.score_a);
                const scoreB = Number(newConfig.score_b);

                const teamAIsWinner = scoreA > scoreB;
                const teamBIsWinner = scoreB > scoreA;
                const isDraw = scoreA === scoreB;

                const teamAStyles = teamAIsWinner ?
                    "bg-gradient-to-r from-yellow-500/70 to-yellow-700/90 shadow-lg" :
                    "bg-transparent";
                const teamBStyles = teamBIsWinner ?
                    "bg-gradient-to-r from-yellow-500/70 to-yellow-700/90 shadow-lg" :
                    "bg-transparent";

                const containerStyles = isDraw ?
                    "bg-gradient-to-r from-gray-700 to-gray-900 shadow-inner border-gray-500" :
                    "bg-black/90 border-yellow-600";

                const teamTextColor = "text-gray-200";

                return `
<div class="flex flex-col items-center w-full max-w-5xl space-y-10 text-center">
  <div class="flex items-center space-x-4 text-2xl text-yellow-400 tracking-wide uppercase font-extrabold">
    <svg class="w-8 h-8 text-yellow-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z" />
      <path d="M15 9l-6 6M9 9l6 6" />
    </svg>
    <span>${newConfig.status}</span>
    ${isDraw ? `<span class="ml-3 px-3 py-1 rounded-full bg-gray-600 text-gray-300 text-sm font-semibold select-none">DRAW</span>` : ""}
  </div>

  <div class="flex justify-between w-full rounded-2xl p-12 shadow-2xl border-2 ${containerStyles} max-w-5xl">
    <!-- Team A -->
    <div class="flex flex-col items-center flex-1 rounded-xl p-6 ${teamAStyles}">
      <img src="${newConfig.team_a_logo || "https://upload.wikimedia.org/wikipedia/commons/6/6e/Football_%28soccer_ball%29.svg"}" 
           alt="${newConfig.team_a}" class="w-32 h-32 rounded-full object-cover mb-4 shadow-xl" />
      <div class="text-4xl font-extrabold ${teamTextColor}">${newConfig.team_a}</div>
      <div class="text-9xl font-extrabold text-white mt-2">${newConfig.score_a}</div>
    </div>

    <!-- VS -->
    <div class="flex items-center justify-center text-white font-extrabold text-8xl mx-8 select-none self-center">
      VS
    </div>

    <!-- Team B -->
    <div class="flex flex-col items-center flex-1 rounded-xl p-6 ${teamBStyles}">
      <img src="${newConfig.team_b_logo || "https://upload.wikimedia.org/wikipedia/commons/6/6e/Football_%28soccer_ball%29.svg"}" 
           alt="${newConfig.team_b}" class="w-32 h-32 rounded-full object-cover mb-4 shadow-xl" />
      <div class="text-4xl font-extrabold ${teamTextColor}">${newConfig.team_b}</div>
      <div class="text-9xl font-extrabold text-white mt-2">${newConfig.score_b}</div>
    </div>
  </div>

  <div class="text-gray-300 bg-black/40 rounded-lg px-8 py-4 border ${isDraw ? "border-gray-500" : "border-yellow-600"} max-w-5xl w-full text-xl">
    Match Time: <span class="font-semibold text-white">${newConfig.match_time || "—"}</span> • 
    Stadium: <span class="font-semibold text-white">${newConfig.stadium || "Unknown"}</span>
  </div>

  <p class="text-yellow-400 text-lg italic">Live match data — Data refreshed in real-time</p>
</div>`;
            }

            function buildScreensFromConfig(cfg) {
                if (cfg.mode === 'soccer') {
                    return [{
                        html: getSoccerHTML(cfg),
                        duration: 999999
                    }];
                } else if (cfg.mode === 'livestream') {
                    return [{
                        html: `
                <div class="flex items-center justify-center w-full h-full bg-black rounded-lg overflow-hidden max-w-5xl mx-auto shadow-lg">
                    <iframe
                        id="livestreamFrame"
                        class="w-full h-full"
                        src="${cfg.livestream || ''}"
                        frameborder="0"
                        allowfullscreen
                        allow="autoplay; fullscreen; picture-in-picture"
                    ></iframe>
                </div>`,
                        duration: 999999
                    }];
                } else if (cfg.mode === 'announcement') {
                    return [{
                        html: `
                <div class="flex flex-col items-center justify-center w-full max-w-6xl mx-auto p-16 text-center">
    <h1 class="text-7xl font-extrabold text-white mb-8 tracking-wide drop-shadow-lg">
        ${cfg.announcement_title || "Announcement"}
    </h1>
    <p class="text-4xl text-gray-200 leading-relaxed max-w-4xl drop-shadow-lg">
        ${cfg.announcement_message || "No announcement at the moment."}
    </p>
    ${cfg.announcement_footer ? `<div class="mt-10 text-2xl text-gray-300 italic drop-shadow-lg">${cfg.announcement_footer}</div>` : ""}
</div>
`,
                        duration: 999999
                    }];
                } else {
                    return [{
                        html: `
                <div class="text-lg text-gray-300 tracking-wide uppercase font-semibold">Next Prayer</div>
                <div id="nextPrayerName" class="text-8xl font-extrabold mt-4 select-text tracking-wider leading-tight">—</div>
                <div class="text-gray-200 text-3xl mt-2 font-semibold">At <span id="nextPrayerTime">—</span></div>
                <div class="mt-10 text-lg text-gray-400 tracking-wide uppercase font-semibold">Time Remaining</div>
                <div id="countdown" class="text-[10rem] font-black mt-4 tracking-wide drop-shadow-[0_2px_8px_rgba(0,0,0,0.7)] select-text tracking-wider leading-tight">--:--:--</div>
                <div class="w-full bg-white/10 h-2 rounded-full overflow-hidden mt-8 max-w-4xl">
                    <div id="progressBar" class="h-2 bg-gradient-to-r from-indigo-500 to-pink-600 w-0 rounded-full"></div>
                </div>
                <p class="text-sm text-gray-400 mt-6 tracking-wide" id="locationInfo">Location: Užringis International Mosque</p>
            `,
                        duration: 30000
                    }, {
                        html: `
                <div id="date2" class="text-lg text-gray-300 tracking-wide font-semibold"></div>
                <div id="clock2" class="text-[7rem] font-black mt-4 tracking-wide drop-shadow-[0_2px_8px_rgba(0,0,0,0.7)] select-text leading-tight"></div>
            `,
                        duration: 5000
                    }];
                }
            }

            let screens = buildScreensFromConfig(config);

            function showScreen(index) {
                sectionEl.innerHTML = screens[index].html;
                clearTimeout(timeoutId);
                timeoutId = setTimeout(() => {
                    currentScreen = (currentScreen + 1) % screens.length;
                    showScreen(currentScreen);
                }, screens[index].duration);
            }

            // Start
            showScreen(currentScreen);
            </script>


            <!-- Prayer times & settings panel -->
            <aside class="bg-black/20 backdrop-blur-sm rounded-xl p-6 border border-white/10 flex flex-col select-text">
                <h2 class="text-sm text-gray-400">Today's Prayer Times</h2>
                <div class="mt-4 space-y-2" id="prayerTimesList"></div>

                <div class="mt-6 p-6 ">
                    <blockquote class="text-gray-200 italic text-lg leading-relaxed">
                        "Indeed, prayer prohibits immorality and wrongdoing, and the remembrance of Allah is greater."
                        <span class="block mt-2 text-gray-400 text-sm">— Quran 29:45</span>
                    </blockquote>
                </div>

                <div class="p-3 bg-black/30 rounded-lg border border-white/10">
                    <div class="flex justify-center mb-2">
                        <img id="weatherIcon" src="" alt="Weather Icon" class="w-28 h-28" style="display:none;" />
                    </div>
                    <div id="weatherLocation" class="text-center text-xl font-semibold mb-3">Loading...</div>
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <svg class="mx-auto mb-1 w-6 h-6 text-blue-400" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24">
                                <path d="M3 12h18" />
                            </svg>
                            <div class="text-gray-300 text-sm">Humidity</div>
                            <div id="weatherHumidity" class="font-semibold">--%</div>
                        </div>
                        <div>
                            <svg class="mx-auto mb-1 w-6 h-6 text-red-400" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24">
                                <path d="M12 2v20" />
                            </svg>
                            <div class="text-gray-300 text-sm">Temp</div>
                            <div id="weatherTemp" class="font-bold text-lg">-- °C</div>
                        </div>
                        <div>
                            <svg class="mx-auto mb-1 w-6 h-6 text-green-400" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24">
                                <path d="M4 12l4 4 8-8" />
                            </svg>
                            <div class="text-gray-300 text-sm">Wind</div>
                            <div id="weatherWind" class="font-semibold">-- km/h</div>
                        </div>
                    </div>
                </div>

            </aside>
        </main>
    </div>

    <!-- Beep Audio -->
    <audio id="beep" preload="auto" src="https://actions.google.com/sounds/v1/alarms/alarm_clock.ogg"></audio>

    <script>
    // Prayer times from PHP
    const prayerTimesRaw = <?php echo json_encode($prayer_times); ?>;

    // Parse prayer times into Date objects today
    function parsePrayerTimes(times) {
        const today = new Date();
        let result = {};
        for (const [name, time] of Object.entries(times)) {
            let [h, m] = time.split(':');
            result[name] = new Date(today.getFullYear(), today.getMonth(), today.getDate(), parseInt(h), parseInt(m), 0);
        }
        return result;
    }

    let prayers = parsePrayerTimes(prayerTimesRaw);

    function formatTime(date) {
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    function getNextPrayer() {
        const now = new Date();
        let upcoming = Object.entries(prayers)
            .filter(([_, time]) => time > now)
            .sort((a, b) => a[1] - b[1]);

        if (upcoming.length === 0) {
            let fajrTomorrow = new Date(prayers['Fajr']);
            fajrTomorrow.setDate(fajrTomorrow.getDate() + 1);
            return ['Fajr', fajrTomorrow];
        }
        return upcoming[0];
    }

    function formatTimeDiff(diffMs) {
        let totalSeconds = Math.floor(diffMs / 1000);
        let hours = Math.floor(totalSeconds / 3600);
        let minutes = Math.floor((totalSeconds % 3600) / 60);
        let seconds = totalSeconds % 60;
        return `${hours.toString().padStart(2,'0')}:${minutes.toString().padStart(2,'0')}:${seconds.toString().padStart(2,'0')}`;
    }

    // Consolidated updateCountdown: updates countdown, progress, background and plays beep
    function updateCountdown() {
        if (!document.getElementById('nextPrayerName')) return;
        const now = new Date();
        const [nextName, nextTime] = getNextPrayer();

        // determine previous prayer name
        const prayerNames = Object.keys(prayers);
        let idx = prayerNames.indexOf(nextName);
        let prevIdx = idx - 1; if (prevIdx < 0) prevIdx = prayerNames.length - 1;
        const prevName = prayerNames[prevIdx];

        // Update background using prayerBackgrounds (fallback to default)
        let bgUrl = (prayerBackgrounds[prevName] || prayerBackgrounds.default || '').trim();
        if (bgUrl) {
            if (!document.body.style.backgroundImage || document.body.style.backgroundImage.indexOf(bgUrl) === -1) {
                document.body.style.backgroundImage = `url('${bgUrl}')`;
            }
        } else {
            document.body.style.backgroundImage = 'none';
        }

        // Update UI elements
        document.getElementById('nextPrayerName').textContent = nextName;
        document.getElementById('nextPrayerTime').textContent = formatTime(nextTime);

        const diff = nextTime - now;
        document.getElementById('countdown').textContent = diff > 0 ? formatTimeDiff(diff) : '00:00:00';

        // Progress bar
        let prevPrayer = prayers[prevName];
        let nextPrayer = prayers[nextName];
        if (nextPrayer < prevPrayer) { nextPrayer = new Date(nextPrayer); nextPrayer.setDate(nextPrayer.getDate() + 1); }
        const totalDuration = nextPrayer - prevPrayer;
        const elapsed = now - prevPrayer;
        const progressPercent = Math.min(100, Math.max(0, (elapsed / totalDuration) * 100));
        const progressEl = document.getElementById('progressBar');
        if (progressEl) progressEl.style.width = progressPercent + '%';

        // Play beep at prayer time (within 1s)
        if (diff > 0 && diff < 1000) {
            const beep = document.getElementById('beep');
            if (beep) beep.play().catch(() => {});
        }
    }

    // Render prayer times list on right panel
    function renderPrayerTimes() {
        const container = document.getElementById('prayerTimesList');
        container.innerHTML = '';
        for (const [name, time] of Object.entries(prayers)) {
            let timeStr = formatTime(time);
            let div = document.createElement('div');
            div.className = 'flex justify-between px-3 py-2 rounded hover:bg-white/10 cursor-default select-text';
            div.innerHTML = `<span>${name}</span><span>${timeStr}</span>`;
            container.appendChild(div);
        }
    }

    // Update clock & date in header
    function updateClockDate(clockIds = [], dateIds = []) {
        const now = new Date();
        const timeString = now.toLocaleTimeString([], {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
        const dateString = now.toLocaleDateString(undefined, {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });

        clockIds.forEach(id => {
            const clockEl = document.getElementById(id);
            if (clockEl) clockEl.textContent = timeString;
        });

        dateIds.forEach(id => {
            const dateEl = document.getElementById(id);
            if (dateEl) dateEl.textContent = dateString;
        });
    }

    let prayerBackgrounds = {};

    fetch('config.json')
        .then(res => res.ok ? res.json() : Promise.reject())
        .then(config => {
            prayerBackgrounds = config.prayer_backgrounds || {};
        })
        .catch(() => {
            showToast("⚠️ Failed to load prayer backgrounds");
        });
    Object.values(prayerBackgrounds).forEach(src => {
        if (src) {
            const img = new Image();
            img.src = src;
        }
    });

    function updateCountdown() {
        if (!document.getElementById('nextPrayerName')) return;
        const now = new Date();
        const [nextName, nextTime] = getNextPrayer();

        let prayerNames = Object.keys(prayers);
        let idx = prayerNames.indexOf(nextName);
        let prevIdx = idx - 1;
        if (prevIdx < 0) prevIdx = prayerNames.length - 1;
        let prevName = prayerNames[prevIdx];

        // Change background to the image for the current interval
        let bgUrl = prayerBackgrounds[prevName] || prayerBackgrounds.default;
        document.body.style.backgroundImage = bgUrl ?
            `url('${bgUrl}')` :
            "none"; // or use a hardcoded fallback like 'url("default-bg.png")'

        // Update UI elements and progress bar as before...
        const diff = nextTime - now;
        document.getElementById('nextPrayerName').textContent = nextName;
        document.getElementById('nextPrayerTime').textContent = formatTime(nextTime);

        if (diff > 0) {
            document.getElementById('countdown').textContent = formatTimeDiff(diff);
        } else {
            document.getElementById('countdown').textContent = "00:00:00";
        }

        let prevPrayer = prayers[prevName];
        let nextPrayer = prayers[nextName];
        if (nextPrayer < prevPrayer) {
            nextPrayer = new Date(nextPrayer);
            nextPrayer.setDate(nextPrayer.getDate() + 1);
        }

        let totalDuration = nextPrayer - prevPrayer;
        let elapsed = now - prevPrayer;

        let progressPercent = Math.min(100, Math.max(0, (elapsed / totalDuration) * 100));
        document.getElementById('progressBar').style.width = progressPercent + '%';

        if (diff > 0 && diff < 1000) {
            const beep = document.getElementById('beep');
            beep.play().catch(() => {});
        }
    }

    function getHijriDate() {
        const islamicDate = new Intl.DateTimeFormat('en-TN-u-ca-islamic', {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
            calendar: 'islamic'
        }).format(new Date());

        return islamicDate;
    }

    function updateHijriDate() {
        const hijriElement = document.getElementById('hijriDate');
        if (hijriElement) {
            hijriElement.textContent = 'Hijri: ' + getHijriDate();
        }
    }

    updateHijriDate();
    // Optional: update every day at midnight
    setInterval(updateHijriDate, 60 * 60 * 1000); // every hour (just to be safe)

    // Initialization
    renderPrayerTimes();
    updateClockDate(['clock', 'clock2'], ['date', 'date2']);;
    updateCountdown();
    setInterval(() => {
        updateClockDate(['clock', 'clock2'], ['date', 'date2']);
        updateCountdown();
    }, 1000);

    async function updateWeather() {
        try {
            const response = await fetch('weather-api.php'); // Your PHP endpoint path here
            if (!response.ok) throw new Error('Network response was not ok');
            const data = await response.json();

            if (data.error) {
                console.error('API error:', data.error);
                return;
            }

            // Update the icon
            const iconElem = document.getElementById('weatherIcon');
            if (data.icon) {
                iconElem.src = `https://openweathermap.org/img/wn/${data.icon}@2x.png`;
                iconElem.alt = data.description;
                iconElem.style.display = 'block';
            } else {
                iconElem.style.display = 'none';
            }

            // Update text fields
            document.getElementById('weatherLocation').textContent = data.description;
            document.getElementById('weatherHumidity').textContent = data.humidity + '%';
            document.getElementById('weatherTemp').textContent = data.temp + ' °C';
            document.getElementById('weatherWind').textContent = data.wind_speed + ' km/h';

        } catch (error) {
            console.error('Failed to update weather:', error);
        }
    }

    // Initial call
    updateWeather();

    // Refresh every 10 minutes = 600000 ms
    setInterval(updateWeather, 600000);

    // Poll every 5s for mode changes
    setInterval(() => {
        fetch('config.json?_=' + Date.now())
            .then(r => r.json())
            .then(newConfig => {
                if (newConfig.mode !== currentMode) {
                    currentMode = newConfig.mode;
                    screens = buildScreensFromConfig(newConfig);
                    currentScreen = 0;
                    clearTimeout(timeoutId); // stop waiting, switch now
                    showScreen(currentScreen);
                } else if (newConfig.mode === 'soccer') {
                    sectionEl.innerHTML = getSoccerHTML(newConfig);
                }
            });
    }, 5000);
    </script>

</body>

</html>