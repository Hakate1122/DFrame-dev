<?php

namespace DLight\Command\Helper;

use DLight\Application\Command;

/**
 * **CommandEntry**
 * 
 * CommandEntry represents a single command registered in the Command system.
 * It holds the command's name, handler, description, and other metadata.
 * 
 * Example usage:
 *   $entry = new CommandEntry($commandSystem, 'hello', $handler);
 *   $entry->info('This command says hello');
 */
class CommandEntry
{
    public ?string $info = null;
    public ?string $alias = null;

    public function __construct(public Command $parent, public string $name, public mixed $handler, public bool $hiddenOnPhar = false)
    {
    }

    /**
     * Sets the info/description for the command.
     */
    public function info(string $text): self
    {
        $this->info = $text;
        return $this;
    }

    /**
     * Sets the info/description for the command by referencing another command's info.
     */
    public function infoAlias(string $otherCommand): self
    {
        if (isset($this->parent->commands[$otherCommand]) && $this->parent->commands[$otherCommand] instanceof CommandEntry) {
            $this->info = $this->parent->commands[$otherCommand]->info;
        }
        return $this;
    }

    public function getHandler(): mixed
    {
        return $this->handler;
    }
}