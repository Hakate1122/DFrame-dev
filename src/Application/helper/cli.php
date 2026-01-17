<?php
if (!function_exists('cli_echo')) {
    /**
     * Echo a message to the CLI.
     * @param string $message
     * @return void
     */
    function cli_echo(string $message): void
    {
        if (php_sapi_name() === 'cli') {
            echo $message . PHP_EOL;
        }
    }
}

if (!function_exists('cli_green')) {
    /**
     * Return a green colored message (do not echo).
     * Use echo cli_green($msg) to print.
     */
    function cli_green(string $message): string
    {
        if (php_sapi_name() === 'cli') {
            return "\033[32m" . $message . "\033[0m";
        }
        return $message;
    }
}

if (!function_exists('cli_red')) {
    /**
     * Return a red colored message (do not echo).
     */
    function cli_red(string $message): string
    {
        if (php_sapi_name() === 'cli') {
            return "\033[31m" . $message . "\033[0m";
        }
        return $message;
    }
}

if (!function_exists('cli_yellow')) {
    /**
     * Return a yellow colored message (do not echo).
     */
    function cli_yellow(string $message): string
    {
        if (php_sapi_name() === 'cli') {
            return "\033[33m" . $message . "\033[0m";
        }
        return $message;
    }
}

if (!function_exists('cli_blue')) {
    /**
     * Return a blue colored message.
     */
    function cli_blue(string $message): string
    {
        if (php_sapi_name() === 'cli') {
            return "\033[34m" . $message . "\033[0m";
        }
        return $message;
    }
}

if (!function_exists('cli_cyan')) {
    /**
     * Return a cyan colored message (do not echo).
     */
    function cli_cyan(string $message): string
    {
        if (php_sapi_name() === 'cli') {
            return "\033[36m" . $message . "\033[0m";
        }
        return $message;
    }
}

if (!function_exists('cli_gray')) {
    /**
     * Return a gray colored message (do not echo).
     */
    function cli_gray(string $message): string
    {
        if (php_sapi_name() === 'cli') {
            return "\033[90m" . $message . "\033[0m";
        }
        return $message;
    }
}