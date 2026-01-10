<?php

namespace DFrame\Application;

/**
 * **WebSocket Server**
 * 
 * A simple WebSocket server implementation in PHP using sockets.
 * This class handles WebSocket handshakes, frame parsing, and basic
 * message broadcasting to connected clients.
 */
class WebSocket
{
    protected \Socket $master;
    protected array $clients = [];
    protected string $host;
    protected int $port;

    public function __construct(string $host = '0.0.0.0', int $port = 9501)
    {
        $this->host = $host;
        $this->port = $port;
    }

    public function start(): void
    {
        $this->master = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($this->master, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind($this->master, $this->host, $this->port);
        socket_listen($this->master);

        if(PHP_SAPI === 'cli') {
            echo "WebSocket server started at ws://{$this->host}:{$this->port}\n";
        }

        $this->loop();
    }

    /**
     * @return never
     */
    private function loop()
    {
        while (true) {
            $read = array_merge([$this->master], $this->clients);
            $write = $except = [];

            if (@socket_select($read, $write, $except, null) < 1) {
                continue;
            }

            // New connection
            if (in_array($this->master, $read, true)) {
                $client = socket_accept($this->master);
                if ($client && $this->handshake($client)) {
                    $this->clients[spl_object_id($client)] = $client;
                    $this->onOpen($client);
                } else {
                    socket_close($client);
                }
                unset($read[array_search($this->master, $read, true)]);
            }

            // Existing clients
            foreach ($read as $client) {
                // Skip master socket
                if ($client === $this->master) {
                    continue;
                }
                
                $frame = $this->readFrame($client);

                if ($frame === null) {
                    // Check if client is still connected
                    $socketError = socket_get_option($client, SOL_SOCKET, SO_ERROR);
                    if ($socketError !== 0) {
                        $this->disconnect($client);
                    }
                    continue;
                }

                if ($frame['type'] === 'ping') {
                    $this->sendPong($client);
                    continue;
                }

                if ($frame['type'] === 'close') {
                    $this->disconnect($client);
                    continue;
                }

                if ($frame['type'] === 'text') {
                    $this->onMessage($client, $frame['payload']);
                }
            }
        }
    }

    // ─────────────────────────────────────────────
    // RFC 6455 HANDSHAKE
    // ─────────────────────────────────────────────

    private function handshake($client): bool
    {
        $request = socket_read($client, 2048);
        if (!$request) return false;

        if (
            !preg_match("/Upgrade:\s*websocket/i", $request) ||
            !preg_match("/Connection:\s*Upgrade/i", $request) ||
            !preg_match("/Sec-WebSocket-Key:\s*(.+)\r\n/i", $request, $match)
        ) {
            return false;
        }

        $key = trim($match[1]);
        $accept = base64_encode(
            sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true)
        );

        $response =
            "HTTP/1.1 101 Switching Protocols\r\n" .
            "Upgrade: websocket\r\n" .
            "Connection: Upgrade\r\n" .
            "Sec-WebSocket-Accept: {$accept}\r\n\r\n";

        socket_write($client, $response);
        return true;
    }

    // ─────────────────────────────────────────────
    // FRAME PARSING (RFC 6455)
    // ─────────────────────────────────────────────

    private function readFrame($client): ?array
    {
        $data = @socket_read($client, 2048, PHP_BINARY_READ);
        if ($data === false || $data === '') {
            return null;
        }
        
        if (strlen($data) < 2) {
            return null;
        }

        $byte1 = ord($data[0]);
        $byte2 = ord($data[1]);

        $fin = ($byte1 & 0x80) !== 0;
        $opcode = $byte1 & 0x0F;
        $masked = ($byte2 & 0x80) !== 0;
        $len = $byte2 & 0x7F;

        // Fragmented frame not supported
        if (!$fin) return null;

        if (!$masked) return null; // client must mask

        $offset = 2;

        if ($len === 126) {
            $len = unpack('n', substr($data, 2, 2))[1];
            $offset = 4;
        } elseif ($len === 127) {
            $len = unpack('J', substr($data, 2, 8))[1];
            $offset = 10;
        }

        $mask = substr($data, $offset, 4);
        $payload = substr($data, $offset + 4, $len);

        $decoded = '';
        for ($i = 0; $i < $len; $i++) {
            $decoded .= $payload[$i] ^ $mask[$i % 4];
        }

        return match ($opcode) {
            0x1 => ['type' => 'text', 'payload' => $decoded],
            0x8 => ['type' => 'close'],
            0x9 => ['type' => 'ping'],
            0xA => ['type' => 'pong'],
            default => null,
        };
    }

    // ─────────────────────────────────────────────
    // SEND FRAME
    // ─────────────────────────────────────────────

    protected function send($client, string $message): void
    {
        $frame = chr(0x81); // FIN + TEXT
        $len = strlen($message);

        if ($len <= 125) {
            $frame .= chr($len);
        } elseif ($len <= 65535) {
            $frame .= chr(126) . pack('n', $len);
        } else {
            $frame .= chr(127) . pack('J', $len);
        }

        socket_write($client, $frame . $message);
    }

    private function sendPong($client): void
    {
        socket_write($client, chr(0x8A) . chr(0x00));
    }

    protected function broadcast(string $message, $except = null): void
    {
        $exceptId = $except ? spl_object_id($except) : null;
        
        foreach ($this->clients as $client) {
            $clientId = spl_object_id($client);
            if ($clientId !== $exceptId) {
                $this->send($client, $message);
            }
        }
    }

    private function disconnect($client): void
    {
        $this->onClose($client);
        unset($this->clients[spl_object_id($client)]);
        @socket_close($client);
    }

    // ─────────────────────────────────────────────
    // EVENTS
    // ─────────────────────────────────────────────

    protected function onOpen(\Socket $client): void
    {
        echo "Client connected\n";
    }

    protected function onMessage(\Socket $client, string $message): void
    {
        $this->broadcast("Client says: $message", $client);
    }

    protected function onClose(\Socket $client): void
    {
        echo "Client disconnected\n";
    }
}
