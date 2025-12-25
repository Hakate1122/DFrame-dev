<?php

namespace DFrame\Command;

use DFrame\Application\App;

/**
 * Core command implementations for the CLI application.
 * 
 * Provides help, version, and command listing functionalities.
 * 
 * Usage:
 **  php dli help[-h]       Show help information
 **  php dli version[-v]    Show application version
 */
class Core
{
    public App $app;
    public static function help()
    {
        $dfver = App::VERSION ?? "unknown";

        $detectDeviceRuntime = static function (): string {
            if (PHP_OS_FAMILY === 'Linux') {
                if (is_file('/system/build.prop')) {
                    return 'android';
                }
                return 'linux';
            }

            return match (PHP_OS_FAMILY) {
                'Windows' => 'windows',
                'Darwin'  => 'macos',
                default   => 'unknown',
            };
        };

        return function () use ($detectDeviceRuntime, $dfver) {
            echo "DLI - DFrame CLI Core Helper\n";
            echo "Version: " . cli_green($dfver) . " | PHP: " . cli_blue(phpversion()) . " on: " . cli_yellow($detectDeviceRuntime()) . "\n";
            echo "Usage: php dli <command> [options]\n\n";

            if (App::isRunningFromPhar()) {
                echo cli_yellow("Note: DLI is running from a PHAR archive. Some features may not work (e.g., starting the server, npm, or file writing)\n\n");
            }
            echo "Available commands:\n";
            echo "  help, -h        Show this help message\n";
            echo "  version, -v     Show application version\n";
            echo "  server, -s      Start the development server\n";
            echo "  list            List all available commands\n";
            echo "  npm-install     Run npm install in the project directory\n";
            echo "  vite            Start the Vite development server\n";
            echo "  compile:ts      Compile TypeScript files to JavaScript\n";
            echo "\n";
            echo "Options:\n";
            echo " Server command options: php dli server[-s] <options>\n";
            echo "  --host          Bind to a specific host (default: localhost)\n";
            echo "  --port          Specify a port (default: 8000)\n";
            echo "  --mode          Specify the mode (default: lan)\n";
            echo "\n";
        };
    }

    public static function version()
    {
        return function () {
            echo "Version: " . cli_green(App::VERSION ?? "unknown") . "\n";
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
}
