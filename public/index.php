<?php
declare(strict_types=1);

// Simple Telegram Bot webhook for song recognition using the Audd API
// Requirements: PHP 7.4+ with cURL enabled. Deployable on cPanel as a webhook endpoint.

// Load config
$configPath = __DIR__ . '/../config.php';
if (!file_exists($configPath)) {
    http_response_code(500);
    echo 'Config file missing. Copy config.sample.php to config.php and fill values.';
    exit;
}
require_once $configPath;

// Basic safety
if (!isset($config['TELEGRAM_BOT_TOKEN']) || !isset($config['AUDD_API_TOKEN'])) {
    http_response_code(500);
    echo 'Config variables missing.';
    exit;
}

// Helpers
function tgApi(string $method, array $params): array {
    global $config;
    $url = "https://api.telegram.org/bot{$config['TELEGRAM_BOT_TOKEN']}/$method";
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $params,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 30,
    ]);
    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    if ($response === false) {
        return ['ok' => false, 'description' => $err];
    }
    $data = json_decode($response, true);
    return is_array($data) ? $data : ['ok' => false, 'description' => 'Invalid JSON from Telegram'];
}

function httpGet(string $url): string {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 60,
    ]);
    $response = curl_exec($ch);
    $err = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($response === false || $httpCode >= 400) {
        throw new RuntimeException("HTTP GET failed ($httpCode): $err");
    }
    return (string)$response;
}

function httpPostMultipart(string $url, array $fields): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $fields,
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_TIMEOUT => 60,
    ]);
    $response = curl_exec($ch);
    $err = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($response === false || $httpCode >= 400) {
        return ['status' => 'error', 'error' => $err ?: ("HTTP $httpCode")];
    }
    $data = json_decode((string)$response, true);
    return is_array($data) ? $data : ['status' => 'error', 'error' => 'Invalid JSON'];
}

function buildSongMessage(array $recognition): string {
    $title = $recognition['title'] ?? '';
    $artist = $recognition['artist'] ?? '';
    $album = $recognition['album'] ?? '';
    $releaseDate = $recognition['release_date'] ?? ($recognition['releaseDate'] ?? '');
    $label = $recognition['label'] ?? '';
    $timecode = $recognition['timecode'] ?? '';
    $deezer = $recognition['deezer']['link'] ?? '';
    $spotify = $recognition['spotify']['external_urls']['spotify'] ?? ($recognition['spotify']['link'] ?? '');
    $apple = $recognition['apple_music']['url'] ?? '';

    $lines = [];
    if ($title || $artist) {
        $lines[] = "🎵 $title" . ($artist ? " — $artist" : '');
    }
    if ($album) { $lines[] = "💿 آلبوم: $album"; }
    if ($releaseDate) { $lines[] = "📅 انتشار: $releaseDate"; }
    if ($label) { $lines[] = "🏷️ لیبل: $label"; }
    if ($timecode) { $lines[] = "⏱️ موقعیت تشخیص: $timecode"; }
    if ($spotify) { $lines[] = "Spotify: $spotify"; }
    if ($deezer) { $lines[] = "Deezer: $deezer"; }
    if ($apple) { $lines[] = "Apple Music: $apple"; }
    if (empty($lines)) {
        $lines[] = 'نتیجه‌ای یافت نشد.';
    }
    return implode("\n", $lines);
}

function recognizeWithAudd(string $filePath, string $auddToken): array {
    $url = 'https://api.audd.io/';
    $params = [
        'api_token' => $auddToken,
        'method' => 'recognize',
        'return' => 'apple_music,spotify,deezer',
        'file' => new CURLFile($filePath),
    ];
    $resp = httpPostMultipart($url, $params);
    if (($resp['status'] ?? '') !== 'success') {
        return ['error' => $resp['error'] ?? 'Request failed'];
    }
    return $resp['result'] ?? [];
}

function downloadTelegramFile(string $fileId): string {
    global $config;
    $getFile = tgApi('getFile', ['file_id' => $fileId]);
    if (!($getFile['ok'] ?? false)) {
        throw new RuntimeException('getFile failed: ' . ($getFile['description'] ?? 'unknown'));
    }
    $filePath = $getFile['result']['file_path'] ?? '';
    if (!$filePath) {
        throw new RuntimeException('Empty file_path');
    }
    $fileUrl = "https://api.telegram.org/file/bot{$config['TELEGRAM_BOT_TOKEN']}/$filePath";
    $binary = httpGet($fileUrl);
    $ext = pathinfo($filePath, PATHINFO_EXTENSION) ?: 'dat';
    $tmpDir = sys_get_temp_dir();
    $localPath = $tmpDir . '/tg_' . bin2hex(random_bytes(6)) . '.' . $ext;
    if (file_put_contents($localPath, $binary) === false) {
        throw new RuntimeException('Failed to save file');
    }
    return $localPath;
}

// Read incoming update
$raw = file_get_contents('php://input');
if ($raw === false || $raw === '') {
    echo 'OK';
    exit;
}

$update = json_decode($raw, true);
if (!is_array($update)) {
    echo 'OK';
    exit;
}

// Support message and edited_message
$message = $update['message'] ?? ($update['edited_message'] ?? null);
if (!$message) {
    echo 'OK';
    exit;
}

$chatId = $message['chat']['id'] ?? null;
if (!$chatId) {
    echo 'OK';
    exit;
}

// Determine audio-like content
$fileId = null;
$kind = null;
if (isset($message['audio'])) {
    $fileId = $message['audio']['file_id'];
    $kind = 'audio';
} elseif (isset($message['voice'])) {
    $fileId = $message['voice']['file_id'];
    $kind = 'voice';
} elseif (isset($message['video'])) {
    $fileId = $message['video']['file_id'];
    $kind = 'video';
} elseif (isset($message['video_note'])) {
    $fileId = $message['video_note']['file_id'];
    $kind = 'video_note';
} elseif (isset($message['document'])) { // mp3/mp4 as document
    $mime = $message['document']['mime_type'] ?? '';
    $name = strtolower($message['document']['file_name'] ?? '');
    if (strpos($mime, 'audio') === 0 || strpos($mime, 'video') === 0 || preg_match('/\.(mp3|mp4|m4a|aac|wav)$/', $name)) {
        $fileId = $message['document']['file_id'];
        $kind = 'document';
    }
}

if (!$fileId) {
    tgApi('sendMessage', [
        'chat_id' => $chatId,
        'text' => "لطفا یک بخش کوتاه از آهنگ را به صورت فایل صوتی، وویس یا ویدیو ارسال کنید تا تشخیص دهم.",
    ]);
    echo 'OK';
    exit;
}

// Acknowledge
tgApi('sendChatAction', ['chat_id' => $chatId, 'action' => 'typing']);

try {
    $localFile = downloadTelegramFile($fileId);
} catch (Throwable $e) {
    tgApi('sendMessage', [
        'chat_id' => $chatId,
        'text' => 'خطا در دریافت فایل از تلگرام: ' . $e->getMessage(),
    ]);
    echo 'OK';
    exit;
}

try {
    $result = recognizeWithAudd($localFile, $config['AUDD_API_TOKEN']);
} catch (Throwable $e) {
    $result = ['error' => $e->getMessage()];
}

@unlink($localFile);

if (isset($result['error'])) {
    tgApi('sendMessage', [
        'chat_id' => $chatId,
        'text' => 'نتوانستم آهنگ را تشخیص دهم: ' . $result['error'],
    ]);
    echo 'OK';
    exit;
}

$messageText = buildSongMessage($result);

tgApi('sendMessage', [
    'chat_id' => $chatId,
    'text' => $messageText,
    'disable_web_page_preview' => true,
]);

echo 'OK';
?>

