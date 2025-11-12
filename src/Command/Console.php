<?php
namespace DFrame\Command;

class Console {
    protected array $commands = [];

    public function register(Command $command) {
        $this->commands[$command->getName()] = $command;
    }

    public function run(array $argv) {
        $commandName = $argv[1] ?? 'help';
        $args = array_slice($argv, 2);

        if (!isset($this->commands[$commandName])) {
            echo "Command '$commandName' not found.\n";
            $this->printHelp();
            return;
        }

        $this->commands[$commandName]->execute($args);
    }

    public function printHelp() {
        echo "Available commands:\n";
        foreach ($this->commands as $cmd) {
            echo "  " . $cmd->getName() . " - " . $cmd->getDescription() . "\n";
        }
    }
}
