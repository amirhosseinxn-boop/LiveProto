<?php
// Configuration for Telegram bot and Audd API

// REQUIRED: Your Telegram bot token from BotFather
define('TELEGRAM_BOT_TOKEN', getenv('TELEGRAM_BOT_TOKEN') ?: 'REPLACE_WITH_TELEGRAM_BOT_TOKEN');

// REQUIRED: Your Audd API token: https://audd.io/
define('AUDD_API_TOKEN', getenv('AUDD_API_TOKEN') ?: 'REPLACE_WITH_AUDD_API_TOKEN');

// OPTIONAL: Add a secret query to your webhook URL for basic protection
// Example webhook: https://your-domain.com/index.php?secret=YOUR_SECRET
define('WEBHOOK_SECRET', getenv('WEBHOOK_SECRET') ?: '');

