<?php

namespace DFrame\Command;

use DFrame\Application\Command;

class Register
{
    public function core(Command $cli): void
    {
        $cli->register('help', Core::help());
        $cli->register('-h', Core::help());
        $cli->register('version', Core::version());
        $cli->register('-v', Core::version());
        $cli->register('server', Core::server());
        $cli->register('-s', Core::server());
        $cli->register('list', Core::list($cli->list()));
    }
}
