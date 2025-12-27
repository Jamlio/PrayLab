<?php
$configFile = __DIR__ . '/config.json';

// Load current config
$config = json_decode(file_get_contents($configFile), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $config['mode'] = $_POST['mode'];
    $config['team_a'] = $_POST['team_a'] ?? $config['team_a'];
    $config['team_b'] = $_POST['team_b'] ?? $config['team_b'];
    $config['team_a_logo'] = $_POST['team_a_logo'] ?? $config['team_a_logo'];
    $config['team_b_logo'] = $_POST['team_b_logo'] ?? $config['team_b_logo'];
    $config['match_time'] = $_POST['match_time'] ?? $config['match_time'];
    $config['stadium'] = $_POST['stadium'] ?? $config['stadium'];
    $config['score_a'] = (int)($_POST['score_a'] ?? $config['score_a']);
    $config['score_b'] = (int)($_POST['score_b'] ?? $config['score_b']);
    $config['status'] = $_POST['status'] ?? $config['status'];
    $config['livestream'] = $_POST['link'] ?? $config['livestream'];
    $config['announcement_title'] = $_POST['announcement_title'] ?? $config['announcement_title'];
    $config['announcement_message'] = $_POST['announcement_message'] ?? $config['announcement_message'];
    $config['announcement_footer'] = $_POST['announcement_footer'] ?? $config['announcement_footer'];
    $config['script_title'] = $_POST['script_title'] ?? $config['script_title'];
    $config['script_weather_api'] = $_POST['script_weather_api'] ?? $config['script_weather_api'];
    $config['script_lat'] = $_POST['script_lat'] ?? $config['script_lat'];
    $config['script_lon'] = $_POST['script_lon'] ?? $config['script_lon'];
    $config['script_timezone'] = $_POST['script_timezone'] ?? $config['script_timezone'];
    $config['script_country'] = $_POST['script_country'] ?? $config['script_country'];

    $prayers = ["Fajr", "Sunrise", "Dhuhr", "Asr", "Maghrib", "Isha"];
    foreach ($prayers as $prayer) {
        $key = "background_$prayer";
        $config['prayer_backgrounds'][$prayer] = $_POST[$key] ?? $config['prayer_backgrounds'][$prayer];
    }
    $config['prayer_backgrounds']['default'] = $_POST['background_default'] ?? $config['prayer_backgrounds']['default'];

    file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT));
    header("Location: control.php?saved=1");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>PrayLab Display Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/prismjs@1/themes/prism-tomorrow.css">
    <script src="https://cdn.jsdelivr.net/npm/prismjs@1/prism.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/prismjs@1/components/prism-json.min.js"></script>
</head>

<body class="bg-gray-950 text-white min-h-screen">
    <div class="max-w-6xl mx-auto px-4 py-6 space-y-6">
        <!-- Header -->
        <header class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 flex items-center justify-center rounded-lg bg-gradient-to-br from-blue-500/20 to-teal-400/8 border border-gray-700 shadow-sm">
                    <i class="ph ph-sliders-horizontal text-blue-300 text-2xl"></i>
                </div>

                <div>
                    <h1 class="text-2xl md:text-3xl font-extrabold leading-tight flex items-center gap-2">
                        <span class="text-blue-300">PrayLab</span>
                        <span class="text-gray-300 font-medium">Manager</span>
                        <span class="ml-2 inline-block text-xs text-gray-300 bg-gray-800/60 px-2 py-0.5 rounded-lg">v<?= $config['script_version'] ?></span>
                    </h1>
                    <div class="text-sm text-gray-400">Admin console — manage displays & settings</div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button onclick="window.location.reload()" title="Reload page"
                    class="px-3 py-2 bg-gray-800/40 hover:bg-gray-800/60 rounded text-sm text-gray-200 transition">🔄 Refresh</button>
                <button onclick="openModal('bugModal')" title="Help & support"
                    class="px-3 py-2 bg-gray-800/40 hover:bg-gray-800/60 rounded text-sm text-gray-200 transition">❓ Help</button>
                <a href="https://www.buymeacoffee.com/legendukas" target="_blank" rel="noopener noreferrer" title="Support development"
                    class="px-3 py-2 bg-gray-800/40 hover:bg-gray-800/60 rounded text-sm text-gray-200 transition">💖 Donate</a>
            </div>
        </header>

        <!-- Responsive Navigation: sidebar (desktop) + top bar (mobile) -->
        <?php
        $modes = [
            'prayer' => ['label' => '🕌 Prayer'],
            'soccer' => ['label' => '⚽ Soccer'],
            'livestream' => ['label' => '📺 Livestream'],
            'announcement' => ['label' => '📢 Announcement'],
            'scriptConfig' => ['label' => '⚙️ Script Configuration']
        ];
        ?>

        <div class="flex gap-6">
            <!-- Sidebar for md+ -->
            <aside class="hidden md:block w-64">
                <div class="sticky top-6 p-4 card bg-gray-900/60">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold">PrayLab</h2>
                        <div class="text-sm text-gray-400">v<?= $config['script_version'] ?></div>
                    </div>
                    <nav class="space-y-2">
                        <?php foreach ($modes as $key => $info): ?>
                        <button data-mode="<?= $key ?>"
                            class="tab-button w-full text-left px-3 py-2 rounded-md text-sm font-medium transition-all duration-150 <?= $config['mode'] === $key ? 'bg-blue-500 text-white' : 'bg-gray-800 text-gray-300 hover:bg-blue-600 hover:text-white' ?>">
                            <?= $info['label'] ?>
                        </button>
                        <?php endforeach; ?>
                    </nav>
                    <div class="mt-4 space-y-2">
                        <button onclick="downloadConfig()"
                            class="w-full bg-gray-800/40 hover:bg-gray-800/60 px-3 py-2 rounded">📥 Export</button>
                        <button onclick="openModal('viewConfigModal')"
                            class="w-full bg-gray-800/40 hover:bg-gray-800/60 px-3 py-2 rounded">🧾 View</button>
                        <label
                            class="w-full block bg-gray-800/40 hover:bg-gray-800/60 px-3 py-2 rounded text-center cursor-pointer">📤
                            Restore<input type="file" id="configUploadSidebar" accept=".json" class="hidden"
                                onchange="restoreConfig(event)"></label>
                    </div>
                </div>
            </aside>

            <div class="flex-1">
                <!-- Mobile top tabs -->
                <nav class="md:hidden sticky top-0 z-10 bg-gray-950 py-2 border-b border-gray-800">
                    <div class="flex flex-wrap gap-2 overflow-x-auto px-1">
                        <?php foreach ($modes as $key => $info): ?>
                        <button data-mode="<?= $key ?>"
                            class="tab-button px-4 py-2 rounded-full text-sm font-medium transition-all duration-200 <?= $config['mode'] === $key ? 'bg-blue-500 text-white' : 'bg-gray-800 text-gray-300 hover:bg-blue-600 hover:text-white' ?>">
                            <?= $info['label'] ?>
                        </button>
                        <?php endforeach; ?>
                    </div>
                </nav>


                <!-- Form Card -->
                <form method="POST" id="configForm" class="bg-gray-900 rounded-xl shadow-lg p-6 space-y-8">
                    <input type="hidden" name="mode" id="modeInput" value="<?= $config['mode'] ?>">

                    <!-- Script Config Section -->
                    <div id="scriptConfigSection" class="mode-section animate-fade">
                        <h2 class="text-xl font-semibold mb-4">⚙️ Script Configuration</h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="card p-4 space-y-3">
                                <h3 class="text-sm font-semibold">General & Location</h3>
                                <div class="space-y-3">
                                    <div class="flex items-center gap-4">
                                        <label class="w-32 text-sm text-gray-300">Display title</label>
                                        <input type="text" name="script_title" placeholder="Display title"
                                            value="<?= $config['script_title'] ?? '' ?>"
                                            class="input flex-1 min-w-0 no-autosave">
                                    </div>

                                    <div class="flex items-center gap-4">
                                        <label class="w-32 text-sm text-gray-300">Timezone</label>
                                        <input type="text" name="script_timezone" placeholder="Timezone"
                                            value="<?= $config['script_timezone'] ?? '' ?>"
                                            class="input flex-1 min-w-0 no-autosave">
                                    </div>

                                    <div class="flex items-center gap-4">
                                        <label class="w-32 text-sm text-gray-300">Country</label>
                                        <input type="text" name="script_country" placeholder="Country code"
                                            value="<?= $config['script_country'] ?? '' ?>"
                                            class="input flex-1 min-w-0 no-autosave">
                                    </div>
                                </div>
                            </div>

                            <div class="card p-4 space-y-3">
                                <h3 class="text-sm font-semibold">Integrations</h3>
                                <div class="space-y-3">
                                    <div class="flex items-center gap-4">
                                        <label class="w-32 text-sm text-gray-300">Weather API</label>
                                        <input type="text" name="script_weather_api" placeholder="API key"
                                            value="<?= $config['script_weather_api'] ?? '' ?>"
                                            class="input flex-1 min-w-0 no-autosave">
                                    </div>

                                    <div class="flex items-center gap-4">
                                        <label class="w-32 text-sm text-gray-300">Latitude</label>
                                        <input type="text" step="any" name="script_lat" placeholder="Latitude"
                                            value="<?= $config['script_lat'] ?? '' ?>"
                                            class="input flex-1 min-w-0 no-autosave">
                                    </div>

                                    <div class="flex items-center gap-4">
                                        <label class="w-32 text-sm text-gray-300">Longitude</label>
                                        <input type="text" step="any" name="script_lon" placeholder="Longitude"
                                            value="<?= $config['script_lon'] ?? '' ?>"
                                            class="input flex-1 min-w-0 no-autosave">
                                    </div>
                                </div>
                                <div class="mt-4 flex gap-2">
                                    <button type="button" onclick="downloadConfig()"
                                        class="px-3 py-2 bg-gray-800/60 hover:bg-gray-800/80 rounded">📥 Export</button>
                                    <button type="button" onclick="openModal('viewConfigModal')"
                                        class="px-3 py-2 bg-gray-800/60 hover:bg-gray-800/80 rounded">🧾 View</button>
                                    <label
                                        class="px-3 py-2 bg-gray-800/40 hover:bg-gray-800/60 rounded cursor-pointer">📤
                                        Restore<input type="file" id="configUpload" accept=".json" class="hidden"
                                            onchange="restoreConfig(event)"></label>
                                </div>
                            </div>
                        </div>

                        <!-- Backgrounds in a separate full-width card -->
                        <div class="card p-4 mt-4">
                            <h3 class="text-sm font-semibold">🌄 Prayer Backgrounds</h3>
                            <div class="mt-3 space-y-3 max-h-60 overflow-auto pr-2">
                                <?php foreach (["Fajr","Sunrise","Dhuhr","Asr","Maghrib","Isha"] as $prayer): ?>
                                <div class="flex items-start md:items-center gap-3">
                                    <div class="w-28 text-sm text-gray-300 pt-2 md:pt-0"><?= $prayer ?></div>
                                    <div class="flex-1 flex gap-2 items-center">
                                        <input type="text" name="background_<?= $prayer ?>"
                                            placeholder="<?= $prayer ?> background URL"
                                            value="<?= $config['prayer_backgrounds'][$prayer] ?? '' ?>"
                                            class="input flex-1 min-w-0 no-autosave">
                                    </div>
                                </div>
                                <?php endforeach; ?>

                                <div class="flex items-start md:items-center gap-3">
                                    <div class="w-28 text-sm text-gray-300 pt-2 md:pt-0">Default</div>
                                    <div class="flex-1 flex gap-2 items-center">
                                        <input type="text" name="background_default"
                                            placeholder="Default background URL"
                                            value="<?= $config['prayer_backgrounds']['default'] ?? '' ?>"
                                            class="input flex-1 min-w-0 no-autosave">
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3 flex gap-2">
                                <button type="button" onclick="downloadPrayerBackgrounds()"
                                    class="px-3 py-2 bg-gray-800/60 hover:bg-gray-800/80 rounded">📥 Download</button>
                                <label class="px-3 py-2 bg-gray-800/40 hover:bg-gray-800/60 rounded cursor-pointer">📤
                                    Upload<input type="file" id="backgroundUpload" accept=".json" class="hidden"
                                        onchange="uploadPrayerBackgrounds(event)"></label>
                            </div>
                        </div>

                        <p class="text-sm text-gray-400 mt-3">These settings affect global script behavior and
                            integrations.</p>
                    </div>

                    <!-- Soccer Section -->
                    <div id="soccerSection" class="mode-section space-y-6 animate-fade">
                        <h2 class="text-xl font-semibold">⚽ Soccer Mode</h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <input type="text" name="team_a" placeholder="Team A Name" value="<?= $config['team_a'] ?>"
                                class="input">
                            <input type="text" name="team_b" placeholder="Team B Name" value="<?= $config['team_b'] ?>"
                                class="input">
                            <input type="text" name="team_a_logo" placeholder="Team A Logo URL"
                                value="<?= $config['team_a_logo'] ?>" class="input">
                            <input type="text" name="team_b_logo" placeholder="Team B Logo URL"
                                value="<?= $config['team_b_logo'] ?>" class="input">
                            <input type="text" name="match_time" placeholder="Match Time"
                                value="<?= $config['match_time'] ?>" class="input">
                            <input type="text" name="stadium" placeholder="Stadium" value="<?= $config['stadium'] ?>"
                                class="input">
                        </div>

                        <input type="text" name="status" placeholder="Match Status" value="<?= $config['status'] ?>"
                            class="input">

                        <!-- Score Controls -->
                        <div class="bg-gray-800 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold mb-4">🎯 Live Mode</h3>
                            <div class="grid grid-cols-2 gap-6 items-center">
                                <?php foreach (['a' => 'Team A', 'b' => 'Team B'] as $key => $label): ?>
                                <div class="flex flex-col items-center">
                                    <span
                                        class="text-sm text-gray-400 mb-1"><?= $config["team_$key"] ?: $label ?></span>
                                    <div class="flex items-center gap-2">
                                        <button type="button" onclick="adjustScore('score_<?= $key ?>', -1)"
                                            class="bg-gray-700 px-3 py-1 rounded hover:bg-red-600">−</button>
                                        <input type="number" name="score_<?= $key ?>" id="score_<?= $key ?>"
                                            value="<?= $config["score_$key"] ?>" class="input w-16 text-center">
                                        <button type="button" onclick="adjustScore('score_<?= $key ?>', 1)"
                                            class="bg-gray-700 px-3 py-1 rounded hover:bg-green-600">+</button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Livestream Section -->
                    <div id="livestreamSection" class="mode-section space-y-4 animate-fade">
                        <h2 class="text-xl font-semibold">📺 Livestream Settings</h2>
                        <input type="text" name="link" placeholder="Embed Link" value="<?= $config['livestream'] ?>"
                            class="input">

                        <div class="mt-4 p-4 rounded-lg bg-gradient-to-r from-gray-800 via-gray-900 to-gray-800 border border-gray-700 shadow-md">
                            <h3 class="text-lg font-semibold text-white">Autoplay & Controls Information</h3>
                            <p class="mt-2 text-sm text-gray-300">
                                Many embed providers require specific URL query parameters to start playback automatically.
                                To enable autoplay without user interaction, append the following parameters to the embed URL:
                            </p>
                            <pre
                                class="mt-3 bg-gray-900 p-3 rounded-md text-xs text-yellow-300 overflow-auto border border-gray-700">?autoplay=1&amp;controls=0&amp;playsinline=1</pre>
                            <p class="mt-3 text-sm text-gray-400">
                                If these parameters are not included, the browser may block autoplay, requiring the viewer to manually start the stream.
                            </p>
                        </div>
                    </div>

                    <!-- Announcement Section -->
                    <div id="announcementSection" class="mode-section space-y-4 animate-fade">
                        <h2 class="text-xl font-semibold">📢 Announcement Mode</h2>
                        <input type="text" name="announcement_title" placeholder="Title"
                            value="<?= $config['announcement_title'] ?>" class="input">
                        <input type="text" name="announcement_message" placeholder="Message"
                            value="<?= $config['announcement_message'] ?>" class="input">
                        <input type="text" name="announcement_footer" placeholder="Footer"
                            value="<?= $config['announcement_footer'] ?>" class="input">
                    </div>

                    <!-- Prayer Section -->
                    <div id="prayerSection" class="mode-section space-y-4 animate-fade">
                        <h2 class="text-xl font-semibold">🕌 Prayer Mode</h2>
                        <p class="text-gray-400">No settings needed. Display will switch to prayer times.</p>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end gap-4 pt-4 border-t border-gray-700">
                        <button type="reset"
                            class="bg-gray-700 hover:bg-gray-600 px-6 py-2 rounded text-white font-semibold">🔄
                            Reset</button>
                        <button type="submit"
                            class="bg-blue-500 hover:bg-blue-600 px-6 py-2 rounded text-white font-semibold">💾
                            Save</button>
                    </div>
                </form>

                <!-- Script Info Section -->
                <section class="bg-gray-900 rounded-xl shadow-lg p-6 space-y-6 mt-10">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-semibold flex items-center gap-2">
                            <i class="ph ph-info text-blue-400"></i> Script Info & Support
                        </h2>
                        <div class="text-sm text-gray-400">Helpful links and status at a glance</div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Version & Changelog -->
                        <div class="info-card p-4 rounded-lg" id="versionCard">
                            <div class="flex items-start justify-between">
                                <div>
                                    <div class="card-title">📌 Version</div>
                                    <div class="text-sm text-gray-300 mt-1">Current: <strong>v<?= $config['script_version'] ?></strong></div>
                                    <div class="text-xs text-gray-400 mt-1">Last Updated: <span><?= $config['script_updated_at'] ?></span></div>
                                </div>
                                <div class="flex flex-col items-end gap-2">
                                    <div id="updateNotice" class="text-xs text-yellow-400 hidden">New update available</div>
                                    <div class="flex gap-2">
                                        <button id="updateButton" onclick="runUpdate()" class="action-btn hidden">🔄 Update</button>
                                        <button onclick="openModal('changelogModal')" class="action-btn bg-transparent text-blue-400">🗒️ Changelog</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bug Report -->
                        <div class="info-card p-4 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="card-title">🐞 Report a Bug</div>
                                    <div class="text-sm text-gray-300 mt-1">Found something broken or weird?</div>
                                </div>
                                <button onclick="openModal('bugModal')" class="action-btn text-red-400">Send</button>
                            </div>
                        </div>

                        <!-- Feature Request -->
                        <div class="info-card p-4 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="card-title">💡 Suggest a Feature</div>
                                    <div class="text-sm text-gray-300 mt-1">Share ideas to improve this panel.</div>
                                </div>
                                <button onclick="openModal('featureModal')" class="action-btn text-yellow-400">Submit</button>
                            </div>
                        </div>

                        <!-- Donate -->
                        <div class="info-card p-4 rounded-lg">
                            <div class="flex items-start justify-between">
                                <div>
                                    <div class="card-title">💖 Support Development</div>
                                    <div class="text-sm text-gray-300 mt-1">Help keep this project alive and evolving.</div>
                                </div>
                                <div class="flex items-center">
                                    <a href="https://www.buymeacoffee.com/legendukas" target="_blank" rel="noopener noreferrer" class="donate-btn" title="Buy me a coffee">
                                        <span class="text-yellow-300">☕</span>
                                        <span class="ml-2">Support</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Update Manager -->
                        <div class="info-card p-4 rounded-lg">
                            <div class="flex items-start justify-between">
                                <div>
                                    <div class="card-title">🔄 Update Manager</div>
                                    <div class="text-sm text-gray-300 mt-1">Check for updates and apply the latest version.</div>
                                </div>
                                <div class="flex flex-col items-end gap-2">
                                    <button onclick="runUpdate()" class="action-btn">Check & Update</button>
                                </div>
                            </div>
                        </div>

                        <!-- Server & API Status -->
                        <div class="info-card p-4 rounded-lg" id="statusCard">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="card-title">📡 System Status</div>
                                    <div class="text-sm text-gray-300 mt-2">
                                        <div class="flex items-center gap-2"><span class="status-dot warn"></span> Update Server: <span id="updateServerStatus" class="ml-2 text-yellow-400">Checking...</span></div>
                                        <div class="flex items-center gap-2 mt-1"><span class="status-dot warn"></span> Weather API: <span id="weatherApiStatus" class="ml-2 text-yellow-400">Checking...</span></div>
                                        <div class="flex items-center gap-2 mt-1"><span class="status-dot warn"></span> Local Server: <span id="localServerStatus" class="ml-2 text-yellow-400">Checking...</span></div>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end gap-2">
                                    <button onclick="checkStatus && checkStatus()" class="action-btn">Refresh</button>
                                    <div class="text-xs text-gray-400" id="lastChecked">Last checked: ...</div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="text-center text-xs text-gray-500 pt-4 border-t border-gray-700">
                        Made with ❤️ by APR • PrayLab Display Manager v<?= $config['script_version'] ?>
                    </div>
                </section>

            </div>

            <!-- Overlay -->
            <div id="modalOverlay" class="fixed inset-0 bg-black/60 z-40 hidden"></div>
            <!-- Update Overlay -->
            <div id="updateOverlay" class="fixed inset-0 bg-black/70 flex items-center justify-center z-50 hidden">
                <div class="w-full max-w-xl mx-4 bg-gradient-to-br from-gray-900/80 to-gray-800/80 backdrop-blur-md rounded-2xl shadow-2xl border border-gray-700 p-6 text-white">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 flex items-center justify-center rounded-full bg-gradient-to-tr from-blue-500 to-blue-400 shadow-lg animate-pulse">
                            <!-- subtle animated icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 3v1m0 16v1m8-9h1M3 12H2m15.536-6.536l.707-.707M5.757 18.243l-.707.707M18.243 18.243l.707.707M5.757 5.757l-.707-.707" />
                            </svg>
                        </div>

                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <h2 class="text-xl font-semibold text-blue-300">🔄 Updating Script</h2>
                                <div class="text-sm text-gray-400" id="updatePercent">0%</div>
                            </div>
                            <p id="updateStatus" class="text-sm text-gray-300 mt-1">Preparing update...</p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div class="w-full h-3 bg-gray-800 rounded overflow-hidden">
                            <div id="updateProgress" class="h-full rounded bg-gradient-to-r from-blue-500 to-teal-400 transition-all duration-500" style="width:0%"></div>
                        </div>
                        <div class="flex items-center justify-between mt-2">
                            <div class="text-xs text-gray-400" id="updateETA">Estimated time: —</div>
                            <div class="text-xs text-gray-400" id="updateSpeed"></div>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3">
                        <button id="cancelUpdateBtn" onclick="cancelUpdate()" class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 rounded text-white text-sm">❌ Cancel</button>
                    </div>

                    <div id="updateLog" class="mt-4 max-h-56 overflow-auto bg-gray-900/60 p-3 rounded-lg text-xs text-gray-300">
                        <div id="updateLogInner">No logs yet. Click "Cancel" to stop the update.</div>
                    </div>
                </div>
            </div>

            <!-- Changelog Modal -->
            <div id="changelogModal" class="fixed inset-0 flex items-center justify-center z-50 hidden">
                <div class="bg-gray-900/95 rounded-xl shadow-2xl p-6 w-full max-w-3xl relative overflow-hidden">
                    <button onclick="closeModal()" class="absolute top-4 right-4 text-gray-400 hover:text-white text-xl">✕</button>

                    <div class="flex items-center justify-between mb-4 gap-4">
                        <h2 class="text-xl font-bold text-blue-400 flex items-center gap-2">📌 Changelog</h2>
                        <div class="flex items-center gap-3">
                            <div id="changelogSpinner" class="w-8 h-8 flex items-center justify-center">
                                <!-- simple spinner (hidden when not loading) -->
                                <svg class="animate-spin text-blue-400 w-6 h-6 hidden" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                </svg>
                            </div>
                            <div class="text-sm text-gray-400" id="changelogCount">—</div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <input type="text" id="changelogSearch" placeholder="Search version or text..." class="input w-full no-autosave" />
                    </div>

                    <div id="changelogContent" class="grid grid-cols-1 gap-3 auto-rows-min overflow-y-auto p-1" style="max-height:70vh"></div>

                    <div id="changelogFooter" class="mt-4 text-xs text-gray-400 text-center">Loaded entries will appear as cards for easier scanning.</div>
                </div>
            </div>

            <!-- Bug Report Modal -->
            <div id="bugModal" class="fixed inset-0 flex items-center justify-center z-50 hidden">
                <div class="bg-gray-900 rounded-xl shadow-xl p-6 w-full max-w-lg space-y-4 relative">
                    <button onclick="closeModal()"
                        class="absolute top-4 right-4 text-gray-400 hover:text-white text-xl">✕</button>
                    <h2 class="text-xl font-bold text-red-400">🐞 Report a Bug</h2>
                    <form onsubmit="submitBug(event)" class="space-y-4">
                        <input type="text" id="bugTitle" placeholder="Bug Title" required class="input no-autosave">
                        <textarea id="bugDetails" placeholder="Describe the issue..." required
                            class="input no-autosave h-32 resize-none"></textarea>
                        <button type="submit"
                            class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded text-white font-semibold">Submit</button>
                    </form>
                </div>
            </div>

            <!-- Feature Request Modal -->
            <div id="featureModal" class="fixed inset-0 flex items-center justify-center z-50 hidden">
                <div class="bg-gray-900 rounded-xl shadow-xl p-6 w-full max-w-lg space-y-4 relative">
                    <button onclick="closeModal()"
                        class="absolute top-4 right-4 text-gray-400 hover:text-white text-xl">✕</button>
                    <h2 class="text-xl font-bold text-yellow-400">💡 Suggest a Feature</h2>
                    <form onsubmit="submitFeature(event)" class="space-y-4">
                        <input type="text" id="featureTitle" placeholder="Feature Title" required
                            class="input no-autosave">
                        <textarea id="featureDetails" placeholder="Describe your idea..." required
                            class="input no-autosave h-32 resize-none"></textarea>
                        <button type="submit"
                            class="bg-yellow-500 hover:bg-yellow-600 px-4 py-2 rounded text-white font-semibold">Submit</button>
                    </form>
                </div>
            </div>

            <!-- View Config Modal -->
            <div id="viewConfigModal" class="fixed inset-0 flex items-center justify-center z-50 hidden">
                <div
                    class="bg-gray-900 rounded-xl shadow-xl p-6 w-full max-w-2xl space-y-4 relative overflow-y-auto max-h-[90vh]">
                    <button onclick="closeModal()"
                        class="absolute top-4 right-4 text-gray-400 hover:text-white text-xl">✕</button>
                    <h2 class="text-xl font-bold text-blue-400">🧾 config.json Preview</h2>
                    <pre class="language-json bg-gray-800 p-4 rounded-lg overflow-x-auto max-h-[60vh]">
                <code id="configPreview" class="language-json text-sm text-gray-300 whitespace-pre-wrap"></code>
            </pre>
                    <button onclick="copyConfig()"
                        class="bg-gray-700 hover:bg-gray-600 px-4 py-2 rounded text-white text-sm font-semibold">
                        📋 Copy to Clipboard
                    </button>
                </div>
            </div>

            <style>
            .input {
                width: 100%;
                padding: 0.75rem;
                border-radius: 0.5rem;
                background-color: #1f2937;
                border: 1px solid #374151;
                transition: border-color 0.2s ease, box-shadow 0.2s ease;
            }

            .input:focus {
                outline: none;
                border-color: #3b82f6;
                box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
            }

            .mode-section {
                display: none;
            }

            .animate-fade {
                animation: fade 0.3s ease-out;
            }

            @keyframes fade {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            /* Changelog card entrance animation */
            .card-animate {
                opacity: 0;
                transform: translateY(8px) scale(0.995);
                animation: card-in 400ms cubic-bezier(.16,.84,.44,1) forwards;
            }

            @keyframes card-in {
                to {
                    opacity: 1;
                    transform: translateY(0) scale(1);
                }
            }

            /* Styled (thin) scrollbar for changelog content */
            #changelogContent {
                scrollbar-width: thin; /* Firefox */
                scrollbar-color: rgba(255,255,255,0.06) transparent;
            }
            #changelogContent::-webkit-scrollbar { width: 8px; height: 8px; }
            #changelogContent::-webkit-scrollbar-track { background: transparent; }
            #changelogContent::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.06); border-radius: 8px; }

            /* Optional: hide large scrollbars on other overlay panels */
            #updateLog::-webkit-scrollbar { width: 6px; }
            #updateLog::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.04); border-radius: 6px; }

            /* Header tweaks */
            header h1 .version-pill {
                background: rgba(31, 41, 55, 0.5);
                padding: 2px 8px;
                border-radius: 8px;
                font-weight: 600;
                font-size: 0.75rem;
            }

            header .w-12 { border: 1px solid rgba(255,255,255,0.03); }

            /* Make the top action buttons slightly translucent */
            header .bg-gray-800\/40 { background-color: rgba(31,41,55,0.35); }

            /* Script Info cards */
            .info-card { background: linear-gradient(180deg, rgba(31,41,55,0.55), rgba(17,24,39,0.45)); border: 1px solid rgba(255,255,255,0.03); box-shadow: 0 4px 10px rgba(2,6,23,0.6); }
            .card-title { font-weight: 700; font-size: 1rem; color: #e6eefc; }
            .action-btn { background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.03); padding: 6px 10px; border-radius: 8px; font-size: 0.85rem; color: #dbeafe; transition: transform 0.12s ease, background 0.12s ease; }
            .action-btn:hover { transform: translateY(-2px); background: rgba(255,255,255,0.04); }
            .donate-btn { display: inline-flex; align-items: center; gap: 6px; padding: 6px 10px; border-radius: 10px; background: linear-gradient(90deg,#b45309,#f59e0b); color: #071135; font-weight:700; box-shadow: 0 6px 18px rgba(245,158,11,0.12); }
            .status-dot { width:10px; height:10px; border-radius:50%; display:inline-block; background: rgba(255,255,255,0.06); box-shadow: 0 0 6px rgba(0,0,0,0.6) inset; }
            .status-dot.ok { background: #10b981; }
            .status-dot.warn { background: #f59e0b; }
            .status-dot.err { background: #ef4444; }
            </style>

            <script>
            const modeInput = document.getElementById('modeInput');
            const tabButtons = document.querySelectorAll('.tab-button');
            const sections = {
                prayer: document.getElementById('prayerSection'),
                soccer: document.getElementById('soccerSection'),
                livestream: document.getElementById('livestreamSection'),
                announcement: document.getElementById('announcementSection'),
                scriptConfig: document.getElementById('scriptConfigSection')
            };

            function showSection(mode) {
                Object.keys(sections).forEach(key => {
                    sections[key].style.display = key === mode ? 'block' : 'none';
                });
                modeInput.value = mode;
                tabButtons.forEach(btn => {
                    btn.classList.remove('bg-blue-500');
                    if (btn.dataset.mode === mode) btn.classList.add('bg-blue-500');
                });
            }

            function adjustScore(id, delta) {
                const input = document.getElementById(id);
                let value = parseInt(input.value) || 0;
                value = Math.max(0, value + delta); // prevent negative scores
                input.value = value;
                input.classList.add('ring-2', 'ring-green-500');
                setTimeout(() => input.classList.remove('ring-2', 'ring-green-500'), 300);

                // Manually trigger input event for auto-save
                const event = new Event('input', {
                    bubbles: true
                });
                input.dispatchEvent(event);
            }


            function autoSave() {
                const form = document.getElementById('configForm');
                const formData = new FormData(form);

                fetch('control.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(() => showToast("✅ Changes saved"))
                    .catch(() => showToast("⚠️ Save failed"));
            }

            function showToast(message) {
                const toast = document.createElement('div');
                toast.textContent = message;
                toast.className =
                    "fixed bottom-6 right-6 bg-green-600 text-white px-4 py-2 rounded shadow-lg animate-fade z-50";
                document.body.appendChild(toast);
                setTimeout(() => toast.remove(), 3000);
            }

            function openModal(id) {
                document.getElementById('modalOverlay').style.display = 'block';
                const modal = document.getElementById(id);
                if (modal) modal.style.display = 'flex';

                if (id === 'changelogModal') {
                    loadChangelog();
                }

                if (id === 'viewConfigModal') {
                    fetch('config.json')
                        .then(res => res.ok ? res.text() : Promise.reject())
                        .then(text => {
                            const codeBlock = document.getElementById('configPreview');
                            codeBlock.textContent = text;
                            Prism.highlightElement(codeBlock);
                        })
                        .catch(() => {
                            document.getElementById('configPreview').textContent = "⚠️ Unable to load config.json";
                        });
                }
            }

            function closeModal() {
                document.getElementById('modalOverlay').style.display = 'none';
                document.getElementById('changelogModal').style.display = 'none';
                document.getElementById('bugModal').style.display = 'none';
                document.getElementById('featureModal').style.display = 'none';
                document.getElementById('viewConfigModal').style.display = 'none';
                document.getElementById('introModal').style.display = 'none';
            }

            function submitBug(e) {
                e.preventDefault();

                // Show a coming soon toast
                showToast("🚧 Bug reporting will be available in the next update!");

                // Optionally clear the form
                document.getElementById('bugTitle').value = '';
                document.getElementById('bugDetails').value = '';

                // Close the modal
                closeModal();
            }

            function submitFeature(e) {
                e.preventDefault();

                // Show a coming soon toast
                showToast("🚧 Feutures requesting will be available in the next update!");

                // Close the modal
                closeModal();
            }

            let changelogData = [];
            let changelogRenderTimer = null;
            const CHUNK_SIZE = 8; // entries per render chunk

             function loadChangelog() {
                const spinner = document.querySelector('#changelogSpinner svg');
                const countEl = document.getElementById('changelogCount');
                const content = document.getElementById('changelogContent');
                content.innerHTML = '';
                if (spinner) spinner.classList.remove('hidden');
                if (countEl) countEl.textContent = 'Loading...';

                fetch('changelog.json')
                    .then(res => res.json())
                    .then(data => {
                        changelogData = data || [];
                        if (spinner) spinner.classList.add('hidden');
                        if (countEl) countEl.textContent = `${changelogData.length} entries`;
                        // incremental rendering to avoid blocking UI
                        renderChangelogIncremental(changelogData);
                    })
                    .catch(() => {
                        if (spinner) spinner.classList.add('hidden');
                        if (countEl) countEl.textContent = 'Failed to load';
                        content.innerHTML = '<div class="text-sm text-red-400 p-3">Unable to load changelog.</div>';
                    });
             }

            function renderChangelogIncremental(data) {
                const container = document.getElementById('changelogContent');
                container.innerHTML = '';
                let idx = 0;

                function renderChunk() {
                    const chunk = data.slice(idx, idx + CHUNK_SIZE);
                    chunk.forEach((entry, ci) => {
                        const card = document.createElement('div');
                        card.className = 'bg-gray-800 p-4 rounded-lg shadow-sm card-animate';
                        // stagger entrance
                        const delay = (idx + ci) * 30; // ms
                        card.style.animationDelay = delay + 'ms';

                        const header = document.createElement('div');
                        header.className = 'flex items-center justify-between gap-3';
                        header.innerHTML = `<div class="font-semibold">${escapeHtml(entry.version)}</div><div class="text-xs text-gray-400">${escapeHtml(entry.date)}</div>`;
                        card.appendChild(header);

                        const list = document.createElement('ul');
                        list.className = 'mt-2 text-sm space-y-1 text-gray-300';
                        for (const [type, items] of Object.entries(entry.changes)) {
                            items.forEach(item => {
                                const li = document.createElement('li');
                                li.innerHTML = `<span class="font-semibold text-${getColor(type)}-400">${escapeHtml(type)}:</span> ${escapeHtml(item)}`;
                                list.appendChild(li);
                            });
                        }
                        card.appendChild(list);

                        if (entry.latest) {
                            const badge = document.createElement('div');
                            badge.className = 'mt-2 inline-block px-2 py-1 text-xs rounded bg-blue-500 text-white';
                            badge.textContent = 'Latest';
                            card.appendChild(badge);
                        }

                        container.appendChild(card);
                    });

                    idx += CHUNK_SIZE;
                    // schedule next chunk if any
                    if (idx < data.length) {
                        changelogRenderTimer = setTimeout(renderChunk, 50);
                    }
                }

                renderChunk();
            }

            function getColor(type) {
                return {
                    New: 'green',
                    Improved: 'yellow',
                    Added: 'blue',
                    Fixed: 'gray'
                } [type] || 'white';
            }

            function debounce(fn, wait) {
                let t;
                return function(...args) {
                    clearTimeout(t);
                    t = setTimeout(() => fn.apply(this, args), wait);
                };
            }

            document.getElementById('changelogSearch').addEventListener('input', debounce(e => {
                const term = e.target.value.toLowerCase().trim();
                if (!term) {
                    renderChangelogIncremental(changelogData);
                    document.getElementById('changelogCount').textContent = `${changelogData.length} entries`;
                    return;
                }

                const filtered = changelogData.filter(entry => {
                    if (entry.version.toLowerCase().includes(term)) return true;
                    return Object.values(entry.changes).flat().some(change => change.toLowerCase().includes(term));
                });

                document.getElementById('changelogCount').textContent = `${filtered.length} entries`;
                renderChangelogIncremental(filtered);
            }, 250));

            document.querySelectorAll('.input:not(.no-autosave)').forEach(input => {
                input.addEventListener('change', autoSave);
            });

            let saveTimeout;
            document.querySelectorAll('.input:not(.no-autosave)').forEach(input => {
                input.addEventListener('input', () => {
                    clearTimeout(saveTimeout);
                    saveTimeout = setTimeout(autoSave, 1000);
                });
            });

            function downloadConfig() {
                fetch('config.json')
                    .then(response => {
                        if (!response.ok) throw new Error("Failed to load config");
                        return response.blob();
                    })
                    .then(blob => {
                        const url = URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = 'config.json';
                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                        URL.revokeObjectURL(url);
                    })
                    .catch(() => {
                        showToast("⚠️ Unable to download config.json");
                    });
            }

            function restoreConfig(event) {
                const file = event.target.files[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = function(e) {
                    try {
                        const json = JSON.parse(e.target.result);
                        fetch('restore.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify(json)
                            })
                            .then(res => res.ok ? showToast("✅ Config restored") : showToast("❌ Restore failed"))
                            .catch(() => showToast("❌ Restore failed"));
                    } catch {
                        showToast("⚠️ Invalid JSON file");
                    }
                };
                reader.readAsText(file);
            }

            function copyConfig() {
                const code = document.getElementById('configPreview').textContent;
                navigator.clipboard.writeText(code)
                    .then(() => showToast("✅ Config copied to clipboard"))
                    .catch(() => showToast("⚠️ Failed to copy config"));
            }

            function downloadPrayerBackgrounds() {
                fetch('config.json')
                    .then(res => res.ok ? res.json() : Promise.reject())
                    .then(data => {
                        const backgrounds = data.prayer_backgrounds || {};
                        const blob = new Blob([JSON.stringify(backgrounds, null, 2)], {
                            type: 'application/json'
                        });
                        const url = URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = 'prayer_backgrounds.json';
                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                        URL.revokeObjectURL(url);
                    })
                    .catch(() => showToast("⚠️ Unable to download backgrounds"));
            }

            function uploadPrayerBackgrounds(event) {
                const file = event.target.files[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = function(e) {
                    try {
                        const json = JSON.parse(e.target.result);
                        fetch('restore_backgrounds.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify(json)
                            })
                            .then(res => res.ok ? showToast("✅ Backgrounds restored") : showToast(
                                "❌ Restore failed"))
                            .catch(() => showToast("❌ Restore failed"));
                    } catch {
                        showToast("⚠️ Invalid JSON file");
                    }
                };
                reader.readAsText(file);
            }

            // --------------- Updating functions ----------------
            let updateCancelled = false;
            let extractionStarted = false;
            let updateStartTime = null;

            function runUpdate() {
                if (updateCancelled) return;

                const overlay = document.getElementById('updateOverlay');
                overlay.classList.remove('hidden');

                if (!updateStartTime) updateStartTime = Date.now();

                updateStatus("🔍 Checking for updates...");
                appendLog('Checking for updates...');
                updateProgress(5);
                updateETA(5);

                fetch('update.php?step=check')
                    .then(res => res.json())
                    .then(data => {
                        if (updateCancelled) return;

                        if (data.status === 'ok') {
                            updateStatus(data.message);
                            appendLog('No update available: ' + data.message);
                            updateProgress(100);
                            updateETA(100);
                            setTimeout(() => overlay.classList.add('hidden'), 2000);
                            return;
                        }

                        if (data.status === 'update') {
                            updateStatus("📥 Update available — starting download...");
                            appendLog('Update found: starting download');
                            updateProgress(25);
                            updateETA(25);

                            fetch('update.php?step=download')
                                .then(res => res.json())
                                .then(data => {
                                    if (updateCancelled) return;

                                    if (data.status === 'error') {
                                        updateStatus('❌ Download error: ' + data.message);
                                        appendLog('Download error: ' + data.message);
                                        updateProgress(0);
                                        updateETA(0);
                                        setTimeout(() => overlay.classList.add('hidden'), 3000);
                                        return;
                                    }

                                    // Show download message and speed
                                    if (data.speed && data.speed.mbps !== undefined) {
                                        updateStatus(`${data.message} 🚀 Speed: ${data.speed.mbps} MB/s`);
                                        appendLog(`Download completed — speed: ${data.speed.mbps} MB/s`);
                                    } else {
                                        updateStatus(data.message);
                                        appendLog('Download completed');
                                    }

                                    updateProgress(data.progress);
                                    updateETA(data.progress);

                                    updateStatus("📦 Extracting update...");
                                    appendLog('Starting extraction');
                                    updateProgress(60);
                                    updateETA(60);
                                    extractionStarted = true;
                                    disableCancelButton();

                                    fetch('update.php?step=extract')
                                        .then(res => res.json())
                                        .then(data => {
                                            if (updateCancelled) return;

                                            updateStatus(data.message);
                                            appendLog('Extraction: ' + data.message);
                                            updateProgress(data.progress);
                                            updateETA(data.progress);

                                            if (data.status === 'ok') {
                                                appendLog('Update completed. Reloading...');
                                                updateProgress(100);
                                                setTimeout(() => location.reload(), 2000);
                                            } else {
                                                appendLog('Extraction failed: ' + (data.message || 'unknown'));
                                                setTimeout(() => overlay.classList.add('hidden'), 3000);
                                            }
                                        });
                                });
                        }
                    })
                    .catch(() => {
                        if (updateCancelled) return;
                        updateStatus("❌ Update failed. Please try again.");
                        appendLog('Network or server error during update.');
                        updateProgress(0);
                        updateETA(0);
                        setTimeout(() => overlay.classList.add('hidden'), 3000);
                    });
            }

            function appendLog(text) {
                const log = document.getElementById('updateLogInner');
                if (!log) return;
                const time = new Date().toLocaleTimeString();
                const line = document.createElement('div');
                line.innerHTML = `<span class="text-gray-400">[${time}]</span> ${escapeHtml(text)}`;
                log.prepend(line);
            }

            function updateETA(currentProgress) {
                const etaElement = document.getElementById('updateETA');

                if (currentProgress <= 0 || currentProgress >= 100) {
                    etaElement.textContent = "Estimated time: —";
                    return;
                }

                const elapsed = (Date.now() - updateStartTime) / 1000; // seconds
                const estimatedTotal = (elapsed / currentProgress) * 100;
                const remaining = estimatedTotal - elapsed;

                const minutes = Math.floor(remaining / 60);
                const seconds = Math.floor(remaining % 60);
                etaElement.textContent = `Estimated time: ${minutes}m ${seconds}s remaining`;
            }

            function cancelUpdate() {
                if (extractionStarted) return;

                const confirmCancel = confirm("Are you sure you want to cancel the update?");
                if (!confirmCancel) return;

                updateCancelled = true;
                updateStatus("⏹️ Update cancelled.");
                updateProgress(0);
                updateETA(0);
                setTimeout(() => {
                    document.getElementById('updateOverlay').classList.add('hidden');
                    updateCancelled = false;
                    updateStartTime = null;
                }, 1500);
            }

            function disableCancelButton() {
                const btn = document.getElementById('cancelUpdateBtn');
                if (!btn) return;
                btn.disabled = true;
                btn.classList.add('opacity-50', 'cursor-not-allowed');
                btn.textContent = "🔒 Cannot cancel now";
            }

            function updateStatus(text) {
                const el = document.getElementById('updateStatus');
                const log = document.getElementById('updateLogInner');
                if (el) el.textContent = text;
                if (log) {
                    const time = new Date().toLocaleTimeString();
                    log.innerHTML = `<div>[${time}] ${escapeHtml(text)}</div>` + log.innerHTML;
                }
            }

            function updateProgress(percent, speedText) {
                const bar = document.getElementById('updateProgress');
                const percentEl = document.getElementById('updatePercent');
                const speedEl = document.getElementById('updateSpeed');
                if (bar) bar.style.width = Math.max(0, Math.min(100, percent)) + '%';
                if (percentEl) percentEl.textContent = Math.round(percent) + '%';
                if (speedEl) speedEl.textContent = speedText || '';
            }

            function escapeHtml(str) {
                return String(str).replace(/[&<>"]+/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[s]));
            }

            function toggleUpdateLog() {
                const log = document.getElementById('updateLog');
                if (!log) return;
                log.classList.toggle('hidden');
            }

            tabButtons.forEach(btn => {
                btn.addEventListener('click', () => showSection(btn.dataset.mode));
            });

            // Initialize
            showSection(modeInput.value);

            document.addEventListener('DOMContentLoaded', () => {
                setTimeout(checkForUpdate, 1000); // Delay to avoid blocking initial render
            });

            function checkForUpdate() {
                fetch('https://pray-lab.vercel.app/latest-version.json')
                    .then(res => res.ok ? res.json() : Promise.reject())
                    .then(info => {
                        const currentVersion = "<?= $config['script_version'] ?>";
                        if (info.version && info.zip && compareVersions(currentVersion, info.version) < 0) {
                            document.getElementById('updateNotice').textContent =
                                `🚀 New version available: v${info.version}`;
                            document.getElementById('updateNotice').classList.remove('hidden');
                            document.getElementById('updateButton').classList.remove('hidden');
                        }
                    })
                    .catch(() => {
                        console.log("No update info available.");
                    });
            }

            // Simple version comparison
            function compareVersions(a, b) {
                const pa = a.split('.').map(Number);
                const pb = b.split('.').map(Number);
                for (let i = 0; i < Math.max(pa.length, pb.length); i++) {
                    const na = pa[i] || 0;
                    const nb = pb[i] || 0;
                    if (na < nb) return -1;
                    if (na > nb) return 1;
                }
                return 0;
            }
            let lastCheckedTime = null;
            window.addEventListener('DOMContentLoaded', () => {
                checkStatus('https://pray-lab.vercel.app/latest-version.json', 'updateServerStatus');
                checkWeatherApiStatus('https://api.openweathermap.org/data/2.5/weather?q=London');
                checkLocalServerStatus();
                lastCheckedTime = new Date();
                updateLastCheckedLive();
                setInterval(updateLastCheckedLive, 1000); // Update every second
            });

            function checkStatus(url, elementId) {
                fetch(url, {
                        method: 'HEAD'
                    })
                    .then(res => {
                        const status = res.ok ? '✅ Online' : `⚠️ ${res.status}`;
                        document.getElementById(elementId).textContent = status;
                        document.getElementById(elementId).className = res.ok ? 'text-green-400' : 'text-red-400';
                    })
                    .catch(() => {
                        document.getElementById(elementId).textContent = '❌ Offline';
                        document.getElementById(elementId).className = 'text-red-400';
                    });
            }

            function checkWeatherApiStatus(url) {
                fetch(url, {
                        method: 'GET'
                    })
                    .then(res => {
                        // Treat any response as "Online"
                        document.getElementById('weatherApiStatus').textContent = '✅ Online';
                        document.getElementById('weatherApiStatus').className = 'text-green-400';
                    })
                    .catch(() => {
                        document.getElementById('weatherApiStatus').textContent = '❌ Offline';
                        document.getElementById('weatherApiStatus').className = 'text-red-400';
                    });
            }

            function checkLocalServerStatus() {
                fetch(window.location.href, {
                        method: 'HEAD'
                    })
                    .then(res => {
                        const status = res.ok ? '✅ Online' : `⚠️ ${res.status}`;
                        document.getElementById('localServerStatus').textContent = status;
                        document.getElementById('localServerStatus').className = res.ok ? 'text-green-400' :
                            'text-red-400';
                    })
                    .catch(() => {
                        document.getElementById('localServerStatus').textContent = '❌ Offline';
                        document.getElementById('localServerStatus').className = 'text-red-400';
                    });
            }

            function updateLastCheckedLive() {
                if (!lastCheckedTime) return;

                const now = new Date();
                const diffMs = now - lastCheckedTime;
                const diffSec = Math.floor(diffMs / 1000);
                const diffMin = Math.floor(diffSec / 60);
                const diffHr = Math.floor(diffMin / 60);

                let text = '';
                if (diffSec < 10) text = 'Just now';
                else if (diffSec < 60) text = `${diffSec} seconds ago`;
                else if (diffMin < 60) text = `${diffMin} minutes ago`;
                else if (diffHr < 24) text = `${diffHr} hours ago`;
                else text = lastCheckedTime.toLocaleString();

                document.getElementById('lastChecked').textContent = `Last checked: ${text}`;
            }
            </script>
</body>

</html>