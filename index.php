<?php
// Simple Telegram bot webhook for song recognition using Audd API
// Compatible with typical cPanel PHP (no Composer required)

require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

function respondOk(): void {
	http_response_code(200);
	echo json_encode(['ok' => true]);
}

function tgApi(string $method, array $params): array {
	$token = TELEGRAM_BOT_TOKEN;
	$url = "https://api.telegram.org/bot{$token}/{$method}";
	$ch = curl_init($url);
	curl_setopt_array($ch, [
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => $params,
		CURLOPT_CONNECTTIMEOUT => 10,
		CURLOPT_TIMEOUT => 30,
	]);
	$response = curl_exec($ch);
	$error = curl_error($ch);
	$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
	if ($response === false) {
		return ['ok' => false, 'error' => $error];
	}
	$data = json_decode($response, true);
	if (!is_array($data)) {
		return ['ok' => false, 'error' => 'Invalid Telegram response', 'http' => $http, 'raw' => $response];
	}
	return $data;
}

function tgGetFilePath(string $fileId): ?string {
	$resp = tgApi('getFile', ['file_id' => $fileId]);
	if (!empty($resp['ok']) && isset($resp['result']['file_path'])) {
		return $resp['result']['file_path'];
	}
	return null;
}

function auddRecognizeByUrl(string $fileUrl): array {
	$endpoint = 'https://api.audd.io/';
	$params = [
		'api_token' => AUDD_API_TOKEN,
		'method' => 'recognize',
		'url' => $fileUrl,
		'return' => 'apple_music,spotify,deezer,timecode',
	];
	$ch = curl_init($endpoint);
	curl_setopt_array($ch, [
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => $params,
		CURLOPT_CONNECTTIMEOUT => 10,
		CURLOPT_TIMEOUT => 60,
	]);
	$response = curl_exec($ch);
	$error = curl_error($ch);
	$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
	if ($response === false) {
		return ['status' => 'error', 'error' => $error];
	}
	$data = json_decode($response, true);
	if (!is_array($data)) {
		return ['status' => 'error', 'error' => 'Invalid Audd response', 'http' => $http, 'raw' => $response];
	}
	return $data;
}

function formatTrackMessage(array $audd): string {
	if (($audd['status'] ?? '') !== 'success' || empty($audd['result'])) {
		$err = $audd['error']['error_message'] ?? ($audd['error'] ?? 'No match found');
		return "نتونستم چیزی پیدا کنم.\n$err";
	}
	$r = $audd['result'];
	$title = $r['title'] ?? '';
	$artist = $r['artist'] ?? '';
	$album = $r['album'] ?? '';
	$release = $r['release_date'] ?? '';
	$label = $r['label'] ?? '';
	$timecode = $r['timecode'] ?? '';

	$links = [];
	if (!empty($r['song_link'])) { $links[] = $r['song_link']; }
	if (!empty($r['spotify']['external_urls']['spotify'])) { $links[] = $r['spotify']['external_urls']['spotify']; }
	if (!empty($r['apple_music']['url'])) { $links[] = $r['apple_music']['url']; }
	if (!empty($r['deezer']['link'])) { $links[] = $r['deezer']['link']; }

	$lines = [];
	$lines[] = "🎵 آهنگ شناسایی شد";
	if ($title || $artist) { $lines[] = "• نام: {$title} — {$artist}"; }
	if ($album) { $lines[] = "• آلبوم: {$album}"; }
	if ($release) { $lines[] = "• انتشار: {$release}"; }
	if ($label) { $lines[] = "• لیبل: {$label}"; }
	if ($timecode) { $lines[] = "• زمان قطعه: {$timecode}"; }
	if (!empty($links)) {
		$lines[] = "• لینک‌ها:";
		foreach ($links as $u) { $lines[] = $u; }
	}
	return implode("\n", $lines);
}

function extractFileId(array $update): ?array {
	$msg = $update['message'] ?? $update['edited_message'] ?? null;
	if (!$msg) { return null; }
	$chatId = $msg['chat']['id'] ?? null;
	// Priority: audio > voice > video (mp4 clips)
	if (!empty($msg['audio']['file_id'])) {
		return ['chat_id' => $chatId, 'file_id' => $msg['audio']['file_id'], 'type' => 'audio'];
	}
	if (!empty($msg['voice']['file_id'])) {
		return ['chat_id' => $chatId, 'file_id' => $msg['voice']['file_id'], 'type' => 'voice'];
	}
	if (!empty($msg['video']['file_id'])) {
		return ['chat_id' => $chatId, 'file_id' => $msg['video']['file_id'], 'type' => 'video'];
	}
	if (!empty($msg['document']['file_id'])) {
		// Fallback: if user sends mp3/mp4 as document
		return ['chat_id' => $chatId, 'file_id' => $msg['document']['file_id'], 'type' => 'document'];
	}
	return null;
}

// Optional basic authentication via query secret
if (defined('WEBHOOK_SECRET') && WEBHOOK_SECRET !== '') {
	$qs = $_GET['secret'] ?? '';
	if ($qs !== WEBHOOK_SECRET) {
		http_response_code(403);
		echo json_encode(['ok' => false, 'error' => 'Forbidden']);
		exit;
	}
}

$raw = file_get_contents('php://input');
$update = json_decode($raw, true) ?: [];

$file = extractFileId($update);
if ($file === null) {
	respondOk();
	exit;
}

$chatId = $file['chat_id'];
$filePath = tgGetFilePath($file['file_id']);
if ($filePath === null) {
	tgApi('sendMessage', [
		'chat_id' => $chatId,
		'text' => 'مشکلی در دریافت فایل از تلگرام پیش آمد.',
	]);
	respondOk();
	exit;
}

$telegramFileUrl = sprintf('https://api.telegram.org/file/bot%s/%s', TELEGRAM_BOT_TOKEN, $filePath);

// Inform user we are recognizing
tgApi('sendChatAction', ['chat_id' => $chatId, 'action' => 'typing']);

$audd = auddRecognizeByUrl($telegramFileUrl);
$msg = formatTrackMessage($audd);

tgApi('sendMessage', [
	'chat_id' => $chatId,
	'text' => $msg,
	'disable_web_page_preview' => false,
]);

respondOk();

