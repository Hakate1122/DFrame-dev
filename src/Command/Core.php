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

        return function ($argv = null) use ($detectDeviceRuntime, $dfver) {
            $scriptName = isset($argv[0]) ? basename($argv[0]) : '';
            echo "DLI - DFrame CLI Core Helper\n";
            echo "Version: " . cli_green($dfver) . " | PHP: " . cli_blue(phpversion()) . " on " . cli_yellow($detectDeviceRuntime()) . "\n";
            echo "Usage: php dli <command> [options]\n\n";

            if (!App::isRunningFromPhar()) {
                if ($scriptName !== 'dli' && $scriptName !== 'dli.php') {
                    echo cli_gray("Don't change name, dli is fast too!\n\n");
                }
            }

            if (App::isRunningFromPhar()) {
                echo cli_yellow("Note: DLI is running from a PHAR archive. Some features may not work (e.g., starting the server, npm, or file writing)\n\n");
            }
            
            echo "Available commands:\n";
            echo "  help, -h        Show this help message\n";
            echo "  version, -v     Show application version\n";
            echo "  server, -s      Start the development server\n";
            echo "  list            List all available commands\n";
            echo "\n";
            echo "Available tools:\n";
            echo "  compile:ts      Compile TypeScript files to JavaScript (requires Node.js + tsc)\n";
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
