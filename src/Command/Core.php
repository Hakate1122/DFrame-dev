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

    private static function shellReadFirstLine(string $command): ?string
    {
        $out = @shell_exec($command);
        if (!is_string($out)) {
            return null;
        }
        $out = trim($out);
        if ($out === '') {
            return null;
        }
        $lines = preg_split('/\R/', $out);
        if (!is_array($lines) || count($lines) === 0) {
            return null;
        }
        $line = trim((string) $lines[0]);
        return $line !== '' ? $line : null;
    }

    private static function parseOsReleasePrettyName(string $path = '/etc/os-release'): ?string
    {
        if (!is_file($path) || !is_readable($path)) {
            return null;
        }
        $content = @file_get_contents($path);
        if (!is_string($content) || $content === '') {
            return null;
        }
        foreach (preg_split('/\R/', $content) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (str_starts_with($line, 'PRETTY_NAME=')) {
                $value = substr($line, strlen('PRETTY_NAME='));
                $value = trim($value);
                if ($value === '') {
                    return null;
                }
                if (($value[0] ?? '') === '"' && str_ends_with($value, '"')) {
                    $value = substr($value, 1, -1);
                }
                return $value !== '' ? $value : null;
            }
        }
        return null;
    }

    private static function readWindowsRegistryValue(string $valueName): ?string
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            return null;
        }

        $key = 'HKLM\SOFTWARE\Microsoft\Windows NT\CurrentVersion';
        $cmd = 'reg query "' . $key . '" /v ' . escapeshellarg($valueName) . ' 2>NUL';
        $out = @shell_exec($cmd);
        if (!is_string($out) || trim($out) === '') {
            return null;
        }

        foreach (preg_split('/\R/', $out) as $line) {
            $line = trim($line);
            if ($line === '' || stripos($line, $valueName) === false) {
                continue;
            }
            // Example: ProductName    REG_SZ    Windows 11 Pro
            $parts = preg_split('/\s{2,}/', $line);
            if (is_array($parts) && count($parts) >= 3) {
                $value = trim((string) end($parts));
                if (preg_match('/^0x[0-9a-f]+$/i', $value)) {
                    $value = (string) hexdec(substr($value, 2));
                }
                return $value !== '' ? $value : null;
            }
        }

        return null;
    }

    private static function getOsDetails(): array
    {
        $details = [
            'family' => PHP_OS_FAMILY,
            'name' => null,
            'version' => null,
            'build' => null,
        ];

        if (PHP_OS_FAMILY === 'Windows') {
            $productName = self::readWindowsRegistryValue('ProductName');
            $displayVersion = self::readWindowsRegistryValue('DisplayVersion');
            $currentBuild = self::readWindowsRegistryValue('CurrentBuild');
            $currentBuildNumber = self::readWindowsRegistryValue('CurrentBuildNumber');
            $ubr = self::readWindowsRegistryValue('UBR');

            $details['name'] = $productName ?: 'Windows';
            $details['version'] = $displayVersion ?: php_uname('r');

            $buildBase = $currentBuild ?: $currentBuildNumber ?: null;
            $details['build'] = $buildBase
                ? ($ubr ? ($buildBase . '.' . $ubr) : $buildBase)
                : php_uname('r');

            return $details;
        }

        if (PHP_OS_FAMILY === 'Darwin') {
            // macOS
            $productName = self::shellReadFirstLine('sw_vers -productName 2>/dev/null');
            $productVersion = self::shellReadFirstLine('sw_vers -productVersion 2>/dev/null');
            $buildVersion = self::shellReadFirstLine('sw_vers -buildVersion 2>/dev/null');

            $details['name'] = $productName ?: 'macOS';
            $details['version'] = $productVersion ?: php_uname('r');
            $details['build'] = $buildVersion ?: php_uname('v');
            return $details;
        }

        if (PHP_OS_FAMILY === 'Linux') {
            // Linux / Android (Termux) friendly
            $pretty = self::parseOsReleasePrettyName('/etc/os-release');
            if ($pretty === null && is_file('/system/build.prop')) {
                $pretty = 'Android';
            }

            $details['name'] = $pretty ?: 'Linux';
            $details['version'] = php_uname('r'); // kernel release
            $details['build'] = php_uname('v');   // kernel version string

            // Try to enrich Android info (best-effort; safe to fail)
            if (is_file('/system/build.prop')) {
                $androidRelease = self::shellReadFirstLine('getprop ro.build.version.release 2>/dev/null');
                $androidSdk = self::shellReadFirstLine('getprop ro.build.version.sdk 2>/dev/null');
                if ($androidRelease || $androidSdk) {
                    $v = trim('Android ' . ($androidRelease ?: '') . ($androidSdk ? ' (SDK ' . $androidSdk . ')' : ''));
                    $details['version'] = $v !== '' ? $v : $details['version'];
                }
            }

            return $details;
        }

        // Generic fallback for other OSes (BSD, Solaris, etc.)
        $details['name'] = php_uname('s');
        $details['version'] = php_uname('r');
        $details['build'] = php_uname('v');
        return $details;
    }

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
            $os = self::getOsDetails();
            echo "DLI - DFrame CLI Core Helper\n";
            echo "Version: " . cli_green($dfver) . " | PHP: " . cli_blue(phpversion()) . " on " . cli_yellow($detectDeviceRuntime()) . "\n";
            if (!empty($os['name']) || !empty($os['version']) || !empty($os['build'])) {
                $osName = (string) ($os['name'] ?? $os['family'] ?? 'unknown');
                $osVersion = (string) ($os['version'] ?? '');
                $osBuild = (string) ($os['build'] ?? '');

                $parts = array_values(array_filter([$osName, $osVersion !== '' ? "($osVersion)" : null, $osBuild !== '' ? "build $osBuild" : null]));
                echo "OS: " . cli_yellow(implode(' ', $parts)) . "\n";
            }
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
            echo "  help, -h                Show this help message\n";
            echo "  version, -v             Show application version\n";
            echo "  server, -s              Start the development server\n";
            echo "  list                    List all available commands\n";
            echo "Add commands - create a new components:\n";
            echo "  add [type]              Create a new component[controller, model, view, middleware, command, mail]\n";
            echo "  add:controller/ctrl     Create a new controller\n";
            echo "  add:model/mdl           Create a new model\n";
            echo "  add:view/vw             Create a new view\n";
            echo "  add:middleware/mdw      Create a new middleware\n";
            echo "  add:command/cmd         Create a new command\n";
            echo "  add:mail                Create a new mail class\n";
            echo "  For add commands, use --name=Name to specify the name of the component\n";
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
