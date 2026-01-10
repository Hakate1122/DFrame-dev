# WebSocket Chat - Hướng dẫn sử dụng

## Tổng quan

Ứng dụng Chat sử dụng WebSocket để giao tiếp real-time giữa nhiều client. Class `Chat` extends từ `WebSocket` và override các event handlers để xử lý kết nối, tin nhắn và ngắt kết nối.

## Cấu trúc

```
app/Chat/
├── Chat.php          # Class Chat extends WebSocket
├── server.php        # Script khởi chạy WebSocket server
└── README.md         # File hướng dẫn này
```

## Yêu cầu

- PHP 8.0+ với extension `sockets` enabled
- Chạy từ CLI (Command Line Interface)

## Cài đặt

### 1. Kiểm tra extension sockets

```bash
php -m | grep sockets
```

Nếu không thấy, cần enable extension trong `php.ini`:
```ini
extension=sockets
```

### 2. Khởi chạy WebSocket Server

```bash
# Sử dụng host và port mặc định (0.0.0.0:9501)
php app/Chat/server.php

# Hoặc chỉ định host và port tùy chỉnh
php app/Chat/server.php --host=127.0.0.1 --port=8080
```

Server sẽ hiển thị:
```
WebSocket server started at ws://0.0.0.0:9501
```

## Sử dụng trong Code

### 1. Tạo Custom Chat Class

```php
<?php
namespace App\Chat;

use DFrame\Application\WebSocket;

class Chat extends WebSocket
{
    /**
     * Event khi client kết nối
     */
    protected function onOpen(\Socket $client): void
    {
        // Gửi thông báo đến tất cả client khác
        $this->broadcast("A new user has joined the chat.");
        
        // Hoặc gửi tin nhắn chào mừng đến client mới
        $this->send($client, "Welcome to the chat!");
    }

    /**
     * Event khi client gửi tin nhắn
     */
    protected function onMessage(\Socket $client, string $message): void
    {
        // Broadcast tin nhắn đến tất cả client (trừ người gửi)
        $this->broadcast($message, $client);
        
        // Hoặc xử lý tin nhắn đặc biệt
        if (str_starts_with($message, '/')) {
            $this->handleCommand($client, $message);
        }
    }

    /**
     * Event khi client ngắt kết nối
     */
    protected function onClose(\Socket $client): void
    {
        // Gửi thông báo đến các client còn lại
        $this->broadcast("A user has left the chat.");
    }
    
    /**
     * Xử lý command đặc biệt
     */
    private function handleCommand(\Socket $client, string $message): void
    {
        // Xử lý các command như /nick, /join, etc.
    }
}
```

### 2. Các Methods có sẵn từ WebSocket

#### `send($client, string $message): void`
Gửi tin nhắn đến một client cụ thể.

```php
$this->send($client, "Hello!");
```

#### `broadcast(string $message, $except = null): void`
Gửi tin nhắn đến tất cả client, có thể loại trừ một client.

```php
// Gửi đến tất cả
$this->broadcast("Hello everyone!");

// Gửi đến tất cả trừ client cụ thể
$this->broadcast("Hello everyone!", $excludeClient);
```

#### `$this->clients`
Array chứa tất cả các client đang kết nối.

```php
$clientCount = count($this->clients);
```

## Client HTML/JavaScript

### Ví dụ Client đơn giản

Tạo file `public/chat.html`:

```html
<!DOCTYPE html>
<html>
<head>
    <title>WebSocket Chat</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        #messages { border: 1px solid #ccc; height: 400px; overflow-y: auto; padding: 10px; margin-bottom: 10px; }
        #messageInput { width: 70%; padding: 5px; }
        button { padding: 5px 15px; }
    </style>
</head>
<body>
    <h1>WebSocket Chat</h1>
    <div id="messages"></div>
    <input type="text" id="messageInput" placeholder="Type a message...">
    <button onclick="sendMessage()">Send</button>

    <script>
        // Kết nối WebSocket (thay đổi host và port theo server của bạn)
        const ws = new WebSocket('ws://localhost:9501');

        ws.onopen = function() {
            addMessage('Connected to chat server');
        };

        ws.onmessage = function(event) {
            addMessage(event.data);
        };

        ws.onclose = function() {
            addMessage('Disconnected from server');
        };

        ws.onerror = function(error) {
            addMessage('Error: ' + error);
        };

        function sendMessage() {
            const input = document.getElementById('messageInput');
            const message = input.value.trim();
            
            if (message) {
                ws.send(message);
                input.value = '';
            }
        }

        function addMessage(message) {
            const messages = document.getElementById('messages');
            const div = document.createElement('div');
            div.textContent = new Date().toLocaleTimeString() + ': ' + message;
            messages.appendChild(div);
            messages.scrollTop = messages.scrollHeight;
        }

        // Gửi tin nhắn khi nhấn Enter
        document.getElementById('messageInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
    </script>
</body>
</html>
```

## Testing với wscat (Command Line Tool)

Cài đặt wscat:
```bash
npm install -g wscat
```

Kết nối:
```bash
wscat -c ws://localhost:9501
```

## Troubleshooting

### Lỗi: "Call to undefined function socket_create()"
- **Giải pháp**: Enable extension `sockets` trong `php.ini`

### Lỗi: "Address already in use"
- **Giải pháp**: Port đang được sử dụng, thử port khác hoặc kill process cũ

### Client không kết nối được
- Kiểm tra firewall có chặn port không
- Kiểm tra server đã chạy chưa
- Kiểm tra URL WebSocket đúng chưa (ws://host:port)

## Ví dụ nâng cao

### Chat với username

```php
protected function onOpen(\Socket $client): void
{
    // Lưu thông tin client
    $clientId = spl_object_id($client);
    $this->clientInfo[$clientId] = [
        'username' => 'Guest' . $clientId,
        'joined_at' => time()
    ];
    
    $this->broadcast("User {$this->clientInfo[$clientId]['username']} joined");
}

protected function onMessage(\Socket $client, string $message): void
{
    $clientId = spl_object_id($client);
    $username = $this->clientInfo[$clientId]['username'] ?? 'Unknown';
    $this->broadcast("{$username}: {$message}", $client);
}
```

### Chat với rooms/channels

```php
protected array $rooms = [];

protected function onMessage(\Socket $client, string $message): void
{
    $clientId = spl_object_id($client);
    
    if (preg_match('/^\/join\s+(.+)$/', $message, $matches)) {
        $room = $matches[1];
        $this->rooms[$clientId] = $room;
        $this->send($client, "Joined room: {$room}");
    } else {
        $room = $this->rooms[$clientId] ?? 'general';
        $this->broadcastToRoom($room, $message, $client);
    }
}

private function broadcastToRoom(string $room, string $message, $exclude): void
{
    $excludeId = spl_object_id($exclude);
    
    foreach ($this->clients as $client) {
        $clientId = spl_object_id($client);
        if ($clientId !== $excludeId && ($this->rooms[$clientId] ?? 'general') === $room) {
            $this->send($client, $message);
        }
    }
}
```

## Tài liệu tham khảo

- [WebSocket RFC 6455](https://tools.ietf.org/html/rfc6455)
- [PHP Socket Functions](https://www.php.net/manual/en/book.sockets.php)

