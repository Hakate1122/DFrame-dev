<?php
namespace DFrame\JsonDB;
class Client
{
    private string $host;
    private int $port;
    private int $timeout;

    public function __construct(
        string $host = '0.0.0.0',
        int $port = 9501,
        int $timeout = 3
    ) {
        $this->host    = $host;
        $this->port    = $port;
        $this->timeout = $timeout;
    }

    private function send(array $payload)
    {
        $fp = @fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout);
        if (!$fp) {
            throw new \Exception("Cannot connect to JsonDB Server: $errstr ($errno)");
        }

        stream_set_timeout($fp, $this->timeout);

        fwrite($fp, json_encode($payload, JSON_UNESCAPED_UNICODE) . "\n");

        $response = '';
        while (!feof($fp)) {
            $response .= fgets($fp, 1024);
        }

        fclose($fp);

        $data = json_decode(trim($response), true);
        return $data ?? $response;
    }

    /* ===== CRUD ===== */

    public function insert(string $table, array $data)
    {
        return $this->send([
            'action' => 'insert',
            'table'  => $table,
            'data'   => $data
        ]);
    }

    public function find(string $table)
    {
        return $this->send([
            'action' => 'find',
            'table'  => $table
        ]);
    }

    public function ping()
    {
        return $this->send([
            'action' => 'ping',
            'table'  => '_system'
        ]);
    }
}
