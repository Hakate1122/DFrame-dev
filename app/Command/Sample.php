<?php

namespace App\Command;

use DFrame\Command\Helper\ConsoleInput as Input;
use DFrame\Command\Helper\ConsoleOutput as Output;

class Sample
{
    public static function handle(): void
    {
        $isOk = Input::askYesNo("Continue ?");
        if ($isOk) {
            Output::success("Continuing...");
        } else {
            Output::error("Cancelled.");
        }
    }

    public static function tryConnectSQL(): void
    {
        $host = Input::prompt("Enter database host", "localhost");
        $port = Input::prompt("Enter database port", "3306", Input::validateNumber());
        $user = Input::prompt("Enter database user", "root");
        $pass = Input::prompt("Enter database password", "");

        // Simulate connection attempt
        echo "Attempting to connect to database at {$host}:{$port} with user '{$user}'...\n";
        // Here you would add actual connection logic

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
            switch (strtolower($dbType)) {
                case 'mysql':
                    $dsn = "mysql:host={$host};port={$port}";
                    break;
                case 'postgres':
                    $dsn = "pgsql:host={$host};port={$port}";
                    break;
                case 'sqlite':
                    $dsn = "sqlite:{$host}";
                    break;
                default:
                    throw new \Exception("Unsupported database type.");
            }

            $pdo = new \PDO($dsn, $user, $pass);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            echo "Connected successfully to {$dbType} database!\n";
        } catch (\Exception $e) {
            echo "Connection failed: " . $e->getMessage() . "\n";
        }

        // Choose database
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
