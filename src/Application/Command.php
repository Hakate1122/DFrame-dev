<?php

namespace DFrame\Application;

class Command
{
    protected array $commands = [];

    public function register(string $name, callable|array|string $handler): void
    {
        $this->commands[$name] = $handler;
    }

    public function run(array $argv): void
    {
        $cmd = $argv[1] ?? 'help';

        if (!isset($this->commands[$cmd])) {
            echo "Unknown command: $cmd\n\n";
            $cmd = 'help';
        }

        $handler = $this->commands[$cmd];

        if (is_string($handler) && class_exists($handler)) {
            $instance = new $handler();
            $instance($argv);
            return;
        }

        if (is_callable($handler)) {
            $handler($argv);
            return;
        }

        echo "Invalid command handler for: $cmd\n";
    }

    public function list(): array
    {
        return array_keys($this->commands);
    }
}
