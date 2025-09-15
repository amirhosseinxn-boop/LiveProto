## Telegram Song Recognition Bot (PHP, cPanel)

This is a lightweight PHP webhook for a Telegram bot that recognizes songs from `audio`, `voice`, or `video` messages using the Audd API, suitable for typical cPanel hosting without Composer.

### Files
- `index.php`: Webhook handler. Receives updates, downloads the file via Telegram API, calls Audd, and replies.
- `config.php`: Configuration constants (tokens and optional webhook secret).

### Requirements
- PHP 7.4+ with cURL enabled (most cPanel hosts have this)
- A public HTTPS URL (use your cPanel domain or subdomain)
- Telegram Bot Token from BotFather
- Audd API token (`https://audd.io/`)

### Deployment (cPanel)
1. Create a folder under your domain (e.g., `public_html/bot`).
2. Upload `index.php` and `config.php` to that folder.
3. Edit `config.php` and set:
   - `TELEGRAM_BOT_TOKEN`
   - `AUDD_API_TOKEN`
   - Optionally `WEBHOOK_SECRET`
4. Open your domain to verify PHP runs (a blank `ok` JSON is fine).

### Set Telegram Webhook
Replace values and open this URL in your browser:

```
https://api.telegram.org/bot<TELEGRAM_BOT_TOKEN>/setWebhook?url=https://your-domain.com/bot/index.php
```

If using a secret:

```
https://api.telegram.org/bot<TELEGRAM_BOT_TOKEN>/setWebhook?url=https://your-domain.com/bot/index.php?secret=YOUR_SECRET
```

To remove webhook:

```
https://api.telegram.org/bot<TELEGRAM_BOT_TOKEN>/deleteWebhook
```

### Usage
- Send a voice note, an audio file (mp3), or a short video (mp4) to your bot.
- The bot will attempt recognition and reply with title, artist, and links.

### Notes
- For documents: Users sending mp3/mp4 as a document are supported.
- Large files: Telegram may expire file URLs quickly; resend if it fails.
- Rate limits: Audd has quotas; handle accordingly for production.

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
