<?php

namespace DFrame\Command;

class ServerCopy
{
    public static function server()
    {
        return function ($argv = []) {
            $opts = [];
            for ($i = 2; $i < count($argv); $i++) {
                $arg = $argv[$i];
                if (str_starts_with($arg, '--')) {
                    $parts = explode('=', substr($arg, 2), 2);
                    $opts[$parts[0]] = $parts[1] ?? true;
                } elseif (str_starts_with($arg, '-')) {
                    $key = ltrim($arg, '-');
                    $val = $argv[$i + 1] ?? true;
                    if (!str_starts_with($val, '-')) {
                        $opts[$key] = $val;
                        $i++;
                    } else {
                        $opts[$key] = true;
                    }
                }
            }

            $mode = strtolower($opts['mode'] ?? $opts['m'] ?? 'lan');
            $port = (int)($opts['port'] ?? $opts['p'] ?? 8000);
            $bind = $opts['bind'] ?? null;

            $detectLanIp = function (): string {
                $ip = gethostbyname(gethostname());
                if (filter_var($ip, FILTER_VALIDATE_IP) && !str_starts_with($ip, '127.')) {
                    return $ip;
                }

                if (stripos(PHP_OS, 'WIN') === 0) {
                    @exec('ipconfig', $out);
                    foreach ($out as $line) {
                        if (preg_match('/IPv4[^\:]*:\s*([\d\.]+)/i', $line, $m)) return $m[1];
                    }
                } else {
                    @exec('hostname -I', $out);
                    if (!empty($out[0])) {
                        $ips = preg_split('/\s+/', trim($out[0]));
                        foreach ($ips as $i) if (!str_starts_with($i, '127.')) return $i;
                    }
                    @exec("ip -4 addr show scope global", $out);
                    foreach ($out as $line) {
                        if (preg_match('/inet\s+([\d\.]+)/', $line, $m)) return $m[1];
                    }
                }
                return '0.0.0.0';
            };

            $public = defined('INDEX_DIR') ? INDEX_DIR : __DIR__ . '/../../public';
            if ($mode === 'local' || $mode === 'localhost') {
                $bindHost = $bind ?? '127.0.0.1';
                $displayHost = $bindHost === '0.0.0.0' ? '127.0.0.1' : $bindHost;
            } else {

                $bindHost = $bind ?? '0.0.0.0';
                $detected = $detectLanIp();
                $displayHost = ($detected === '0.0.0.0') ? 'localhost' : $detected;
            }

            echo "Starting development server (mode: $mode)\n";
            echo "Document root: $public\n";
            echo "Listening on: $bindHost:$port\n";
            echo "Accessible at: http://" . $displayHost . ":" . $port . "\n";
            echo "Press Ctrl+C to stop\n\n";

            // Use a router script so we can customize access logs
            $router = $public . '/server-router.php';
            passthru(sprintf('php -S %s:%d -t %s %s', $bindHost, $port, escapeshellarg($public), escapeshellarg($router)));
        };
    }
}
