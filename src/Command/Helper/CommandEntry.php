<?php

namespace DFrame\Command\Helper;

use DFrame\Application\Command;

class CommandEntry
{
    public Command $parent;
    public string $name;
    public mixed $handler;
    public ?string $info = null;
    public ?string $alias = null;
    public bool $hiddenOnPhar = false;

    public function __construct(Command $parent, string $name, mixed $handler, bool $hiddenOnPhar = false)
    {
        $this->parent = $parent;
        $this->name = $name;
        $this->handler = $handler;
        $this->hiddenOnPhar = $hiddenOnPhar;
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