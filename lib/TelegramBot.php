<?php

class TelegramBot
{
    private $apiToken;
    private $apiBase;

    public function __construct(string $apiToken)
    {
        $this->apiToken = $apiToken;
        $this->apiBase = 'https://api.telegram.org/bot' . $apiToken . '/';
    }

    public function sendMessage(int $chatId, string $text, array $options = []): array
    {
        $payload = array_merge([
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
        ], $options);

        return $this->request('sendMessage', $payload);
    }

    public function answerCallbackQuery(string $callbackQueryId, string $text = '', bool $showAlert = false): array
    {
        $payload = [
            'callback_query_id' => $callbackQueryId,
            'text' => $text,
            'show_alert' => $showAlert,
        ];

        return $this->request('answerCallbackQuery', $payload);
    }

    private function request(string $method, array $params): array
    {
        $url = $this->apiBase . $method;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);

        $response = curl_exec($ch);
        if ($response === false) {
            $err = curl_error($ch);
            curl_close($ch);
            return ['ok' => false, 'error' => $err];
        }

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $decoded = json_decode($response, true);

        if ($status >= 200 && $status < 300 && is_array($decoded)) {
            return $decoded;
        }

        return ['ok' => false, 'status' => $status, 'response' => $response];
    }
}

