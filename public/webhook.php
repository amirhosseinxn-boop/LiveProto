<?php
// Telegram webhook endpoint

// Load configuration
$config = require dirname(__DIR__) . '/config.php';

// Basic safety checks
if ($config['TELEGRAM_BOT_TOKEN'] === 'REPLACE_WITH_TELEGRAM_BOT_TOKEN' || $config['AUDD_API_TOKEN'] === 'REPLACE_WITH_AUDD_API_TOKEN') {
    http_response_code(500);
    echo 'Bot is not configured.';
    exit;
}

require_once dirname(__DIR__) . '/lib/TelegramBot.php';
require_once dirname(__DIR__) . '/lib/AuddClient.php';

// Read input JSON
$raw = file_get_contents('php://input');
$update = json_decode($raw, true);
if (!$update) {
    http_response_code(400);
    echo 'Invalid input';
    exit;
}

$bot = new TelegramBot($config['TELEGRAM_BOT_TOKEN']);
$audd = new AuddClient($config['AUDD_API_TOKEN']);

// Utility: extract first Instagram URL from text
function extractInstagramUrl(string $text, array $allowedHosts): ?string {
    $pattern = '~https?://[^\s]+~i';
    if (!preg_match_all($pattern, $text, $matches)) {
        return null;
    }
    foreach ($matches[0] as $url) {
        $parts = parse_url($url);
        if (!$parts || empty($parts['host'])) {
            continue;
        }
        $host = strtolower($parts['host']);
        if (!in_array($host, $allowedHosts, true)) {
            continue;
        }
        // Accept reels or short links
        if (isset($parts['path']) && preg_match('~^/(reel|reels|p)/~i', $parts['path'])) {
            return $url;
        }
        // Some Instagram links may redirect; still try
        return $url;
    }
    return null;
}

// Handle message updates
if (isset($update['message'])) {
    $message = $update['message'];
    $chatId = $message['chat']['id'] ?? null;
    $text = $message['text'] ?? '';

    if ($chatId === null) {
        http_response_code(200);
        exit;
    }

    if (trim($text) === '/start') {
        $bot->sendMessage($chatId, "سلام! لینک ریلز اینستاگرام رو بفرست تا اسم آهنگ رو بگم 🎵");
        http_response_code(200);
        exit;
    }

    $url = extractInstagramUrl($text, $config['ALLOWED_HOSTS']);
    if (!$url) {
        $bot->sendMessage($chatId, "لطفاً یک لینک معتبر ریلز اینستاگرام بفرست.");
        http_response_code(200);
        exit;
    }

    $bot->sendMessage($chatId, "در حال شناسایی آهنگ... ⏳");

    $result = $audd->recognizeByUrl($url);

    if (($result['status'] ?? '') === 'success' && !empty($result['result'])) {
        $r = $result['result'];
        $artist = $r['artist'] ?? '';
        $title = $r['title'] ?? '';
        $album = $r['album'] ?? '';
        $confidence = isset($r['confidence']) ? (int)$r['confidence'] : null;

        $lines = [];
        if ($artist || $title) {
            $lines[] = "🎶 <b>" . htmlspecialchars($artist . ' - ' . $title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</b>";
        }
        if ($album) {
            $lines[] = "آلبوم: " . htmlspecialchars($album, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }
        if ($confidence !== null) {
            $lines[] = "اطمینان: " . $confidence . "%";
        }

        // Add links if available
        $spotifyUrl = $r['spotify']['external_urls']['spotify'] ?? ($r['spotify']['url'] ?? null);
        $appleUrl = $r['apple_music']['url'] ?? null;
        if ($spotifyUrl) {
            $lines[] = "Spotify: " . $spotifyUrl;
        }
        if ($appleUrl) {
            $lines[] = "Apple Music: " . $appleUrl;
        }

        $reply = implode("\n", $lines);
        if ($reply === '') {
            $reply = "آهنگ پیدا شد اما اطلاعات کافی در دسترس نیست.";
        }
        $bot->sendMessage($chatId, $reply);
    } else {
        $errorText = 'متأسفانه نتونستم آهنگ رو شناسایی کنم. مطمئن شو لینک ریلز عمومی باشه و دوباره تلاش کن.';
        if (!empty($result['error'])) {
            $errorText .= "\nخطا: " . $result['error'];
        }
        $bot->sendMessage($chatId, $errorText);
    }

    http_response_code(200);
    exit;
}

// Acknowledge other update types
http_response_code(200);
echo 'OK';

