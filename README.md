## PHP Telegram Bot: Instagram Reels song detector (AudD)

### Requirements
- PHP 7.4+ with cURL enabled (available on typical cPanel)
- A publicly accessible HTTPS URL (your cPanel domain)
- Telegram Bot token (from @BotFather)
- AudD API token (`https://audd.io`)

### Files
- `config.php` – set your tokens here (or via environment variables)
- `public/webhook.php` – Telegram webhook endpoint
- `lib/TelegramBot.php` – minimal Telegram client
- `lib/AuddClient.php` – AudD API client

### Deployment on cPanel
1. Create a folder in your hosting, e.g. `bot/` and upload all files preserving the structure. Ensure `public/webhook.php` is reachable via HTTPS, e.g. `https://yourdomain.com/bot/public/webhook.php`.
2. Edit `config.php` and replace `REPLACE_WITH_TELEGRAM_BOT_TOKEN` and `REPLACE_WITH_AUDD_API_TOKEN` with your real tokens.
3. In Telegram, set the webhook:

```bash
curl -s "https://api.telegram.org/bot<TELEGRAM_BOT_TOKEN>/setWebhook" \
  -d url="https://yourdomain.com/bot/public/webhook.php"
```

Check webhook info:

```bash
curl -s "https://api.telegram.org/bot<TELEGRAM_BOT_TOKEN>/getWebhookInfo" | jq .
```

### Usage
Send an Instagram Reels link to your bot. The bot replies with the detected song title and artist. Make sure the Reel is public/viewable.

### Notes
- AudD recognizes audio available through the provided URL. For private or region-locked content, recognition may fail.
- If your hosting enforces firewall rules, allow outbound HTTPS to `api.audd.io` and `api.telegram.org`.

# LiveProto

<p>
  <img src = "docs/_images/logo.svg" alt = "logo" style = "vertical-align : middle; width : 24px; height : 24px;"/>
  An <strong>async</strong> , <strong>Pure-PHP</strong> MTProto Telegram client library for both <em>bot</em> & <em>user account</em> handling
</p>

---

## 🚀 Features

* **Full MTProto Protocol** : Complete implementation of Telegram's low-level protocol
* **Asynchronous I/O** : Built with PHP 8's async primitives ( Fibers / Amp ), enabling non-blocking requests
* **Session Management** : Automatic key exchange, session storage, and reconnection logic
* **Comprehensive API Coverage** : Send and receive messages, manage chats and channels, handle updates, upload/download media, and more

---

## 📦 Installation

Install via Composer :

```bash
composer require taknone/liveproto
```

Then use it like this :

```php
<?php

require 'vendor/autoload.php';
```

Install via Phar :

```php
<?php

if(file_exists('liveproto.php') === false):
    copy('https://installer.liveproto.dev/liveproto.php','liveproto.php');
endif;

require_once 'liveproto.php';
```

---

## 🏁 Getting Started

Example Usage :

```php
<?php

if(file_exists('vendor/autoload.php')):
    require 'vendor/autoload.php';
elseif(file_exists('liveproto.phar')):
    require_once 'liveproto.phar';
elseif(file_exists('liveproto.php') === false):
    copy('https://installer.liveproto.dev/liveproto.php','liveproto.php');
    require_once 'liveproto.php';
endif;

use Tak\Liveproto\Network\Client;

use Tak\Liveproto\Utils\Settings;

$settings = new Settings();
$settings->setApiId(21724);
$settings->setApiHash('3e0cb5efcd52300aec5994fdfc5bdc16');
$settings->setHideLog(false);

$client = new Client('testSession','sqlite',$settings);

$client->connect();

try {
	if($client->isAuthorized() === false){
		$client->sign_in(bot_token : '123456:AAEK.....');
	}
	/* 😁 If you would like to avoid errors, enter your username in the line below 😎 */
	$peer = $client->get_input_peer('@TakNone');
	print_r($client->messages->sendMessage($peer,'👋',random_int(PHP_INT_MIN,PHP_INT_MAX)));
} catch(Throwable $error){
	var_dump($error);
} finally {
	$client->disconnect();
}

?>
```

---

## 💬 Community & Chat
Join the project community :
- Chat ( Telegram ) : https://t.me/LiveProtoChat
- News ( Telegram channel ) : https://t.me/LiveProto
- Snippets ( Telegram ) : https://t.me/LiveProtoSnippets

## 🎓 Documentation

Visit [Docs LiveProto](https://docs.LiveProto.dev) and [TL LiveProto](https://tl.LiveProto.dev)

## 📜 License

This project is licensed under the [MIT License](LICENSE)
