<?php

namespace DLight\Application;

use DLight\Command\Helper\CommandEntry;

/**
 * **Command Manager**
 * 
 * Command class for registering and executing CLI commands.
 */
class Command
{
    public array $commands = [];

    /**
     * Register a new command.
     *
     * **Example**: register('greet', [\App\Command\Greet::class, 'handle'])
     *
     * @param string $name Command name
     * @param callable|array|string $handler Function, method array, or class name
     * @param bool $hiddenOnPhar Whether to hide this command in Phar builds
     */
    public function register(string $name, callable|array|string $handler, bool $hiddenOnPhar = false): CommandEntry
    {
        $entry = new CommandEntry($this, $name, $handler, $hiddenOnPhar);
        $this->commands[$name] = $entry;
        return $entry;
    }

    /**
     * Register an alias for an existing command.
     *
     * Example: registerAlias('say:greet', 'greet')
     *
     * @param string $target Name of the existing command to alias
     */
    public function registerAlias(string $alias, string $target): CommandEntry
    {
        if (!isset($this->commands[$target])) {
            throw new \InvalidArgumentException("Target command not found: $target");
        }
        $this->commands[$alias] = $this->commands[$target];
        return $this->commands[$target];
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

