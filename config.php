<?php
// Configuration for Telegram bot and AudD API

return [
    // Telegram Bot API token: get it from @BotFather
    'TELEGRAM_BOT_TOKEN' => getenv('TELEGRAM_BOT_TOKEN') ?: 'REPLACE_WITH_TELEGRAM_BOT_TOKEN',

    // AudD API token: get it from https://audd.io
    'AUDD_API_TOKEN' => getenv('AUDD_API_TOKEN') ?: 'REPLACE_WITH_AUDD_API_TOKEN',

    // Optional: restrict bot to only accept instagram.com or instagr.am links
    'ALLOWED_HOSTS' => [
        'instagram.com',
        'www.instagram.com',
        'instagr.am',
        'www.instagr.am',
    ],
];

