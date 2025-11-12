<?php
namespace App\Console;

use DFrame\Command\Command;
use DFrame\Command\Core;

class VersionCommand extends Command {
    protected $name = 'version';
    protected $description = 'Show DFrame core version';

    public function execute(array $args = []) {
        echo "DFrame Framework Version: " . Core::getVersion() . "\n";
    }
}
