<?php

declare(strict_types=1);

namespace DFrame\Command\Helper;

/**
 * **ConsoleOutput Helper**
 * 
 * ConsoleOutput provides methods to output messages to the console
 * with different levels and optional color coding.
 * 
 * Example levels: info, success/ok, warning, error/fail, muted.
 * 
 * Example output:
 *   [INFO] This is an informational message.
 */
class ConsoleOutput
{
    protected static bool $useColors = true;

    protected const RESET  = "\033[0m";
    protected const RED    = "\033[31m";
    protected const GREEN  = "\033[32m";
    protected const YELLOW = "\033[33m";
    protected const BLUE   = "\033[34m";
    protected const GRAY   = "\033[90m";

    /**
     * Enable or disable colored output.
     * @param bool $enable Whether to enable colored output.
     */
    public static function enableColors(bool $enable = true): void
    {
        self::$useColors = $enable;
    }

    /**
     *  Apply color codes to text if colors are enabled.
     * @param string $text The text to color.
     * @param string $color The color code to apply.
     * @return string The colored text.
     */
    protected static function color(string $text, string $color): string
    {
        if (!self::$useColors) {
            return $text;
        }

        return $color . $text . self::RESET;
    }

    /**
     *  Write a line to the console.
     * @param string $text The text to write.
     */
    protected static function writeln(string $text): void
    {
        echo $text . PHP_EOL;
    }

    // ---- Output levels ----

    /**
     * Output informational message: [INFO] This is an informational message.
     * @param string $message The message to output.
     */
    public static function info(string $message): void
    {
        self::writeln(
            self::color("[INFO] ", self::BLUE) . $message
        );
    }

    /**
     * Output success message: [OK] This is a success message.
     * @param string $message The message to output.
     */
    public static function success(string $message): void
    {
        self::writeln(
            self::color("[OK] ", self::GREEN) . $message
        );
    }
    /**
     * Alias for success message
     * @param string $message The message to output.
     */
    public static function ok(string $message): void
    {
        self::success($message);
    }

    /**
     * Output warning message: [WARN] This is a warning message.
     * @param string $message The message to output.
     */
    public static function warning(string $message): void
    {
        self::writeln(
            self::color("[WARN] ", self::YELLOW) . $message
        );
    }

    /**
     * Output error message: [ERROR] This is an error message.
     * @param string $message The message to output.
     */
    public static function error(string $message): void
    {
        self::writeln(
            self::color("[ERROR] ", self::RED) . $message
        );
    }

    /**
     * Output failure message (alias for error).
     * @param string $message The message to output.
     */
    public static function fail(string $message): void
    {
        self::error($message);
    }

    /**
     * Output muted message (gray color).
     * @param string $message The message to output.
     */
    public static function muted(string $message): void
    {
        self::writeln(
            self::color($message, self::GRAY)
        );
    }
}
