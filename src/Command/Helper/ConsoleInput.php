<?php

namespace DFrame\Command\Helper;

/**
 * ConsoleInput provides methods to prompt user input from the console,
 * including validation and special input types.
 * 
 * Example usage: 
 * * ConsoleInput::prompt("Enter your name:");
 * * ConsoleInput::askYesNo("Continue?", true);
 * * ConsoleInput::select("Choose option:", ["1" => "One", "2" => "Two"], "1");
 */
class ConsoleInput
{
    /**
     * Basic prompt with optional validation.
     *
     * @param string $message The prompt message.
     * @param string|null $default The default value if input is empty.
     * @param callable|null $validator  Return true if valid, or string error message if invalid.
     * @return string
     */
    public static function prompt(
        string $message,
        ?string $default = null,
        ?callable $validator = null
    ): string {
        while (true) {
            $msg = $default !== null
                ? "{$message} [default: {$default}]: "
                : "{$message}: ";

            echo $msg;

            $input = fgets(STDIN);

            if ($input === false) {
                return $default ?? '';
            }

            $input = trim($input);

            if ($input === '' && $default !== null) {
                return $default;
            }

            if ($validator !== null) {
                $valid = $validator($input);

                if ($valid === true) {
                    return $input;
                }

                if (is_string($valid)) {
                    echo "Wrong input: {$valid}\n";
                    continue;
                }
            }

            if ($input !== '') {
                return $input;
            }

            echo "Please enter a non-empty value.\n";
        }
    }


    /**
     * Ask Yes/No question.
     * Return: true = Yes, false = No
     * 
     * @param string $message The question message.
     * @param bool $default Default answer if input is empty (true = Yes, false = No).
     * @return bool
     */
    public static function askYesNo(
        string $message,
        bool $default = true
    ): bool {
        $defaultText = $default ? "Y/n" : "y/N";

        while (true) {
            echo "{$message} [{$defaultText}]: ";
            $input = trim(fgets(STDIN));

            if ($input === '') {
                return $default;
            }

            $lower = strtolower($input);

            if (in_array($lower, ['y', 'yes'], true)) return true;
            if (in_array($lower, ['n', 'no'], true)) return false;

            echo "Please answer yes or no.\n";
        }
    }


    /**
     * Let user select an option from a list.
     *
     * @param string $message The prompt message.
     * @param array $options Key-value pairs of options (key => label).
     * @param string|null $defaultKey
     * @return string selected key
     */
    public static function select(
        string $message,
        array $options,
        ?string $defaultKey = null
    ): string {

        echo "{$message}:\n";

        foreach ($options as $key => $label) {
            echo "  [$key] $label\n";
        }

        while (true) {
            $prompt = $defaultKey ? "Choose option [default: {$defaultKey}]: " : "Choose option: ";
            echo $prompt;

            $input = trim(fgets(STDIN));

            if ($input === '' && $defaultKey !== null) {
                return $defaultKey;
            }

            if (array_key_exists($input, $options)) {
                return $input;
            }

            echo "Invalid option. Try again.\n";
        }
    }

    /**
     * Prompt for secret input (e.g., password) without echoing.
     * 
     * **Note:** Not working on Windows consoles.
     *
     * @param string $message The prompt message.
     * @param string|null $default The default value if input is empty.
     * @return string
     */
    public static function promptSecret(
        string $message,
        ?string $default = null
    ): string {
        if (strncasecmp(PHP_OS, 'WIN', 3) == 0) {
            // Windows does not support hiding input in console easily.
            echo "Warning: Secret input may be visible on Windows consoles.\n";
            return self::prompt($message, $default);
        } else {
            $cmd = "/usr/bin/env bash -c 'read -s -p \"" . addslashes($message) . ": \" mypassword && echo \$mypassword'";
            $input = rtrim(shell_exec($cmd));
            echo "\n";

            if ($input === '' && $default !== null) {
                return $default;
            }

            return $input;
        }
    }


    // --- Validators ---

    /** Validate that input is a number. */
    public static function validateNumber(): callable
    {
        return function ($value) {
            return is_numeric($value)
                ? true
                : "Value must be a number.";
        };
    }

    /** Validate that input is a valid email address. */
    public static function validateEmail(): callable
    {
        return function ($value) {
            return filter_var($value, FILTER_VALIDATE_EMAIL)
                ? true
                : "Invalid email format.";
        };
    }

    /** Validate that input matches a regex pattern. */
    public static function validateRegex(string $pattern): callable
    {
        return function ($value) use ($pattern) {
            return preg_match($pattern, $value)
                ? true
                : "Input does not match required pattern.";
        };
    }
}
