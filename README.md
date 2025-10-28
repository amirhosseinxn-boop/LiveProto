# منوی آنلاین کافه رادیو

سایت ساده و سریع برای نمایش منوی کافه رادیو به زبان فارسی (RTL) با تم سفید و قرمز.

## اجرا

1. این پوشه را روی هر هاست استاتیک آپلود کنید (GitHub Pages, Vercel, Netlify، هاست شخصی).
2. فایل `menu.json` را با آیتم‌های واقعی خودتان به‌روز کنید.
3. آدرس QR کد را به `index.html` (صفحه اصلی) بدهید.

برای تست محلی:

```bash
python3 -m http.server 8080
# سپس مرورگر: http://localhost:8080
```

## ویرایش منو

- دسته‌بندی‌ها و آیتم‌ها در فایل `menu.json` هستند.
- برای هر آیتم فیلدهای زیر را پر کنید:

```json
{
  "id": "espresso",
  "categoryId": "coffee-hot",
  "title": "اسپرسو",
  "description": "اختیاری",
  "priceTomans": 65000,
  "image": "images/espresso.jpg"
}
```

- قیمت را به تومان و به صورت عدد (بدون جداکننده) وارد کنید. نمایش فارسی و "تومان" به صورت خودکار انجام می‌شود.
- اگر عکس ندارید، می‌توانید فعلاً از `images/placeholder.png` استفاده کنید.

## دسته‌بندی‌ها

- در بخش `categories` هر دسته یک `id` و `title` دارد. مقدار `id` را برای آیتم‌های همان دسته در `categoryId` استفاده کنید.

## جستجو و فیلتر

- نوار جستجو عنوان و توضیح کالاها را فیلتر می‌کند.
- چیپ‌های بالای صفحه برای فیلتر دسته‌ها هستند. «همه» همه آیتم‌ها را نشان می‌دهد.

## شخصی‌سازی ظاهر

- رنگ‌ها در `styles.css` با متغیرهای CSS قابل تغییر هستند:

```css
:root {
  --red-600: #e10600;
  --red-700: #c10500;
}
```

## نکات QR

- وقتی سایت را روی دامنه خودتان قرار دادید، یک QR کد به URL صفحه بسازید و روی میزها بچسبانید.

## ساختار فایل‌ها

```
index.html
styles.css
app.js
menu.json
images/
  placeholder.png
```

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
