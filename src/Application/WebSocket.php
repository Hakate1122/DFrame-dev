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

    /**
     * Constructor to init new WebSocket server instance.
     *
     * @param string $host The host address to bind the server to.
     * @param int $port The port number to listen on.
     */
    public function __construct(string $host = '0.0.0.0', int $port = 9501)
    {
        $this->host = $host;
        $this->port = $port;
    }

    /**
     * Start the WebSocket server.
     *
     * @return void
     */
    public function start(): void
    {
        $this->master = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($this->master, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind($this->master, $this->host, $this->port);
        socket_listen($this->master);

        if (PHP_SAPI === 'cli') {
            echo "WebSocket server started at ws://{$this->host}:{$this->port}\n";
        }

        $this->loop();
    }

    /**
     * Main server loop to handle connections and messages.
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

            foreach ($read as $client) {
                if ($client === $this->master) {
                    continue;
                }

                $frame = $this->readFrame($client);

                if ($frame === null) {
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

    /**
     * Perform the WebSocket handshake with a client.
     *
     * @param \Socket $client The client socket.
     * @return bool True on successful handshake, false otherwise.
     */
    private function handshake($client): bool
    {
        $request = '';
        $maxHeaderSize = 8192;
        socket_set_nonblock($client);
        $start = microtime(true);
        while (strpos($request, "\r\n\r\n") === false && strlen($request) < $maxHeaderSize) {
            $part = @socket_read($client, 2048);
            if ($part === false) {
                $err = socket_last_error($client);
                if ($err === SOCKET_EWOULDBLOCK || $err === SOCKET_EAGAIN) {
                    if (microtime(true) - $start > 2.0) break;
                    usleep(10000);
                    continue;
                }
                $this->logSocketError('handshake read failed', $err);
                socket_set_block($client);
                return false;
            }

            if ($part === '') {
                socket_set_block($client);
                return false;
            }

            $request .= $part;
            if (microtime(true) - $start > 5.0) break;
        }
        socket_set_block($client);

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

        $sent = @socket_write($client, $response);
        if ($sent === false) {
            $this->logSocketError('handshake write failed', socket_last_error($client));
            return false;
        }
        return true;
    }

    // ─────────────────────────────────────────────
    // FRAME PARSING (RFC 6455)
    // ─────────────────────────────────────────────

    /**
     * Read and parse a WebSocket frame from a client.
     *
     * @param \Socket $client The client socket.
     * @return array|null Parsed frame data or null on failure.
     */
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

        if (!$fin) return null;

        if (!$masked) return null;

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

    /**
     * Send a text message to a client.
     *
     * @param \Socket $client The client socket.
     * @param string $message The message to send.
     * @return void
     */
    protected function send($client, string $message): void
    {
        $frame = chr(0x81);
        $len = strlen($message);

        if ($len <= 125) {
            $frame .= chr($len);
        } elseif ($len <= 65535) {
            $frame .= chr(126) . pack('n', $len);
        } else {
            $frame .= chr(127) . pack('J', $len);
        }

        $data = $frame . $message;
        $total = strlen($data);
        $written = 0;

        while ($written < $total) {
            $chunk = substr($data, $written);
            $sent = @socket_write($client, $chunk);

            if ($sent === false) {
                $err = socket_last_error($client);
                $this->logSocketError('send failed', $err);
                $this->disconnect($client);
                return;
            }

            $written += $sent;
        }
    }

    /**
     * Send a Pong frame to a client in response to a Ping.
     *
     * @param \Socket $client The client socket.
     * @return void
     */
    private function sendPong($client): void
    {
        $data = chr(0x8A) . chr(0x00);
        $sent = @socket_write($client, $data);
        if ($sent === false) {
            $err = socket_last_error($client);
            $this->logSocketError('sendPong failed', $err);
            $this->disconnect($client);
        }
    }

    /**
     * Log a socket error to stdout with human readable message.
     */
    private function logSocketError(string $context, int $errno): void
    {
        $msg = socket_strerror($errno);
        $time = (new \DateTime())->format('H:i:s');
        if (PHP_SAPI === 'cli') {
            echo "[{$time}] Socket error ({$context}): ({$errno}) {$msg}\n";
        } else {
            error_log("Socket error ({$context}): ({$errno}) {$msg}");
        }
    }

    /**
     * Broadcast a message to all connected clients except the sender.
     *
     * @param string $message The message to broadcast.
     * @param \Socket|null $except The client socket to exclude from broadcasting.
     * @return void
     */
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

    /**
     * Disconnect a client and clean up resources.
     *
     * @param \Socket $client The client socket to disconnect.
     * @return void
     */
    private function disconnect($client): void
    {
        $this->onClose($client);
        unset($this->clients[spl_object_id($client)]);
        @socket_close($client);
    }
    // ─────────────────────────────────────────────
    /**
     * Handle new client connection.
     *
     * @param \Socket $client The client socket.
     * @return void
     */
    protected function onOpen(\Socket $client): void
    {
        echo "Client connected\n";
    }

    /**
     * Handle incoming message from a client.
     *
     * @param \Socket $client The client socket.
     * @param string $message The received message.
     * @return void
     */
    protected function onMessage(\Socket $client, string $message): void
    {
        $this->broadcast("Client says: $message", $client);
    }

    /**
     * Handle client disconnection.
     *
     * @param \Socket $client The client socket.
     * @return void
     */
    protected function onClose(\Socket $client): void
    {
        echo "Client disconnected\n";
    }
}
