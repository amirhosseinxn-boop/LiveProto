### Telegram Song Recognizer Bot (PHP, cPanel-ready)

This is a simple Telegram bot webhook in PHP that recognizes songs from short audio/voice/video clips using the Audd API and replies with the song title, artist, and links.

### Features
- Accepts Telegram `audio`, `voice`, `video`, `video_note`, and `document` (mp3/mp4)
- Uploads the received file to Audd for recognition
- Replies with title, artist, album, release date, label, and links (Spotify/Deezer/Apple)

### Files
- `public/index.php`: Webhook endpoint
- `config.sample.php`: Sample configuration; copy to `config.php`

### Configuration
1. Copy the sample config:
```bash
cp config.sample.php config.php
```
2. Edit `config.php` and set:
   - `TELEGRAM_BOT_TOKEN` from BotFather
   - `AUDD_API_TOKEN` from `https://audd.io`

### Deploy on cPanel
1. Create a folder (e.g., `songbot`) under your domain. If your domain root is `public_html`, upload this repo so that `public/index.php` is accessible at `https://yourdomain.com/songbot/index.php`.
2. Ensure PHP 7.4+ with cURL is enabled in your cPanel PHP Selector.
3. Place `config.php` at the project root (same level as `public/`).

### Set Telegram Webhook
Replace placeholders and open this URL in your browser:
```
https://api.telegram.org/bot<TELEGRAM_BOT_TOKEN>/setWebhook?url=https://yourdomain.com/songbot/index.php
```
If your endpoint is `public/index.php`, ensure the URL points to it. On some hosts, map `public/` as the document root, then the URL is just the directory path.

To delete the webhook:
```
https://api.telegram.org/bot<TELEGRAM_BOT_TOKEN>/deleteWebhook
```

### Test
Send a short audio/voice/video clip of a playing song to your bot. The bot will reply with recognized song info and links.

### Notes
- Audd requires sufficiently clear audio and might not recognize very short or noisy clips.
- Large files may be rejected by hosting limits. Prefer short clips (<20s) for faster recognition and better accuracy.

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
