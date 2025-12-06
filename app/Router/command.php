<?php

// $cli được truyền từ dli

$cli->register('hello', [\App\Command\Hello::class, 'handle']);

$cli->register('sample', [\App\Command\Sample::class, 'handle']);
