<?php

namespace DFrame\Application;

use DFrame\Command\Helper\CommandEntry;

/**
 * **Command Manager**
 * 
 * Command class for registering and executing CLI commands.
 */
class Command
{
    public array $commands = [];

    public function register(string $name, callable|array|string $handler, bool $hiddenOnPhar = false): CommandEntry
    {
        $entry = new CommandEntry($this, $name, $handler, $hiddenOnPhar);
        $this->commands[$name] = $entry;
        return $entry;
    }

    public function property(string $name, mixed $default = null){}

    public function hasCommand(string $name): bool{
        return isset($this->commands[$name]);
    }

    public function run(array $argv): void
    {
        $cmd = $argv[1] ?? 'help';

        if (!isset($this->commands[$cmd])) {
            echo cli_red("Command not found: $cmd\n");
            exit(1);
        }

        $entry = $this->commands[$cmd];
        $handler = $entry instanceof CommandEntry ? $entry->getHandler() : $entry;

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
        $list = [];
        foreach ($this->commands as $name => $entry) {
            $info = '';
            if ($entry instanceof CommandEntry && $entry->info !== null) {
                $info = ' -> ' . $entry->info;
            }
            $list[] = $name . $info;
        }
        return $list;
    }
}

