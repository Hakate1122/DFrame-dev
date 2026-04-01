<?php

namespace DFrame\Command;

class Setting
{
    public static function handle()
    {
        return function ($argv = null) {
            echo "DLI - DFrame CLI Settings\n";
            echo "Usage: php dli setting [key] [value]\n\n";

            if (isset($argv[2])) {
                $key = $argv[2];
                $value = $argv[3] ?? null;
                if ($value === null) {
                    echo "Current value of '$key': " . (getenv($key) ?: 'not set') . "\n";
                } else {
                    putenv("$key=$value");
                    echo "Set '$key' to '$value'\n";
                }
            } else {
                echo "No key provided. Use 'php dli setting [key] [value]' to set a value or 'php dli setting [key]' to view a value.\n";
            }
        };
    }
}