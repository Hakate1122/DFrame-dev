<?php

namespace DFrame\Command\Helper;

/**
 * ConsoleOutput provides methods to output messages to the console
 * with different levels and optional color coding.
 * 
 * Example levels: info, success, warning, error, muted.
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
     */
    public static function enableColors(bool $enable = true): void
    {
        self::$useColors = $enable;
    }

    /** Apply color codes to text if colors are enabled.
     */
    protected static function color(string $text, string $color): string
    {
        if (!self::$useColors) {
            return $text;
        }

        return $color . $text . self::RESET;
    }

    /** Write a line to the console.
     */
    protected static function writeln(string $text): void
    {
        echo $text . PHP_EOL;
    }

    // ---- Output levels ----

    /** Output informational message: [INFO] This is an informational message.
     */
    public static function info(string $message): void
    {
        self::writeln(
            self::color("[INFO] ", self::BLUE) . $message
        );
    }

    /** Output success message: [OK] This is a success message.
     */
    public static function success(string $message): void
    {
        self::writeln(
            self::color("[OK] ", self::GREEN) . $message
        );
    }
    /** Alias for success message
     */
    public static function ok(string $message): void
    {
        self::success($message);
    }

    /** Output warning message: [WARN] This is a warning message.
     */
    public static function warning(string $message): void
    {
        self::writeln(
            self::color("[WARN] ", self::YELLOW) . $message
        );
    }

    /** Output error message: [ERROR] This is an error message.
     */
    public static function error(string $message): void
    {
        self::writeln(
            self::color("[ERROR] ", self::RED) . $message
        );
    }

    /** Output muted message (gray color).
     */
    public static function muted(string $message): void
    {
        self::writeln(
            self::color($message, self::GRAY)
        );
    }
}
