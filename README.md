# FCM Client (PHP)

[![PHP](https://img.shields.io/badge/PHP-%5E8.0-blue)](https://www.php.net/)
[![Packagist](https://img.shields.io/badge/Composer-Package-brightgreen)](https://packagist.org/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

A simple **PHP client** to send messages with **Firebase Cloud Messaging (FCM)** using the **HTTP v1 API**.  
It does **not** rely on the official Google SDK, only `guzzlehttp/guzzle` and `firebase/php-jwt`.

---

## 📦 Installation

Install via **Composer**:

```bash
composer require httd1/fcmclient
```

## ⚙️ Setup
Download the private key JSON file in the Firebase Console.

Place this file in your project, ex. firebase-credentials.json.

## 🚀 Usage
### Send to a single device
```php
<?php

require 'vendor/autoload.php';

use FcmClient\FcmClient;

try {

    // initialize client with service account JSON
    $client = new FcmClient(__DIR__ . '/firebase-credentials.json');

    // send notification
    $response = $client->send([
        'message' => [
            'token' => 'FCM_DEVICE_TOKEN_HERE',
            'notification' => [
                'title' => 'Hello!',
                'body'  => 'Test notification 🚀'
            ]
        ]
    ]);

    print_r($response);

} catch (\Throwable $e) {
    echo 'Error: ' . $e->getMessage();
}

```

Send to a topic
```php
$response = $client->send([
    'message' => [
        'topic' => 'my_app_topic',
        'notification' => [
            'title' => 'Update',
            'body'  => 'Message sent to all subscribers of the topic'
        ]
    ]
]);

print_r($response);
```

Send with custom data payload
```php
$response = $client->send([
    'message' => [
        'token' => 'FCM_DEVICE_TOKEN_HERE',
        'data' => [
            'action' => 'open_chat',
            'chat_id' => '12345'
        ]
    ]
]);
```

## 📚 References
[Official FCM HTTP v1 API Documentation](https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages)