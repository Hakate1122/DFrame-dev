<?php

namespace App\Command;

use DFrame\Command\Helper\ConsoleInput as Input;
use DFrame\Command\Helper\ConsoleOutput as Output;

class Sample
{
    /**
     * A simple command that asks the user a yes/no question and responds accordingly.
     */
    public static function handle(): void
    {
        $isOk = Input::askYesNo("Are u gay ?");
        if ($isOk) {
            Output::success("Hahahahaha, you are gay");
        } else {
            Output::info("Are you sure ?");
        }
    }

    /**
     * A simple quiz command to test PHP knowledge.
     * It asks 10 questions with multiple choice answers and keeps track of the score.
     */
    public static function quiz(): void
    {
        $questions = [
            [
                'question' => 'PHP stands for?',
                'choices' => [
                    'Personal Home Page',
                    'Private Home Page',
                    'Preprocessed Hypertext Page',
                    'Pretty Hot Pizza',
                ],
                'correctIndex' => 0,
            ],
            [
                'question' => 'Which symbol is used to access object properties in PHP?',
                'choices' => ['.', '->', '::', '=>'],
                'correctIndex' => 1,
            ],
            [
                'question' => 'Which is a PHP superglobal?',
                'choices' => ['$_GET', '$GET', '$GLOBALS_GET', '$SUPERGET'],
                'correctIndex' => 0,
            ],
            [
                'question' => 'Which operator is used for strict comparison in PHP?',
                'choices' => ['==', '===', '!=', '<>'],
                'correctIndex' => 1,
            ],
            [
                'question' => 'What does `count($arr)` return?',
                'choices' => ['Array keys', 'Array length', 'Array values', 'Array sum'],
                'correctIndex' => 1,
            ],
            [
                'question' => 'Which keyword is used to define a class in PHP?',
                'choices' => ['function', 'class', 'struct', 'object'],
                'correctIndex' => 1,
            ],
            [
                'question' => 'Which statement is used to handle exceptions?',
                'choices' => ['try/catch', 'if/else', 'switch/case', 'for/while'],
                'correctIndex' => 0,
            ],
            [
                'question' => 'What does `require` do when a file is missing?',
                'choices' => [
                    'Raises a fatal error and stops',
                    'Shows a warning and continues',
                    'Ignores silently',
                    'Auto-downloads the file',
                ],
                'correctIndex' => 0,
            ],
            [
                'question' => 'Which is the correct way to declare an array in PHP 7+?',
                'choices' => ['array(1,2,3)', '[1,2,3]', 'Both A and B', '{1,2,3}'],
                'correctIndex' => 2,
            ],
            [
                'question' => 'Which function returns the length of a string?',
                'choices' => ['str_len()', 'strlen()', 'length()', 'count()'],
                'correctIndex' => 1,
            ],
        ];

        shuffle($questions);

        $score = 0;
        $total = count($questions);

        Output::info("Quiz started: {$total} questions.\n");

        foreach ($questions as $i => $q) {
            $n = $i + 1;
            Output::info("---- Question {$n}/{$total} ----");

            $isCorrect = Input::askChoice($q['question'], $q['choices'], $q['correctIndex']);
            if ($isCorrect) {
                $score++;
                Output::success("Correct!\n");
            } else {
                Output::error("Wrong.\n");
            }
        }

        $percent = $total > 0 ? (int) round(($score / $total) * 100) : 0;
        Output::info("Quiz finished!");
        Output::success("Score: {$score}/{$total} ({$percent}%)");
    }

    public static function tryConnectSQL(): void
    {
        $host = Input::prompt("Enter database host", "localhost");
        $port = Input::prompt("Enter database port", "3306", Input::validateNumber());
        $user = Input::prompt("Enter database user", "root");
        $pass = Input::prompt("Enter database password", "");

        echo "Attempting to connect to database at {$host}:{$port} with user '{$user}'...\n";

        try {
            $pdo = new \PDO("mysql:host={$host};port={$port}", $user, $pass);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            echo "Connected successfully!\n";
        } catch (\Exception $e) {
            echo "Connection failed: " . $e->getMessage() . "\n";
        }
    }

    public static function tryConnectDB(): void
    {
        $dbType = Input::prompt("Enter database type (mysql/postgres/sqlite)", "mysql", function ($input) {
            $validTypes = ['mysql', 'postgres', 'sqlite'];
            if (!in_array(strtolower($input), $validTypes)) {
                throw new \Exception("Invalid database type. Choose from: " . implode(", ", $validTypes));
            }
            return true;
        });

        $host = Input::prompt("Enter database host", "localhost");
        $port = Input::prompt("Enter database port", $dbType === 'mysql' ? "3306" : ($dbType === 'postgres' ? "5432" : ""));
        $user = Input::prompt("Enter database user", "root");
        $pass = Input::prompt("Enter database password", "");

        echo "Attempting to connect to {$dbType} database at {$host}:{$port} with user '{$user}'...\n";

        try {
            $dsn = match (strtolower($dbType)) {
                'mysql' => "mysql:host={$host};port={$port}",
                'postgres' => "pgsql:host={$host};port={$port}",
                'sqlite' => "sqlite:{$host}",
                default => throw new \Exception("Unsupported database type."),
            };

            $pdo = new \PDO($dsn, $user, $pass);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            echo "Connected successfully to {$dbType} database!\n";
        } catch (\Exception $e) {
            echo "Connection failed: " . $e->getMessage() . "\n";
        }

        $dbName = Input::prompt("Enter database name to use", "testdb");
        echo "Switching to database '{$dbName}'...\n";
        try {
            $pdo->exec("USE {$dbName}");
            echo "Switched to database '{$dbName}' successfully!\n";
        } catch (\Exception $e) {
            echo "Failed to switch database: " . $e->getMessage() . "\n";
        }
    }
}
