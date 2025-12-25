<?php

namespace DFrame\Command;

use DFrame\Application\Command;

/**
 * Registers core commands to the CLI application.
 */
class Register
{
    public function core(Command $cli): void
    {
        $cli->register('help', Core::help())->info('Show help information');
        $cli->register('-h', Core::help())->infoAlias('help');
        $cli->register('version', Core::version())->info('Show application version');
        $cli->register('-v', Core::version())->infoAlias('version');
        $cli->register('server', Server::server());
        $cli->register('-s', Server::server());
        $cli->register('list', Core::list($cli->list()));
        $cli->register('vite', Vite::create());
        $cli->register('compile:ts', Compiler::compileTS());
    }
}
