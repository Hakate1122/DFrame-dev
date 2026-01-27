<?php
namespace DFrame\JsonDB;
class Server
{
    public function __construct($addressServer = '0.0.0.0', $portServer = 9501)
    {
        set_time_limit(0);
        error_reporting(E_ALL);

        $address = $addressServer;
        $port    = $portServer;

        $sock = stream_socket_server("tcp://$address:$port", $errno, $errstr);
        if (!$sock) {
            die("Socket error: $errstr ($errno)\n");
        }

        echo "JsonDB Server running at $address:$port\n";

        $db = [];

        while ($conn = @stream_socket_accept($sock, -1)) {

            $input = trim(fgets($conn));

            // ⚠️ client không gửi gì
            if ($input === '') {
                fwrite($conn, json_encode([
                    'error' => 'Empty request'
                ]));
                fclose($conn);
                continue;
            }

            $req = json_decode($input, true);

            // ⚠️ JSON lỗi
            if (!is_array($req)) {
                fwrite($conn, json_encode([
                    'error' => 'Invalid JSON'
                ]));
                fclose($conn);
                continue;
            }

            // ⚠️ thiếu field
            if (empty($req['action']) || empty($req['table'])) {
                fwrite($conn, json_encode([
                    'error' => 'Missing action or table'
                ]));
                fclose($conn);
                continue;
            }

            $action = $req['action'];
            $table  = preg_replace('/[^a-zA-Z0-9_\-]/', '', $req['table']);
            $file   = ROOT_DIR . "/app/database/$table.json";

            // load table vào RAM
            if (!isset($db[$table])) {
                $db[$table] = file_exists($file)
                    ? json_decode(file_get_contents($file), true)
                    : [];
            }

            // xử lý action
            switch ($action) {
                case 'ping':
                    // return a simple status object
                    fwrite($conn, json_encode([
                        'status' => 'ok',
                        'time' => time(),
                        'tables' => array_keys($db),
                    ]));
                    break;
                case 'insert':
                    $data = $req['data'] ?? [];
                    $data['id'] = uniqid();
                    $db[$table][] = $data;
                    file_put_contents($file, json_encode($db[$table], JSON_PRETTY_PRINT));
                    fwrite($conn, json_encode($data));
                    break;

                case 'find':
                    fwrite($conn, json_encode($db[$table]));
                    break;

                default:
                    fwrite($conn, json_encode([
                        'error' => 'Unknown action'
                    ]));
            }

            fclose($conn);
        }
    }
}
