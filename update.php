<?php
header('Content-Type: application/json');

$step = $_GET['step'] ?? 'check';
$configFile = __DIR__ . '/config.json';
$zipFile = __DIR__ . '/update.zip';
$infoUrl = 'https://pray-lab.vercel.app/latest-version.json';

// Load config and update info
$config = json_decode(@file_get_contents($configFile), true);
$info = json_decode(@file_get_contents($infoUrl), true);

// Validate metadata
if (!$config || !$info || !isset($info['version'], $info['zip'])) {
    echo json_encode([
        'status' => 'error',
        'message' => '❌ Invalid update metadata',
        'progress' => 0,
        'stage' => 'check'
    ]);
    exit;
}

switch ($step) {
    case 'check':
        if (version_compare($config['script_version'], $info['version'], '>=')) {
            echo json_encode([
                'status' => 'ok',
                'message' => '✅ Already up to date',
                'progress' => 100,
                'stage' => 'check'
            ]);
        } else {
            echo json_encode([
                'status' => 'update',
                'message' => '📥 Update available',
                'progress' => 10,
                'stage' => 'check',
                'version' => $info['version'],
                'zip' => $info['zip']
            ]);
        }
        break;

    case 'download':
        $startTime = microtime(true);
        $zipUrl = $info['zip'];
        $zipFile = __DIR__ . '/update.zip';

        $readStream = @fopen($zipUrl, 'rb');
        $writeStream = @fopen($zipFile, 'wb');

        if (!$readStream || !$writeStream) {
            echo json_encode([
                'status' => 'error',
                'message' => '❌ Unable to open streams',
                'progress' => 30,
                'stage' => 'download'
            ]);
            exit;
        }

        $totalBytes = 0;
        while (!feof($readStream)) {
            $chunk = fread($readStream, 8192); // 8KB chunks
            fwrite($writeStream, $chunk);
            $totalBytes += strlen($chunk);
        }

        fclose($readStream);
        fclose($writeStream);

        $endTime = microtime(true);
        $duration = $endTime - $startTime;

        $speedBytesPerSec = $duration > 0 ? $totalBytes / $duration : 0;
        $speedKbps = round($speedBytesPerSec / 1024, 2);
        $speedMbps = round($speedKbps / 1024, 2);

        echo json_encode([
            'status' => 'ok',
            'message' => "✅ Downloaded update at {$speedMbps} MB/s",
            'progress' => 60,
            'stage' => 'download',
            'speed' => [
                'bytes_per_sec' => $speedBytesPerSec,
                'kbps' => $speedKbps,
                'mbps' => $speedMbps
            ]
        ]);
        break;

    case 'extract':
        $zip = new ZipArchive;
        if ($zip->open($zipFile) === TRUE) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entry = $zip->getNameIndex($i);
                if (basename($entry) === 'config.json') continue;
                $zip->extractTo(__DIR__, $entry);
            }
            $zip->close();
            @unlink($zipFile);

            $config['script_version'] = $info['version'];
            $config['script_updated_at'] = date('F j');
            file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT));

            echo json_encode([
                'status' => 'ok',
                'message' => '✅ Update complete',
                'progress' => 100,
                'stage' => 'complete'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => '❌ Extraction failed',
                'progress' => 80,
                'stage' => 'extract'
            ]);
        }
        break;

    default:
        echo json_encode([
            'status' => 'error',
            'message' => '❌ Unknown step',
            'progress' => 0,
            'stage' => 'check'
        ]);
}