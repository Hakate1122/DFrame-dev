<?php

namespace DFrame\Command;

class Core
{
    public static function help()
    {
        return function () {
            echo "DLI - DFrame CLI Core Help\n";
            echo "Version: " . cli_green(\DFrame\Application\App::VERSION ?? "unknown") . "\n\n";
            echo "Usage: dli <command> [options]\n\n";
            echo "Available commands:\n";
            echo "  help, -h        Show this help message\n";
            echo "  version, -v     Show application version\n";
            echo "  server, -s      Start the development server\n";
            echo "  list            List all available commands\n";
        };
    }

    public static function version()
    {
        return function () {
            echo "Version: " . (\DFrame\Application\App::VERSION ?? "unknown") . "\n";
        };
    }

    public static function list(array $commands)
    {
        return function () use ($commands) {
            echo "Available commands:\n";
            foreach ($commands as $cmd) {
                echo "  - $cmd\n";
            }
        };
    }

    public static function server()
    {
        return function () {
            $host = "0.0.0.0:8000";
            $public = defined('INDEX_DIR') ? INDEX_DIR : __DIR__ . '/../../public';

            echo "Starting server at http://$host\n";
            passthru("php -S $host -t $public");
        };
    }
}
