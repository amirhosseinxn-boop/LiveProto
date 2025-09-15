<?php

class AuddClient
{
    private $apiToken;
    private $apiUrl;

    public function __construct(string $apiToken)
    {
        $this->apiToken = $apiToken;
        $this->apiUrl = 'https://api.audd.io/';
    }

    // Recognize music from a public URL (Instagram Reel URL is accepted if accessible)
    public function recognizeByUrl(string $mediaUrl, array $extraParams = []): array
    {
        $params = array_merge([
            'api_token' => $this->apiToken,
            'url' => $mediaUrl,
            'return' => 'apple_music,spotify',
        ], $extraParams);

        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        if ($response === false) {
            $err = curl_error($ch);
            curl_close($ch);
            return ['status' => 'error', 'error' => $err];
        }

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $decoded = json_decode($response, true);
        if ($status >= 200 && $status < 300 && is_array($decoded)) {
            return $decoded;
        }

        return ['status' => 'error', 'http_status' => $status, 'response' => $response];
    }
}

